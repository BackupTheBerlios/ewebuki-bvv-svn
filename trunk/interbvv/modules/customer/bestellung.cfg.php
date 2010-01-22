<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer.cfg.php,v 1.5 2006/09/22 06:16:23 chaot Exp $";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2006 Werner Ammon ( wa<at>chaos.de )

    This script is a part of eWeBuKi

    eWeBuKi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    eWeBuKi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with eWeBuKi; If you did not, you may download a copy at:

    URL:  http://www.gnu.org/licenses/gpl.txt

    You may also request a copy from:

    Free Software Foundation, Inc.
    59 Temple Place, Suite 330
    Boston, MA 02111-1307
    USA

    You may contact the author/development team at:

    Chaos Networks
    c/o Werner Ammon
    Lerchenstr. 11c

    86343 KÃ¶nigsbrunn

    URL: http://www.chaos.de
*/
////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////

    $cfg["bestellung"] = array(
           "subdir" => "customer",
             "name" => "bestellung",
            "basis" => $pathvars["virtual"]."/bestellung", # crc = -1468826685 *
         "iconpath" => "", # leer: /images/default/; automatik: $pathvars["images"]
            "color" => array(
                        "a" => "#eeeeee",
                        "b" => "#ffffff",
                       ),
         "function" => array(
                      "add" => array(""),
                     "edit" => array(""),
                   "delete" => array(""),
                  "details" => array(""),
              #"edit,shared" => array("shared1", "shared2"),
              #"edit,global" => array("global1", "global2"),
                       ),
               "db" => array(

              ),
               "order_fields" => array(
                    "top10"     => "DVD TOP 10",
                    "top50"     => "DVD TOP 50",
                    "luftnb"   => "Luftbilder von Niederbayern (Aufnahme 2007)",
                    "luftop"   => "Luftbilder der Oberpfalz (Aufnahme 2007)",
                    "top25"     => "Amtliche Topographische Karte 1:25 000",
                    "histk"    => "Historische Karten (Urpositionsbl�tter)",
                    "ok10"      => "Digitale Ortskarte 1:10 000",
              ),
               "data_fields" => array(
                    "surname"   => array("Name",-1),
                    "forename"  => array("Vorname",-1),
                    "strasse"   => array("Strasse",-1),
                    "plz"       => array("PLZ",-1),
                    "ort"       => array("Ort",-1),
                    "land"      => array("Land"),
                    "email"     => array("E-Mail"),
                    "tel"       => array("Telefon"),
              ),
                "email"     => array(
//                     "owner"     => "top50@lvg.bayern.de",
                    "owner"     => "krom@va-a.bayern.de",
                    "subject"   => "TEST-Bestellung Online-Bestellung DVD Top10 Bayern, DVD Top50 Bayern V5, Top Maps",
                    "text"      => "Online-Bestellung\n==========================\nDie folgende Bestellung wurde auf dem Geodatenserver\n (http://www.vermessung.bayern.de)\ndurch einen Kunden abgesendet:",
                  )
    );

    // * tipp: fuer das einfache modul muss der wert $cfg["basis"] natuerlich
    // "/my" lauten. es funktioniert im beispiel nur ohne aenderung, da das
    // einfache script $cfg["basis] nicht nutzt.

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
