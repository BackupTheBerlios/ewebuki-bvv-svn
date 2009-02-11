<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: bloglist.inc.php $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001, 2002, 2003 Werner Ammon <wa@chaos.de>

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

    // warnung ausgeben
    if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];


    // label bearbeitung aktivieren
    if ( isset($HTTP_GET_VARS["edit"]) ) {
        $specialvars["editlock"] = 0;
    } else {
        $specialvars["editlock"] = -1;
    }

    // erstellen der crc
    if ( $environment["ebene"] == "" ) {
        $kat = "/".$environment["kategorie"];
    } else {
        $kat = $environment["ebene"]."/".$environment["kategorie"];
    }

    // kalender einblenden
    include $pathvars["moduleroot"]."libraries/function_calendar.inc.php";
    $ausgaben["calendar"] .= "<div class=\"box\">".calendar("","","cal_termine",-1,-1)."</div>";

    include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
    include $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
    $tags[titel] = "H1";
    $tags[teaser] = "P=teaser";
    $tags[image] = "IMG=";

    // loopen der artikel
    $dataloop["artikel"] = show_blog("/aktuell/archiv",$tags,"disabled","0,1","/aktuell/archiv");
    $dataloop["artikel"][1]["teaser_org"] = tagremove($dataloop["artikel"][1]["teaser_org"]);
    $dataloop["artikel2"] = show_blog("/aktuell/archiv",$tags,"disabled","1,3","/aktuell/archiv");

    // loopen der pressemitteilungen
    $dataloop["presse"] = show_blog("/aktuell/presse",$tags,"disabled","0,4","/aktuell/presse");

    // loopen der termine
    $tags = "";
    $tags["termin1"] = "SORT";
    $tags["titel"] = "_NAME";
    $tags["termin2"] = "_TERMIN";
    $work_array = show_blog("/aktuell/termine",$tags,"disabled","0,4","/aktuell/termine");

    if ( is_array($work_array) ) {
        foreach ( $work_array as $key => $value ) {
            $value =array_pad($value,-16,mktime(0,0,0,substr($value["termin1_org"],8,2),substr($value["termin1_org"],5,2),substr($value["termin1_org"],0,4)));
            $work_array[$key] = $value;
        }

        ksort($work_array);
        foreach ( $work_array as $key => $value ) {
            $dataloop["termine"][$value["id"]]["datum"] = substr($value["termin1_org"],8,2).".".substr($value["termin1_org"],5,2).".".substr($value["termin1_org"],0,4);
            $dataloop["termine"][$value["id"]]["titel"] = $value["titel_org"];
            $dataloop["termine"][$value["id"]]["detaillink"] = $pathvars["virtual"]."/aktuell/termine,,".$value["id"].".html";
        }
    }

    $mapping["main"] = "aktuell";

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
