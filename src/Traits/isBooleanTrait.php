<?php

namespace App\Traits;

trait isBooleanTrait
{

    protected function isBool(mixed $input): bool
    {
        if (is_bool($input)) {
            return $input;
        } elseif (is_numeric($input)) {
            return $input > 0;
        } elseif (is_array($input)) {
            return !empty($input);
        } elseif (is_object($input)) {
            return !empty($input);
        } elseif (is_string($input)) {
            switch (strtoupper($input)) {
                case 'YES':
                case 'TRUE':
                case '1':
                    return true;
                    break;
            }
        }
        return false;
    }

}