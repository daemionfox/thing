<?php

namespace App\Enumerations;


use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class RegFoxHeaderEnumeration extends AbstractEnumeration
{
    use EnumerationGetTrait;

    private $constprefix = 'REGFOX_';
    private $labelprefix = 'REGFOX_';

    const REGFOX_REGID = "Registrant ID";
    const REGFOX_DEALERAPPSTATUS = "Dealer Application Status";
    const REGFOX_DEALERSTATUS = "Dealer Status";
    const REGFOX_DEALERSTATUSAMOUNT = "Dealer Status (Amount)";
    const REGFOX_FIRSTNAME = "Name (As shown on your ID) (First Name)";
    const REGFOX_LASTNAME = "Name (As shown on your ID) (Last Name)";
    const REGFOX_MIDDLEINIT = "Name (As shown on your ID) (M.I.)";
    const REGFOX_PREFERREDNAME = "Preferred Name";
    const REGFOX_BADGENAME = "Badge Name";
    const REGFOX_EMAIL = "Email";
    const REGFOX_DEALERNAME = "Dealership Name/Title";
    const REGFOX_TAXID = "Pennsylvania Tax ID (Revenue ID from PA-100 form)";
    const REGFOX_DEALERRATING = "Dealership Rating";
    const REGFOX_PRIMARYCATEGORY = "Primary Dealership Category";
    const REGFOX_SECONDARYCATEGORY_ACCESSORIES = "Secondary Dealership Category (Select all that apply) (Accessories / Jewelry)";
    const REGFOX_SECONDARYCATEGORY_ARTWORK = "Secondary Dealership Category (Select all that apply) (Artwork (Originals / Prints / Commissions))";
    const REGFOX_SECONDARYCATEGORY_BOOKS = "Secondary Dealership Category (Select all that apply) (Books / Magazines)";
    const REGFOX_SECONDARYCATEGORY_CLOTHING = "Secondary Dealership Category (Select all that apply) (Clothing)";
    const REGFOX_SECONDARYCATEGORY_COMICS = "Secondary Dealership Category (Select all that apply) (Comic Books / Graphic Novels)";
    const REGFOX_SECONDARYCATEGORY_FURSUITS = "Secondary Dealership Category (Select all that apply) (Fursuits / Costuming)";
    const REGFOX_SECONDARYCATEGORY_GAMES = "Secondary Dealership Category (Select all that apply) (Games (Board / Video / Tabletop ))";
    const REGFOX_SECONDARYCATEGORY_HOMEGOODS = "Secondary Dealership Category (Select all that apply) (Home Goods (Mugs / Pillows / etc))";
    const REGFOX_SECONDARYCATEGORY_OTHER = "Secondary Dealership Category (Select all that apply) (Other (please describe below))";
    const REGFOX_SECONDARYCATEGORY_PERFUMES = "Secondary Dealership Category (Select all that apply) (Perfumes / Soaps / Sprays)";
    const REGFOX_SECONDARYCATEGORY_TOYS = "Secondary Dealership Category (Select all that apply) (Plush Animals / Toys)";
    const REGFOX_SECONDARYCATEGORY_PRINTING = "Secondary Dealership Category (Select all that apply) (Printing / Lamination)";
    const REGFOX_SECONDARYCATEGORY_SCULPTURE = "Secondary Dealership Category (Select all that apply) (Sculpture / Figurines)";
    const REGFOX_PRODUCTSSOLD = "Products or Services Sold";
    const REGFOX_DEALERPHOTOS = "Dealership Setup Photographs";
    const REGFOX_WEBSITE = "Website";
    const REGFOX_TWITTER = "Twitter";
    const REGFOX_SEATINGREQUESTS = "Seating Requests";
    const REGFOX_NEIGHBORREQUESTS = "Neighbor Requests";
    const REGFOX_OTHERREQUESTS = "Other Requests/Info";
    const REGFOX_TABLE_HALF = "Half Table";
    const REGFOX_TABLE_SINGLE = "Single Table";
    const REGFOX_TABLE_SINGLEHALF = "Single+Half Table";
    const REGFOX_TABLE_DOUBLE = "Double Table";
    const REGFOX_TABLE_TRIPLE = "Triple Table";
    const REGFOX_TABLE_QUAD = "Quad Table";
    const REGFOX_TABLE_QUINT = "Quint Table";
    const REGFOX_TABLE_SMALLBOOTH = "Small Booth";
    const REGFOX_TABLE_LARGEBOOTH = "Large Booth";
    const REGFOX_TABLE_ENDCAP = "EndCap";
    const REGFOX_TABLE_AMOUNT_HALF = "Half Table (Amount)";
    const REGFOX_TABLE_AMOUNT_SINGLE = "Single Table (Amount)";
    const REGFOX_TABLE_AMOUNT_SINGLEHALF = "Single+Half Table (Amount)";
    const REGFOX_TABLE_AMOUNT_DOUBLE = "Double Table (Amount)";
    const REGFOX_TABLE_AMOUNT_TRIPLE = "Triple Table (Amount)";
    const REGFOX_TABLE_AMOUNT_QUAD = "Quad Table (Amount)";
    const REGFOX_TABLE_AMOUNT_QUINT = "Quint Table (Amount)";
    const REGFOX_TABLE_AMOUNT_SMALLBOOTH = "Small Booth (Amount)";
    const REGFOX_TABLE_AMOUNT_LARGEBOOTH = "Large Booth (Amount)";
    const REGFOX_TABLE_AMOUNT_ENDCAP = "EndCap (Amount)";
    const REGFOX_ASSISTANT_SINGLE = "Pay for up to 1 dealer assistant (Half and Single Table)";
    const REGFOX_ASSISTANT_SINGLEHALF = "Pay for up to 2 dealer assistants (Single+Half Table)";
    const REGFOX_ASSISTANT_DOUBLE = "Pay for up to 3 dealer assistants (Double Table)";
    const REGFOX_ASSISTANT_TRIPLE = "Pay for up to 4 dealer assistants (Triple Table)";
    const REGFOX_ASSISTANT_QUAD = "Pay for up to 5 dealer assistants (Quad Table)";
    const REGFOX_ASSISTANT_QUINTBOOTH = "Pay for up to 6 dealer assistants (Quint tables and booths)";
    const REGFOX_ASSISTANT_AMOUNT_SINGLE = "Pay for up to 1 dealer assistant (Half and Single Table) (Amount)";
    const REGFOX_ASSISTANT_AMOUNT_SINGLEHALF = "Pay for up to 2 dealer assistants (Single+Half Table) (Amount)";
    const REGFOX_ASSISTANT_AMOUNT_DOUBLE = "Pay for up to 3 dealer assistants (Double Table) (Amount)";
    const REGFOX_ASSISTANT_AMOUNT_TRIPLE = "Pay for up to 4 dealer assistants (Triple Table) (Amount)";
    const REGFOX_ASSISTANT_AMOUNT_QUAD = "Pay for up to 5 dealer assistants (Quad Table) (Amount)";
    const REGFOX_ASSISTANT_AMOUNT_QUINTBOOTH = "Pay for up to 6 dealer assistants (Quint tables and booths) (Amount)";


}