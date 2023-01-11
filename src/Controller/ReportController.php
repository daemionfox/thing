<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VendorCategory;
use App\Entity\VoteEvent;
use App\Entity\VoteItem;
use App\Enumerations\VendorStatusEnumeration;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    protected $doctrine;

    #[Route('/reports', name: 'app_reportlist')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $data = [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],

        ];

        return $this->render('reports/index.html.twig', $data);
    }


    #[Route('/reports/votebreakdown', name: 'app_reportvotes')]
    public function voteBreakdown(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();


        $vendors = $entityManager->getRepository(Vendor::class)->findAll();
        $voteEvents = $entityManager->getRepository(VoteEvent::class)->findAll();

        usort($vendors, function($a, $b){
            /**
             * @var Vendor $a
             * @var Vendor $b
             */
            return $a->getName() <=> $b->getName();
        });

        usort($voteEvents, function($a, $b){
            /**
             * @var VoteEvent $a
             * @var VoteEvent $b
             */
            return $a->getCreatedOn() <=> $b->getCreatedOn();
        });
        $headers =             [
            'Name',
            'Contact',
            'Email',
            'Table Requested',
            'Status',
            'Total Votes'
        ];
        /**
         * @var VoteEvent $voteEvent
         */
        foreach ($voteEvents as $voteEvent) {
            $headers[] = $voteEvent->getName();
        }

        $csv = [
            $headers
        ];

        /**
         * @var Vendor $v
         */
        foreach ($vendors as $v) {
            $votes = ['Total Votes' => 0];
            foreach ($voteEvents as $ve) {
                /**
                 * @var VoteEvent $ve
                 */
                $vitems = $v->getVoteEventItems($ve->getId());
                $vsum = 0;
                foreach ($vitems as $vi) {
                    /**
                     * @var VoteItem $vi
                     */
                    $vsum += $vi->getVotes();
                }
                $votes['Total Votes'] += $vsum;
                $votes[$ve->getName()] = $vsum;
            }
            $temp = [
                $v->getName(),
                $v->getVendorContact()->getFirstName() . " " . $v->getVendorContact()->getLastName(),
                $v->getVendorContact()->getEmailAddress(),
                $v->getTableRequestType(),
                $v->getStatus(),
                $votes['Total Votes']
            ];
            foreach ($voteEvents as $ve) {
                /**
                 * @var VoteEvent $ve
                 */
                $temp[] = $votes[$ve->getName()];
            }
            $csv[] = $temp;
        }
        $writer = Writer::createFromString();
        $writer->insertAll($csv);
        $output = $writer->toString();
        $response = new Response($output);

        $disp = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            "votebreakdown_" . date("Ymd-his") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disp);
        return $response;
    }


}