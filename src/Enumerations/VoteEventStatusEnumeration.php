<?php

namespace App\Enumerations;

use Eloquent\Enumeration\AbstractEnumeration;

class VoteEventStatusEnumeration extends AbstractEnumeration
{
    const STATUS_SCHEDULED = "Scheduled";
    const STATUS_RUNNING = "Running";
    const STATUS_PROCESSING = "Processing";
    const STATUS_COMPLETE = "Complete";
    const STATUS_CANCELLED = "Cancelled";
    const STATUS_DELETED = "Deleted";
}