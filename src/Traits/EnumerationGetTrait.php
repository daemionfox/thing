<?php

namespace App\Traits;

use App\Exceptions\OptionNotFoundException;

trait EnumerationGetTrait
{



    static public function getList()
    {
        $reflect = new \ReflectionClass(__CLASS__);
        $consts = $reflect->getConstants();
        $prefix = $reflect->getProperty('constprefix')->getDefaultValue();
        $label = $reflect->getProperty('labelprefix')->getDefaultValue();
        $output = [];
        foreach (array_keys($consts) as $const)
        {
            if (substr($const,0, strlen($prefix)) === $prefix) {
                $key = $const;
                $suffix = substr($const, strlen($prefix));
                $constval = $reflect->getConstant("{$prefix}{$suffix}");
                $labelval = $reflect->getConstant("{$label}{$suffix}");

                if ($labelval !== false) {
                    $output[$labelval] = $constval;
                }

            }
        }

        return $output;
    }

    public static function get(string $string): string
    {
        $class = static::class;
        $reflect = new \ReflectionClass($class);
        $constants = $reflect->getConstants();

        foreach ($constants as $c => $v) {
            if (strtoupper($v) === strtoupper($string)) {
                return $v;
            }
        }
        throw new OptionNotFoundException("Could not identify value: {$string}");
    }

    public static function simplify(string $string): string
    {
        $string = preg_replace("/\([0-9]+\)/", "", $string);
        $string = trim($string);
        return $string;
    }

}