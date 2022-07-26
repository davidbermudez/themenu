<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Business;
use App\Repository\BusinessRepository;
use App\Entity\Dishes;
use App\Repository\DishesRepository;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Entity\Variation;
use App\Repository\VariationRepository;
use App\Form\BusinessFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategoryFormType;
use App\Form\DisheFormType;
use App\Form\VariationFormType;
use Symfony\Contracts\Translation\TranslatorInterface;

use DateTime;


#[Route('/{_locale<%app.supported_locales%>}/cpanel')]
class ControlPanelController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/{hash}', name: 'app_panel', methods: ['GET'])]
    public function cpanel(
        User $user, 
        $hash, 
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        DishesRepository $dishesRepository): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            // business
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user
            ]);
            // Menu
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business
            ]);

            // Dishes
            $dishes = new Dishes();
            $dishes = $dishesRepository->findAll([
                'menu' => $menu
            ]);

            return $this->render('control_panel/index.html.twig', [
                'user' => $user,
                'business' => $business,
                'menu' => $menu,
                'dishes' => $dishes,
            ]);
        }
    }

    #[Route('/{hash}/new_business', name: 'app_add_busines', methods: ['GET', 'POST'])]
    public function addBusiness(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        DishesRepository $dishesRepository): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $form = $this->createForm(BusinessFormType::class, $business);
            dump($form);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // verificar datos y crear un nuevo Business
                $datenow = new Datetime(date('Y-m-d H:i:s'));
                $business->setUser($user);
                $business->setDateCreated($datenow);
                $businessRepository->add($business, true);
                $this->addFlash(
                    'success',
                    'Se ha creado un nuevo Business'
                );
                //redirect
                return $this->redirectToRoute('app_panel', ['hash' => $user->getHash()]);
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
                
            }
            return $this->render('control_panel/new_businnes.html.twig', [
                'user' => $user,                
                'formBusiness' => $form->createView(),
            ]);
        }
    }


    #[Route('/{hash}/edit_business', name: 'app_edit_business', methods: ['GET', 'POST'])]
    public function editBusiness(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        DishesRepository $dishesRepository): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $form = $this->createForm(BusinessFormType::class, $business);
            dump($form);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // verificar datos y crear un nuevo Business
                $datenow = new Datetime(date('Y-m-d H:i:s'));
                $business->setUser($user);
                $business->setDateModify($datenow);
                // redes
                $urlTw = $form->get('twitter_profile')->getData();                
                if($urlTw!='')
                {
                    if(substr($urlTw, -1) == "/"){
                        $business->setTwitterProfile(substr($urlTw, 0, strlen($urlTw) - 1));
                    }
                }
                $this->entityManager->persist($business);
                $this->entityManager->flush();
                
                $this->addFlash(
                    'success',
                    'Actualizados los datos del Establecimiento'
                );
                //redirect
                //return true;
                return $this->redirectToRoute('app_panel', ['hash' => $user->getHash()]);
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
                
            }
            return $this->render('control_panel/edit_businnes.html.twig', [
                'user' => $user,                
                'formBusiness' => $form->createView(),
            ]);
        }
    }


    #[Route('/{hash}/edit_menu/{caption}', name: 'app_edit_menu', methods: ['GET', 'POST'])]
    public function editMenu(
        Request $request,
        User $user,
        $hash,        
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        $caption = ''): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
                        
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business,
            ]);
            // si todavía no hay un menú, crearlo
            if(is_null($menu)){
                $qr = hash("crc32", $user->getEmail(), false);                
                $menu = new Menu();
                $menu->SetLangEs(true);
                $menu->SetLangEn(false);
                $menu->SetLangCa(false);
                $menu->SetBusiness($business);
                $menu->SetQrCode($qr);

                $menuRepository->add($menu, true);
            }
            $category = new Category();
            $category = $categoryRepository->findAll([
                'menu' => $menu,
            ]);
            
            $dishes = new Dishes();
            $dishes = $dishesRepository->findAll([
                //'category' => 
            ]);

            return $this->render('control_panel/edit_menu.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'caption' => $caption,
                'categories' => $category,
                'dishes' => $dishes,
            ]);
        }
    }


    #[Route('/{hash}/new_category', name: 'app_new_category', methods: ['GET', 'POST'])]
    public function addCategory(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        ): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business,
            ]);
            //dump($menu);
            $category = new Category();
            // ver si existen otras para extraer el order_by
            $category = $categoryRepository->findBy(
                ['menu' => $menu],
                ['order_by' => 'DESC']
            );
            $nextOrder = 1;
            if(count($category) > 0){
                $nextOrder = $category[0]->getOrderBy() + 1;
            };
            $category = new Category();
            $form = $this->createForm(CategoryFormType::class, $category);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $category->setMenu($menu);
                $category->setOrderBy($nextOrder);
                $categoryRepository->add($category, true);
                $this->addFlash(
                    'success',
                    'Creada una nueva Sección de Menú'
                );
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'caption' => $category->getCaptionEs()]);
            }
            return $this->render('control_panel/new_category.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formCategory' => $form->createView(),
            ]);
        }
    }

    #[Route('/{hash}/new_dishe', name: 'app_new_dishe', methods: ['GET', 'POST'])]
    public function addDishe(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        TranslatorInterface $translator
        ): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business,
            ]);
            $category = new Category();
            $category = $categoryRepository->findAll([
                'menu' => $menu,
            ]);
            $dishe = new Dishes();
            $dishe->setCategory($categoryRepository->findOneBy(['id' => $request->get('category')]));            
            $form = $this->createForm(DisheFormType::class, $dishe);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {            
                $categoryForm = $form->get('category')->getData()->getCaptionEs();
                $dishesRepository->add($dishe, true);
                $this->addFlash(
                    'success',
                    'Creado un nuevo plato'
                );
                //return true;
                return $this->redirectToRoute('app_add_variation', ['hash' => $user->getHash(), 'dishe' => $dishe->getId()]);
            }
            return $this->render('control_panel/dishe.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formDishe' => $form->createView(),                
                'button' => $translator->trans('button.caption.add'),
                'caption' => $form->get('category')->getData()->getCaptionEs(),
                //'formVariation' => $formVariation->createView(),
            ]);
        }
    }    

    
    #[Route('/{hash}/edit_dishe', name: 'app_edit_dishes', methods: ['GET', 'POST'])]
    public function editDishe(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        VariationRepository $variationRepository,
        TranslatorInterface $translator
        ): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business,
            ]);
            $category = new Category();
            $category = $categoryRepository->findAll([
                'menu' => $menu,
            ]);
            // verificamos que $_GET['dishe'], se corresponda a un plato del menú del usuario
            $dishe = $request->get('dishe');            
            $dishes = new Dishes();
            $dishes = $dishesRepository->findOneBy([
                'id' => $dishe,
            ]);
            $variation = new Variation();
            $formVariation = $this->createForm(VariationFormType::class, $variation);
            $formVariation->handleRequest($request);
            $form = $this->createForm(DisheFormType::class, $dishes);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $categoryForm = $form->get('category')->getData()->getCaptionEs();                
                $dishesRepository->add($dishes, true);
                $this->addFlash(
                    'success',
                    'Actualizado plato'
                );
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'caption' => $categoryForm]);
            }
            return $this->render('control_panel/dishe.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formDishe' => $form->createView(),
                'button' => $translator->trans('button.caption.update'),
                'caption' => $form->get('category')->getData()->getCaptionEs(),
                'formVariation' => $formVariation->createView(),
            ]);
        }
    }

    
    #[Route('/{hash}/add_variation/{dishe}', name: 'app_add_variation', methods: ['GET', 'POST'])]
    public function addVariation(
        Request $request,
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        VariationRepository $variationRepository,
        TranslatorInterface $translator
        ): Response
    {
        // verify user
        $user = $this->getUser();
        if ($user == null || $hash!=$user->getHash()){
            return $this->redirectToRoute('app_login');
        } elseif($user->isVerified()==false) {
            return $this->redirectToRoute('app_not_verify');
        } else {
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'business' => $business,
            ]);
            $category = new Category();
            $category = $categoryRepository->findAll([
                'menu' => $menu,
            ]);
            $dishe = $request->get('dishe');            
            $dishes = new Dishes();
            $dishes = $dishesRepository->findOneBy([
                'id' => $dishe,
            ]);            
            $variation = new Variation();
            $formVariation = $this->createForm(VariationFormType::class, $variation);
            $formVariation->handleRequest($request);            
            if ($formVariation->isSubmitted() && $formVariation->isValid()) {
                $variation->setDishe($dishes);
                dump($variation);
                $variationRepository->add($variation, true);
                $this->addFlash(
                    'success',
                    'Actualizado plato'
                );
                //return true;
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash()]);
            }
            return $this->render('control_panel/variation.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'button' => $translator->trans('button.caption.add'),
                'formVariation' => $formVariation->createView(),
                'dishe' => $dishes,
            ]);
        }
    }
}
