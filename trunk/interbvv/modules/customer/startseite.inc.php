<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2008 Werner Ammon ( wa<at>chaos.de )

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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

        $ausgaben["form_error"] = "";

        // weiterleitung zur login-seite
        if ( $_SERVER["REDIRECT_URL"] == "/" && ( $_SERVER["SERVER_NAME"] == "internetredakteur.bvv.bayern.de" || $_SERVER["SERVER_NAME"] == "lvg-entw-intertest") ) {
            header("Location: /login.html");
        }

        // skripte einbinden
        require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
        require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

        // artikel-bestandteile
        $tags["titel"] = "H1";
        $tags["teaser"] = "P=teaser";
        $tags["image"] = "IMG=";

        // artikelausgabe
        $dataloop["list"] = show_blog("/aktuell/archiv",$tags,"admin","0,4","/aktuell/archiv");
        foreach ( $dataloop["list"] as $key => $value ) {
            $dataloop["list"][$key]["teaser_org"] = tagremove($dataloop["list"][$key]["teaser_org"]);
        }

        // zufalls-banner
        if ( is_array($cfg["startseite"]["pics"]) ) {
            $index = mt_rand( 0 , (count($cfg["startseite"]["pics"])-1) );
            if ( file_exists($pathvars["fileroot"]."images/html/".$cfg["startseite"]["pics"][$index]) ) {
                $hidedata["startbanner"]["src"] = $pathvars["images"].$cfg["startseite"]["pics"][$index];
            }
        }
        header("HTTP/1.0 200 OK");

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
