<?php

namespace App\Traits;

trait RandomStringTrait
{
    protected function generateRandomString($length = 12)
    {
        $string = '!#%+23456789:=?@ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $strlen = strlen($string);
        $out = "";
        for ($i = 0; $i<$length; $i++) {
            $out .= substr($string, rand(0, $strlen), 1);
        }
        return $out;
    }
}