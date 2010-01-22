<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: headnavi.cfg.php,v 1.5 2006/09/22 06:16:23 chaot Exp $";
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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
    $dataloop["headnavi"] = array(
//                                 array(
//                                         "url" => "index.html",
//                                         "desc" => "Startseite"),
                                array(
                                         "url" => $pathvars["virtual"]."/index.html",
                                        "desc" => "Startseite",
                                       "title" => "Startseite: http://www.geodaten.online.de"
                                ),
                                array(
                                         "url" => $pathvars["virtual"]."/service/download.html",
                                        "desc" => "Download",
                                       "title" => "zum Downloadbereich"
                                ),
                                array(
                                         "url" => $pathvars["virtual"]."/service/presse.html",
                                        "desc" => "Presse",
                                       "title" => "Informationen f&uuml;r Pressevertreter"
                                ),
                                array(
                                         "url" => $pathvars["virtual"]."/sitemap.html",
                                        "desc" => "Sitemap",
                                       "title" => "Inhaltsverzeichnis"
                                ),
                                array(
                                         "url" => $pathvars["virtual"]."/service/kontakt.html",
                                        "desc" => "Kontakt",
                                       "title" => "Kontakt"
                                ),
                                array(
                                         "url" => $pathvars["virtual"]."/service/rssfeed.html",
                                        "desc" => "RSS",
                                       "title" => "RSS"
                                ),
                            );

////+///////+///////+///////+///////+///////+///////+///////////////////////////////////////////////////////////
?>
