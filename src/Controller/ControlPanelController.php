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
use App\Form\BusinessFormType;

use DateTime;


#[Route('/{_locale<%app.supported_locales%>}/cpanel')]
class ControlPanelController extends AbstractController
{
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
}
