<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;

class VendorNoteLevelEnumeration extends \Eloquent\Enumeration\AbstractEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'LEVEL_';
    private $labelprefix = 'LABEL_';

    const LEVEL_WARNING = 'warning';
    const LEVEL_CAUTION = 'caution';
    const LEVEL_CLEAR = 'clear';
    const LEVEL_INFO = 'info';

    const LABEL_WARNING = 'Warning';
    const LABEL_CAUTION = 'Possible Issue';
    const LABEL_CLEAR = 'All Clear';
    const LABEL_INFO = 'Requires Investigation';

}