<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: termine.inc.php 1131 2007-12-12 08:45:50Z chaot $";
  $Script["desc"] = "short description";
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
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    ////////////////////////////////////////////////////////////////////
    // achtung: bei globalen funktionen, variablen nicht zuruecksetzen!
    // z.B. $ausgaben["form_error"],$ausgaben["inaccessible"]
    ////////////////////////////////////////////////////////////////////

    // warnung ausgeben
    if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

    // path fuer die schaltflaechen anpassen
    if ( $cfg["leer"]["iconpath"] == "" ) $cfg["leer"]["iconpath"] = "/images/default/";

    // label bearbeitung aktivieren
    if ( isset($HTTP_GET_VARS["edit"]) ) {
        $specialvars["editlock"] = 0;
    } else {
        $specialvars["editlock"] = -1;
    }

    include $pathvars["moduleroot"]."admin/bloged.cfg.php";

    // laden der eigentlichen funktion
    include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";

    $url = $environment["ebene"]."/".$environment["kategorie"];
    $id = make_id($url);

    if ( $environment["parameter"][4] == "add" ) {
        $ausgaben["form_aktion"] = $pathvars["virtual"]."/admin/bloged/add,".$id["mid"].".html";
        $hidedata["add"]["on"] = "on";
        $count = 0;
        foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value ) {
            $count++;
            $dataloop["add"][$count]["label"] = $key;
            $dataloop["add"][$count]["name"] = $value["name"];
        }
    } else {
        // laden der eigentlichen funktion
        include $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

        // erstellen der tags die angezeigt werden
        foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value) {
            if ( $value["show"] == 1 ) {
                $tags[$key] = $value["name"];
            }
        }

       // erstellen der tags die angezeigt werden
        foreach ( $cfg["bloged"]["blogs"][$url]["tags"] as $key => $value) {
            if ( $value["show"] == 1 ) {
                $tags[$key] = $value["name"];
            }
        }

        $work = show_blog($url,$tags,"admin","termine","0,10");

        foreach ( $work as $key => $value ) {
            $array[$value["veranstalter"]][$key]["name"] = $value["name"];
            $array[$value["veranstalter"]][$key]["termin_bg"] = $value["termin_bg"];
            $array[$value["veranstalter"]][$key]["termin_en"] = $value["termin_en"];
            $array[$value["veranstalter"]][$key]["veranstalter"] = $value["veranstalter"];
            $array[$value["veranstalter"]][$key]["datum"] = $value["datum"];
            $array[$value["veranstalter"]][$key]["ort"] = $value["ort"];
            $array[$value["veranstalter"]][$key]["beschreibung"] = $value["beschreibung"];
            $array[$value["veranstalter"]][$key]["deletelink"] = $value["deletelink"];
            $array[$value["veranstalter"]][$key]["editlink"] = $value["editlink"];
            $array[$value["veranstalter"]][$key]["detaillink"] = $value["detaillink"];
            $array[$value["veranstalter"]][$key]["id"] = $value["id"];
        }

// echo "<pre>";
// print_r($work);
// print_r($array);
// echo "</pre>";

        if ( $environment["parameter"][2] != "" ) {
            $hidedata["detail"] = $work[1];
            foreach ( $tags as $key => $value ) {
                if ( $key == "titel" && $work[1][$key] != "" ) {
                    $dataloop["detail"][$key]["desc"] = "Weitere Informationen";
                    $dataloop["detail"][$key]["name"] = "<a href=\"termine,,".$work[1]["id"].",all.html\">bitte drücken</a>";
                }
                if ( !array_key_exists($key,$array[$work[1]["veranstalter"]][1]) )continue;
                $dataloop["detail"][$key]["name"] = $array[$work[1]["veranstalter"]][1][$key];
                $dataloop["detail"][$key]["desc"] = "#(".$key.")";
            }
            if ( $environment["parameter"][3] == "all" ) {
                $sql = "SELECT html, content FROM ". SITETEXT ." WHERE tname='".crc32($url).".".$work[1]["id"]."' AND lang='".$environment["language"]."'AND label='inhalt' ORDER BY version DESC LIMIT 0,1";
                $result = $db -> query($sql);
                $data = $db -> fetch_array($result,1);
                $hidedata["detail_all"]["tet"] = tagreplace($data["content"]);
            }
        } else {
            // new link
            if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
                $hidedata["newlink"]["link"] = $pathvars["virtual"].$url.",,,,add.html";
            }
            $hidedata["list"]["on"] = "on";
            $counter = 0;
            foreach ( $array as $key => $value ) {
                $table = "";
                $counter++;
                $table .= "<tr><th align=\"left\" colspan=\"2\">Veranstalter:".$key."</th></tr>";
                $table .= "<tr><th align=\"left\" width=\"20%\"><b>Datum</b></th><th align=\"left\" width=\"80%\"><b>Beschreibung</b></th>";
                foreach ( $value as $test => $test1 ) {
                    $counter++;
                    $table .= "<tr><td>".$test1["termin_bg"]."&nbsp;-&nbsp;".$test1["termin_en"]."</td><td><a href=\"termine,,".$test1["id"].".html\">".$test1["name"]."</a></td>";
                    if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
                        $table .= "<td>".$test1["editlink"]."</td>";
                        $table .= "<td>".$test1["deletelink"]."</td>";
                    }
                    $table .= "</tr>";
                }
                $ausgaben["row"] .= parser( "-1721433623.list-row", "");
            }
        }
    }
    // fehlermeldungen
    if ( $HTTP_GET_VARS["error"] != "" ) {
        if ( $HTTP_GET_VARS["error"] == 1 ) {
            $ausgaben["form_error"] = "#(error1)";
        }
    } else {
        $ausgaben["form_error"] = "";
    }

    // was anzeigen
    $mapping["main"] = crc32($environment["ebene"]).".list";
    #$mapping["navi"] = "leer";

if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
