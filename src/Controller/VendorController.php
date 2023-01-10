<?php

namespace App\Controller;

use App\Command\VendorImageDownloadCommand;
use App\Entity\Action;
use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VendorCategory;
use App\Entity\VendorImage;
use App\Enumerations\ActionEnumeration;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorCategoryEnumeration;
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
            'status' => $request->query->get('filter_status'),
            'category' => $request->query->get('filter_category'),
            'table' => $request->query->get('filter_table')
        ]
        ;

//        dd($filter);
        return $this->render('vendor/index.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'vendors' => $paginator->paginate($this->getVendorList($filter), $request->query->getInt('page', 1), 50),
            'status' => VendorStatusEnumeration::getList(),
            'category' => VendorCategoryEnumeration::getList(),
            'table' => TableTypeEnumeration::getList(),
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
            $vendor->setStatus($status);
            $entityManager->persist($vendor);

        }




        return $this->render("vendor/view.html.twig", [
            'vendor' => $vendor,
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'vendorStatusForm' => $form->createView()
        ]);

    }


    #[Route('/vendor/edit', name: 'app_editvendor')]
    public function editvendor(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');

        $vendorID = $request->query->get('vendor', '');
        if (empty($vendorID)) {
            return new RedirectResponse("/vendor");
        }

        $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);

        $form = $this->createForm(VendorFormType::class, $vendor);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Here we do the field map.

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
        if (empty($filter['status'])) {
            $vendors = $this->doctrine->getRepository(Vendor::class)->findAll();
        } else {
            $vendors = $this->doctrine->getRepository(Vendor::class)->findBy(['status' => $filter['status']]);
        }
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


}
