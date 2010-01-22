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

    $cfg["aemter"] = array(
           "subdir" => "customer",
             "name" => "aemter",
            "basis" => $pathvars["virtual"]."/dir/my", # crc = -1468826685 *
         "iconpath" => "", # leer: /images/default/; automatik: $pathvars["images"]
            "color" => array(
                       "a" => "#f4f4f4",
                       "b" => "#ffffff",
                       ),
         "function" => array(
                      "index" => "",
                      "artikel" => "",
                      "presse" => "",
                      "termine" => "",
                     "standort" => "",
                   "amtsbezirk" => "",
                   "info" => "",
                   "ansprech" => "",
                   "amtschronik" => "",
                   "kontakt" => "",
                   "va-aktuell" => "",
                   "va-archiv" => "",
                   "va-termine" => "",
                   "va-presse" => "",
                       ),
               "db" => array(
                     "dst" => array(
                          "entries" => "db_aemter",
                              "key" => "adid",
                              "amt" => "adststelle",
                              "akz" => "adakz",
                              "str" => "adstr",
                              "plz" => "adplz",
                              "ort" => "adort",
                              "tel" => "adtelver",
                              "fax" => "adfax",
                            "email" => "ademail",
                           "parent" => "adparent",
                       "rechtswert" => "georef_rw",
                         "hochwert" => "georef_hw",
                         "oeffnung" => "ad_oeffnung",
                       "behinderte" => "ad_behinderte",
                         "internet" => "adinternet",
                  "ansprechpartner" => "ansprechpartner",
                             "kate" => "adkate",
                        "kategorie" => "adkate",
                            "order" => "sort, label",
                            "rows"  => 4,
                     ),
                     "kate" => array(
                          "entries" => "db_adrd_kate",
                              "key" => "katid",
                             "kate" => "kat_lang",
                     ),
              ),
         "sub_menu" => array(
                 "index" => array("index.html","&Uuml;bersicht"),
               "kontakt" => array("kontakt.html","Kontakt"),
              "standort" => array("standort.html","Standort"),
              "ansprech" => array("ansprech.html","Ansprechpartner"),
            "amtsbezirk" => array("amtsbezirk.html","Amtsbezirk"),
                  "info" => array("info.html","Behindertenzugang"),
              ),
              "wms" => array(
                   "url" => "http://www.geodaten.bayern.de/ogc/getogc.cgi?REQUEST=GetMap&SRS=EPSG:31468&VERSION=1.1.1&FORMAT=image/png&Layers=##LAYERS##&BBOX=##BBOX##&WIDTH=##WIDTH##&HEIGHT=##HEIGHT##&STYLES=",
                "layers" => "DOP",
                 "width" => "540",
                "height" => "120",
                     "m" => 2        // meter pro pixel (massstabszahl)
              ),
            "email"     => -1,
            "right" => "",
    );


    $dataloop["headnavi"] = array(
                                array(
                                         "url" => $pathvars["virtual"]."/index.html",
                                        "desc" => "Startseite",
                                       "title" => "Startseite des Amtes"
                                ),
//                                 array(
//                                          "url" => "http://www.vermessung.bayern.de".$pathvars["virtual"]."/index.html",
//                                         "desc" => "BVV-Startseite",
//                                        "title" => "Startseite der Bayerischen Vermessungsverwaltung"
//                                 ),
                                array(
                                        "url" => $pathvars["virtual"]."/kontakt.html",
                                        "desc" => "Kontakt",
                                       "title" => "Kontakt"
                                ),
                            );

    // * tipp: fuer das einfache modul muss der wert $cfg["basis"] natuerlich
    // "/my" lauten. es funktioniert im beispiel nur ohne aenderung, da das
    // einfache script $cfg["basis] nicht nutzt.

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
