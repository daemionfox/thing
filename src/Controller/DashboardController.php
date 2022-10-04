<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangepasswordType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Entity\NetworkResponseEntity;
use HeadlessChromium\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var $user User
         */
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    #[Route('/changepassword', name: 'app_changepassword')]
    public function changepassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var $user User
         */
        $user = $this->getUser();

        $form = $this->createForm(ChangepasswordType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $pass = $form->get('newPassword')->getData();
            $repeat = $form->get('repeatPassword')->getData();

            if ($pass !== $repeat) {
                $this->addFlash('changepassword_error', 'Passwords did not match');
                return $this->render('registration/changepassword.html.twig', [
                    'changepasswordForm' => $form->createView(),
                    'user' => [
                        'name' => $user->getName(),
                        'roles' => $user->getRoles(),
                    ]
                ]);
            }


            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $pass
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            return new RedirectResponse("/");
        }

            // do anything else you need here, like send an email


        return $this->render('registration/changepassword.html.twig', [
            'changepasswordForm' => $form->createView(),
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/stafflist', name: 'app_stafflist')]
    public function stafflist(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var $user User
         */
        $user = $this->getUser();
        return $this->render('dashboard/staff.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'users' => $this->getAllUsers()
        ]);
    }

    protected function getAllUsers(): array
    {
        $users = $this->doctrine->getRepository(User::class)->findAll();
        return $users;
    }
}
