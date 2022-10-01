<?php

namespace App\Traits;

trait CleanNumberTrait
{
    protected function cleanNumbers($string): float
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $string);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $string);

        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

        return (float) str_replace(',', '.', $removedThousandSeparator);
    }

}