<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class MessageIconEnumeration extends AbstractEnumeration
{

    use EnumerationGetTrait;

    private $constprefix = 'TYPE_';
    private $labelprefix = 'TYPE_';

    const TYPE_FEATURE = 'Feature';
    const ICON_FEATURE = 'fa-wrench';

    const TYPE_BUG = 'Bug';
    const ICON_BUG = 'fa-bug';

    const TYPE_ANNOUNCEMENT = 'Announcement';
    const ICON_ANNOUNCEMENT = 'fa-radio';

    const TYPE_NEWS = 'News';
    const ICON_NEWS = 'fa-newspaper';

    const ICON_TURKEY = 'fa-turkey';
    
    public static function isType(string $type): bool
    {
        switch (strtoupper($type)) {
            case strtoupper(self::TYPE_FEATURE):
            case strtoupper(self::TYPE_BUG):
            case strtoupper(self::TYPE_ANNOUNCEMENT):
            case strtoupper(self::TYPE_NEWS):
                return true;
        }
        return false;
    }

    public static function getIcon(string|null $type): string
    {
        switch (strtoupper($type)) {
            case strtoupper(self::TYPE_FEATURE):
                return self::ICON_FEATURE;
            case strtoupper(self::TYPE_BUG):
                return self::ICON_BUG;
            case strtoupper(self::TYPE_ANNOUNCEMENT):
                return self::ICON_ANNOUNCEMENT;
            case strtoupper(self::TYPE_NEWS):
                return self::ICON_NEWS;

        }
        return self::ICON_TURKEY;
    }


    public static function getTypes(): array
    {
        return [
            ucfirst(strtolower(self::TYPE_ANNOUNCEMENT)),
            ucfirst(strtolower(self::TYPE_BUG)),
            ucfirst(strtolower(self::TYPE_FEATURE)),
            ucfirst(strtolower(self::TYPE_NEWS)),
        ];

    }

}