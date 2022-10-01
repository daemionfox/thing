<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Entity\NetworkResponseEntity;
use HeadlessChromium\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function changepassword(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var $user User
         */
        $user = $this->getUser();




        return $this->render('dashboard/changepassword.html.twig', [
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
