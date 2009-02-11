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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    if ( $cfg["aemter"]["right"] == "" || $rechte[$cfg["aemter"]["right"]] == -1 ) {

        // funktions bereich
        // ***

        // amtkennzahl bestimmen
        $arrEbene = explode("/",$environment["ebene"]);
        $amtid = $arrEbene["2"];

        // datensatz holen
        $sql = "SELECT *
                  FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                 WHERE ".$cfg["aemter"]["db"]["dst"]["akz"]."='".$amtid."'";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $form_values = $db -> fetch_array($result,1);
        $akz_array = array($form_values[$cfg["aemter"]["db"]["dst"]["akz"]]);

        // ausgabe-marken belegen
        $felder = array("amt","akz","str","plz","ort","tel","fax","email","rechtswert","hochwert");
        foreach ( $felder as $feld ) {
            $ausgaben[$feld] = $form_values[$cfg["aemter"]["db"]["dst"][$feld]];
            $dataloop["stellen"][0][$feld] = $form_values[$cfg["aemter"]["db"]["dst"][$feld]];
        }
        $hauptamt = $form_values["adststelle"];
        $ausgaben["amt"] = "Vermessungsamt ".$form_values["adststelle"];
        $ausgaben["akz"] = $amtid;
        $dataloop["stellen"][0]["src"] = $pathvars["images"]."aemter/va".$form_values[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.gif";
        $dataloop["stellen"][0]["class"] = "selected";
        $dataloop["stellen"][0]["display"] = "block";

        function aussenstellen($id){
            global $db, $cfg, $dataloop, $hidedata, $form_values, $environment, $felder, $pathvars, $akz_array;

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
                    $buffer[] = $data[$cfg["aemter"]["db"]["dst"]["amt"]];
                    $akz_array[] = $data[$cfg["aemter"]["db"]["dst"]["akz"]];
                    // informationen der einzelnen stellen
                    $class = ""; $display = "none"; $i++;
                    if ( $environment["parameter"][1] == $data[$cfg["aemter"]["db"]["dst"]["akz"]] ) {
                        $class = "selected";
                        $display = "";
                        $dataloop["stellen"][0]["class"] = "";
                        $dataloop["stellen"][0]["display"] = "none";
                    }

                    $dataloop["stellen"][$i] = array(
                                "akz" => $data[$cfg["aemter"]["db"]["dst"]["akz"]],
                              "class" => $class,
                                "src" => $pathvars["images"]."aemter/va".$data[$cfg["aemter"]["db"]["dst"]["akz"]]."_gebaeude.gif",
                            "display" => $display,
                    );
                    // fuer jede stelle die informationen eintragen
                    foreach ( $felder as $feld ) {
                        $dataloop["stellen"][$i][$feld] = $data[$cfg["aemter"]["db"]["dst"][$feld]];
                    }
                    $dataloop["stellen"][$i]["link_suffix"] = $data[$cfg["aemter"]["db"]["dst"]["akz"]];
                    $dataloop["stellen"][$i]["display"] = $display;
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
// echo "<pre>".print_r($dataloop["stellen"],true)."</pre>";

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
        $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/index.html\">".$ausgaben["amt"]."</a>";

        $hidedata["sub_menu"][0] = "enable";
        foreach ( $cfg["aemter"]["sub_menu"] as $key => $value ) {
            $dataloop["sub_menu"][$key] = array(
                 "link" => $value[0],
                "label" => $value[1],
                "class" => "",
            );
        }

        $ausgaben["artikel"] = "";
        $ausgaben["presse"] = "";
        $ausgaben["termine"] = "";
        switch ($environment["parameter"][0]) {
            // startseite
            case "index":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $hidedata["index"]["heading"] = "#(index)";
                $hidedata["heading"]["heading"] = "#(index)";
                unset($hidedata["sub_menu"]);

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

                if ( $environment["parameter"][2] == "" ) {
                    $dataloop["artikel"] = show_blog("/aktuell/archiv",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/archiv"]["rows"],$kat);
                    $hidedata["artikel"] = $hidedata["new"];
                    if ( count($dataloop["artikel"]) > 0 ) {
                        $ausgaben["artikel"] = "<h2>Aktuelle Artikel</h2>";
                    }


                    $dataloop["presse"] = show_blog("/aktuell/presse",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/presse"]["rows"],$kat);
                    $hidedata["presse"] = $hidedata["new"];
                    if ( count($dataloop["presse"]) > 0 ) {
                        $ausgaben["presse"] = "<h2>Aktuelle Pressemitteilungen</h2>";
                    }
                    $tags["titel"]["tag"] = "_NAME";
                    $dataloop["termine"] = show_blog("/aktuell/termine",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/termine"]["rows"],$kat);

                    $hidedata["termine"] = $hidedata["new"];
                    if ( count($dataloop["termine"]) > 0 ) {
                        $ausgaben["termine"] = "<h2>Aktuelle Termine</h2>";
                    }
                }

                break;
            case "artikel":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $tags[] = "";
                $all = show_blog("/aktuell/archiv",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/archiv"]["rows"],$kat);
                $hidedata["all"]["out"] = $all[1]["all"];
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


                $dataloop["edit_lokale_artikel"][]["lokal_edit"] = $pathvars["virtual"]."/wizard/show,".DATABASE.",".eCrc("/aktuell/archiv").".".$environment["parameter"][2].",inhalt,,,none.html";

                    $sql = "SELECT ".$cfg["changed"]["db"]["changed"]["lang"].",
                                ".$cfg["changed"]["db"]["changed"]["changed"].",
                                ".$cfg["changed"]["db"]["changed"]["surname"].",
                                ".$cfg["changed"]["db"]["changed"]["forename"].",
                                ".$cfg["changed"]["db"]["changed"]["email"].",
                                ".$cfg["changed"]["db"]["changed"]["alias"]."
                            FROM ".$cfg["changed"]["db"]["changed"]["entries"]."
                            WHERE label='inhalt' and  tname = '".eCRC("/aktuell/archiv").".".$environment["parameter"][2]."'
                        ORDER BY ".$cfg["changed"]["db"]["changed"]["changed"];

                    $result = $db -> query($sql);
                    $data = $db -> fetch_array($result);
                    $hidedata["all"]["changed"] = date($cfg["changed"]["format"],strtotime($data["changed"]));
                break;
            case "presse":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $tags[] = "";
                $all = show_blog("/aktuell/presse",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/presse"]["rows"],$kat);
                $hidedata["all"]["out"] = $all[1]["all"];
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
                    $hidedata["all"]["out"] = $all[1]["all"];
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

                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/standort.html\">Standort</a>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["heading"]["heading"] = "#(location)";

                foreach ( $akz_array as $key=>$value ) {
                    $amt = $dataloop["stellen"][$key]["amt"];
                    if ( $hauptamt != $dataloop["stellen"][$key]["amt"] ) {
                        $amt = "VA ".$hauptamt."/".$dataloop["stellen"][$key]["amt"];
                    } else {
                        $amt = "VA ".$dataloop["stellen"][$key]["amt"];
                    }
                    $link = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$dataloop["stellen"][$key]["rechtswert"].
                                                                               "&amp;hw=".$dataloop["stellen"][$key]["hochwert"].
                                                                              "&amp;str=".urlencode(utf8_decode($amt)).
                                                                              "&amp;ort=".urlencode(utf8_decode($dataloop["stellen"][$key]["str"].", ".$dataloop["stellen"][$key]["plz"]." ".$dataloop["stellen"][$key]["ort"]));
                    $dataloop["stellen"][$key]["viewer"] = $link;
                }

                $link = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$form_values["georef_rw"].
                                                                           "&amp;hw=".$form_values["georef_hw"].
                                                                           "&amp;str=".$ausgaben["amt"].
                                                                           "&amp;ort=".$form_values["adstr"].", ".$form_values["adplz"]." ".$form_values["adort"];


                $hidedata["gallery"]["viewer"] = $form_values["adbayernviewer"];
                $hidedata["gallery"]["viewer"] = $link;

                if ( $environment["parameter"][2] == "print" ) {
                    $hidedata["gal_print"]["akz"] = $form_values["adakz"];
                } else {
                    $hidedata["gal_sel"]["akz"] = $form_values["adakz"];
                }


                break;

            case "amtsbezirk":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/amtsbezirk.html\">Amtsbezirk</a>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["bezirk"] = array();

                $hidedata["heading"]["heading"] = "#(bezirk)";

                foreach ( $akz_array as $key=>$value ) {
                    $sql = "SELECT DISTINCT gmd.gdecode, gmd.name as gemeinde".
                            " FROM (gemeinden_intranet as gmd LEFT JOIN gmn_gemeinden ON (gmd.gdecode=gemeinde)) ".
                            " WHERE buort='".$value."'".
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
                        $gmkg = "";
                        while ( $dat_gmkg = $db->fetch_array($res_gmkg,1) ){
                            if ( $gmkg != "" ) $gmkg .= ", ";
                            $gmkg .= $dat_gmkg["name"];
                        }

                        $dataloop["gmd"][] = array(
                            "item" => $data["gemeinde"],
                            "gmkg" => $gmkg,
                            "color" => $cfg["aemter"]["color"]["set"]
                        );

                        $buffer[] = "<tr>
                                        <td>".$data["gemeinde"]."</td>
                                        <td>$gmkg</td>
                                     </tr>";
                    }
                    if ( count($buffer) > 0 ) {
                        $dataloop["stellen"][$key]["gmkg"] = implode("\n",$buffer);
                    }
                }

                break;

            case "info":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/info.html\">Informationen f&uuml;r behinderte Menschen</a>";
                $dataloop["sub_menu"][$environment["parameter"][0]]["class"] = "selected";

                $hidedata["info"]["inhalt"] = "#(handicap_".$amtid.")";
                if ( priv_check("/aemter/".$amtid,"edit") ) {
                    $hidedata["info"]["wizard"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,interbvv,amt-allg,handicap_".$amtid.".html\" class=\"button\">VA".$amtid.": Informationen bearbeiten</a>";
                }
                break;

            case "ansprech":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/ansprech.html\">Ansprechpartner</a>";
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
                foreach ( $akz_array as $key=>$value ) {
                    $buffer = array();
                    foreach ( $ansprech as $function ) {
                        $sql = "SELECT *
                                  FROM db_ansprech JOIN db_aemter ON (akz=adakz)
                                 WHERE akz='".$value."'
                                   AND function='".$function."'";
                        $result = $db -> query($sql);
                        if ( $db->num_rows($result) > 0 ) {
                            while ( $data = $db->fetch_array($result,1) ) {
                                $buffer[] = "<tr>
                                                <td>#(".$function.")</td>
                                                <td style=\"white-space:nowrap;\">".$data["name"]."</td>
                                                <td style=\"white-space:nowrap;\">".$data["telefon"]."</td>
                                                <td align=\"center\">
                                                    <a href=\"".$data["ademail"]."\" title=\"".$data["ademail"]."\">
                                                        <img src=\"/images/html/icon_email.jpg\" alt=\"".$data["ademail"]."\" />
                                                    </a>
                                                </td>
                                            </tr>";
                            }
                        } else {
                            $sql = "SELECT *
                                      FROM db_aemter
                                     WHERE adakz='".$value."'";
                            $result = $db -> query($sql);
                            $data = $db->fetch_array($result,1);
                            $buffer[] = "<tr>
                                            <td>#(".$function.")</td>
                                            <td style=\"white-space:nowrap;\">Servicezentrum</td>
                                            <td style=\"white-space:nowrap;\">".$data["adtelver"]."</td>
                                            <td align=\"center\">
                                                <a href=\"".$data["ademail"]."\" title=\"".$data["ademail"]."\">
                                                    <img src=\"/images/html/icon_email.jpg\" alt=\"".$data["ademail"]."\" />
                                                </a>
                                            </td>
                                        </tr>";
                        }
                    }
                    if ( count($buffer) > 0 ) {
                        $dataloop["stellen"][$key]["ansprech"] = implode("\n",$buffer);
                    }
                    // ggf belegschaftsbild
                    $beleg_img_src = $pathvars["fileroot"]."images/html/aemter/va".$value."_belegschaft.jpg";
                    if ( file_exists($beleg_img_src) ) {
                        $beleg_img_web = $pathvars["images"]."aemter/va".$value."_belegschaft.jpg";
                        $dataloop["stellen"][$key]["beleg_img"] = "<img src=\"".$beleg_img_web."\" alt=\"Belegschaft VA ".$dataloop["stellen"][$key]["amt"]."\" />";
                    } else {
                        $dataloop["stellen"][$key]["beleg_img"] = "";
                    }
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
