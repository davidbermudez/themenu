<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/{_locale<%app.supported_locales%>}/user')]
class UserController extends AbstractController
{    

    public function __construct(EntityManagerInterface $entityManager)
    {    
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        UserRepository $userRepository,
        MailerInterface $mailer,        
        TranslatorInterface $translator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);        

        if ($form->isSubmitted() && $form->isValid()) {
            // verify checkbox
            if ($request->request->get('conditions')!="on"){
                $this->addFlash(
                    'danger',
                    'not_accept_conditions'
                );
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
            // Verificar que email no existe
            $replay = new User();
            dump($form);
            $replay = $userRepository->findOneBy([
                'email' => $form['email']->getData(),
            ]);
            if($replay){
                $this->addFlash(
                    'danger',
                    'user_duplicated'
                );
            } else {
                $token['token'] = md5(date('Y-m-d H:i:s'));
                $token['expirationMessageKey'] = 3600;
                $token['expirationMessageData'] = '';
                $user->setIsVerified(false);
                $user->setHash($token['token']);
                $userRepository->add($user, true);
                // Send Mail Confirmation
                $texto = "<h1>Hola</h1>\n<p>Ha recibido este mensaje porque V. o alguien ha solicitado restaurar su contraseña de usuario en Compartecoche.</p>\n<p>Para resetear su contreseña, haga click en el siguiente enlace</p>\n<a href=\"{{ url('app_activate_account', {token: token.token}) }}\">{{ url('app_activate_account', {token: token.token}) }}</a>\n<p>Este enlace caduca en {{ token.expirationMessageKey }}.</p>\n<p>Saludos</p>";

                $email = (new Email())
                    ->from(new Address('elarahal.1972@gmail.com', 'Soporte'))
                    ->to($user->getEmail())
                    ->subject('Account activation - your free account is ready')
                    ->text('HTML Format')
                    ->html($texto)                    
                ;

                $mailer->send($email);
                //dump($mailer);
                $this->addFlash(
                    'success',
                    'send_email_verification'
                );
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }        
        //dlfkj84nfd7lñkR*df
        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/activate', name: 'app_activate_account', methods: ['GET'])]
    public function activate(User $user): Response
    {
        return new Response("hola");
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->edit($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

}
