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

    $ausgaben["row"] = "";
    $ausgaben["inhalt"] = "";

    #include $pathvars["moduleroot"]."admin/bloged.cfg.php";

    // laden der eigentlichen funktion
    #include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";

    $url = $environment["ebene"]."/".$environment["kategorie"];
    $id = make_id($url);

    // laden der eigentlichen funktion
    #include $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

    // erstellen der tags die angezeigt werden
    foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value) {
        if ( $value["show"] == 1 ) {
            $tags[$key] = $value["name"];
        }
    }

    // erstellen der tags die angezeigt werden
    foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value) {
        $tags[$key] = $value;
    }

    // erstellen der tags die angezeigt werden
    foreach ( $cfg["bloged"]["blogs"][$url]["tags"] as $key => $value) {
        $tags[$key] = $value;
    }

    if ( $environment["parameter"][2] == "" ) {
        $work = $dataloop["list"];
    } else {
        $work = $all;
    }

    // timestamp als erstes voranstellen zwecks sortierung
    if ( is_array($work) ) {
        foreach ( $work as $key => $value ) {
           $anzahl = -(count($value)+1);
            $value =array_pad($value,$anzahl,mktime(0,0,0,substr($value["termin_org"],5,2),substr($value["termin_org"],8,2),substr($value["termin_org"],0,4)));
            $work[$key] = $value;
        }

        if ( $environment["parameter"][2] == "" ) {
            ksort($work);
        } else {
            sort($work);
        }
    }

    // Anzeige der Metadaten
    if ( $environment["parameter"][2] != "" ) {
        $show_array = array("name_org","termin_org","termin_en_org","veranstalter_org","ort_org","beschreibung_org");
        $ausgaben["calendar"] = "";
        $hidedata["detail"] = $work[0];
        foreach ( $show_array as $value ) {
            if ( strstr($value,"termin")) {
                if ( $work[0][$value] == "1970-01-01" ) {
                    continue;
                } else {
                    $dataloop["detail"][$value]["name"] = substr($work[0][$value],8,2).".".substr($work[0][$value],5,2).".".substr($work[0][$value],0,4);
                }
            } else {
                $dataloop["detail"][$value]["name"] = $work[0][$value];
            }
            $dataloop["detail"][$value]["desc"] = "g(t_".$value.")";
        }

        if ( $work[0]["titel"] != "" ) {
            if ( $environment["parameter"][3] == "all" ) {
                $dataloop["detail"]["weitere"]["name"] = "<a href=\"termine,,".$work[0]["id"].".html\">Schlie&szlig;en</a>";
            } else {
                $dataloop["detail"]["weitere"]["name"] = "<a href=\"termine,,".$work[0]["id"].",all.html\">&Ouml;ffnen</a>";
            }
            $dataloop["detail"]["weitere"]["desc"] = "Weitere Informationen";
        }

        if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
            $dataloop["detail"]["edit"]["name"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,".DATABASE.",".eCRC($url).".".$work[0]["id"].",inhalt.html\"> |Termin bearbeiten|"."</a>";
            $dataloop["detail"]["edit"]["desc"] = "Aktionen:";
        }

        // gesamten content betrachten
        if ( $environment["parameter"][3] == "all" ) {
            $hidedata["detail_all"]["tet"] = $work[0]["all"];
        }

    } else {
        // liste 
        $ausgaben["inhalt"] = "#(inhalt)";
        // new link
        if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
            $hidedata["newlink"]["link"] = $pathvars["virtual"].$url.",,,,add.html";
        }

        switch ( $environment["parameter"][7] ) {
            case "group":
                if ( is_array($work) ) {
                    foreach ( $work as $key => $value ) {
                        $array[$value["veranstalter_org"]][$key]["name"] = $value["name_org"];
                        $array[$value["veranstalter_org"]][$key]["termin"] = $value["termin_org"];
                        $array[$value["veranstalter_org"]][$key]["termin_en"] = $value["termin_en_org"];
                        $array[$value["veranstalter_org"]][$key]["veranstalter"] = $value["veranstalter_org"];
                        $array[$value["veranstalter_org"]][$key]["datum"] = $value["datum"];
                        $array[$value["veranstalter_org"]][$key]["ort"] = $value["ort_org"];
                        $array[$value["veranstalter_org"]][$key]["beschreibung"] = $value["beschreibung_org"];
                        $array[$value["veranstalter_org"]][$key]["deletelink"] = $value["deletelink"];
                        $array[$value["veranstalter_org"]][$key]["editlink"] = $value["editlink"];
                        $array[$value["veranstalter_org"]][$key]["detaillink"] = $value["detaillink"];
                        $array[$value["veranstalter_org"]][$key]["id"] = $value["id"];
                    }
                }

                if ( is_array($array) ) {
                    foreach ( $array as $key => $value ) {

                        $hidedata["list"]["on"] = "on";
                        $counter = 0;
                        $table = "";
                        $counter++;
                        $table .= "<tr><th align=\"left\" colspan=\"2\">Veranstalter: ".$key."</th></tr>";
                        $table .= "<tr><th align=\"left\" width=\"30%\"><b>Datum</b></th><th align=\"left\" width=\"80%\"><b>Beschreibung</b></th><tr>";
                        foreach ( $value as $test => $test1 ) {
                            if ( $test1["termin_en"] == "1970-01-01" ) {
                                $anzeige = substr($test1["termin"],8,2).".".substr($test1["termin"],5,2).".".substr($test1["termin"],0,4);
                            } else {
                                $anzeige = substr($test1["termin"],8,2).".".substr($test1["termin"],5,2).".".substr($test1["termin"],0,4)."&nbsp;-&nbsp;".substr($test1["termin_en"],8,2).".".substr($test1["termin_en"],5,2).".".substr($test1["termin_en"],0,4);
                            }
                            $table .= "<tr><td align=\"left\">".$anzeige."</td><td><a href=\"termine,,".$test1["id"].".html\">".$test1["name"]."</a> ".$test1["deletelink"]."</td></tr>";
                        }
                        $ausgaben["row"] .= parser( "-1721433623.termine-row", "");
                    }
                }

                break;
            default:

                $hidedata["defaultlist"]["on"] = "on";
                if ( is_array($work) ) {
                    $hidedata["headdefaultlist"]["on"] = "on";
                    foreach ( $work as $key => $value ) {
                        $today = date('U');
                        if ( $value["termin_en_org"] == "1970-01-01" ) {
                            if ( $value[0] < $today && ( $environment["parameter"][4] == "" && $environment["parameter"][5] == "" && $environment["parameter"][6] == "") ) continue;
                            $dataloop["defaultlist"][$key]["desc"] = date("d.m.Y",$value[0]);
                        } else {
                            if ( mktime(0,0,0,substr($value["termin_en_org"],5,2),substr($value["termin_en_org"],8,2),substr($value["termin_en_org"],0,4)) < $today && ( $environment["parameter"][4] == "" && $environment["parameter"][5] == "" && $environment["parameter"][6] == "") ) continue;
                            $dataloop["defaultlist"][$key]["desc"] = date("d.m.Y",$value[0])."&nbsp;-&nbsp;".substr($value["termin_en_org"],8,2).".".substr($value["termin_en_org"],5,2).".".substr($value["termin_en_org"],0,4);
                        }
                        $dataloop["defaultlist"][$key]["name"] = "<a href=\"termine,,".$value["id"].".html\">".$value["name_org"]."</a>";
                        $dataloop["defaultlist"][$key]["veranstalter"] = $value["veranstalter_org"];
                    }
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
    $mapping["main"] = eCRC($environment["ebene"]).".termine";
    #$mapping["navi"] = "leer";

if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
