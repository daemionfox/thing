<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;

class VendorAreaEnumeration
{
    use EnumerationGetTrait;
    protected $constprefix = 'AREA_';
    protected $labelprefix = 'AREA_';

    const AREA_GENERAL = "General area";
    const AREA_BODEGA = "Bodega";
    const AREA_MATURE = "18+ Section";

}