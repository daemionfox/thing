<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;

class VendorStatusEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'STATUS_';
    private $labelprefix = 'STATUS_';

    const STATUS_APPROVED = 'Approved';
    const STATUS_NOTAPPROVED = "Not Approved";

//    const STATUS_APPLIED = 'Applied';
//    const STATUS_PENDING = 'Pending';
//    const STATUS_DENIED = 'Denied';
//    const STATUS_CANCELLED = 'Cancelled';
//    const STATUS_DEFERRED = 'Deferred';
//    const STATUS_PASSED = 'Passed';
//    const STATUS_FAILED = 'Failed';
//    const STATUS_DELETED = 'Deleted';

}