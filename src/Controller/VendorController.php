<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vendor;
use App\Form\VendorFormType;
use App\Security\LoginAuthenticator;
use ContainerEUEbPjn\PaginatorInterface_82dac15;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class VendorController extends AbstractController
{
    protected $doctrine;

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
            'vendors' => $paginator->paginate($this->getVendorList(), $request->query->getInt('page', 1), 10)
        ]);
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



    /**
     * @return array
     */
    protected function getVendorList(): array
    {
        $vendors = $this->doctrine->getRepository(Vendor::class) ->findAll();
        return $vendors;
    }
}
