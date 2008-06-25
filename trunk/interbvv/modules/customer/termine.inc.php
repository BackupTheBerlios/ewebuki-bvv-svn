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

    $work = $dataloop["list"];
    if ( is_array($work) ) {
        sort($work);
    }

    // ADD und EDIT von Terminen
    if ( $environment["parameter"][4] == "add" || $environment["parameter"][4] == "edit" ) {
        $hidedata["add"]["link"] = $url;
        $hidedata["add"]["termin_bg0"] = date("d");
        $hidedata["add"]["termin_bg1"] = date("m");
        $hidedata["add"]["termin_bg2"] = date("Y");
        $hidedata["add"]["termin_en0"] = "";
        $hidedata["add"]["termin_en1"] = "";
        $hidedata["add"]["termin_en2"] = "";
        $ausgaben["form_aktion"] = $pathvars["virtual"]."/admin/bloged/add,".$id["mid"].".html";
        $sql = "SELECT content FROM site_text WHERE tname='".eCRC($url).".".$work[0]["id"]."'";
        $result = $db -> query($sql);
        $data = $db -> fetch_array($result,1);
        if ( $environment["parameter"][4] == "edit" ) {
            foreach ( $cfg["bloged"]["blogs"][$url]["addons"] as $key => $value ) {
                    preg_match("/\[$value\](.*)\[\/$value\]/",$data["content"],$regs);
                    if ( strstr($key,"termin")) {
                        if ( $regs[1] != 0 ) {
                            $hidedata["add"][$key."0"] = date ("d", $regs[1]);
                            $hidedata["add"][$key."1"] = date ("m", $regs[1]);
                            $hidedata["add"][$key."2"] = date ("Y", $regs[1]);
                        }
                    } else {
                        $hidedata["add"][$key] = $regs[1];
                    }
            }
            $ausgaben["form_aktion"] = $pathvars["virtual"].$url.",,".$environment["parameter"][2].",,edit.html";
        }

        if ( $_POST ) {
            foreach ( $_POST as $key => $value ) {
                if ( strstr($key,"TERMIN")) {
                    if ( $value[1] == "" ) {
                        $value = mktime(1,0,0,1,1,1970);
                    } else {
                        $value = mktime(0,0,0,(int)$value[1],(int)$value[0],(int)$value[2]);
                    }
                }
                $data["content"] = preg_replace("/\[$key\].*\[\/$key\]/","[".$key."]".$value."[/".$key."]",$data["content"]);
            }
            $sql = "UPDATE site_text SET content ='".$data["content"]."' WHERE tname='".eCRC($url).".".$work[0]["id"]."'";
            $result = $db -> query($sql);
            header("Location: ".$pathvars["virtual"].$url.",,".$work[0]["id"].".html");
        }

    } else {
        if ( is_array($work) ) {
            foreach ( $work as $key => $value ) {
                $array[$value["veranstalter_org"]][$key]["name"] = $value["name_org"];
                $array[$value["veranstalter_org"]][$key]["termin_bg"] = $value["termin_bg_org"];
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
            $hidedata["detail"] = $work[0];
            foreach ( $tags as $key => $value ) {
                if ( !array_key_exists($key,$array[$work[0]["veranstalter_org"]][0]) )continue;
                if ( strstr($key,"termin")) {
                    $dataloop["detail"][$key]["name"] = date ("d.m.Y",$array[$work[0]["veranstalter_org"]][0][$key]);
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
                        ( $test1["termin_en"] == 0 ) ? $anzeige = date ("d.m.Y", $test1["termin_bg"]) : $anzeige = date ("d.m.Y", $test1["termin_bg"])."&nbsp;-&nbsp;".date ("d.m.Y", $test1["termin_en"]);
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
