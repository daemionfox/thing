<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VoteEvent;
use App\Entity\VoteItem;
use App\Enumerations\ActionEnumeration;
use App\Exceptions\BadFormDataException;
use App\Exceptions\VoteException;
use App\Form\CreateVoteType;
use App\Form\VoteVendorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
    #[Route('/vote/create', 'app_createvoteevent')]
    public function createVote(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_CREATEVOTE');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $voteEvent = new VoteEvent();
        $form = $this->createForm(CreateVoteType::class, $voteEvent);
        $form->handleRequest($request);

        try {

            if ($form->isSubmitted() && $form->isValid()) {
                $voteEvent->setCreatedBy($user);
                /** @noinspection SqlNoDataSourceInspection */
                /** @noinspection SqlResolve */
                $checkVotes = $entityManager
                    ->getConnection()
                    ->prepare("SELECT * from vote_event ve where ve.ends_on > :start AND ve.vote_complete = 'false'")
                    ->executeQuery([':start' => $voteEvent->getStartsOn()->format("Y-m-d H:i:s")])
                    ->fetchAllAssociative();

                if (!empty($checkVotes)) {
                    $this->addFlash("flash_error", "This voting event starts before other events have ended.  Please select a start date after the last vote ends.");
                    throw new BadFormDataException("Bad form data");
                }

                $entityManager->persist($voteEvent);

                new Action($user, ActionEnumeration::ACTION_VOTE, "New Vote Event has been created.", $entityManager);
                $entityManager->flush();
                return new RedirectResponse("/vote/list");
            }
        } catch (BadFormDataException ) {} //

        return $this->render("vote/createvote.html.twig", [
            'createvoteForm' => $form->createView(),
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);




    }

    #[Route('/vote', 'app_staffvote')]
    public function vote(EntityManagerInterface $entityManager, Request $request, Session $session)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $vendorID = $request->query->get('vendor');
        try {
            /**
             * @var VoteEvent $voteEvent
             */
            $voteEvent = $this->getRunningVoteEvent($entityManager);
        } catch (VoteException) {
            $session->getFlashBag()->add("flash_error", "Vote Machine is not running");
            return new RedirectResponse("/");
        }

        $items = $this->getVoteItems($entityManager, $user, $voteEvent);


        if (empty($vendorID)) {

            return $this->render("vote/startvote.html.twig", [
                'user' => [
                    'name' => $user->getName(),
                    'roles' => $user->getRoles()
                ],
                'items' => $items,
                'event' => $voteEvent
            ]);
        }



        $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);
        $voteItem = $entityManager->getRepository(VoteItem::class)->findOneBy([
            'User' => $user,
            'Vendor' => $vendor,
            'VoteEvent' => $voteEvent
        ]);

        if (empty($voteItem)) {
            $voteItem = new VoteItem();
            $voteItem->setVoteEvent($voteEvent)->setVendor($vendor)->setUser($user);
        }
        $voteItem->setIsSkip(false)->setMaxVotes($voteEvent->getMaxVendorVotes());
        $form = $this->createForm(VoteVendorType::class, $voteItem);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($voteItem);
                $entityManager->flush();
                $nextVend = $this->getPrevNextVendor($entityManager, $vendor, 1);
                return new RedirectResponse("/vote?vendor={$nextVend}");
            }
        } catch (BadFormDataException) {}

        $itemVotes = 0;
        /**
         * @var VoteItem $item
         */
        foreach ($items as $item) {
            $v = $item->getVotes();
            $v = !empty($v) ? (int)$v : 0;
            $itemVotes += $v;
        }

        $remainingVotes = (int)$voteEvent->getStaffVotes() - $itemVotes;
        return $this->render("vote/vote.html.twig", [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'event' => $voteEvent,
            'items' => $items,
            'voteItem' => $voteItem,
            'remainingVotes' => $remainingVotes,
            'voteForm' => $form->createView(),
            'prevID' =>  $this->getPrevNextVendor($entityManager, $vendor, -1),
            'nextID' => $this->getPrevNextVendor($entityManager, $vendor, 1)
        ]);

    }

    #[Route('/vote/list', 'app_listvoteevents')]
    public function listVoteEvents(EntityManagerInterface $entityManager) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $events = $entityManager->getRepository(VoteEvent::class)->findAll();

        /**
         * @var VoteEvent $e
         */
        foreach ($events as &$e) {
            $e->calculations = $this->getVoteTotals($entityManager, $e);
            $e->isRunning = $this->voteIsActive($e);
            $e->isEnded = $this->voteIsEnded($e);
        }

        /**
         * @var User $user
         */
        $user = $this->getUser();

        return $this->render('vote/listvote.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'events' => $events
        ]);

    }

    #[Route('/vote/start', 'app_startvoteevent')]
    public function startVote(EntityManagerInterface $entityManager, Request $request)
    {
        $voteID = $request->query->get('voteevent');
        /**
         * @var VoteEvent $event
         */
        $event = $entityManager->getRepository(VoteEvent::class)->find($voteID);
        $now = new \DateTime();
        $event->setStartsOn($now);
        $entityManager->persist($event);
        $entityManager->flush();
        return new RedirectResponse("/vote/list");
    }


    #[Route('/vote/end', 'app_endvoteevent')]
    public function endVote(EntityManagerInterface $entityManager, Request $request)
    {
        $voteID = $request->query->get('voteevent');
        /**
         * @var VoteEvent $event
         */
        $event = $entityManager->getRepository(VoteEvent::class)->find($voteID);
        $now = new \DateTime();
        $event->setEndsOn($now);
        $event->setVoteComplete(true);
        $entityManager->persist($event);
        $entityManager->flush();
        return new RedirectResponse("/vote/list");
    }

    private function getVoteItems(EntityManagerInterface $entityManager, User $user, VoteEvent $voteEvent)
    {
        $allItems = $user->getVoteItems();
        $voteEventId = $voteEvent->getId();
        $tempItems = [];
        $voteItems = [];
        /**
         * @var VoteItem $item
         */
        foreach ($allItems as $item) {
            if ($item->getVoteEvent()->getId() !== $voteEventId) {
                continue;
            }
            $tempItems[] = $item;
        }
        $vendors = $entityManager->getRepository(Vendor::class)->findAll();

        /**
         * @var Vendor $vendor
         */
        foreach ($vendors as $vendor) {
            $vendorSeen = false;
            /**
             * @var VoteItem $item
             */
            foreach ($tempItems as $item) {
                if ($item->getVendor()->getId() === $vendor->getId()) {
                    $voteItems[] = $item;
                    $vendorSeen = true;
                    break;
                }
            }
            if (!$vendorSeen) {
                $newItem = new VoteItem();
                $newItem->setVoteEvent($voteEvent)->setVendor($vendor)->setUser($user);
                $voteItems[] = $newItem;
            }
        }



        return $voteItems;
    }

    private function voteIsEnded(VoteEvent $event)
    {
        $now = new \DateTime();
        $end = $event->getEndsOn();
        $isEnded = ($now->diff($end, false))->invert;
        return $isEnded === 1;
    }

    private function voteIsActive(VoteEvent $event)
    {
        $now = new \DateTime();

        $start = $event->getStartsOn();
        $end = $event->getEndsOn();
        $isComplete = $event->isVoteComplete();

        if ($isComplete) {
            return false;
        }

        $isStarted = ($now->diff($start, false))->invert;
        $isEnded = ($now->diff($end, false))->invert;

        if ($isStarted === 1 && $isEnded !== 1) {
            return true;
        }

        return false;
    }



    private function getVoteTotals(EntityManagerInterface $entityManager, VoteEvent $event)
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $totalVotes = count($users) * $event->getStaffVotes();
        $currentVotes = 0;
        $items = $event->getVoteItems();
        /**
         * @var VoteItem $i
         */
        foreach ($items as $i) {
            $currentVotes += $i->getVotes();
        }
        $percentComplete = ($currentVotes/$totalVotes) * 100;

        return ([
            'total' => $totalVotes,
            'current' => $currentVotes,
            'percent' => $percentComplete
        ]);


    }

    private function getRunningVoteEvent(EntityManagerInterface $entityManager)
    {
        $events = $entityManager->getRepository(VoteEvent::class)->findAll();
        foreach ($events as $e) {
            if ($this->voteIsActive($e)) {
                return $e;
            }
        }
        throw new VoteException("No currently runnning votes");
    }

    private function getPrevNextVendor(EntityManagerInterface $entityManager, Vendor $vendor, int $offset = 0): int|null
    {
        if ($offset === 0) {
            return $vendor->getId();
        }


        $gtlt = $offset > 0 ? ">" : "<";
        $asde = $offset > 0 ? "ASC" : "DESC";

        $doctrine = $entityManager->getConnection();
        $sql = "SELECT v.id FROM vendor v WHERE v.id {$gtlt} :vid ORDER BY v.id {$asde} LIMIT 1";
        $query = $doctrine->prepare($sql)->executeQuery([':vid'=>$vendor->getId()]);
        $vid = $query->fetchOne();

        /**
         * @var Vendor $newVend
         */
        $newVend = $entityManager->getRepository(Vendor::class)->find($vid);
        if (empty($newVend)) {
            return null;
        }
        return $newVend->getId();
    }
}