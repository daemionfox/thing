<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class VendorCategoryEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'CATEGORY_';
    private $labelprefix = 'CATEGORY_';

    const CATEGORY_ADULT = "Adult Novelties";
    const CATEGORY_ACCESSORIES = "Small Accessories (Pins, Keychains, Buttons, Charms, Lanyards, Jewelry)";
    const CATEGORY_ARTWORK = "Commissions, Sketches, and Badges";
    const CATEGORY_BOOKS = "Print Media (Books, Calendars, Prints)";
    const CATEGORY_CLOTHING = "Clothing & Clothing Accessories";
    const CATEGORY_COMICS = "Comic Books / Graphic Novels";
    const CATEGORY_FURSUITS = "Fursuit \/ Costume Accessories and Props";
    const CATEGORY_GAMES = "Games (Tabletop Games, Dice, Figurines, Playmats, Video Games)";
    const CATEGORY_HOMEGOODS = "Home Goods (Mugs / Pillows / etc)";
    const CATEGORY_OTHER = "Other (Please describe below)";
    const CATEGORY_PERFUMES = "Soaps, Perfumes, and Bathroom Accessories";
    const CATEGORY_TOYS = "Plushies & Toys";
    const CATEGORY_PRINTING = "Printing / Lamination";
    const CATEGORY_SCULPTURE = "Sculpture / Figurines";
    const CATEGORY_LEATHER = "Collars, Cuffs, Harnesses, and Belts";
    const CATEGORY_ELECTRONICS = "Electronic Accessories (LED Badges)";
    const CATEGORY_DECOR = "Interior Decor (Posters, Artwork, Sculptures)";


    const COLOR_ACCESSORIES = "rgb(192, 75, 43)";
    const COLOR_ARTWORK = "rgb(22, 160, 133)";
    const COLOR_BOOKS = "rgb(231, 76, 60)";
    const COLOR_CLOTHING = "rgb(39, 174, 96)";
    const COLOR_COMICS = "rgb(155, 89, 182)";
    const COLOR_FURSUITS = "rgb(46, 204, 113)";
    const COLOR_GAMES = "rgb(142, 68, 173)";
    const COLOR_HOMEGOODS = "rgb(241, 196, 15)";
    const COLOR_OTHER = "rgb(41, 128, 185)";
    const COLOR_PERFUMES = "rgb(243, 156, 18)";
    const COLOR_TOYS = "rgb(52, 152, 219)";
    const COLOR_PRINTING = "rgb(230, 126, 34)";
    const COLOR_SCULPTURE = "rgb(26, 188, 156)";
    const COLOR_ADULT = "rgb(227,28,121)";
    const COLOR_LEATHER = "rgb(183,119,41)";
    const COLOR_ELECTRONICS = "rgb(229,225,230)";
    const COLOR_DECOR = "rgb(68,215,168)";


    const COLOR_DEFAULT = "rgb(127,127,127)";


    public static function getColor($category)
    {
        switch ($category) {
            case self::CATEGORY_ACCESSORIES:
                return self::COLOR_ACCESSORIES;
            case self::CATEGORY_ARTWORK:
                return self::COLOR_ARTWORK;
            case self::CATEGORY_BOOKS:
                return self::COLOR_BOOKS;
            case self::CATEGORY_CLOTHING:
                return self::COLOR_CLOTHING;
            case self::CATEGORY_COMICS:
                return self::COLOR_COMICS;
            case self::CATEGORY_FURSUITS:
                return self::COLOR_FURSUITS;
            case self::CATEGORY_GAMES:
                return self::COLOR_GAMES;
            case self::CATEGORY_HOMEGOODS:
                return self::COLOR_HOMEGOODS;
            case self::CATEGORY_OTHER:
                return self::COLOR_OTHER;
            case self::CATEGORY_PERFUMES:
                return self::COLOR_PERFUMES;
            case self::CATEGORY_TOYS:
                return self::COLOR_TOYS;
            case self::CATEGORY_PRINTING:
                return self::COLOR_PRINTING;
            case self::CATEGORY_SCULPTURE:
                return self::COLOR_SCULPTURE;
        }
        return self::COLOR_DEFAULT;
    }
    


}