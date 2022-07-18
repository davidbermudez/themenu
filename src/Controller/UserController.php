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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
                $token['token'] = md5(date('Y-m-d HH:ii:ss'));
                $token['expirationMessageKey'] = 3600;
                $token['expirationMessageData'] = '';
                $user->setIsVerified(false);
                $user->setHash($token['token']);
                $userRepository->add($user, true);
                // Send Mail Confirmation
                
                $email = (new TemplatedEmail())
                    ->from(new Address('elarahal.1972@gmail.com', 'Soporte'))
                    ->to($user->getEmail())
                    ->subject('Account activation - your free account is ready')
                    ->htmlTemplate('user/activation.html.twig')
                    ->context([
                        'token' => $token,
                    ])
                ;

                $mailer->send($email);
                dump($mailer);
                $this->addFlash(
                    'success',
                    'send_email_verification'
                );
                return true;
                //$this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }        
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

    #[Route('/check-email', name: 'app_check_email', methods: ['POST'])]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    
    

    private function processSendingActivationEmail(
        string $hash,
        string $emailFormData, 
        MailerInterface $mailer, 
        TranslatorInterface $translator
        ): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $token['token'] = $hash;
            $token['expirationMessageKey'] = 3600;
            //$resetToken = $this->resetPasswordHelper->generateResetToken($user);

        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('elarahal.1972@gmail.com', 'Soporte'))
            ->to($user->getEmail())
            ->subject('Account activation - your free account is ready')
            ->htmlTemplate('user/activation.html.twig')
            ->context([
                'token' => $token,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($token);

        return $this->redirectToRoute('app_check_email');
    }
}
