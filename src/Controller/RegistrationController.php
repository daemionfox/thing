<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DeleteStaffFormType;
use App\Form\EditStaffFormType;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use App\Traits\RandomStringTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    use RandomStringTrait;

    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITSTAFF');

        /**
         * @var $user User
         */

        $user = $this->getUser();
        $newuser = new User();
        $form = $this->createForm(RegistrationFormType::class, $newuser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $password = $this->generateRandomString();

            $newuser->setPassword(
                $userPasswordHasher->hashPassword(
                    $newuser,
                    $password
                )
            );

            $entityManager->persist($newuser);
            $entityManager->flush();

            $data = [
                'url' => $_SERVER['SERVER_NAME'],
                'username' => $newuser->getEmail(),
                'password' => $password
            ];

            $html = $this->render('registration/confirmation_email.html.twig', $data);

            $from = $this->getParameter('smtpfrom');
            $email = (new Email())
                ->from($from)
                ->to($newuser->getEmail())
                ->subject('Please Confirm your Email')
                ->html($html->getContent());
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $te) {
                $foo = $te->getMessage();
            }

            // do anything else you need here, like send an email


            return new RedirectResponse("stafflist");
        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'user' => [
                $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }


    #[Route('/editstaff', name: 'app_editstaff')]
    public function editstaff(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITSTAFF');

        /**
         * @var $user User
         */

        $user = $this->getUser();
        $editname = $request->get('user');
        if (empty($editname)) {
            return new RedirectResponse("stafflist");
        }
        $newuser = $doctrine->getRepository(User::class)->findOneBy(['name' => $editname]);
        $form = $this->createForm(EditStaffFormType::class, $newuser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password

            $entityManager->persist($newuser);
            $entityManager->flush();

            return new RedirectResponse("stafflist");
        }

        $myForm = $form->createView();

        return $this->render('registration/editstaff.html.twig', [
            'editstaffForm' => $form->createView(),
            'user' => [
                $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }






    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('dashboard');
    }

    #[Route('/deletestaff', name: "app_deletestaff")]
    public function remove(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITSTAFF');

        /**
         * @var $user User
         */
        $user = $this->getUser();

        $delname = $request->query->get('user');

        if (empty($delname)) {
            throw new \Exception("Cannot delete user without name");
        }
        $deluser = $doctrine->getRepository(User::class)->findOneBy(['name' => $delname]);
        $form = $this->createForm(DeleteStaffFormType::class, $deluser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($deluser);
            $entityManager->flush();
            return $this->redirectToRoute('app_stafflist');
        }
        return $this->render('registration/deletestaff.html.twig', [
            'deletestaffForm' => $form->createView(),
            'user' => [
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ],
            'delname' => $delname
        ]);
    }
}
