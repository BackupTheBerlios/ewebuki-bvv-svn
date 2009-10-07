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

    if ( preg_match("/^\/aemter/",$environment["ebene"]."/".$environment["kategorie"]) || strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") ) {
        foreach ( $cfg["rss-feeds"]["default"] as $key=>$value ) {
            $cfg["rss-feeds"]["default"][$key]["title"] = $ausgaben["amt"].": ".$value["title"];
            $cfg["rss-feeds"]["default"][$key]["href"] = $value["href"]."?kategorie=/aemter/".$current_akz;
        }
    } elseif ( !preg_match("/^\/aktuell/",$environment["ebene"]."/".$environment["kategorie"]) ) {
        $cfg["rss-feeds"]["default"][] = array(
                            "title" => "RSS-Feed: ".$ausgaben["pagetitle"],
                             "href" => $environment["ebene"]."/".$environment["kategorie"]."/rss.html",
                            );
    }
    $dataloop["rss"] = $cfg["rss-feeds"]["default"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
