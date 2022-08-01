<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserFormType;
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


use DateTime;


#[Route('/{_locale<%app.supported_locales%>}/cpanel')]
class ControlPanelController extends AbstractController
{

    private $entityManager;
    private $locale;

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

            // business
            $business = new Business();
            $business = $businessRepository->findOneBy([
                'user' => $user
            ]);
            if(is_null($business)){
                return $this->redirectToRoute('app_add_busines', ['hash' => $hash]);
            }            
            // Menu
            $menu = new Menu();
            $menu = $menuRepository->findBy([
                'business' => $business
            ]);            
            // Si no hay ningún menú, crearlo
            if(count($menu) == 0){
                $qr = hash("crc32", $user->getEmail(), false);                
                $menu = new Menu();
                $menu->SetLang01(true);
                $PREMIUM = false;
                if ($this->isGranted('ROLE_PREMIUN')){
                    $PREMIUM = true;
                }
                $menu->Setlang02($PREMIUM);
                $menu->Setlang03($PREMIUM);
                $menu->SetBusiness($business);
                $menu->SetQrCode($qr);
                $menu->SetCaption('UNNAMED');
                $menuRepository->add($menu, true);
                $menu = $menuRepository->findBy([
                    'business' => $business
                ]);
            }

            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => true,                
                ],
            ];
            dump($breadcrumb);
            return $this->render('control_panel/index.html.twig', [
                'user' => $user,
                'business' => $business,
                'menues' => $menu,                
                'breadcrumb' => $breadcrumb,
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
            $form = $this->createForm(BusinessFormType::class, $business);
            //dump($form);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // verificar datos y crear un nuevo Business
                $datenow = new Datetime(date('Y-m-d H:i:s'));
                $business->setUser($user);
                $business->setDateCreated($datenow);
                $businessRepository->add($business, true);
                $this->addFlash(
                    'success',
                    $translator->trans('Flash.Business.Create'),
                );
                //redirect
                return $this->redirectToRoute('app_panel', ['hash' => $user->getHash()]);
            } elseif ($form->isSubmitted() && !$form->isValid()) {
                $this->addFlash(
                    'danger',
                    'Los datos introducidos no son válidos'
                );
                
            }

            $locale = $request->getLocale();
            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.NewBusiness'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/businnes.html.twig', [
                'user' => $user,                
                'formBusiness' => $form->createView(),
                'breadcrumb' => $breadcrumb,
                'button' => $translator->trans('CPanel.Button.CreateBusiness'),
            ]);
        }
    }


    #[Route('/{hash}/edit_user', name: 'app_edit_user', methods: ['GET', 'POST'])]
    public function editUser(
        Request $request,
        User $user,
        $hash,
        UserRepository $businessRepository,        
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
            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {                                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                $this->addFlash(
                    'success',
                    $translator->trans('Flash.User.Update'),
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
            $locale = $request->getLocale();
            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditBusiness'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/edit_user.html.twig', [
                'user' => $user,                
                'formUser' => $form->createView(),
                'breadcrumb' => $breadcrumb,
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
            $form = $this->createForm(BusinessFormType::class, $business);
            //dump($form);
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
            $locale = $request->getLocale();
            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditBusiness'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/edit_businnes.html.twig', [
                'user' => $user,                
                'formBusiness' => $form->createView(),
                'breadcrumb' => $breadcrumb,
                'button' => $translator->trans('Cpanel.Business.ButtonEdit'),
            ]);
        }
    }


    #[Route('/{hash}/edit_menu/{menu_id}/{slug}', name: 'app_edit_menu', methods: ['GET', 'POST'])]
    public function editMenu(
        Request $request,
        User $user,
        $hash,        
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        TranslatorInterface $translator,
        $menu_id = null,
        $slug = ''): Response
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
                'id' => $menu_id,
            ]);
            // si todavía no hay un menú, crearlo
            if(is_null($menu)){
                return $this->redirectToRoute('app_login');                
            }
