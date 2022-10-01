<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class DealerAssistantEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;

    const ASSISTANT_AMOUNT = 55.00;

    function calculate(int $assistants) {
        return self::ASSISTANT_AMOUNT * $assistants;
    }

}