<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//Translator
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatableMessage;

class IndexController extends AbstractController
{

    #[Route('/')]
    public function indexNoLocale(){
        return $this->redirectToRoute('app_index', ['_locale' => 'es']);
    }

    #[Route('/user')]
    public function indexUserNoLocale(){
        return $this->redirectToRoute('app_user_index', ['_locale' => 'es']);
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'app_index')]
    public function index(): Response
    {
        
        return $this->render('index/index.html.twig', [
            
        ]);
    }
}
