<?php

namespace App\Controller;

use App\Entity\Vendor;
use App\Entity\VendorCategory;
use App\Enumerations\VendorCategoryEnumeration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    #[Route('/chart', name: 'app_chart')]
    public function index(): Response
    {
        return $this->render('chart/index.html.twig', [
            'controller_name' => 'ChartController',
        ]);
    }


    #[Route('/chart/vendors/all', name: 'app_chartallvendors')]
    public function allvendors(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vendors = $entityManager->getRepository(Vendor::class)->findAll();

        $pieData = [];


        /**
         * @var Vendor $vendor
         */
        foreach ($vendors as $vendor) {
            $categories = $vendor->getVendorCategories();
            /**
             * @var VendorCategory $category
             */
            foreach ($categories as $category) {
                if (!$category->isIsPrimary()) {
                    continue;
                }
                if (!isset($pieData[$category->getCategory()])) {
                    $pieData[$category->getCategory()] = 0;
                }
                $pieData[$category->getCategory()]++;
            }


        }

        $colors = [];
        $labels = array_keys($pieData);
        foreach ($labels as $label) {
            $colors[] = VendorCategoryEnumeration::getColor($label);
        }
        $data = [
            'labels' => array_keys($pieData),
            'datasets' => [[
                'label' => 'Categories',
                'data' => array_values($pieData),
                'backgroundColor' => $colors,
                'hoverOffset' => 4,
                'options' => [
                    'legend' => [
                        'display' => false
                    ]
                ]


            ]],
        ];


        return new JsonResponse($data);
    }
}
