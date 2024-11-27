<?php

namespace App\Enumerations;


use App\Traits\EnumerationGetTrait;
use Eloquent\Enumeration\AbstractEnumeration;

class RegFoxHeaderEnumeration extends VendorMapEnumeration
{
/*
Phone Number

Products or Services Sold
Dealership Setup Photographs
Website
Twitter
Seating Requests
Neighbor Requests
Other Requests/Info
*/


    const VENDOR_REGID = "Registrant ID";
    const VENDOR_DEALERAPPSTATUS = "Dealer Application Status";
    const VENDOR_DEALERSTATUS = "Dealer Status";
    const VENDOR_DEALERSTATUSAMOUNT = "Dealer Status (Amount)";
    const VENDOR_FIRSTNAME = "Name (As shown on registrant's ID) (First Name)";
    const VENDOR_LASTNAME = "Name (As shown on registrant's ID) (Last Name)";
    const VENDOR_MIDDLEINIT = "Name (As shown on registrant's ID) (M.I.)";
    const VENDOR_PREFERREDNAME = "Preferred Name";
    const VENDOR_BADGENAME = "Badge Name";
    const VENDOR_EMAIL = "Email";
    const VENDOR_DEALERNAME = "Dealership Name/Title";
    const VENDOR_TAXID = "Pennsylvania Tax ID (Revenue ID from PA-100 form)";
    const VENDOR_DEALERRATING = "Dealership Rating";
    const VENDOR_MATUREDEALERS = 'Mature Dealers Section (Are you interested in being placed in the Mature Dealers Section?)';

    const VENDOR_PRIMARYCATEGORY = "Primary Dealership Category";
    const VENDOR_SECONDARYCATEGORY_ACCESSORIES = "Secondary Dealership Category (Select all that apply) (Accessories / Jewelry)";
    const VENDOR_SECONDARYCATEGORY_ARTWORK = "Secondary Dealership Category (Select all that apply) (Artwork (Originals / Prints / Commissions))";
    const VENDOR_SECONDARYCATEGORY_BOOKS = "Secondary Dealership Category (Select all that apply) (Books / Magazines)";
    const VENDOR_SECONDARYCATEGORY_CLOTHING = "Secondary Dealership Category (Select all that apply) (Clothing)";
    const VENDOR_SECONDARYCATEGORY_COMICS = "Secondary Dealership Category (Select all that apply) (Comic Books / Graphic Novels)";
    const VENDOR_SECONDARYCATEGORY_FURSUITS = "Secondary Dealership Category (Select all that apply) (Fursuits / Costuming)";
    const VENDOR_SECONDARYCATEGORY_GAMES = "Secondary Dealership Category (Select all that apply) (Games (Board / Video / Tabletop ))";
    const VENDOR_SECONDARYCATEGORY_HOMEGOODS = "Secondary Dealership Category (Select all that apply) (Home Goods (Mugs / Pillows / etc))";
    const VENDOR_SECONDARYCATEGORY_OTHER = "Secondary Dealership Category (Select all that apply) (Other (please describe below))";
    const VENDOR_SECONDARYCATEGORY_PERFUMES = "Secondary Dealership Category (Select all that apply) (Perfumes / Soaps / Sprays)";
    const VENDOR_SECONDARYCATEGORY_TOYS = "Secondary Dealership Category (Select all that apply) (Plush Animals / Toys)";
    const VENDOR_SECONDARYCATEGORY_PRINTING = "Secondary Dealership Category (Select all that apply) (Printing / Lamination)";
    const VENDOR_SECONDARYCATEGORY_SCULPTURE = "Secondary Dealership Category (Select all that apply) (Sculpture / Figurines)";
    const VENDOR_PRODUCTSSOLD = "Products or Services Sold";
    const VENDOR_DEALERPHOTOS = "Dealership Setup Photographs";
    const VENDOR_WEBSITE = "Website";
    const VENDOR_TWITTER = "Twitter";
    const VENDOR_SEATINGREQUESTS = "Seating Requests";
    const VENDOR_NEIGHBORREQUESTS = "Neighbor Requests";
    const VENDOR_OTHERREQUESTS = "Other Requests/Info";
    const VENDOR_TABLE_HALF = "Half Table";
    const VENDOR_TABLE_SINGLE = "Single Table";
    const VENDOR_TABLE_SINGLEHALF = "Single+Half Table";
    const VENDOR_TABLE_DOUBLE = "Double Table";
    const VENDOR_TABLE_TRIPLE = "Triple Table";
    const VENDOR_TABLE_QUAD = "Quad Table";
    const VENDOR_TABLE_QUINT = "Quint Table";
    const VENDOR_TABLE_SEXTUP = "Sextuple Table With Endcap";
    const VENDOR_TABLE_SMALLBOOTH = "Small Booth";
    const VENDOR_TABLE_LARGEBOOTH = "Large Booth";
    const VENDOR_TABLE_ENDCAP = "EndCap";
    const VENDOR_TABLE_AMOUNT_HALF = "Half Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_SINGLE = "Single Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_SINGLEHALF = "Single+Half Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_DOUBLE = "Double Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_TRIPLE = "Triple Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_QUAD = "Quad Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_QUINT = "Quint Table ($ Amount)";
    const VENDOR_TABLE_AMOUNT_SEXTUP = "Sextuple Table With Endcap ($ Amount)";
    const VENDOR_TABLE_AMOUNT_SMALLBOOTH = "Small Booth ($ Amount)";
    const VENDOR_TABLE_AMOUNT_LARGEBOOTH = "Large Booth ($ Amount)";
    const VENDOR_TABLE_AMOUNT_ENDCAP = "EndCap (Amount)";

    const VENDOR_ASSISTANT_SINGLE = "Pay for up to 1 dealer assistant (Half and Single Table)";
    const VENDOR_ASSISTANT_SINGLEHALF = "Pay for up to 2 dealer assistants (Single+Half Table)";
    const VENDOR_ASSISTANT_DOUBLE = "Pay for up to 3 dealer assistants (Double Table)";
    const VENDOR_ASSISTANT_TRIPLE = "Pay for up to 4 dealer assistants (Triple Table)";
    const VENDOR_ASSISTANT_QUAD = "Pay for up to 5 dealer assistants (Quad Table)";
    const VENDOR_ASSISTANT_QUINTBOOTH = "Pay for up to 6 dealer assistants (Quint tables and booths)";
    const VENDOR_ASSISTANT_AMOUNT_SINGLE = "Pay for up to 1 dealer assistant (Half and Single Table) (Amount)";
    const VENDOR_ASSISTANT_AMOUNT_SINGLEHALF = "Pay for up to 2 dealer assistants (Single+Half Table) (Amount)";
    const VENDOR_ASSISTANT_AMOUNT_DOUBLE = "Pay for up to 3 dealer assistants (Double Table) (Amount)";
    const VENDOR_ASSISTANT_AMOUNT_TRIPLE = "Pay for up to 4 dealer assistants (Triple Table) (Amount)";
    const VENDOR_ASSISTANT_AMOUNT_QUAD = "Pay for up to 5 dealer assistants (Quad Table) (Amount)";
    const VENDOR_ASSISTANT_AMOUNT_QUINTBOOTH = "Pay for up to 6 dealer assistants (Quint tables and booths) (Amount)";


}