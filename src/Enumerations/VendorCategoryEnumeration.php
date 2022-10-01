<?php

namespace App\Enumerations;

use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class VendorCategoryEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;

    const CATEGORY_ACCESSORIES = "Accessories / Jewelry";
    const CATEGORY_ARTWORK = "Artwork (Originals / Prints / Commissions)";
    const CATEGORY_BOOKS = "Books / Magazines";
    const CATEGORY_CLOTHING = "Clothing";
    const CATEGORY_COMICS = "Comic Books / Graphic Novels";
    const CATEGORY_FURSUITS = "Fursuits / Costuming";
    const CATEGORY_GAMES = "Games (Board / Video / Tabletop )";
    const CATEGORY_HOMEGOODS = "Home Goods (Mugs / Pillows / etc)";
    const CATEGORY_OTHER = "Other (Please describe below)";
    const CATEGORY_PERFUMES = "Perfumes / Soaps / Sprays";
    const CATEGORY_TOYS = "Plush Animals / Toys";
    const CATEGORY_PRINTING = "Printing / Lamination";
    const CATEGORY_SCULPTURE = "Sculpture / Figurines";

}