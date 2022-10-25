<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\User;
use App\Entity\VoteEvent;
use App\Entity\VoteItem;
use App\Enumerations\ActionEnumeration;
use App\Exceptions\BadFormDataException;
use App\Form\CreateVoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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


    #[Route('/vote/list', 'app_listvoteevents')]
    public function listVoteEvents(EntityManagerInterface $entityManager) {
        $events = $entityManager->getRepository(VoteEvent::class)->findAll();

        /**
         * @var VoteEvent $e
         */
        foreach ($events as &$e) {
            $e->calculations = $this->getVoteTotals($entityManager, $e);
            $e->isRunning = $this->voteIsActive($e);
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
        $users = $entityManager->getRepository(VoteEvent::class)->findAll();
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


}