<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;

abstract class VendorMapEnumeration
{
    use EnumerationGetTrait;
    protected $constprefix = 'VENDOR_';
    protected $labelprefix = 'VENDOR_';
    
}