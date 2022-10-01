<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class RoleEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;
    private $constprefix = 'ROLE_';
    private $labelprefix = 'LABEL_';

    const ROLE_EDITSTAFF  = 'ROLE_EDITSTAFF';
    const ROLE_EDITVENDOR = 'ROLE_EDITVENDOR';
    const ROLE_CREATEVOTE = 'ROLE_CREATEVOTE';
    const ROLE_EDITROOM   = 'ROLE_EDITROOM';

    const LABEL_EDITSTAFF  = 'Edit Staff Members';
    const LABEL_EDITVENDOR = 'Edit Vendors';
    const LABEL_CREATEVOTE = 'Create Votes';
    const LABEL_EDITROOM   = 'Edit Rooms';

}