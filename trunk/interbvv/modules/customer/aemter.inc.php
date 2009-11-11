<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php,v 1.6 2006/09/22 06:16:23 chaot Exp $";
  $Script["desc"] = "short description";
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

    86343 Koenigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    if ( $cfg["aemter"]["right"] == "" || $rechte[$cfg["aemter"]["right"]] == -1 ) {

        // funktions bereich
        // ***
        unset($hidedata["head_subnavi"]);
        // amtkennzahl bestimmen
        if ( strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") && !preg_match("/^\/aemter\/[0-9]{1,2}$/",$environment["ebene"]) ) {
            preg_match("/.*(vermessungsamt-.*)[\.]{1}.*/U",$_SERVER["SERVER_NAME"],$match);

            // feststellen, ob die url eine aussenstelle darstellt
            $sql = "SELECT *
                      FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                     WHERE ".$cfg["aemter"]["db"]["dst"]["internet"]." LIKE '%".$match[1]."%'
                       AND ".$cfg["aemter"]["db"]["dst"]["kate"]." IN ('5','8')";
            $result = $db -> query($sql);
            if ( $db->num_rows($result) > 0 ) {
                // hauptamt rausfinden und weiterleiten
                $data = $db -> fetch_array($result,1);
                $sql = "SELECT *
                          FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                         WHERE ".$cfg["aemter"]["db"]["dst"]["key"]."=".$data[$cfg["aemter"]["db"]["dst"]["parent"]];
                $result = $db -> query($sql);
                $data1 = $db -> fetch_array($result,1);
//                 echo "Location:".$data1[$cfg["aemter"]["db"]["dst"]["internet"]]."/index,".$data[$cfg["aemter"]["db"]["dst"]["akz"]].".html<br>";
                header("Location:".$data1[$cfg["aemter"]["db"]["dst"]["internet"]]."/index,".$data[$cfg["aemter"]["db"]["dst"]["akz"]].".html");
            }

            // amtskennzahl feststellen
            $sql = "SELECT *
                      FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                     WHERE ".$cfg["aemter"]["db"]["dst"]["internet"]." LIKE '%".$match[1]."%'
                       AND adkate IN ('3','4')";
            $result = $db -> query($sql);
            $data = $db -> fetch_array($result,1);
            $amtid = $data[$cfg["aemter"]["db"]["dst"]["akz"]];
        } else {
            // amtskennzahl aus url bestimmen
            $arrEbene = explode("/",$environment["ebene"]);
            $amtid = $arrEbene["2"];
            if ( !strstr($_SERVER["SERVER_ADDR"],"10.248.65") && !strstr($_SERVER["SERVER_ADDR"],"10.192.101.47") ) {
                // adresse aus db-holen
                $sql = "SELECT *
                          FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                         WHERE ".$cfg["aemter"]["db"]["dst"]["akz"]."='".$amtid."'";

                $result = $db -> query($sql);
                $data = $db -> fetch_array($result,1);
                // weiterleiten
                $header = $data[$cfg["aemter"]["db"]["dst"]["internet"]];
                header("Location:".$header);
            }
        }

        // menu ausblenden
        #$ausgaben["menu"] = "";
        $hidedata["amtnavi"] = array();

        // datensatz holen
        $sql = "SELECT *
                  FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                 WHERE ".$cfg["aemter"]["db"]["dst"]["akz"]."='".$amtid."'";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $form_values = $db -> fetch_array($result,1);
        $akz_array = array();
        $akz_array[$amtid] = $form_values[$cfg["aemter"]["db"]["dst"]["akz"]];

        // ausgabe-marken belegen
        $felder = array("amt","akz","str","plz","ort","tel","fax","email","rechtswert","hochwert","oeffnung","behinderte");
        foreach ( $felder as $feld ) {
            $ausgaben[$feld] = $form_values[$cfg["aemter"]["db"]["dst"][$feld]];
            $dataloop["stellen"][$amtid][$feld] = $form_values[$cfg["aemter"]["db"]["dst"][$feld]];
        }
        $hauptamt = $form_values["adststelle"];
        $ausgaben["amt"] = "Vermessungsamt ".$form_values["adststelle"];
        $ausgaben["akz"] = $amtid;
        // amtsgebaeude-bild
        if ( file_exists($pathvars["fileroot"].trim($pathvars["images"],"/")."/aemter/va".$form_values[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.jpg") ) {
            $dataloop["stellen"][$amtid]["src"] = $pathvars["images"]."aemter/va".$form_values[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.jpg";
        } else {
            $dataloop["stellen"][$amtid]["src"] = $pathvars["images"]."aemter/va".$form_values[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.gif";
        }
        $dataloop["stellen"][$amtid]["class"] = "selected";
        $dataloop["stellen"][$amtid]["display"] = "block";
        $dataloop["stellen"][$amtid]["oeffnung"] = nl2br(strip_tags($dataloop["stellen"][$amtid]["oeffnung"]));
        $dataloop["stellen"][$amtid]["behinderte"] = nl2br(strip_tags($dataloop["stellen"][$amtid]["behinderte"]));

        function aussenstellen($id){
            global $db, $cfg, $dataloop, $hidedata, $form_values, $environment, $felder, $pathvars, $akz_array, $amtid;

            $sql = "SELECT *
                      FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                     WHERE ".$cfg["aemter"]["db"]["dst"]["parent"]."=".$id."
                       AND ".$cfg["aemter"]["db"]["dst"]["kategorie"]." IN ('5','8')
                  ORDER BY ".$cfg["aemter"]["db"]["dst"]["amt"];
            $result = $db -> query($sql);
            if ( $db->num_rows($result) > 0 ){
                $buffer = array(); $i = 0;
                while ( $data = $db->fetch_array($result,1) ){
                    // welche aussenstellen
                    $sql = "SELECT *
                              FROM ".$cfg["aemter"]["db"]["kate"]["entries"]."
                             WHERE ".$cfg["aemter"]["db"]["kate"]["key"]."='".$data[$cfg["aemter"]["db"]["dst"]["kate"]]."'";
                    $res = $db -> query($sql);
                    $dat = $db->fetch_array($res,1);
                    $buffer[] = $dat[$cfg["aemter"]["db"]["kate"]["kate"]]." ".$data[$cfg["aemter"]["db"]["dst"]["amt"]];
                    $akz_array[] = $data[$cfg["aemter"]["db"]["dst"]["akz"]];
                    // informationen der einzelnen stellen
                    $class = ""; $display = "none"; $i++;
                    if ( $environment["parameter"][1] == $data[$cfg["aemter"]["db"]["dst"]["akz"]] ) {
                        $class = "selected";
                        $display = "";
                        $dataloop["stellen"][$amtid]["class"] = "";
                        $dataloop["stellen"][$amtid]["display"] = "none";
                    }

                    if ( file_exists($pathvars["fileroot"].trim($pathvars["images"],"/")."/aemter/va".$data[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.jpg") ) {
                        $src = $pathvars["images"]."aemter/va".$data[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.jpg";
                    } else {
                        $src = $pathvars["images"]."aemter/va".$data[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.gif";
                    }

                    $dataloop["stellen"][$data[$cfg["aemter"]["db"]["dst"]["akz"]]] = array(
                                "akz" => $data[$cfg["aemter"]["db"]["dst"]["akz"]],
                              "class" => $class,
                                "src" => $src,
                            "display" => $display,
                           "oeffnung" => nl2br($data[$cfg["aemter"]["db"]["dst"]["oeffnung"]]),
                         "behinderte" => $data[$cfg["aemter"]["db"]["dst"]["behinderte"]],
                    );
                    // fuer jede stelle die informationen eintragen
                    foreach ( $felder as $feld ) {
                        $dataloop["stellen"][$data[$cfg["aemter"]["db"]["dst"]["akz"]]][$feld] = $data[$cfg["aemter"]["db"]["dst"][$feld]];
                    }
                    $dataloop["stellen"][$data[$cfg["aemter"]["db"]["dst"]["akz"]]]["oeffnung"] = nl2br(strip_tags($data[$cfg["aemter"]["db"]["dst"]["oeffnung"]]));
                    $dataloop["stellen"][$data[$cfg["aemter"]["db"]["dst"]["akz"]]]["link_suffix"] = $data[$cfg["aemter"]["db"]["dst"]["akz"]];
                    $dataloop["stellen"][$data[$cfg["aemter"]["db"]["dst"]["akz"]]]["display"] = $display;
                }
                $hidedata["aussenstelle"]["ast"] = implode(", ",$buffer);
            }
        }

        // gibt es einen aussenstelle?
        $sql = "SELECT *
                  FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                 WHERE ".$cfg["aemter"]["db"]["dst"]["parent"]."=".$form_values["adid"]."
                   AND ".$cfg["aemter"]["db"]["dst"]["kategorie"]." IN ('5','8')";
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            aussenstellen($form_values[$cfg["aemter"]["db"]["dst"]["key"]]);
        }

        // ist das amt eine aussenstelle?
        $sql = "SELECT *
                  FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                 WHERE ".$cfg["aemter"]["db"]["dst"]["kategorie"]." IN ('3','4')
                   AND ".$cfg["aemter"]["db"]["dst"]["key"]."=".$form_values["adparent"];
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            // Weiterleitung zum Hauptamt
            $data = $db->fetch_array($result,1);
            $header = $pathvars["virtual"]."/aemter/".$data[$cfg["aemter"]["db"]["dst"]["akz"]]."/".$environment["kategorie"].".html";
            header("Location: ".$header);
        }

        // kekse anpassen
//         if ( !strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") ) {
//             $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/index.html\">".$ausgaben["amt"]."</a>";
//         }

        $hidedata["sub_menu"]["link"] = "index.html";
        foreach ( $cfg["aemter"]["sub_menu"] as $key => $value ) {
            $dataloop["sub_menu"][$key] = array(
                 "link" => $value[0],
                "label" => $value[1],
                "class" => "",
            );
            $class = "Level1";
            if ( $key == $environment["kategorie"] ) $class = "Level1Active";
//             $dataloop["amtnavi"][$key] = array(
//                  "link" => $value[0],
//                 "label" => $value[1],
//                 "class" => $class,
//             );
        }



        // was ist die aktuelle Amtskennzahl
        if ( is_array($dataloop["stellen"][$environment["parameter"][1]]) ) {
            $current_akz = $environment["parameter"][1];
        } else {
            $current_akz = $amtid;
        }




        $ausgaben["artikel"] = "";
        $ausgaben["presse"] = "";
        $ausgaben["termine"] = "";
        $ausgaben["neuigkeiten"] = "";
        switch ($environment["parameter"][0]) {
            // startseite
            case "index":
//                 require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
//                 require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $hidedata["index"]["heading"] = "#(index)";
                $hidedata["heading"]["heading"] = "#(index)";
                unset($hidedata["sub_menu"]);

                // BayernViewer-Link
                $bv_link = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$dataloop["stellen"][$current_akz]["rechtswert"].
                                                                            "&amp;hw=".$dataloop["stellen"][$current_akz]["hochwert"].
                                                                            "&amp;str=".urlencode(utf8_decode($dataloop["stellen"][$current_akz]["amt"])).
                                                                            "&amp;ort=".urlencode(utf8_decode($dataloop["stellen"][$current_akz]["str"].", ".$dataloop["stellen"][$current_akz]["plz"]." ".$dataloop["stellen"][$current_akz]["ort"]));

                // schauen, ob anfahrtsskizzen vorhanden sind
                $dir = $pathvars["fileroot"].trim($pathvars["images"],"/")."/aemter/";
                $files = array();
                foreach ( scandir($dir) as $filename ) {
                    if ( !strstr($filename,"va".$current_akz."_anfahrt") ) continue;
                    $anfahrts_pics[] = $pathvars["images"]."aemter/".$filename;
                }
                // falls es keine skizzen gibt, bayernviewer verlinken
                if ( count($anfahrts_pics) > 0 ) {
                    $ausgaben["standort_link"] = "standort,".$current_akz.".html";
                    $ausgaben["standort_onclick"] = "";
                } else {
                    $ausgaben["standort_link"] = $bv_link;
                    $ausgaben["standort_onclick"] = " onclick=\"window.open('".$bv_link."');return false;\"";
                }

                if ( $environment["ebene"] == "" ) {
                    $kat = "/".$environment["kategorie"];
                } else {
                    $kat = $environment["ebene"]."/".$environment["kategorie"];
                }

                // erstellen der tags die angezeigt werden
                if ( is_array($cfg["bloged"]["blogs"]["/aktuell/archiv"]["tags"]) ) {
                    foreach ( $cfg["bloged"]["blogs"]["/aktuell/archiv"]["tags"] as $key => $value) {
                        $tags[$key] = $value;
                    }
                }
                $dd = date('U');
                $art_tname = eCRC("/aktuell/archiv").".%";
                $pre_tname = eCRC("/aktuell/presse").".%";
                $ter_tname = eCRC("/aktuell/termine").".%";

                // gibts artikel?
                $sql = "SELECT Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,
                               tname,
                               ebene,
                               kategorie,
                               content
                          FROM site_text
                         WHERE status='1'
                           AND ( tname LIKE '".$art_tname."' OR tname LIKE '".$pre_tname."' OR tname LIKE '".$ter_tname."' )
                           AND SUBSTR(content,POSITION('[KATEGORIE]' IN content),POSITION('[/KATEGORIE]' IN content)-POSITION('[KATEGORIE]' IN content))= '[KATEGORIE]/aemter/".$amtid."/index'
                      ORDER BY date DESC
                            ";
                $result = $db -> query($sql);
                $count = 0;
                $today = mktime(23,59,59,date('m'),date('d'),date('Y'));
                while ( $data = $db->fetch_array($result,1) ) {
                    // nur drei werden angezeigt
                    $count++;
                    if ( $count > 3) break;

                    // ist der beitrag schon abgelaufen
                    $startdatum = mktime(0,0,0,substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
                    if ( preg_match("/\[ENDE\](.*)\[\/ENDE\]/Uis",$data["content"],$endmatch) ) {
                        if ( $today >  mktime(0,0,0,substr($endmatch[1],5,2),substr($endmatch[1],8,2),substr($endmatch[1],0,4)) && ( $endmatch[1] != "1970-01-01" ) ) {
                            continue;
                        }
                    }

                    if ( strstr($data["tname"],"1884525588") ) {
                        // termine
                        $link  = "termin,,".$data["kategorie"].",all.html";
                        preg_match("/\[_NAME\](.*)\[\/_NAME\]/Uis",$data["content"],$match);
                        $title = $match[1];
                    } else {
                        // artikel, presse
                        if ( $startdatum > $today ) continue;
                        preg_match("/\[H1\](.*)\[\/H1\]/Uis",$data["content"],$match);
                        $title = $match[1];
                        if ( strstr($data["tname"],"1255365051") ) {
                            // artikel
                            $link = "artikel,,".$data["kategorie"].".html";
                        } elseif ( strstr($data["tname"],"2211586253") ) {
                            // presse
                            $link = "presse,,".$data["kategorie"].".html";
                        }
                    }

                    $dataloop["/aktuell/archiv"][$count]["sort"] =  mktime('00','00','00',substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
                    $dataloop["/aktuell/archiv"][$count]["link"] =  $link;
                    $dataloop["/aktuell/archiv"][$count]["text"] =  $title;
                    $dataloop["/aktuell/archiv"][$count]["date"] =  substr($data["date"],8,2).".".substr($data["date"],5,2).".".substr($data["date"],0,4);

                }

                // gibts presse?
//                $sql = "Select Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,tname,ebene,kategorie,content from site_text
//                        WHERE
//                            status='1' AND
//                            ( tname like '".$pre_tname."') AND
//                            Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) > '".date('Y-m-d',$dd - ( 86400 * 20 ) )." 00:00:00' AND
//                            SUBSTR(content,POSITION('[KATEGORIE]' IN content),POSITION('[/KATEGORIE]' IN content)-POSITION('[KATEGORIE]' IN content))= '[KATEGORIE]/aemter/".$amtid."/index'
//                            ";
//                $result = $db -> query($sql);
//                $count = 0;
//                while ( $data = $db->fetch_array($result,1) ) {
//                    $count++;
//                    preg_match("/\[H1\](.*)\[\/H1\]/Uis",$data["content"],$match);
//                    $dataloop[$data["ebene"]][$count]["sort"] =  mktime('00','00','00',substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
//                    $dataloop[$data["ebene"]][$count]["link"] =  "presse,,".$data["kategorie"].".html";
//                    $dataloop[$data["ebene"]][$count]["text"] =  $match[1];
//                    $dataloop[$data["ebene"]][$count]["date"] =  substr($data["date"],8,2).".".substr($data["date"],5,2).".".substr($data["date"],0,4);
//
//                }
                // gibts termine?
//                $sql_t = "Select Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,tname,ebene,kategorie,content from site_text
//                        WHERE
//                            status='1' AND
//                            ( tname like '".$ter_tname."') AND (
//                            Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) > '".date('Y-m-d',$dd )." 00:00:00'
//                            OR
//                            Cast(SUBSTR(content,POSITION('[_TERMIN]' IN content)+9,POSITION('[/_TERMIN]' IN content)-POSITION('[_TERMIN]' IN content)-9) as DATETIME) > '".date('Y-m-d',$dd )." 00:00:00'
//                            ) AND
//                            SUBSTR(content,POSITION('[KATEGORIE]' IN content),POSITION('[/KATEGORIE]' IN content)-POSITION('[KATEGORIE]' IN content))= '[KATEGORIE]/aemter/".$amtid."/index'
//                            ";
//                $result_t = $db -> query($sql_t);
//                $count = 0;
//                while ( $data = $db->fetch_array($result_t,1) ) {
//                    $count++;
//                    preg_match("/\[_NAME\](.*)\[\/_NAME\]/Ui",$data["content"],$match);
//                    $dataloop[$data["ebene"]][$count]["sort"] =  mktime('00','00','00',substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
//                    $dataloop[$data["ebene"]][$count]["link"] =  "termine,,".$data["kategorie"].",all.html";
//                    $dataloop[$data["ebene"]][$count]["text"] =  $match[1];
//                    $dataloop[$data["ebene"]][$count]["date"] =  substr($data["date"],8,2).".".substr($data["date"],5,2).".".substr($data["date"],0,4);
//                }

//                if ( count($dataloop["/aktuell/termine"]) > 0 ) {
//                    asort($dataloop["/aktuell/termine"]);
//                    $hidedata["aktuelles_termine"]["on"] = "on";
//                }
                if ( count($dataloop["/aktuell/archiv"]) > 0 ) {
                    arsort($dataloop["/aktuell/archiv"]);
                    $hidedata["aktuelles_archiv"]["on"] = "on";
                }
//                if ( count($dataloop["/aktuell/presse"]) > 0 ) {
//                    arsort($dataloop["/aktuell/presse"]);
//                    $hidedata["aktuelles_presse"]["on"] = "on";
//                }
                if ( count($dataloop["/aktuell/archiv"]) > 0 || $db->num_rows($result) > 0 || $db->num_rows($result_t) > 0 ) $hidedata["aktuelles"]["text"] = "Aktuelles vom Vermessungsamt ".$form_values["adststelle"];

                // bayernweite artikel anzeigen
                $sql = "SELECT Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,
                               tname,
                               ebene,
                               kategorie,
                               content
                          FROM site_text
                         WHERE status='1'
                           AND ( tname like '".$art_tname."')
                           AND Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) > '".date('Y-m-d',$dd - ( 86400 * 20 ) )." 00:00:00'
                           AND SUBSTR(content,POSITION('[KATEGORIE]' IN content),POSITION('[/KATEGORIE]' IN content)-POSITION('[KATEGORIE]' IN content))= '[KATEGORIE]/aktuell/archiv'
                      ORDER BY date DESC";
                $result = $db -> query($sql);
                $count = 0; $dataloop["bvv_artikel"] = array();
                while ( $data = $db->fetch_array($result,1) ) {
                    $count++;
                    preg_match("/\[H1\](.*)\[\/H1\]/Uis",$data["content"],$match);
                    $dataloop["bvv_artikel"][$count]["sort"] =  mktime('00','00','00',substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
                    $dataloop["bvv_artikel"][$count]["link"] =  "artikel,,".$data["kategorie"].".html";
                    $dataloop["bvv_artikel"][$count]["text"] =  $match[1];
                    $dataloop["bvv_artikel"][$count]["date"] =  substr($data["date"],8,2).".".substr($data["date"],5,2).".".substr($data["date"],0,4);
                }
                if ( count($dataloop["bvv_artikel"]) > 0 ) {
// echo "<pre>".print_r($dataloop["bvv_artikel"],true)."</pre>";
                    $hidedata["bvv_artikel"] = array();
                }

                break;
            case "artikel":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
                $tags["titel"] = "SORT";
                $all = show_blog("/aktuell/archiv",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/archiv"]["rows"],$kat);
                $hidedata["all"]["out"] = content($all[1]["all"],"amt-allg");
                unset($hidedata["aussenstelle"]);
                if ( preg_match("/index,([0-9]{2}).html/Ui",basename($_SERVER["HTTP_REFERER"]),$match) ) {
                    foreach ( $cfg["aemter"]["sub_menu"] as $key => $value ) {
                        $dataloop["sub_menu"][$key] = array(
                            "link" => str_replace(".html",",".$match[1].".html",$value[0]),
                            "label" => $value[1],
                            "class" => "",
                        );
                    }
                }
                $hidedata["all"]["changed"] = date('d.m.Y',strtotime($all[1]["titel_org"]));
                break;
            case "presse":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
//                 $hidedata["sub_menu"]["link"] = "aktuell.html";
                $tags[] = "";
                $all = show_blog("/aktuell/presse",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/presse"]["rows"],$kat);
//                 $hidedata["all"]["out"] = $all[1]["all"];
                $hidedata["all"]["out"] = content($all[1]["all"],"amt-allg");
                unset($hidedata["aussenstelle"]);
                if ( preg_match("/index,([0-9]{2}).html/Ui",basename($_SERVER["HTTP_REFERER"]),$match) ) {
                    foreach ( $cfg["aemter"]["sub_menu"] as $key => $value ) {
                        $dataloop["sub_menu"][$key] = array(
                            "link" => str_replace(".html",",".$match[1].".html",$value[0]),
                            "label" => $value[1],
                            "class" => "",
                        );
                    }
                }
                $sql = "SELECT * From db_aemter where adakz = '".str_replace("/aemter/","",$environment["ebene"])."'";
                $result = $db -> query($sql);
                $data = $db -> fetch_array($result);
                $hidedata["presse_footer"]["strasse"] = $data["adstr"];
                $hidedata["presse_footer"]["ort"] = $data["adplz"]." ".$data["adort"];
                $hidedata["presse_footer"]["tel"] = $data["adtelver"];
                $hidedata["presse_footer"]["fax"] = $data["adfax"];
                #$dataloop["edit_lokale_presse"][]["lokal_edit"] = $pathvars["virtual"]."/wizard/show,".DATABASE.",".eCrc("/aktuell/archiv").".".$environment["parameter"][2].",inhalt,,,none.html";
//
//                     $sql = "SELECT ".$cfg["changed"]["db"]["changed"]["lang"].",
//                                 ".$cfg["changed"]["db"]["changed"]["changed"].",
//                                 ".$cfg["changed"]["db"]["changed"]["surname"].",
//                                 ".$cfg["changed"]["db"]["changed"]["forename"].",
//                                 ".$cfg["changed"]["db"]["changed"]["email"].",
//                                 ".$cfg["changed"]["db"]["changed"]["alias"]."
//                             FROM ".$cfg["changed"]["db"]["changed"]["entries"]."
//                             WHERE label='inhalt' and  tname = '".eCRC("/aktuell/archiv").".".$environment["parameter"][2]."'
//                         ORDER BY ".$cfg["changed"]["db"]["changed"]["changed"];
//                     $result = $db -> query($sql);
//                     $data = $db -> fetch_array($result);
//                     $hidedata["all"]["changed"] = date($cfg["changed"]["format"],strtotime($data["changed"]));
//

                break;

            case "termine":
                $url = $environment["ebene"]."/".$environment["kategorie"];
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
              #  $hidedata["sub_menu"]["link"] = "aktuell.html";
                $hidedata["termine_detail"]["in"] = "on";
                unset($hidedata["aussenstelle"]);
                if ( preg_match("/index,([0-9]{2}).html/Ui",basename($_SERVER["HTTP_REFERER"]),$match) ) {
                    foreach ( $cfg["aemter"]["sub_menu"] as $key => $value ) {
                        $dataloop["sub_menu"][$key] = array(
                            "link" => str_replace(".html",",".$match[1].".html",$value[0]),
                            "label" => $value[1],
                            "class" => "",
                        );
                    }
                }
                $tags["name"] = "_NAME";
                $tags["veranstalter"] = "_VERANSTALTER";
                $tags["termin"] = "SORT";
                $tags["termin_en"] = "_TERMIN";
                $tags["ort"] = "_ORT";
                $tags["beschreibung"] = "_BESCHREIBUNG";
                $tags["titel"] = "H1";
                $all = show_blog("/aktuell/termine",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/termine"]["rows"],$kat);

                if ( $environment["parameter"][3] == "all" ) {
//                     $hidedata["all"]["out"] = $all[1]["all"];
                    $hidedata["all"]["out"] = content($all[1]["all"],"amt-allg");
                }

                foreach ( $tags as $key => $value ) {
                    if ( $key == "titel" ) continue;
                    if ( strstr($key,"termin") ) {
                        if ( $all[1][$key."_org"] == "1970-01-01" ) continue;
                        $all[1][$key."_org"] = substr($all[1][$key."_org"],8,2).".".substr($all[1][$key."_org"],5,2).".".substr($all[1][$key."_org"],0,4);
                    }
                    $dataloop["termine_detail"][$key]["name"] = $all[1][$key."_org"];
                    $dataloop["termine_detail"][$key]["desc"] = "g(termine_".$key.")";
                }

                if ( $all[1]["titel"] != "" ) {
                    if ( $environment["parameter"][3] == "all" ) {
                        $dataloop["termine_detail"]["weitere"]["name"] = "<a href=\"termine,,".$environment["parameter"][2].".html\">g(close)</a>";
                    } else {
                        $dataloop["termine_detail"]["weitere"]["name"] = "<a href=\"termine,,".$environment["parameter"][2].",all.html\">g(open)</a>";
                    }
                    $dataloop["termine_detail"]["weitere"]["desc"] = "#(more_infos)";
                }

                if ( $environment["parameter"][2] != "" ) {
                    if ( $cfg["bloged"]["blogs"]["/aktuell/termine"]["right"] == "" || priv_check($url,$cfg["bloged"]["blogs"]["/aktuell/termine"]["right"])  ) {
                        $dataloop["termine_detail"]["edit"]["name"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,".DATABASE.",".eCRC("/aktuell/termine").".".$environment["parameter"][2].",inhalt.html\"> #(add_infos)"."</a>";
                        $dataloop["termine_detail"]["edit"]["desc"] = "Aktionen:";
                    }
                }
                break;

            case "standort":
                // kekse manipulieren
                $environment["kekse"] .= $defaults["split"]["kekse"]."<span class=\"last_bread_crumb\">Standort</span>";
                // ueberschrift setzen
                $hidedata["heading"]["heading"] = "#(location)";

                // BayernViewer-Link
                $bv_link = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$dataloop["stellen"][$current_akz]["rechtswert"].
                                                                              "&amp;hw=".$dataloop["stellen"][$current_akz]["hochwert"].
                                                                             "&amp;str=".urlencode(utf8_decode($dataloop["stellen"][$current_akz]["amt"])).
                                                                             "&amp;ort=".urlencode(utf8_decode($dataloop["stellen"][$current_akz]["str"].", ".$dataloop["stellen"][$current_akz]["plz"]." ".$dataloop["stellen"][$current_akz]["ort"]));

                // schauen, welche anfahrtsskizzen vorhanden sind
                $dir = $pathvars["fileroot"].trim($pathvars["images"],"/")."/aemter/";
                $files = array();
                foreach ( scandir($dir) as $filename ) {
                    if ( !strstr($filename,"va".$current_akz."_anfahrt") ) continue;
                    $anfahrts_pics[] = $pathvars["images"]."aemter/".$filename;
                }

                if ( $environment["parameter"][2] == "print" ) {
                    $show_part = "gal_print";
                } else {
                    $show_part = "gal_sel";
                }
                $hidedata[$show_part] = array(
                       "akz" => $current_akz,
                       "str" => $dataloop["stellen"][$current_akz]["str"],
                       "plz" => $dataloop["stellen"][$current_akz]["plz"],
                       "ort" => $dataloop["stellen"][$current_akz]["ort"],
                    "viewer" => $bv_link,
                );

                if ( is_array($anfahrts_pics) ) {
                    foreach ( $anfahrts_pics as $key=>$value ) {
                        $dataloop["anfahrtspics"][] = array(
                               "index" => ($key + 1),
                                 "akz" => $current_akz,
                                "path" => $value,
                        );
                    }
                }


                break;

            case "amtsbezirk":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<span class=\"last_bread_crumb\">Amtsbezirk</span>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["bezirk"] = array();

                $hidedata["heading"]["heading"] = "#(bezirk)";

                $sql = "SELECT DISTINCT gmd.gdecode, gmd.name as gemeinde, gmd.gdeart".
                        " FROM (gemeinden_intranet as gmd LEFT JOIN gmn_gemeinden ON (gmd.gdecode=gemeinde)) ".
                        " WHERE buort='".$current_akz."'".
                        " ORDER BY gmd.name";
                $result = $db -> query($sql);
                $prev = ""; $buffer = array();
                while ( $data = $db->fetch_array($result,1) ) {
                    // gemarkungen
                    $sql = "SELECT DISTINCT name".
                            " FROM gmn_gemeinden JOIN gmn_intranet ON (gmn=gmcode)".
                        " WHERE gemeinde=".$data["gdecode"].
                        " ORDER BY name";

                    $res_gmkg = $db->query($sql);
                    $gmkg_array = array();
                    while ( $dat_gmkg = $db->fetch_array($res_gmkg,1) ){
                        if ( $gmkg != "" ) $gmkg .= ", ";
                        $gmkg .= $dat_gmkg["name"];
                        $gmkg_array[] = $dat_gmkg["name"];
                    }
                    if ( count($gmkg_array) > 0 ) {
                        $gmkg = implode(", ",$gmkg_array);
                    } else {
                        $gmkg = "&nbsp;";
                    }

                    $dataloop["gmd"][] = array(
                        "item" => $data["gemeinde"],
                        "gmkg" => $gmkg,
                        "color" => $cfg["aemter"]["color"]["set"]
                    );
                    $gmd = $data["gemeinde"];

                    $gmd_frei = "&nbsp;";
                    if ( $data["gdeart"] == "Gemeindefreies Gebiet" ) {
                        $gmd = "&nbsp;";
                        $gmd_frei = $data["gemeinde"];
                    }

                    $buffer[] = "<tr>
                                    <td>".$gmd."</td>
                                    <td>".$gmd_frei."</td>
                                    <td>$gmkg</td>
                                    </tr>";
                }
                if ( count($buffer) > 0 ) {
                    $dataloop["stellen"][$current_akz]["gmkg"] = implode("\n",$buffer);
                }

                break;

            case "info":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<span class=\"last_bread_crumb\">Informationen f&uuml;r behinderte Menschen</span>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["info"] = array();

                $hidedata["heading"]["heading"] = "#(info)";

//                 foreach ( $akz_array as $key=>$value ) {
//                     $dataloop["stellen"][$key]["behinderte"] = implode("\n",$buffer);
//                 }

                if ( priv_check("/aemter/".$amtid,"edit") ) {
                    $hidedata["info"]["wizard"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,interbvv,amt-allg,handicap_".$amtid.".html\" class=\"button\">VA".$amtid.": Informationen bearbeiten</a>";
                }
                break;

            case "ansprech":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<span class=\"last_bread_crumb\">Ansprechpartner</span>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["ansprech"] = array();

                $hidedata["heading"]["heading"] = "#(ansprech)";

                $ansprech = array (
                            "adleiter",
                            "adstellvertreter",
                            "ad_ansprech_auskunft",
                            "ad_ansprech_lika",
                            "ad_ansprech_koord",
                            "ad_ansprech_fn",
                            "ad_ansprech_gebaeude",
                            "ad_ansprech_gebuehren",
                            "ad_ansprech_umlegung",
                );
                $buffer = array();
                foreach ( $ansprech as $function ) {
                    $sql = "SELECT *
                                FROM db_ansprech JOIN db_aemter ON (akz=adakz)
                                WHERE akz='".$current_akz."'
                                AND function='".$function."'";
                    $result = $db -> query($sql);
                    $num_rows = $db->num_rows($result);
                    $i = 1;
                    if ( $num_rows > 0 ) {
                        if ( $num_rows == 1 ) {
                            $row_header = "<th>#(".$function.")</th>";
                        } else {
                            $row_header = "<th rowspan=\"".$num_rows."\">#(".$function.")</th>";
                        }
                        while ( $data = $db->fetch_array($result,1) ) {
                            $buffer[] = "<tr>
                                            ".$row_header."
                                            <td style=\"white-space:nowrap;\">".$data["name"]."</td>
                                            <td style=\"white-space:nowrap;\">".$data["telefon"]."</td>
                                        </tr>";
                            $row_header = "";
                        }
                    }
                }
                if ( count($buffer) > 0 ) {
                    $sql = "SELECT *
                                FROM db_aemter
                                WHERE adakz='".$current_akz."'";
                    $result = $db -> query($sql);
                    $data = $db->fetch_array($result,1);
                    $buffer[] = "<tr>
                                    <th>#(weitere)</th>
                                    <td style=\"white-space:nowrap;\">Servicezentrum</td>
                                    <td style=\"white-space:nowrap;\">".$data["adtelver"]."</td>
                                </tr>";
                    $dataloop["stellen"][$current_akz]["ansprech"] = implode("\n",$buffer);
                }
                // ggf belegschaftsbild
                $beleg_img_src = $pathvars["fileroot"]."images/html/aemter/va".$current_akz."_belegschaft.jpg";
                if ( file_exists($beleg_img_src) ) {
                    $amt_name = $ausgaben["amt"];
                    if ( $hidedata["aussenstelle"]["ast"] != "" ) {
                        $amt_name .= " mit ".$hidedata["aussenstelle"]["ast"];
                    }
                    $beleg_img_web = $pathvars["images"]."aemter/va".$current_akz."_belegschaft.jpg";
                    $dataloop["stellen"][$current_akz]["beleg_img"] = "<img src=\"".$beleg_img_web."\" alt=\"Belegschaft ".$amt_name."\" />";
                    $dataloop["stellen"][$current_akz]["beleg_lb"] = "<a href=\"".$beleg_img_web."\" alt=\"Belegschaft ".$amt_name."\" title=\"Belegschaft ".$amt_name."\"  rel=\"lightbox[skizze_".$current_akz."]\">#(bel_foto)</a>";
                } else {
                    $dataloop["stellen"][$current_akz]["beleg_img"] = "";
                    $dataloop["stellen"][$current_akz]["beleg_lb"] = "";
                }
                break;

            case "amtschronik":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/amtschronik.html\">Amtschronik</a>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["amtschronik"]["inhalt"] = "#(amtschronik_".$amtid.")";
                if ( priv_check("/aemter/".$amtid,"edit") ) {
                    $hidedata["amtschronik"]["wizard"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,interbvv,amt-allg,amtschronik_".$amtid.".html\" class=\"button\">VA".$amtid.": Amtschronik</a>";
                }
                break;
            case "kontakt":
                if ( $environment["ebene"] == "" || strstr($environment["ebene"],"/aemter/") ) {
                    $sql = "SELECT ".$cfg["aemter"]["db"]["dst"]["email"]."
                              FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                             WHERE ".$cfg["aemter"]["db"]["dst"]["akz"]."='".$environment["parameter"][1]."'";
                    $result = $db -> query($sql);
                    $data = $db->fetch_array($result,1);
                    $hidedata["heading"]["heading"] = "#(kontakt)";
                    $environment["ebene"] = "/service";
                    $environment["kategorie"] = "kontakt";
                    include $pathvars["moduleroot"]."addon/kontakt.cfg.php";
                    $cfg["kontakt"]["basis"] = "kontakt";
                    if ( $cfg["aemter"]["email"] == -1 ) {
                        $cfg["kontakt"]["email"]["owner"] = $data["ademail"];
                    }
                    include $pathvars["moduleroot"]."addon/kontakt.inc.php";
                    $hidedata["kontakt"]["inhalt"] = "on";
                    $ausgaben["kontakt"] = parser("aemter-kontakt","");
                }
                break;
            case "va-aktuell":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
                unset($hidedata["aussenstelle"]);
                $tags["titel"] = "H1";
                $tags["teaser"] = "P=teaser";
                $tags["image"] = "IMG=";
                $tags["termine"] = "_NAME";

                $hidedata["sub_menu"]["link"] = "aktuell.html";

                if ( $environment["parameter"][1] == "archiv" ) {
                    $dataloop["artikel2"] = show_blog("/aktuell/archiv",$tags,"disabled","","/aemter/".$amtid."/index");
                } elseif ( $environment["parameter"][1] == "termine" ) {
                    $dataloop["termine"] = show_blog("/aktuell/termine",$tags,"disabled","","/aemter/".$amtid."/index");
                } elseif ( $environment["parameter"][1] == "presse" ) {
                    $dataloop["presse"] = show_blog("/aktuell/presse",$tags,"disabled","","/aemter/".$amtid."/index");
                } else {
                    $sql = "Select Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,tname,ebene,kategorie,content from site_text
                        WHERE
                            status='1' AND
                            ( tname like '".$art_tname."') AND
                            Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) > '".date('Y-m-d',$dd - ( 86400 * 20 ) )." 00:00:00' AND
                            Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) <= '".date('Y-m-d',$dd)." 00:00:00' AND
                            SUBSTR(content,POSITION('[KATEGORIE]' IN content),POSITION('[/KATEGORIE]' IN content)-POSITION('[KATEGORIE]' IN content))= '[KATEGORIE]/aemter/".$amtid."/index'
                            ";

                    $hidedata["sub_menu"]["link"] = "index.html";
                    $dataloop["artikel"] = show_blog("/aktuell/archiv",$tags,"disabled","0,1","/aemter/".$amtid."/index");
                    $dataloop["artikel2"] = show_blog("/aktuell/archiv",$tags,"disabled","1,4","/aemter/".$amtid."/index");
                    $dataloop["presse"] = show_blog("/aktuell/presse",$tags,"disabled","0,4","/aemter/".$amtid."/index");
                    $dataloop["termine"] = show_blog("/aktuell/termine",$tags,"disabled","0,4","/aemter/".$amtid."/index");

                    if ( count($dataloop["artikel"]) > 0 ) $hidedata["artikel"]["ueberschrift"] = "Meldungen";
                    if ( count($dataloop["presse"]) > 0 ) $hidedata["presse"]["ueberschrift"] = "Pressemitteilungen";
                    if ( count($dataloop["termine"]) > 0 ) $hidedata["termine"]["ueberschrift"] = "Termine";
                }
                break;
            case "va-archiv":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
                unset($hidedata["aussenstelle"]);
                $tags["titel"] = "H1";
                $tags["teaser"] = "P=teaser";
                $tags["image"] = "IMG=";
                $tags["termine"] = "_NAME";
                $hidedata["sub_menu"]["link"] = "va-aktuell.html";
                // artikel des amtes
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["artikel2"] = show_blog("/aktuell/archiv",$tags,"disabled","10","/aemter/".$amtid."/index",-1);
                $ausgaben["office_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["office_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["artikel2"]) > 0 ) $hidedata["artikel_amt"] = array();
                if ($ausgaben["office_anzahl"] > 10 ) {
                    $hidedata["office_artikel_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                // bayernweite artikel
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["artikel_bvv"] = show_blog("/aktuell/archiv",$tags,"disabled","10","/aktuell/archiv");
                $ausgaben["bvv_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["bvv_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["artikel_bvv"]) > 0 ) $hidedata["artikel_bvv"] = array();
                if ($ausgaben["bvv_anzahl"] > 10 ) {
                    $hidedata["bvv_artikel_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                break;
            case "va-termine":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
                unset($hidedata["aussenstelle"]);
                $tags["titel"] = "H1";
                $tags["teaser"] = "P=teaser";
                $tags["image"] = "IMG=";
                $tags["termine"] = "_NAME";
                $hidedata["sub_menu"]["link"] = "va-aktuell.html";
                // termine des amtes
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["termine_amt"] = show_blog("/aktuell/termine",$tags,"disabled","10","/aemter/".$amtid."/index");
                $ausgaben["office_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["office_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["termine_amt"]) > 0 ) $hidedata["termine_amt"] = array();
                if ($ausgaben["office_anzahl"] > 10 ) {
                    $hidedata["office_termin_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                // bayernweite termine
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["termine_bvv"] = show_blog("/aktuell/termine",$tags,"disabled","10","/aktuell/termine");
                $ausgaben["bvv_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["bvv_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["termine_bvv"]) > 0 ) $hidedata["termine_bvv"] = array();
                if ($ausgaben["bvv_anzahl"] > 10 ) {
                    $hidedata["bvv_termin_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                break;
            case "va-presse":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";
                unset($hidedata["aussenstelle"]);
                $tags["titel"] = "H1";
                $tags["teaser"] = "P=teaser";
                $tags["image"] = "IMG=";
                $tags["termine"] = "_NAME";
                $hidedata["sub_menu"]["link"] = "va-aktuell.html";
                // pressemitteilungen des amtes
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["presse_amt"] = show_blog("/aktuell/presse",$tags,"disabled","10","/aemter/".$amtid."/index");
                $ausgaben["office_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["office_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["presse_amt"]) > 0 ) $hidedata["presse_amt"] = array();
                if ($ausgaben["office_anzahl"] > 10 ) {
                    $hidedata["office_presse_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                // bayernweite pressemitteilungen
                $ausgaben["inhalt_selector"] = ""; $ausgaben["anzahl"] = "";
                $dataloop["presse_bvv"] = show_blog("/aktuell/presse",$tags,"disabled","10","/aktuell/presse");
                $ausgaben["bvv_inhalt_selector"] = $ausgaben["inhalt_selector"];
                $ausgaben["bvv_anzahl"] = $ausgaben["anzahl"];
                if ( count($dataloop["presse_bvv"]) > 0 ) $hidedata["presse_bvv"] = array();
                if ($ausgaben["bvv_anzahl"] > 10 ) {
                    $hidedata["bvv_presse_inhalt_selector"] = array();
                }
                unset($hidedata["inhalt_selector"]);
                break;
        }

        // +++
        // funktions bereich


        // page basics
        // ***

        // navigation erstellen
        $ausgaben["add"] = $cfg["aemter"]["basis"]."/add,".$environment["parameter"][1].",verify.html";
        #$mapping["navi"] = "leer";

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "amt-allg";
        #$mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // wohin schicken
        #n/a

        // +++
        // page basics

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
