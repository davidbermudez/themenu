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

class PresentationController extends AbstractController
{

    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;        
    }


    #[Route('/noencontrado', name: 'app_no_encontrado')]
    public function notfound(
        TranslatorInterface $translator,
        ): Response
    {

        return $this->render('presentation/not_found.html.twig', [
            'hola' => 'hola',
        ]);
    }


    #[Route('/{qr_code}', name: 'app_presentation')]
    public function presentation(
        $qr_code,
        BusinessRepository $businessRepository,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        DishesRepository $dishesRepository,
        TranslatorInterface $translator,
        ): Response
    {
        // Buscamos el qr_code
        $menu = new Menu();
        $menu = $menuRepository->findOneBy([
            'qr_code' => $qr_code,
        ]);
        if(is_null($menu)){
            return $this->redirectToRoute('app_no_encontrado');
        }
        return $this->render('presentation/index.html.twig', [
            'menu' => $menu,
        ]);
    }


    

}
