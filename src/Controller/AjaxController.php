<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Menu;
use App\Entity\Category;
use App\Repository\MenuRepository;
use App\Repository\CategoryRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/ajax')]
class AjaxController extends AbstractController
{
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;        
    }

    #[Route('/updateCaptionMenu', name: 'ajax_updateCaptionMenu',  methods: ['POST'])]
    public function updateCaptionMenu(
        Request $request,
        MenuRepository $menuRepository,
        TranslatorInterface $translator
        ): Response
    {
        $request = Request::createFromGlobals();
        $user = $this->getUser();
        $token = $request->request->get('token');
        $error = false;
        if($this->isCsrfTokenValid($user->getId(), $token)){
            
            $texto = $request->request->get('texto');
            $menuid = $request->request->get('menu');
            
            $locale = $request->request->get('_locale');
            $request->setLocale($locale);            
            
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'id' => $menuid,
            ]);

            if(is_null($menu)) {
                $msg = $translator->trans('Ajax.Error.NotFound', [], 'messages', $locale);
                $error = true;
            } 
            
            if($error==false){
                if($menu->getBusiness()->getUser()!=$user){
                    $msg = $translator->trans('Ajax.Hack', [], 'messages', $locale);
                    $error = true;
                }
            }
            if($error==false){
                $menu->setCaption($texto);
                $this->em->persist($menu);
                $this->em->flush();
                $msg = $translator->trans('Ajax.Update.Text', [], 'messages', $locale);
            }
            $response = new JsonResponse([
                'result' => true,
                'mensaje' => $msg,
            ]);
            return $response;
        } else {
            return new JsonResponse([
                'result' => false,
                'mensaje' => $translator->trans('Ajax.Error.CsrToken', [], 'messages', $locale)
            ]);
        }
    }


    
    #[Route('/orderSections', name: 'ajax_orderSections',  methods: ['POST'])]
    public function orderSections(
        Request $request,
        MenuRepository $menuRepository,
        CategoryRepository $categoryRepository,
        TranslatorInterface $translator
        ): Response
    {
        $request = Request::createFromGlobals();
        $user = $this->getUser();
        $token = $request->request->get('token');
        $error = false;
        if($this->isCsrfTokenValid($user->getId(), $token)){            
            dump($request);
            $sections = [];
            $sections = $request->request->all('sections');
            
            $menuid = $request->request->get('menu');

            $locale = $request->request->get('_locale');
            $request->setLocale($locale);            
            
            $menu = new Menu();
            $menu = $menuRepository->findOneBy([
                'id' => $menuid,
            ]);

            if(is_null($menu)) {
                $msg = $translator->trans('Ajax.Error.NotFound', [], 'messages', $locale);
                $error = true;
            } 
                        
            if($error==false){                
                if($menu->getBusiness()->getUser()!=$user){
                    $msg = $translator->trans('Ajax.Hack', [], 'messages', $locale);
                    $error = true;
                }
            }
            if($error==false){                
                $categories = new Category();
                $categories = $categoryRepository->findBy([
                    'menu' => $menu,                    
                ]);                
                foreach($categories as $category){
                    foreach($sections as $key => $section){
                        if($section == $category->getCaptionEs()){
                            $category->setOrderBy($key + 1);
                            $this->em->persist($category);
                            $this->em->flush();
                        }
                    }                    
                }
                $msg = true;
            }
            $response = new JsonResponse([
                'result' => true,
                'mensaje' => $msg,
            ]);
            return $response;
        } else {
            return new JsonResponse([
                'result' => false,
                'mensaje' => $translator->trans('Ajax.Error.CsrToken', [], 'messages', $locale)
            ]);
        }
    }    
}