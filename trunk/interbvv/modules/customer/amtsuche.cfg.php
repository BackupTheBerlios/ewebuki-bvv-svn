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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////

    $cfg = array(
        "subdir" => "customer",
        "name" => "amtsuche",
        "link" => "aemter",
        "kurz" => "VA",
        "basis" => $pathvars["virtual"]."/dir/my", # crc = -1468826685 *
        "iconpath" => "", # leer: /images/default/; automatik: $pathvars["images"]
        "function" => array(
                    "add" => "",
                    "edit" => "",
                "delete" => "",
                    ),
        "sql_auto"  => array(
                "sql" => "SELECT DISTINCT gdeart, gemeinden_intranet.name as ort, gmn_intranet.name as gemarkung, buort".
                                 " FROM (gmn_gemeinden JOIN gemeinden_intranet ON (gemeinde=gdecode)) JOIN gmn_intranet ON (gmn=gmcode)".
                                " WHERE gmn_intranet.name LIKE '##values##%' OR gemeinde LIKE '##values##%'".
                             " ORDER BY ort, gemarkung",
                          "kz"  => "buort",
                          "art" => "gdeart",
                          "ort" => "ort",
                         "gmkg" => "gemarkung",
                            ),
        "sql_auto_plz"  => array(
                        "sql"   =>  "SELECT DISTINCT akplz, adakz".
                                    " FROM db_adrk JOIN db_adrd ON (akdst=adid)".
                                    " WHERE akplz LIKE '##values##%'".
                                    " ORDER BY akplz",
                          "kz"  => "adakz",
                          "plz" => "akplz",
                          "ort" => "ort",
                            ),
        "sql_selected"  => array(
                        "sql"   =>  "SELECT DISTINCT name, buort, adststelle, adkate, adparent".
                                " FROM (gemeinden_intranet".
                                " JOIN gmn_gemeinden ON (gdecode=gemeinde))".
                                " JOIN db_adrd ON (buort=adakz)".
                                " WHERE name LIKE '##values##%'",
                            ),
        "db"    => array(
                    "main"   => array(
                                    "entries" => "db_adrd",
                                    "id"      => "adid",
                                    "parent"  => "adparent",
                                    "key"     => "adakz",
                                    "value"   => "buort",
                                    "name"    => "adststelle",
                               "kategorie"    => "adkate",
                                    "str"     => "adstr",
                                    "ort"     => "adort",
                                    "plz"     => "adplz",
                             "zusatz_bez"     => "Aussenstelle"
                                    )
                        ),
        "right" => "",
    );

    // * tipp: fuer das einfache modul muss der wert $cfg["basis"] natuerlich
    // "/my" lauten. es funktioniert im beispiel nur ohne aenderung, da das
    // einfache script $cfg["basis] nicht nutzt.

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
