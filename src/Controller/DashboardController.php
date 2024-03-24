<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Vendor;
use App\Enumerations\MessageIconEnumeration;
use App\Enumerations\VendorStatusEnumeration;
use App\Form\ChangepasswordType;
use App\Form\MessageType;
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
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $vendorAll = $entityManager->getRepository(Vendor::class)->count([]);
        $vendorApproved = $entityManager->getRepository(Vendor::class)->count(['status' => VendorStatusEnumeration::STATUS_APPROVED]);
        $data = [
            'controller_name' => 'DashboardController',
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ],
            'messages' => array_slice($this->getMessages(), 0, 5, true),
            'vendorCount' => $vendorAll,
            'vendorApproved' => $vendorApproved
        ];

        return $this->render('dashboard/index.html.twig', $data);
    }

    #[Route('/addmessage', name:'app_addmessage')]
    public function addmessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITSTAFF');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $details = $form->get('message')->getData();
            $subject = $form->get('subject')->getData();
            $type = $form->get('type')->getData();

            $message = new Message();

            $message
                ->setUser($user)
                ->setMessage($details)
                ->setSubject($subject)
                ->setType($type);

            $entityManager->persist($message);
            $entityManager->flush();
            return new RedirectResponse("/");
        }



        return $this->render('dashboard/message.html.twig', [
            'messageForm' => $form->createView(),
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/changepassword', name: 'app_changepassword')]
    public function changepassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $form = $this->createForm(ChangepasswordType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $pass = $form->get('newPassword')->getData();
            $repeat = $form->get('repeatPassword')->getData();

            if ($pass !== $repeat) {
                $this->addFlash('flash_error', 'Passwords did not match');
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
         * @var User $user
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

    protected function getMessages(): array
    {
        $messages = $this->doctrine->getRepository(Message::class)->findAll();
        foreach ($messages as &$m) {
            /**
             * @var Message $m
             */
            $m->loadIcon();
        }

        $files = glob(__DIR__ . "/../../changelog/*.json");
        $data = [];
        foreach ($files as $f) {
            $temp = json_decode(file_get_contents($f));
            $ftime = filemtime($f);
            if (is_array($temp)) {
                foreach ($temp as $t) {
                    $messages[] = $this->normalizeChangelog($t, $ftime);
                }
            } else {
                $messages[] = $this->normalizeChangelog($temp, $ftime);
            }
        }

        $pinned = [];
        $unpinned = [];
        /**
         * @var Message $message
         */
        foreach ($messages as $message) {
            if ($message->isPinned()) {
                $pinned[] = $message;
            } else {
                $unpinned[] = $message;
            }
        }

        usort($pinned, function($a, $b){
            /**
             * @var Message $a
             * @var Message $b
             */
            return $a->getCreatedOn()->getTimestamp() <= $b->getCreatedOn()->getTimestamp();
        });

        usort($unpinned, function($a, $b){
            /**
             * @var Message $a
             * @var Message $b
             */
            return $a->getCreatedOn()->getTimestamp() <= $b->getCreatedOn()->getTimestamp();
        });

        $messages = array_merge($pinned, $unpinned);


        return $messages;
    }

    protected function normalizeChangelog($data, $filetime)
    {
        $message = new Message();

        $createdon = !empty($data->createdon) ? strtotime($data->createdon) : strtotime($filetime);
        $type = !empty($data->type) ? $data->type : "unknown";
        $subject = !empty($data->subject) ? $data->subject : "";
        $details = !empty($data->message) ? $data->message : "";
        $pinned = !empty($data->pinned) ? $data->pinned : false;
        $message
            ->setCreatedon((new \DateTime())->setTimestamp($createdon))
            ->setType($type)
            ->setSubject($subject)
            ->setMessage($details)
            ->setPinned($pinned);
        ;


        return $message;

    }
}
