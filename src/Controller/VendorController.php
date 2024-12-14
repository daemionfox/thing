<?php

namespace App\Controller;

use App\Command\VendorImageDownloadCommand;
use App\Entity\Action;
use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VendorCategory;
use App\Entity\VendorContact;
use App\Entity\VendorImage;
use App\Entity\VendorNote;
use App\Enumerations\ActionEnumeration;
use App\Enumerations\TableCategoryEnumeration;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorCategoryEnumeration;
use App\Enumerations\VendorNoteLevelEnumeration;
use App\Enumerations\VendorStatusEnumeration;
use App\Exceptions\DownloadException;
use App\Form\ScrubVendorsType;
use App\Form\UpdateVendorStatusType;
use App\Form\VendorFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use VStelmakh\UrlHighlight\UrlHighlight;

class VendorController extends AbstractController
{

    protected ManagerRegistry $doctrine;


    protected int $minWidth = 640;
    protected int $minHeight = 480;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/vendor', name: 'app_vendor')]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $filter = [
            'search' => $request->query->get('filter_search'),
            'status' => $request->query->get('filter_status'),
            'category' => $request->query->get('filter_category'),
            'table' => $request->query->get('filter_table')
        ]
        ;

//        dd($filter);
        $filteredList = $this->getVendorList($filter);
        return $this->render('vendor/index.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'vendors' => $paginator->paginate($filteredList, $request->query->getInt('page', 1), 50),
            'search' => $filter['search'],
            'status' => VendorStatusEnumeration::getList(),
            'category' => VendorCategoryEnumeration::getList(),
            'table' => TableCategoryEnumeration::getList(),
            'filter' => $filter
        ]);
    }




    #[Route('/vendor/collectimages', 'app_collectvendorimages')]
    public function collectimages(Request $request, KernelInterface $kernel, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag, LoggerInterface $logger): Response
    {
        set_time_limit(-1);
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        $vendorID = $request->query->get('vendor', '');
        if (empty($vendorID)) {
            return new RedirectResponse("/vendor");
        }

        $application = new Application($kernel);
        $input = new ArrayInput([
            'command' => 'vendor:collectimages',
            'vendorid' => $vendorID
        ]);


        $output = new BufferedOutput();
        $application->run($input, $output);


        return new RedirectResponse("/vendor");
    }

    #[Route('/vendor/view', name: 'app_viewvendor')]
    public function viewvendor(Request $request, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');


        $user = $this->getUser();
        $form = $this->createForm(UpdateVendorStatusType::class);
        $form->handleRequest($request);
        $vendorID = $request->query->get('vendor', '');
        if (empty($vendorID)) {
            return new RedirectResponse("/vendor");
        }
        /**
         * @var Vendor $vendor
         */
        $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);

        if ($form->isSubmitted() && $form->isValid()) {
            // Woohoo!
            $status = $form->get('status')->getData();
            /**
             * @var User $user
             */
            new Action($user, ActionEnumeration::ACTION_VENDOR, "Set status of {$vendor->getName()} to {$status}", $entityManager);
            $vendor->setStatus($status);
            $entityManager->persist($vendor);
            $entityManager->flush();
        }




        return $this->render("vendor/view.html.twig", [
            'vendor' => $vendor,
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'vendorStatusForm' => $form->createView(),
            'noteLabels' => VendorNoteLevelEnumeration::getList(),
        ]);

    }

    #[Route('/vendor/addnote', name: 'app_addvendornote')]
    public function addnote(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $vendid = $request->request->get('vendorid');
        $message = $request->request->get('vendor-note-text');
        $type = $request->request->get('vendor-note-type');

        /**
         * @var Vendor $vendor
         */
        $vendor = $entityManager->getRepository(Vendor::class)->find($vendid);
        $note = new VendorNote();
        $note
            ->setOwner($user)
            ->setVendor($vendor)
            ->setMessage($message)
            ->setType($type)
            ->setCreatedon(new \DateTime())
        ;
        $entityManager->persist($note);
        $entityManager->flush();

        $returnTo = $request->headers->get('referer');
        return new RedirectResponse($returnTo);
    }


    #[Route('/vendor/deletenote', name: 'app_deletevendornote')]
    public function deletenote(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $noteid = $request->query->get('noteid');
        $note = $entityManager->getRepository(VendorNote::class)->find($noteid);
        $entityManager->remove($note);
        $entityManager->flush();

        $returnTo = $request->headers->get('referer');
        return new RedirectResponse($returnTo);
    }

    #[Route('/vendor/edit/{vendorID?}', name: 'app_editvendor')]
    public function editvendor(Request $request, EntityManagerInterface $entityManager, ?string $vendorID): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        $user = $this->getUser();

        $vendor = new Vendor();
        $vendor->setVendorContact(new VendorContact())->addVendorCategory(new VendorCategory());
        if (!empty($vendorID)) {
            /**
             * @var Vendor $vendor
             */
            $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);
        } else {
            $vendor->setRemoteId($this->getNewRemoteID($entityManager));
        }


        $form = $this->createForm(VendorFormType::class, $vendor);
        $form->handleRequest($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Here we do the field map.
            new Action($user, ActionEnumeration::ACTION_VENDOR, "Editing vendor {$vendor->getName()} id {$vendor->getId()}", $entityManager);
            $vendor->detectTableCategory();
            $entityManager->persist($vendor);
            $entityManager->flush();

            return new RedirectResponse("/vendor");
        }
        return $this->render("vendor/edit.html.twig", [
            'vendorForm' => $form->createView(),
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);


    }

    protected function getNewRemoteID(EntityManagerInterface $entityManager, string $prefix = 'NRF-'): string
    {
        $ender = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $id = "{$prefix}{$ender}";
        $vend = $entityManager->getRepository(Vendor::class)->findOneBy(['remoteId' => $id]);
        if (empty($vend)) {
            return $id;
        }
        return $this->getNewRemoteID($entityManager, $prefix);
    }

    #[Route('/vendor/delete', name: "app_deletevendor")]
    public function deletevendor(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $vendorID = $request->query->get('vendor', '');
        /**
         * @var Vendor $vendor
         */
        $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);
        new Action($user, ActionEnumeration::ACTION_VENDOR, "Deleting vendor {$vendor->getName()} id {$vendor->getId()}", $entityManager);
        $entityManager->remove($vendor);
        $entityManager->flush();
        return new RedirectResponse('/vendor');
    }

    #[Route('/vendor/scrub', name: "app_scrubvendors")]
    public function scrubvendors(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITSTAFF');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $form = $this->createForm(ScrubVendorsType::class, new Vendor());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            new Action($user, ActionEnumeration::ACTION_VENDOR, "All vendors have been deleted from the database.", $entityManager);
            $connection = $entityManager->getConnection();

            $tables = [
                'vendor_note',
                'vendor_image',
                'vendor_address',
                'vendor_contact',
                'vendor_category',
                'vote_item',
                'vote_event',
                'vendor'
            ];

            foreach ($tables as $t) {
                /** @noinspection SqlNoDataSourceInspection */
                $sql = "DELETE FROM {$t}";
                $statement = $connection->prepare($sql);
                $statement->executeQuery([]);
            }


            /** @noinspection SqlNoDataSourceInspection */
            $sql = "DELETE FROM vendor";
            $statement = $connection->prepare($sql);
            $statement->executeQuery([]);
            $entityManager->flush();
            return $this->redirectToRoute('app_dashboard');
        }
        return $this->render('vendor/scrubvendors.html.twig', [
            'scrubvendorForm' => $form->createView(),
            'user' => [
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    #[Route('/vendor/getlist', name: "app_downloadvendorlist")]
    public function downloadFilteredList(Request $request, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');

        /**
         * @var User $user
         */
        $user = $this->getUser();


        $filter = [
            'status' => $request->query->get('filter_status'),
            'category' => $request->query->get('filter_category'),
            'table' => $request->query->get('filter_table')
        ]
        ;
        $vendors = $this->getVendorList($filter);

        usort($vendors, function($a, $b){
            return $a->getName() <=> $b->getName();
        });

        $csv = [
            [
                'Name',
                'Contact',
                'Email',
                'Table Requested',
                'Status'
            ]
        ];

        /**
         * @var Vendor $v
         */
        foreach ($vendors as $v) {
            $temp = [
                $v->getName(),
                $v->getVendorContact()->getFirstName() . " " . $v->getVendorContact()->getLastName(),
                $v->getVendorContact()->getEmailAddress(),
                $v->getTableRequestType(),
                $v->getStatus()
            ];
            $csv[] = $temp;
        }
        $writer = Writer::createFromString();
        $writer->insertAll($csv);
        $output = $writer->toString();
        $response = new Response($output);

        $stat = !empty($filter['status']) ? "_{$filter['status']}" : "";
        $cat = !empty($filter['category']) ? "_{$filter['category']}" : "";
        $tab = !empty($filter['table']) ? "_{$filter['table']}" : "";

        $disp = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            "dealerslist" . strtolower(str_replace(" ", "", $stat)) . strtolower(str_replace(" ", "", $cat)) . strtolower(str_replace(" ", "", $tab)) . "_" . date("Ymd-his") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disp);
        return $response;

    }

    /**
     * @return array
     */
    protected function getVendorList($filter = null): array
    {

        $vendors = $this->doctrine->getRepository(Vendor::class)->findByFilter($filter);
        if(empty($filter['category'])) {
            return $vendors;
        }
        $output = [];
        /**
         * @var Vendor $v
         */
        foreach ($vendors as $v) {
            $cats = $v->getVendorCategories();
            /**
             * @var VendorCategory $c
             */
            foreach ($cats as $c) {
                if ($c->getCategory() === $filter['category'] && $c->isIsPrimary() === true) {
                    $output[] = $v;
                }
            }
        }


        return $output;
    }

    #[Route('/vendor/resetcategories', name: "app_resetvendorcategory")]
    public function fixcategories(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');

        $vendors = $entityManager->getRepository(Vendor::class)->findAll();

        /**
         * @var Vendor $vendor
         */
        foreach ($vendors as $vendor) {
            $vendor->detectTableCategory();
            $entityManager->persist($vendor);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Table Categories have been rebuilt.');

        return new RedirectResponse('/vendor');
    }


}
