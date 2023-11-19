<?php

namespace App\Enumerations;

use App\Entity\Vendor;
use App\Traits\EnumerationGetTrait;

class TableCategoryEnumeration extends \Eloquent\Enumeration\AbstractEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'CATEGORY_';
    private $labelprefix = 'CATEGORY_';

    const CATEGORY_LBOOTH = 'Large Booth';
    const CATEGORY_SBOOTH = 'Small Booth';
    const CATEGORY_BODEGA = 'Bodega';
    const CATEGORY_MATURE = 'Mature Dealers Section';
    const CATEGORY_ENDCAP = 'With Endcap';
    const CATEGORY_EVERYTHINGELSE = 'Standard Table';


    public static function category(Vendor $vendor)
    {

        if ($vendor->getTableRequestType() === TableTypeEnumeration::TABLESIZE_LARGEBOOTH) {
            return self::CATEGORY_LBOOTH;
        } elseif ($vendor->getTableRequestType() === TableTypeEnumeration::TABLESIZE_SMALLBOOTH) {
            return self::CATEGORY_SBOOTH;
        } elseif ($vendor->getTableRequestType() === TableTypeEnumeration::TABLETYPE_HALF) {
            return self::CATEGORY_BODEGA;
        } elseif ($vendor->isHasEndcap()){
            return self::CATEGORY_ENDCAP;
        } elseif ($vendor->isMatureDealersSection()) {
            return self::CATEGORY_MATURE;
        }
        return self::CATEGORY_EVERYTHINGELSE;
    }



}