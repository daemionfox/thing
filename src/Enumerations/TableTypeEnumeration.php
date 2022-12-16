<?php

namespace App\Enumerations;

use App\Exceptions\OptionNotFoundException;
use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class TableTypeEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;
    private $constprefix = 'TABLETYPE_';
    private $labelprefix = 'TABLETYPE_';


    const TABLETYPE_HALF = "Half Table";
    const TABLETYPE_SINGLE = "Single Table";
    const TABLETYPE_SINGLEHALF = "Single+Half Table";
    const TABLETYPE_DOUBLE = "Double Table";
    const TABLETYPE_TRIPLE = "Triple Table";
    const TABLETYPE_QUAD = "Quad Table";
    const TABLETYPE_QUINT = "Quint Table";
    const TABLETYPE_SMALLBOOTH = "Small Booth";
    const TABLETYPE_LARGEBOOTH = "Large Booth";
    const TABLETYPE_ENDCAP = "EndCap";

    const TABLEAMOUNT_HALF = 99;
    const TABLEAMOUNT_SINGLE = 200;
    const TABLEAMOUNT_SINGLEHALF = 320;
    const TABLEAMOUNT_DOUBLE = 450;
    const TABLEAMOUNT_TRIPLE = 700;
    const TABLEAMOUNT_QUAD = 950;
    const TABLEAMOUNT_QUINT = 1300;
    const TABLEAMOUNT_SMALLBOOTH = 0;
    const TABLEAMOUNT_LARGEBOOTH = 1600;
    const TABLEAMOUNT_ENDCAP = 100;

    const TABLEASSTMAX_HALF = 1;
    const TABLEASSTMAX_SINGLE = 1;
    const TABLEASSTMAX_SINGLEHALF = 2;
    const TABLEASSTMAX_DOUBLE = 3;
    const TABLEASSTMAX_TRIPLE = 4;
    const TABLEASSTMAX_QUAD = 5;
    const TABLEASSTMAX_QUINT = 6;
    const TABLEASSTMAX_SMALLBOOTH = 6;
    const TABLEASSTMAX_LARGEBOOTH = 6;
    const TABLEASSTMAX_ENDCAP = 0;
    
    const TABLESIZE_HALF = .5;
    const TABLESIZE_SINGLE = 1;
    const TABLESIZE_SINGLEHALF = 1.5;
    const TABLESIZE_DOUBLE = 2;
    const TABLESIZE_TRIPLE = 3;
    const TABLESIZE_QUAD = 4;
    const TABLESIZE_QUINT = 5;
    const TABLESIZE_SMALLBOOTH = "Small Booth";
    const TABLESIZE_LARGEBOOTH = "Large Booth";
    const TABLESIZE_ENDCAP = 0;
       
    public static function getSize($type)
    {
        switch(strtoupper($type)) {
            case strtoupper(self::TABLETYPE_HALF):
                return self::TABLESIZE_HALF;
            case strtoupper(self::TABLETYPE_SINGLE):
                return self::TABLESIZE_SINGLE;
            case strtoupper(self::TABLETYPE_SINGLEHALF):
                return self::TABLESIZE_SINGLEHALF;
            case strtoupper(self::TABLETYPE_DOUBLE):
                return self::TABLESIZE_DOUBLE;
            case strtoupper(self::TABLETYPE_TRIPLE):
                return self::TABLESIZE_TRIPLE;
            case strtoupper(self::TABLETYPE_QUAD):
                return self::TABLESIZE_QUAD;
            case strtoupper(self::TABLETYPE_QUINT):
                return self::TABLESIZE_QUINT;
            case strtoupper(self::TABLETYPE_SMALLBOOTH):
                return self::TABLESIZE_SMALLBOOTH;
            case strtoupper(self::TABLETYPE_LARGEBOOTH):
                return self::TABLESIZE_LARGEBOOTH;
        }
        return 0;
    }
    /**
     * @param $type
     * @return int
     * @throws OptionNotFoundException
     */
    public static function getTableAmount($type) {
        switch ($type) {
            case self::TABLETYPE_HALF:
                return self::TABLEAMOUNT_HALF;
            case self::TABLETYPE_SINGLE:
                return self::TABLEAMOUNT_SINGLE;
            case self::TABLETYPE_SINGLEHALF:
                return self::TABLEAMOUNT_SINGLEHALF;
            case self::TABLETYPE_DOUBLE:
                return self::TABLEAMOUNT_DOUBLE;
            case self::TABLETYPE_TRIPLE:
                return self::TABLEAMOUNT_TRIPLE;
            case self::TABLETYPE_QUAD:
                return self::TABLEAMOUNT_QUAD;
            case self::TABLETYPE_QUINT:
                return self::TABLEAMOUNT_QUINT;
            case self::TABLETYPE_SMALLBOOTH:
                return self::TABLEAMOUNT_SMALLBOOTH;
            case self::TABLETYPE_LARGEBOOTH:
                return self::TABLEAMOUNT_LARGEBOOTH;
            case self::TABLETYPE_ENDCAP:
                return self::TABLEAMOUNT_ENDCAP;
        }
        throw new OptionNotFoundException("Could not find option for {$type}");
    }

    /**
     * @param $type
     * @return int
     * @throws OptionNotFoundException
     */
    public static function getMaxAssistants($type) {
        switch ($type) {
            case self::TABLETYPE_HALF:
                return self::TABLEASSTMAX_HALF;
            case self::TABLETYPE_SINGLE:
                return self::TABLEASSTMAX_SINGLE;
            case self::TABLETYPE_SINGLEHALF:
                return self::TABLEASSTMAX_SINGLEHALF;
            case self::TABLETYPE_DOUBLE:
                return self::TABLEASSTMAX_DOUBLE;
            case self::TABLETYPE_TRIPLE:
                return self::TABLEASSTMAX_TRIPLE;
            case self::TABLETYPE_QUAD:
                return self::TABLEASSTMAX_QUAD;
            case self::TABLETYPE_QUINT:
                return self::TABLEASSTMAX_QUINT;
            case self::TABLETYPE_SMALLBOOTH:
                return self::TABLEASSTMAX_SMALLBOOTH;
            case self::TABLETYPE_LARGEBOOTH:
                return self::TABLEASSTMAX_LARGEBOOTH;
            case self::TABLETYPE_ENDCAP:
                return self::TABLEASSTMAX_ENDCAP;
        }
        throw new OptionNotFoundException("Could not find option for {$type}");
    }

}