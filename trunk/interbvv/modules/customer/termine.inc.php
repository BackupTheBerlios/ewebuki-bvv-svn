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
    if ( is_array($work) ) {
        foreach ( $work as $key => $value ) {
            $value =array_pad($value,-31,mktime(0,0,0,substr($value["termin_org"],3,2),substr($value["termin_org"],0,2),substr($value["termin_org"],6,4)));
            $work[$key] = $value;
        }

        sort($work);
    }

    // ADD und EDIT von Terminen
    if ( $environment["parameter"][4] == "add" || $environment["parameter"][4] == "edit" ) {
        $hidedata["add"]["link"] = $url;
        $hidedata["add"]["name"] = "";
        $hidedata["add"]["ort"] = "";
        $hidedata["add"]["beschreibung"] = "";
        $hidedata["add"]["sort"] = "";
        $hidedata["add"]["termin"] = "";
        $hidedata["add"]["termin_en"] = "";
        $hidedata["add"]["wizard"] = "artikel";

        $ausgaben["form_aktion"] = $pathvars["virtual"]."/admin/bloged/add,".$id["mid"].".html";
        $sql = "SELECT content FROM site_text WHERE tname='".eCRC($url).".".$work[0]["id"]."'";
        $result = $db -> query($sql);
        $data = $db -> fetch_array($result,1);
        if ( $environment["parameter"][4] == "edit" ) {
            foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value ) {
                if ( is_array($value) ) {
                    $value = $value["tag"];
                }
                preg_match("/\[$value\](.*)\[\/$value\]/",$data["content"],$regs);
                if ( $regs[1] == "01-01-1970" ) $regs[1] = "";
                $hidedata["add"][$key] = $regs[1];
            }
            $ausgaben["form_aktion"] = $pathvars["virtual"].$url.",,".$environment["parameter"][2].",,edit.html";
        }
        $ausgaben["calendar"] = "";
        if ( $_POST ) {
            foreach ( $_POST as $key => $value ) {
                 if ( $key == "TERMIN" && $value == "" ) $value = "01-01-1970";
                $data["content"] = preg_replace("/\[$key\].*\[\/$key\]/","[".$key."]".$value."[/".$key."]",$data["content"]);
            }
            $sql = "UPDATE site_text SET content ='".$data["content"]."[!]wizard:artikel[/!]' WHERE tname='".eCRC($url).".".$work[0]["id"]."'";
            $result = $db -> query($sql);
            header("Location: ".$pathvars["virtual"].$url.",,".$work[0]["id"].".html");
        }
    } else {
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

        // Anzeige der Metadaten
        if ( $environment["parameter"][2] != "" ) {
            $ausgaben["calendar"] = "";
            $hidedata["detail"] = $work[0];
            foreach ( $tags as $key => $value ) {
                if ( !array_key_exists($key,$array[$work[0]["veranstalter_org"]][0]) )continue;
                if ( strstr($key,"termin")) {
                    if ( $array[$work[0]["veranstalter_org"]][0][$key] == "01-01-1970" ) {
                       continue;
                    } else {
                        $dataloop["detail"][$key]["name"] = substr($array[$work[0]["veranstalter_org"]][0][$key],0,2).".".substr($array[$work[0]["veranstalter_org"]][0][$key],3,2).".".substr($array[$work[0]["veranstalter_org"]][0][$key],6,4);
                    }
                } else {
                    $dataloop["detail"][$key]["name"] = $array[$work[0]["veranstalter_org"]][0][$key];
                }
                $dataloop["detail"][$key]["desc"] = "#(".$key.")";
            }

            if ( $work[0]["titel"] != "" ) {
                if ( $environment["parameter"][3] == "all" ) {
                    $dataloop["detail"]["weitere"]["name"] = "<a href=\"termine,,".$work[0]["id"].".html\">Schließen</a>";
                } else {
                    $dataloop["detail"]["weitere"]["name"] = "<a href=\"termine,,".$work[0]["id"].",all.html\">Öffnen</a>";
                }
                $dataloop["detail"]["weitere"]["desc"] = "Weitere Informationen";
            }

            if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
                $dataloop["detail"]["edit"]["name"] = "<a href=\"".$pathvars["virtual"].$url.",,".$work[0]["id"].",,edit.html\">|Metadaten editieren|"."</a><a href=\"".$pathvars["virtual"]."/wizard/show,".DATABASE.",".eCRC($url).".".$work[0]["id"].",inhalt.html\"> |Weitere Infos hinzufügen|"."</a>";
                $dataloop["detail"]["edit"]["desc"] = "Aktionen:";
            }

            // gesamten content betrachten
            if ( $environment["parameter"][3] == "all" ) {
                $hidedata["detail_all"]["tet"] = $work[0]["all"];
            }

        } else {
            // liste 
            // new link
            if ( $cfg["bloged"]["blogs"][$url]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"][$url]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
                $hidedata["newlink"]["link"] = $pathvars["virtual"].$url.",,,,add.html";
            }
            $hidedata["list"]["on"] = "on";
            $counter = 0;
            if ( is_array($array) ) {
                foreach ( $array as $key => $value ) {

                    $table = "";
                    $counter++;
                    $table .= "<tr><th align=\"left\" colspan=\"2\">Veranstalter: ".$key."</th></tr>";
                    $table .= "<tr><th align=\"center\" width=\"30%\"><b>Datum</b></th><th align=\"center\" width=\"80%\"><b>Beschreibung</b></th><tr>";
                    foreach ( $value as $test => $test1 ) {
                        if ( $test1["termin_en"] == "01-01-1970" ) {
                            $anzeige = substr($test1["termin"],0,2).".".substr($test1["termin"],3,2).".".substr($test1["termin"],6,4);
                        } else {
                            $anzeige = substr($test1["termin"],0,2).".".substr($test1["termin"],3,2).".".substr($test1["termin"],6,4)."&nbsp;-&nbsp;".substr($test1["termin_en"],0,2).".".substr($test1["termin_en"],3,2).".".substr($test1["termin_en"],6,4);
                        }
                        $table .= "<tr><td align=\"center\">".$anzeige."</td><td><a href=\"termine,,".$test1["id"].".html\">".$test1["name"]."</a> ".$test1["deletelink"]."</td></tr>";
                    }

                    $ausgaben["row"] .= parser( "-1721433623.list-row", "");
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
    $mapping["main"] = eCRC($environment["ebene"]).".list";
    #$mapping["navi"] = "leer";

if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
