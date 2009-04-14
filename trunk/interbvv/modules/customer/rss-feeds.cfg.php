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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////

    $cfg["rss-feeds"] = array(
           "subdir" => "",
             "name" => "rss",
          "default" => array(
                  "artikel" => array(
                            "title" => "Aktuelle Meldungen",
                             "href" => "/aktuell/archiv/rss.html",
                               ),
                   "presse" => array(
                            "title" => "Pressemitteilungen",
                             "href" => "/aktuell/presse/rss.html",
                               ),
                  "termine" => array(
                            "title" => "Termine",
                             "href" => "/aktuell/termine/rss.html",
                               ),
                        ),
    );

    // * tipp: fuer das einfache modul muss der wert $cfg["basis"] natuerlich
    // "/my" lauten. es funktioniert im beispiel nur ohne aenderung, da das
    // einfache script $cfg["basis] nicht nutzt.

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
