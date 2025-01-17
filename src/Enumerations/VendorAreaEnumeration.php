<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;

class VendorAreaEnumeration
{
    use EnumerationGetTrait;
    protected $constprefix = 'AREA_';
    protected $labelprefix = 'AREA_';

    const AREA_GENERAL = "General Area";
    const AREA_BODEGA = "Bodega";
    const AREA_MATURE = "18+ Section";


    public static function normalize(string $string)
    {
        switch(strtoupper($string)){
            case strtoupper(self::AREA_BODEGA):
                return self::AREA_BODEGA;
            case strtoupper(self::AREA_MATURE):
                return self::AREA_MATURE;

        }
        return self::AREA_GENERAL;
    }

}