/*
            $qr = hash("crc32", $user->getEmail(), false);                
            $menu = new Menu();
            $menu->SetLangEs(true);
            $PREMIUM = false;
            if ($user->isGranted('ROLE_PREMIUN')){
                $PREMIUM = true;
            }
            $menu->Setlang02($PREMIUM);
            $menu->Setlang03($PREMIUM);
            $menu->SetBusiness($business);
            $menu->SetQrCode($qr);
            $menu->SetCaption('UNNAMED');

            $menuRepository->add($menu, true);
*/
            $category = new Category();
            $category = $categoryRepository->findBy([
                'menu' => $menu,
            ],
                array('order_by' => 'ASC')
            );            
            dump($category);
            $dishes = new Dishes();
            $dishes = $dishesRepository->findAll([
                //'category' => 
            ]);
            
            $locale = $request->getLocale();
            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/edit_menu.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'slug' => $slug,
                'categories' => $category,
                'dishes' => $dishes,
                'menu_id' => $menu_id,
                'breadcrumb' => $breadcrumb,
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
                $res = $categoryRepository->customAdd($category, true);

                if($res){
                    $this->addFlash(
                        'success',
                        $translator->trans('Flash.Category.Create'),
                    );
                } else {
                    $this->addFlash(
                        'danger',
                        $translator->trans('Flash.Error.Duplicate'),
                    );
                }                
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'slug' => $category->getSlug(), 'menu_id' => $menu->getId()]);
            }

            // breadcrumb
            $locale = $request->getLocale();
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.AddSection'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/category.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formCategory' => $form->createView(),
                'breadcrumb' => $breadcrumb,
                'button' => $translator->trans('Cpanel.Business.ButtonAdd')
            ]);
        }
    }


    #[Route('/{hash}/edit_category/{category}', name: 'app_edit_category', methods: ['GET', 'POST'])]
    public function editCategory(
        Request $request,
        User $user,
        $hash,
        $category,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
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
            $categories = new Category();            
            $categories = $categoryRepository->findOneBy([
                'menu' => $menu,
                'id' => $category
            ]);
            if(is_null($categories)){
                return $this->redirectToRoute('app_login');
            }            
            $form = $this->createForm(CategoryFormType::class, $categories);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $categoryRepository->add($categories, true);

                $this->addFlash(
                    'success',
                    $translator->trans('Flash.Category.Create'),
                );                
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'slug' => $categories->getSlug(), 'menu_id' => $menu->getId()]);
            }

            // breadcrumb
            $locale = $request->getLocale();
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.AddSection'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/category.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formCategory' => $form->createView(),
                'breadcrumb' => $breadcrumb,
                'button' => $translator->trans('button.caption.update')
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
                //'id' => $request->get('category'),
            ]);
            $dishe = new Dishes();
            $dishe->setCategory($categoryRepository->findOneBy(['id' => $request->get('category')]));
            $variations = new Variation();
            $variations = $variationRepository->findBy([
                'dishe' => $dishe,
            ]);
            $form = $this->createForm(DisheFormType::class, $dishe);
            $form->handleRequest($request);
            // ordenation dishe
            $disheorder = $dishesRepository->findBy(
                ['category' => $categoryRepository->findOneBy(['id' => $request->get('category')])],
                ['order_by' => 'DESC']
            );
            $nextOrder = 1;
            if(count($disheorder) > 0){
                $nextOrder = $disheorder[0]->getOrderBy() + 1;
            };
            if ($form->isSubmitted() && $form->isValid()) {
                $categoryForm = $form->get('category')->getData()->getCaption();
                $dishe->setOrderBy($nextOrder);
                $dishesRepository->add($dishe, true);
                $this->addFlash(
                    'success',
                    'Creado un nuevo plato'
                );
                //return true;
                return $this->redirectToRoute('app_add_variation', ['hash' => $user->getHash(), 'dishe' => $dishe->getId()]);
            }

            // breadcrumb
            $locale = $request->getLocale();
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.AddDishe'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/dishe.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'formDishe' => $form->createView(),
                'button' => $translator->trans('button.caption.add'),
                'slug' => $form->get('category')->getData()->getSlug(),
                'breadcrumb' => $breadcrumb,
                'variations' => $variations,
            ]);
        }
    }    

    
    #[Route('/{hash}/edit_dishe/{dishe}', name: 'app_edit_dishes', methods: ['GET', 'POST'])]
    public function editDishe(
        Request $request,
        User $user,
        $hash,
        $dishe,
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
            // $dishe = $request->get('dishe');
            $dishes = new Dishes();
            $dishes = $dishesRepository->findOneBy([
                'id' => $dishe,
            ]);
            $variations = new Variation();
            $variations = $variationRepository->findBy([
                'dishe' => $dishes,
            ]);
            $form = $this->createForm(DisheFormType::class, $dishes);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $categoryForm = $form->get('category')->getData()->getSlug();
                $dishesRepository->add($dishes, true);
                $this->addFlash(
                    'success',
                    $translator->trans('CPanel.Dishe.Update'),
                );
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'slug' => $categoryForm, 'menu_id' => $menu->getId()]);
            }
            $locale = $request->getLocale();
            // breadcrumb
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditDishe'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/dishe.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'dishe' => $dishe,
                'formDishe' => $form->createView(),
                'button' => $translator->trans('button.caption.update'),
                'slug' => $form->get('category')->getData()->getSlug(),
                'breadcrumb' => $breadcrumb,
                'variations' => $variations,
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
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'menu_id' => $menu->getId()]);
            }

            // breadcrumb
            $locale = $request->getLocale();
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditDishe'),
                    'active' => false,
                    'href' => 'app_edit_dishes',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                        'dishe' => $dishes->getId(),
                    ]
                ],
                '3' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.AddVariation'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/variation.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'button' => $translator->trans('button.caption.add'),
                'formVariation' => $formVariation->createView(),
                'dishe' => $dishes,
                'breadcrumb' => $breadcrumb,
            ]);
        }
    }


    #[Route('/{hash}/edit_variation/{dishe}', name: 'app_edit_variation', methods: ['GET', 'POST'])]
    public function editVariation(
        Request $request,
        User $user,
        $hash,
        $dishe,
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
            //$dishe = $request->get('dishe');
            $dishes = new Dishes();
            $dishes = $dishesRepository->findOneBy([
                'id' => $dishe,
            ]);            
            $variation = $request->get('variation');
            $variations = new Variation();
            $variations = $variationRepository->findOneBy([
                'id' => $variation,
            ]);
            $formVariation = $this->createForm(VariationFormType::class, $variations);
            $formVariation->handleRequest($request);            
            if ($formVariation->isSubmitted() && $formVariation->isValid()) {
                $variationRepository->add($variations, true);
                $this->addFlash(
                    'success',
                    $translator->trans('CPanel.Variation.Update'),
                );                
                return $this->redirectToRoute('app_edit_menu', ['hash' => $user->getHash(), 'menu_id' => $menu->getId()]);
            }

            // breadcrumb
            $locale = $request->getLocale();
            $breadcrumb = [
                '0' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.Index'),
                    'active' => false,
                    'href' => 'app_panel',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                    ]
                ],
                '1' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditMenu'),
                    'active' => false,
                    'href' => 'app_edit_menu',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                    ]
                ],
                '2' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.EditDishe'),
                    'active' => false,
                    'href' => 'app_edit_dishes',
                    'parameters' => [
                        '_locale' => $locale,
                        'hash' => $user->getHash(),
                        'menu_id' => $menu->getId(),
                        'dishe' => $dishe,
                    ]
                ],
                '3' => [
                    'title' => $translator->trans('CPanel.Breadcrumb.AddVariation'),
                    'active' => true,
                ]
            ];
            return $this->render('control_panel/variation.html.twig', [
                'user' => $user,
                'menu' => $menu,
                'button' => $translator->trans('button.caption.add'),
                'formVariation' => $formVariation->createView(),
                'dishe' => $dishes,
                'breadcrumb' => $breadcrumb,
            ]);
        }
    }


    private function permission(
        User $user,
        $hash,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository = null,
        DishesRepository $dishesRepository = null,
        VariationRepository $variationRepository = null,
        $business = null,
        $menu = null,
        $category = null,
        $dishe = null,
        $variation = null,
    ): string
    {

        // verify user
        $user = $this->getUser();
        if ($user == null || $hash != $user->getHash()){
            return 'app_login';
        } elseif($user->isVerified()==false) {
            return 'app_not_verify';
        } else {

            $userBusiness = new Business();
            $userBusiness = $businessRepository->findOneBy([
                'user' => $user,
            ]);
            if(is_null($userBusiness)){
                return 'app_not_business';
            }
            
            $userMenu = new Menu();
            $userMenu = $menuRepository->findOneBy([
                'business' => $userBusiness,
            ]);
            if(is_null($userMenu) && $userMenu->getId() != $menu){
                return 'app_not_menu';
            }
            return 'hola';
            $userCategory = new Category();
            $userCategory = $categoryRepository->findAll([
                'menu' => $userMenu,
            ]);
            if(is_null($userMenu)){
                return 'app_not_menu';
            }
            
            $userDishe = new Category();
            $userCategory = $categoryRepository->findOneBy([
                'menu' => $userMenu,
            ]);
            if(is_null($userMenu)){
                return 'app_not_menu';
            }

            if(is_null($dishe)){       
                $dishes = $dishesRepository->findOneBy([
                    'id' => $dishe,
                ]);
            }
            if(is_null($variation)){   
                $variations = $variationRepository->findOneBy([
                    'id' => $variation,
                ]);
            }



        }

    }
}
