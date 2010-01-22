<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer.cfg.php 1131 2007-12-12 08:45:50Z chaot $";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2007 Werner Ammon ( wa<at>chaos.de )

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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////

    $cfg["sapos_get_data"] = array(
           "subdir" => "customer",
             "name" => "leer",
            "basis" => $pathvars["virtual"]."/dir/my", # crc = -1468826685 *
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
                "stationen" => array(
                          "entries" => "afis.INTERNETVIEW",
                     ),
              ),
//           "oracle" => array(        // alt
//                 "user" => "service_saposinternet",
//                 "pass" => 'UK8T77$ERMLP3K',
//                 "host" => "10.2.140.40",
//                   "db" => "ora1prod",
//                 "port" => "1526"
//               ),
           "oracle" => array(       // neu, ab 10.07.2009
                "user" => "service_saposinternet",
                "pass" => 'UK8T77$ERMLP3K',
                "host" => "10.192.120.53",
                  "db" => "ora1prod",
                "port" => "1523"
              ),
              "allowed_ip" => array("10.248.65.23","10.248.65.7","10.192.101.31"),
            "right" => "",
    );

    // * tipp: fuer das einfache modul muss der wert $cfg["basis"] natuerlich
    // "/my" lauten. es funktioniert im beispiel nur ohne aenderung, da das
    // einfache script $cfg["basis] nicht nutzt.

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
