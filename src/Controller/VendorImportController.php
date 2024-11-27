<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\User;
use App\Entity\Vendor;
use App\Entity\VendorAddress;
use App\Entity\VendorCategory;
use App\Entity\VendorContact;
use App\Enumerations\ActionEnumeration;
use App\Enumerations\ConCatHeaderEnumeration;
use App\Enumerations\RegFoxHeaderEnumeration;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorCategoryEnumeration;
use App\Exceptions\OptionNotFoundException;
use App\Form\VendorImportFormType;
use App\Traits\CleanNumberTrait;
use App\Traits\isBooleanTrait;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VendorImportController extends AbstractController
{
    use isBooleanTrait;
    use CleanNumberTrait;

    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->doctrine = $entityManager;
    }

    #[Route('/vendor/image/progress', 'app_vendorimageprogress')]
    public function getProgress(Request $request): Response
    {
        $cacheDir = __DIR__ . "/../../cache";
        $progressid = $request->query->get('batch');
        $progress = [
            'current' =>0,
            'total' => 0
        ];
        if (file_exists("{$cacheDir}/{$progressid}.json")) {
            $progress = json_decode(file_get_contents("{$cacheDir}/{$progressid}.json"), true);
        }
        return new JsonResponse($progress);
    }



    #[Route('/vendor/import', name: 'app_vendorimport')]
    public function importCSVForm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');


        $form = $this->createForm(VendorImportFormType::class);
        $form->handleRequest($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Here we do the field map.
            new Action($user, ActionEnumeration::ACTION_VENDOR, "Import of vendors via CSV", $this->doctrine);
            return $this->runImport($request, $form);
        }

        return $this->render("vendor/import.html.twig", [
            'importForm' => $form->createView(),
            'user' => [
                $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);


    }

    public function runImport(Request $request, FormInterface $form): Response
    {
        set_time_limit(-1);

        $cacheDir = __DIR__ . "/../../cache";
        $regfoxUpload = $form->get('regfox_csv')->getData();
        $filename = $regfoxUpload->getClientOriginalName();
        $regfoxUpload->move($cacheDir, $filename);

        if (str_ends_with(strtoupper($filename), "JSON")) {
            $records = json_decode(file_get_contents("{$cacheDir}/{$filename}"), true);
        } elseif (str_ends_with(strtoupper($filename), "CSV")) {
            $csv = Reader::createFromPath("{$cacheDir}/{$filename}", 'r');
            $headers = $csv->setHeaderOffset(0)->getHeader();
            $records = $csv->getRecords($headers);
        } else {
            throw new \Exception("Could not understand file");
        }
        $conn = $this->doctrine->getConnection();
        $conn->getConfiguration()->setMiddlewares([]);
        $recordCount = 0;
        foreach ($records as $offset => $record) {
            $vend = $this->buildVendor((array) $record);
            $this->doctrine->persist($vend);
            $this->doctrine->flush();
            $this->doctrine->clear();
        }
        return new RedirectResponse("/vendor");
    }




    protected function buildVendor(array $record): Vendor
    {
        $vendor = new Vendor();
        $vendorContact = null;
        $tableAmount = 0;
        $endcapAmount = 0;
        $vendorEnumeration = new ConCatHeaderEnumeration();

        if (!empty($record[$vendorEnumeration::VENDOR_REGID])) {
            /**
             * @var Vendor $vendor
             */
            $vendor = $this->doctrine->getRepository(Vendor::class)->findOneBy(['remoteId' => $record[$vendorEnumeration::VENDOR_REGID]]);
            $vendor = $vendor ?? new Vendor();
            $vendorContact = $vendor->getVendorContact() ?? new VendorContact();
        }


        foreach ($record as $key => $value) {
            try {
                $cleanKey = $vendorEnumeration::get($vendorEnumeration::simplify($key));
                if (empty($cleanKey)) {
                    continue;
                }
                switch ($cleanKey) {
                    case $vendorEnumeration::VENDOR_AREA:
                        $vendor->setArea($value);
                        break;
                    case $vendorEnumeration::VENDOR_REGID:
                        $vendor->setRemoteId($value);
                        break;
                    case $vendorEnumeration::VENDOR_FIRSTNAME:
                        $vendorContact->setFirstName($value);
                        break;
                    case $vendorEnumeration::VENDOR_LASTNAME:
                        $vendorContact->setLastName($value);
                        break;
                    case $vendorEnumeration::VENDOR_BADGENAME:
                        $vendorContact->setBadgeName($value);
                        break;
                    case $vendorEnumeration::VENDOR_EMAIL:
                        $vendorContact->setEmailAddress($value);
                        break;
                    case $vendorEnumeration::VENDOR_DEALERNAME:
                        $vendor->setName($value);
                        break;
                    case $vendorEnumeration::VENDOR_TAXID:
                        $vendor->setTaxid($value);
                        break;
                    case $vendorEnumeration::VENDOR_DEALERRATING:
                        $vendor->setRating($value);
                        break;
                    case $vendorEnumeration::VENDOR_MATUREDEALERS:
                        $vendor->setMatureDealersSection($this->isBool($value));
                        break;
                    case $vendorEnumeration::VENDOR_SPECIALREQUESTS:
                        $vendor->setSpecialRequests($value);
                        break;
                    case $vendorEnumeration::VENDOR_ITEMCATEGORIES:
                        $primarySet = true;
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        foreach ($value as $val) {
                            $vencat = new VendorCategory();
                            $vencat->setIsPrimary($primarySet)->setCategory(VendorCategoryEnumeration::get($val));
                            $vendor->addVendorCategory($vencat);
                            $primarySet = false;
                        }
                        break;
                    case $vendorEnumeration::VENDOR_PRIMARYCATEGORY:
                        $vencat = new VendorCategory();
                        $vencat->setIsPrimary(true)->setCategory(VendorCategoryEnumeration::get($value));
                        $vendor->addVendorCategory($vencat);
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_ACCESSORIES:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_ACCESSORIES));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_ARTWORK:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_ARTWORK));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_BOOKS:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_BOOKS));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_CLOTHING:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_CLOTHING));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_COMICS:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_COMICS));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_FURSUITS:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_FURSUITS));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_GAMES:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_GAMES));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_HOMEGOODS:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_HOMEGOODS));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_OTHER:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_OTHER));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_PERFUMES:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_PERFUMES));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_TOYS:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_TOYS));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_PRINTING:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_PRINTING));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_SECONDARYCATEGORY_SCULPTURE:
                        if ($this->isBool($value)) {
                            $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_SCULPTURE));
                        }
                        break;
                    case $vendorEnumeration::VENDOR_PRODUCTSSOLD:
                        $vendor->setProductsAndServices($value);
                        break;
                    case $vendorEnumeration::VENDOR_DEALERPHOTOS:
                        $vendor->setImageBlock($value);
                        break;
                    case $vendorEnumeration::VENDOR_WEBSITE:
                        $vendor->setWebsite($value);
                        break;
                    case $vendorEnumeration::VENDOR_TWITTER:
                        $vendor->setTwitter($value);
                        break;
                    case $vendorEnumeration::VENDOR_SEATINGREQUESTS:
                        $vendor->setSeatingRequests($value);
                        break;
                    case $vendorEnumeration::VENDOR_NEIGHBORREQUESTS:
                        $vendor->setNeighborRequests($value);
                        break;
                    case $vendorEnumeration::VENDOR_OTHERREQUESTS:
                        $vendor->setOtherRequests($value);
                        break;
                    case $vendorEnumeration::VENDOR_TABLETYPE:
                        $vendor->setTableRequestType(TableTypeEnumeration::get($value));
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_HALF:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_HALF);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_SINGLE:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLE);
                        } elseif (strtoupper($value) === "1 SINGLE TABLE WITH ENDCAP") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLE);
                            $vendor->setHasEndcap(true);
                        } elseif (strtoupper($value) === "1 SINGLE TABLE") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLE);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_SINGLEHALF:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLEHALF);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_DOUBLE:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_DOUBLE);
                        } elseif (strtoupper($value) === "1 DOUBLE TABLE WITH ENDCAP") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_DOUBLE);
                            $vendor->setHasEndcap(true);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_TRIPLE:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_TRIPLE);
                        } elseif (strtoupper($value) === "1 TRIPLE TABLE WITH ENDCAP") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_TRIPLE);
                            $vendor->setHasEndcap(true);
                        } elseif (strtoupper($value) === "1 TRIPLE TABLE") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_TRIPLE);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_QUAD:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUAD);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_QUINT:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUINT);
                        } elseif (strtoupper($value) === "1 QUINT TABLE WITH ENDCAP") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUINT);
                            $vendor->setHasEndcap(true);
                        } elseif (strtoupper($value) === "1 QUINT TABLE") {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUINT);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_SMALLBOOTH:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SMALLBOOTH);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_LARGEBOOTH:
                        if ($this->isBool($value) === true) {
                            $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_LARGEBOOTH);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_ENDCAP:
                        if ($this->isBool($value) === true) {
                            $vendor->setHasEndcap(true);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_LARGEBOOTH:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_SMALLBOOTH:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_QUINT:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_QUAD:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_TRIPLE:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_DOUBLE:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_SINGLEHALF:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_SINGLE:
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_HALF:
                        if (!empty($value)) {
                            $tableAmount = $this->cleanNumbers($value);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_TABLE_AMOUNT_ENDCAP:
                        if (!empty($value)) {
                            $endcapAmount = $this->cleanNumbers($value);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_ASSISTANT_SINGLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_SINGLEHALF:
                    case $vendorEnumeration::VENDOR_ASSISTANT_DOUBLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_TRIPLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_QUAD:
                    case $vendorEnumeration::VENDOR_ASSISTANT_QUINTBOOTH:
                        if (!empty($value)) {
                            $vendor->setNumAssistants((int)$value);
                        }
                        break;
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_SINGLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_SINGLEHALF:
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_DOUBLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_TRIPLE:
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_QUAD:
                    case $vendorEnumeration::VENDOR_ASSISTANT_AMOUNT_QUINTBOOTH:
                        if (!empty($value)) {
                            $vendor->setAssistantAmount($this->cleanNumbers($value));
                        }
                        break;



                    // Not doing anything with this yet
                    //                case $vendorEnumeration::VENDOR_DEALERAPPSTATUS:   //vendor
                    //                case $vendorEnumeration::VENDOR_DEALERSTATUS:  // vendor
                    //                case $vendorEnumeration::VENDOR_DEALERSTATUSAMOUNT:    // vendor
                    //                case $vendorEnumeration::VENDOR_MIDDLEINIT:     // vendor-contact
                    //                case $vendorEnumeration::VENDOR_PREFERREDNAME:     // vendor-contact

                }
            } catch (OptionNotFoundException $onfe) {
                $foo = 'bar';
            }
        }
        $vendor->setVendorContact($vendorContact);
        $vendor->setTableAmount($tableAmount + $endcapAmount);
        $vendor->detectTableCategory();
        return $vendor;
    }

}