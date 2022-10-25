<?php

namespace App\Controller;

use App\Command\VendorImageDownloadCommand;
use App\Entity\Action;
use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VendorImage;
use App\Enumerations\ActionEnumeration;
use App\Exceptions\DownloadException;
use App\Form\ScrubVendorsType;
use App\Form\VendorFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
         * @var $user User
         */
        $user = $this->getUser();

        return $this->render('vendor/index.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ],
            'vendors' => $paginator->paginate($this->getVendorList(), $request->query->getInt('page', 1), 50)
        ]);
    }




    #[Route('/vendor/collectimages', 'app_collectvendorimages')]
    public function collectimages(Request $request, KernelInterface $kernel, EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');
        $vendorID = $request->query->get('vendor', '');
        if (empty($vendorID)) {
            return new RedirectResponse("/vendor");
        }

        $collect = new VendorImageDownloadCommand($entityManager, $parameterBag);

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
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $vendorID = $request->query->get('vendor', '');
        if (empty($vendorID)) {
            return new RedirectResponse("/vendor");
        }
        $vendor = $entityManager->getRepository(Vendor::class)->find($vendorID);
        return $this->render("vendor/view.html.twig", [
            'vendor' => $vendor,
            'user' => [
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
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

    /**
     * @return array
     */
    protected function getVendorList(): array
    {
        $vendors = $this->doctrine->getRepository(Vendor::class) ->findAll();
        return $vendors;
    }


}
