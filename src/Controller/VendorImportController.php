<?php

namespace App\Controller;

use App\Entity\Vendor;
use App\Entity\VendorAddress;
use App\Entity\VendorCategory;
use App\Entity\VendorContact;
use App\Enumerations\RegFoxHeaderEnumeration;
use App\Enumerations\TableTypeEnumeration;
use App\Enumerations\VendorCategoryEnumeration;
use App\Form\VendorImportFormType;
use App\Traits\CleanNumberTrait;
use App\Traits\isBooleanTrait;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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

    #[Route('/vendor/import', name: 'app_vendorimport')]
    public function importCSVForm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_EDITVENDOR');


        $form = $this->createForm(VendorImportFormType::class);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Here we do the field map.
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
        set_time_limit(900);
        $cacheDir = __DIR__ . "/../../cache";
        $regfoxUpload = $form->get('regfox_csv')->getData();
        $filename = $regfoxUpload->getClientOriginalName();
        $regfoxUpload->move($cacheDir, $filename);

        $csv = Reader::createFromPath("{$cacheDir}/{$filename}", 'r');

        $headers = $csv->setHeaderOffset(0)->getHeader();

        $records = $csv->getRecords($headers);
        foreach ($records as $offset => $record) {
            $vend = $this->buildVendor((array) $record);
            $this->doctrine->persist($vend);
        }
        $this->doctrine->flush();
        return new RedirectResponse("/vendor");
    }




    protected function buildVendor(array $record): Vendor
    {
        $vendor = new Vendor();
        $vendorContact = new VendorContact();
        $tableAmount = 0;
        $endcapAmount = 0;

        if (!empty($record[RegFoxHeaderEnumeration::REGFOX_REGID])){
            /**
             * @var $vendor Vendor
             * @var $vendorContact VendorContact
             */
            $vendor = $this->doctrine->getRepository(Vendor::class)->findOneBy(['regfoxid' => $record[RegFoxHeaderEnumeration::REGFOX_REGID]]);
            $vendorContact = $vendor->getVendorContact() ?? new VendorContact();
        }



        foreach ($record as $key => $value) {
            $cleanKey = RegFoxHeaderEnumeration::get($key);
            
            switch($cleanKey) {
                case RegFoxHeaderEnumeration::REGFOX_REGID:
                    $vendor->setRegfoxid($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_FIRSTNAME:
                    $vendorContact->setFirstName($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_LASTNAME:
                    $vendorContact->setLastName($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_BADGENAME:
                    $vendorContact->setBadgeName($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_EMAIL:
                    $vendorContact->setEmailAddress($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_DEALERNAME:
                    $vendor->setName($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TAXID:
                    $vendor->setTaxid($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_DEALERRATING:
                    $vendor->setRating($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_PRIMARYCATEGORY:
                    $vencat = new VendorCategory();
                    $vencat->setIsPrimary(true)->setCategory(VendorCategoryEnumeration::get($value));
                    $vendor->addVendorCategory($vencat);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_ACCESSORIES:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_ACCESSORIES));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_ARTWORK:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_ARTWORK));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_BOOKS:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_BOOKS));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_CLOTHING:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_CLOTHING));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_COMICS:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_COMICS));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_FURSUITS:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_FURSUITS));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_GAMES:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_GAMES));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_HOMEGOODS:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_HOMEGOODS));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_OTHER:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_OTHER));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_PERFUMES:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_PERFUMES));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_TOYS:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_TOYS));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_PRINTING:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_PRINTING));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SECONDARYCATEGORY_SCULPTURE:
                    if ($this->isBool($value)) {
                        $vendor->addVendorCategory(new VendorCategory(VendorCategoryEnumeration::CATEGORY_SCULPTURE));
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_PRODUCTSSOLD:
                    $vendor->setProductsAndServices($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_DEALERPHOTOS:
                    $vendor->setImageBlock($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_WEBSITE:
                    $vendor->setWebsite($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TWITTER:
                    $vendor->setTwitter($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_SEATINGREQUESTS:
                    $vendor->setSeatingRequests($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_NEIGHBORREQUESTS:
                    $vendor->setNeighborRequests($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_OTHERREQUESTS:
                    $vendor->setOtherRequests($value);
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_HALF:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_HALF);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_SINGLE:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLE);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_SINGLEHALF:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SINGLEHALF);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_DOUBLE:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_DOUBLE);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_TRIPLE:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_TRIPLE);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_QUAD:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUAD);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_QUINT:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_QUINT);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_SMALLBOOTH:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_SMALLBOOTH);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_LARGEBOOTH:
                    if ($this->isBool($value) === true) {
                        $vendor->setTableRequestType(TableTypeEnumeration::TABLETYPE_LARGEBOOTH);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_ENDCAP:
                    if ($this->isBool($value) === true) {
                        $vendor->setHasEndcap(true);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_LARGEBOOTH:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_SMALLBOOTH:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_QUINT:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_QUAD:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_TRIPLE:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_DOUBLE:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_SINGLEHALF:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_SINGLE:
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_HALF:
                    if (!empty($value)) {
                        $tableAmount = $this->cleanNumbers($value);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_TABLE_AMOUNT_ENDCAP:
                    if (!empty($value)) {
                        $endcapAmount = $this->cleanNumbers($value);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_SINGLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_SINGLEHALF:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_DOUBLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_TRIPLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_QUAD:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_QUINTBOOTH:
                    if (!empty($value)) {
                        $vendor->setNumAssistants((int)$value);
                    }
                    break;
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_SINGLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_SINGLEHALF:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_DOUBLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_TRIPLE:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_QUAD:
                case RegFoxHeaderEnumeration::REGFOX_ASSISTANT_AMOUNT_QUINTBOOTH:
                if (!empty($value)) {
                    $vendor->setAssistantAmount($this->cleanNumbers($value));
                }
                break;



                // Not doing anything with this yet
//                case RegFoxHeaderEnumeration::REGFOX_DEALERAPPSTATUS:   //vendor
//                case RegFoxHeaderEnumeration::REGFOX_DEALERSTATUS:  // vendor
//                case RegFoxHeaderEnumeration::REGFOX_DEALERSTATUSAMOUNT:    // vendor
//                case RegFoxHeaderEnumeration::REGFOX_MIDDLEINIT:     // vendor-contact
//                case RegFoxHeaderEnumeration::REGFOX_PREFERREDNAME:     // vendor-contact

            }
            
            
            
        }
        $vendor->setVendorContact($vendorContact);
        $vendor->setTableAmount($tableAmount + $endcapAmount);
        return $vendor;
    }


}