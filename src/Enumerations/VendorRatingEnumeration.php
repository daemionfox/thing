<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class VendorRatingEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'RATING_';
    private $labelprefix = 'RATING_';

    const RATING_GPG = 'G or PG';
    const RATING_PG13R = 'PG-13 or R';

}