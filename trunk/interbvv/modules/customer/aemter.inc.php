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

        // page basics
        // ***

        // +++
        // page basics


        // funktions bereich
        // ***

        ### put your code here ###

        // amtkennzahl bestimmen
        $arrEbene = explode("/",$environment["ebene"]);
        $amtid = $arrEbene["2"];

        // datensatz holen
        $sql = "SELECT *
                  FROM ".$cfg["aemter"]["db"]["dst"]["entries"]."
                  JOIN db_adrd_kate on (cast(adkate as signed)=katid)
                 WHERE adakz='".$amtid."'";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $form_values = $db -> fetch_array($result,1);

        // ausgabe-marken belegen
        $ausgaben["amt"] = "Vermessungsamt ".$form_values["adststelle"];
        $ausgaben["akz"] = $amtid;
        $ausgaben["str"] = $form_values["adstr"];
        $ausgaben["plz"] = $form_values["adplz"];
        $ausgaben["ort"] = $form_values["adort"];
        $ausgaben["tel"] = $form_values["adtelver"];
        $ausgaben["fax"] = $form_values["adfax"];
        $ausgaben["email"] = $form_values["ademail"];

        function aussenstellen($id){
            global $db, $amtid, $environment, $dataloop, $pathvars;

            $sql = "SELECT *
                      FROM db_adrd
                      JOIN db_adrd_kate ON (cast(adkate as signed)=katid)
                     WHERE adid=".$id." AND adkate IN ('3','4','5','8')";
            $result = $db -> query($sql);
            $data = $db->fetch_array($result,1);
            $amt  = $data["kat_lang"]." ".$data["adststelle"];
            $link = $pathvars["virtual"]."/aemter/".$data["adakz"]."/".$environment["kategorie"].".html";
            $dataloop["ast"][$data["adakz"]] = array(
                "amt"  => "zum Hauptamt ".$data["adststelle"],
                "link" => $link
            );

            $sql = "SELECT *
                      FROM db_adrd
                        JOIN db_adrd_kate ON (cast(adkate as signed)=katid)
                       WHERE adparent=".$id." AND adkate IN ('5','8')";
            $result = $db -> query($sql);
            while ( $data = $db->fetch_array($result,1) ){
                $amt  = $data["kat_lang"]." ".$data["adststelle"];
                $link = $pathvars["virtual"]."/aemter/".$data["adakz"]."/".$environment["kategorie"].".html";
                $dataloop["ast"][$data["adakz"]] = array(
                    "amt"  => "zur ".$amt,
                    "link" => $link
                );
            }

            unset( $dataloop["ast"][$amtid] );
        }

        // gibt es einen aussenstelle?
        $sql = "SELECT *
                  FROM db_adrd
                  JOIN db_adrd_kate ON (cast(adkate as signed)=katid)
                 WHERE adparent=".$form_values["adid"]."
                   AND adkate IN ('5','8')";
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            $ausgaben["amt"] .= " mit Au&szlig;enstelle";
            aussenstellen($form_values["adid"]);
        }else{
//             echo "Keine Aussenstelle";
        }

        // ist das amt eine aussenstelle?
        $sql = "SELECT *
                  FROM db_adrd WHERE adkate IN ('3','4') AND adid=".$form_values["adparent"];
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            $data = $db->fetch_array($result,1);
            $ausgaben["amt"] = "Vermessungsamt ".$data["adststelle"]." - ".$form_values["kat_lang"]." ".$form_values["adststelle"];
            aussenstellen($data["adid"]);
        }

        // kekse anpassen
        $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/index.html\">".$ausgaben["amt"]."</a>";

        // bild v. amtsgebaeude
        $extensions = array_keys($cfg["file"]["filetyp"],"img");
        foreach ( $extensions as $value ) {
            if ( file_exists(rtrim($pathvars["fileroot"],"/").$pathvars["images"]."aemter/va".$ausgaben["akz"]."_gebaeude.".$value) ) {
                $hidedata["amtpic"]["src"] = $pathvars["images"]."aemter/va".$ausgaben["akz"]."_gebaeude.".$value;
                break;
            }
        }
        // wms-aufruf
        $bbox = array(
            "lu_x" => $form_values["adrechtswert"] - ($cfg["aemter"]["wms"]["width"]*$cfg["aemter"]["wms"]["m"]/2),
            "lu_y" => $form_values["adhochwert"] - ($cfg["aemter"]["wms"]["height"]*$cfg["aemter"]["wms"]["m"]/2),
            "ro_x" => $form_values["adrechtswert"] + ($cfg["aemter"]["wms"]["width"]*$cfg["aemter"]["wms"]["m"]/2),
            "ro_y" => $form_values["adhochwert"] + ($cfg["aemter"]["wms"]["height"]*$cfg["aemter"]["wms"]["m"]/2),
        );
        $hidedata["amtpic"]["scr_bg"] = str_replace(array("##LAYERS##","##BBOX##","##WIDTH##","##HEIGHT##"
                                                    ),
                                                    array($cfg["aemter"]["wms"]["layers"],implode(",",$bbox),$cfg["aemter"]["wms"]["width"],$cfg["aemter"]["wms"]["height"]
                                                    ),
                                                    $cfg["aemter"]["wms"]["url"]
                                        );
        $wms_background = $cfg["aemter"]["wms"]["url"];

        $hidedata["sub_menu"][0] = "enable";
        $ausgaben["artikel"] = "";
        $ausgaben["presse"] = "";
        $ausgaben["termine"] = "";
        switch ($environment["parameter"][0]){
            // startseite
            case "index":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $hidedata["index"][0] = "enable";
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
                    $tags["titel"]["tag"] = "NAME";
                    $dataloop["termine"] = show_blog("/aktuell/termine",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/termine"]["rows"],$kat);

                    $hidedata["termine"] = $hidedata["new"];
                    if ( count($dataloop["termine"]) > 0 ) {
                        $ausgaben["termine"] = "<h2>Aktuelle Termine</h2>";
                    }


                }

//                 if ( $environment["parameter"][2] != "" ) {
//                     $hidedata["artikel"][0] = "enable";
//                 } else {
//                     $hidedata["index"][0] = "enable";
//                     unset($hidedata["sub_menu"]);
//                 }

                break;
            case "artikel":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $tags[] = "";
                $all = show_blog("/aktuell/archiv",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/archiv"]["rows"],$kat);
                $hidedata["all"]["out"] = $all[1]["all"];
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
            case "presse":
                require_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                require_once $pathvars["moduleroot"]."libraries/function_show_blog.inc.php";

                $tags[] = "";
                $all = show_blog("/aktuell/presse",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/presse"]["rows"],$kat);
                $hidedata["all"]["out"] = $all[1]["all"];
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

                if ( $environment["parameter"][4] == "add" || $environment["parameter"][4] == "edit" ) {
                    $hidedata["termine_add"]["name"] = "";
                    $hidedata["termine_add"]["ort"] = "";
                    $hidedata["termine_add"]["beschreibung"] = "";
                    $hidedata["termine_add"]["sort"] = "";
                    $hidedata["termine_add"]["termin"] = "";
                    $hidedata["termine_add"]["termin_en"] = "";
                    $hidedata["termine_add"]["wizard"] = "artikel";

                    $id = make_id("/aktuell/termine");
                    $ausgaben["form_aktion"] = $pathvars["virtual"]."/admin/bloged/add,".$id["mid"].".html";
                    $sql = "SELECT content FROM site_text WHERE tname='".eCRC("/aktuell/termine").".".$environment["parameter"][2]."'";
                    $result = $db -> query($sql);
                    $data = $db -> fetch_array($result,1);

                    if ( $environment["parameter"][4] == "edit" ) {
                        foreach ( $cfg["bloged"]["blogs"]["/aktuell/termine"]["addons"] as $key => $value ) {
                            if ( is_array($value) ) {
                                $value = $value["tag"];
                            }
                            preg_match("/\[$value\](.*)\[\/$value\]/",$data["content"],$regs);
                            if ( $regs[1] == "1970-01-01" ) $regs[1] = "";
                            $hidedata["termine_add"][$key] = $regs[1];
                        }
                        $ausgaben["form_aktion"] = $pathvars["virtual"].$url.",,".$environment["parameter"][2].",,edit.html";
                        if ( $_POST ) {
                            foreach ( $_POST as $key => $value ) {
                                if ( $key == "TERMIN" && $value == "" ) $value = "1970-01-01";
                                $data["content"] = preg_replace("/\[$key\].*\[\/$key\]/","[".$key."]".$value."[/".$key."]",$data["content"]);
                            }
                            $sql = "UPDATE site_text SET content ='".$data["content"]."[!]wizard:artikel[/!]' WHERE tname='".eCRC("/aktuell/termine").".".$environment["parameter"][2]."'";
                            $result = $db -> query($sql);
                            header("Location: termine,,".$environment["parameter"][2].".html");
                        }
                    }

                } else {
                    $hidedata["termine_detail"]["in"] = "on";
                    $tags["name"] = "NAME";
                    $tags["veranstalter"] = "VERANSTALTER";
                    $tags["termin"] = "SORT";
                    $tags["termin_en"] = "TERMIN";
                    $tags["ort"] = "ORT";
                    $tags["beschreibung"] = "BESCHREIBUNG";
                    $tags["titel"] = "H1";
                    $all = show_blog("/aktuell/termine",$tags,$cfg["auth"]["ghost"]["contented"],$cfg["bloged"]["blogs"]["/aktuell/termine"]["rows"],$kat);

                    if ( $environment["parameter"][3] == "all" ) {
                        #$hidedata["out"]["i"] = $all[1]["all"];
                        $hidedata["all"]["out"] = $all[1]["all"];
                    }

                    foreach ( $tags as $key => $value ) {
                        if ( $key == "titel" ) continue;
                        if ( $all[1][$key."_org"] == "1970-01-01" ) continue;
                        $dataloop["termine_detail"][$key]["name"] = $all[1][$key."_org"];
                        $dataloop["termine_detail"][$key]["desc"] = "g(termine_".$key.")";
                    }
                }

                if ( $all[1]["titel"] != "" ) {
                    if ( $environment["parameter"][3] == "all" ) {
                        $dataloop["termine_detail"]["weitere"]["name"] = "<a href=\"termine,,".$environment["parameter"][2].".html\">g(close)</a>";
                    } else {
                        $dataloop["termine_detail"]["weitere"]["name"] = "<a href=\"termine,,".$environment["parameter"][2].",all.html\">g(open)</a>";
                    }
                    $dataloop["termine_detail"]["weitere"]["desc"] = "#(more_infos)";
                }

                if ( $cfg["bloged"]["blogs"]["/aktuell/termine"]["right"] == "" || ( priv_check($url,$cfg["bloged"]["blogs"]["/aktuell/termine"]["right"]) || ( function_exists(priv_check_old) && priv_check_old("",$cfg["bloged"]["blogs"][$url]["right"]) ) ) ) {
                    $dataloop["termine_detail"]["edit"]["name"] = "<a href=\"".$pathvars["virtual"].$url.",,".$environment["parameter"][2].",,edit.html\">#(meta)"."</a>
                                                                  <a href=\"".$pathvars["virtual"]."/wizard/show,".DATABASE.",".eCRC("/aktuell/termine").".".$environment["parameter"][2].",inhalt.html\"> #(add_infos)"."</a>";
                    $dataloop["termine_detail"]["edit"]["desc"] = "Aktionen:";
                }

                #$hidedata["all"]["inhalt"] = $all[1]["all"];
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



            case "standort":

                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/standort.html\">Standort</a>";

                for ($i=1;$i<4;$i++){
                    $dataloop["gallery"][] = array(
                        "id"     => $i,
                        "amtakz" => $amtid
                    );
                }
                $hidedata["gallery"][0] = "enable";

                $link = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$form_values["adrechtswert"].
                                                                           "&amp;hw=".$form_values["adhochwert"].
                                                                           "&amp;str=".$ausgaben["amt"].
                                                                           "&amp;ort=".$form_values["adstr"].", ".$form_values["adplz"]." ".$form_values["adort"];


                $hidedata["gallery"]["viewer"] = $form_values["adbayernviewer"];
                $hidedata["gallery"]["viewer"] = $link;

                if ( $environment["parameter"][1] == "print" ) {
                    $hidedata["gal_print"][] = "enable";
                } else {
                    $hidedata["gal_sel"][] = "enable";
                }


                break;

            case "amtsbezirk":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/amtsbezirk.html\">Amtsbezirk</a>";
                $hidedata["bezirk"]["amtakz"] = $amtid;
                $sql = "SELECT DISTINCT gmd.gdecode, gmd.name as gemeinde".
                        " FROM (gemeinden_intranet as gmd LEFT JOIN gmn_gemeinden ON (gmd.gdecode=gemeinde)) ".
                        " WHERE buort='".$amtid."'".
                        " ORDER BY gmd.name";
                $result = $db -> query($sql);
                $prev = "";
                while ( $data = $db->fetch_array($result,1) ) {
                    // gemarkungen
                    $sql = "SELECT DISTINCT name".
                            " FROM gmn_gemeinden JOIN gmn_intranet ON (gmn=gmcode)".
                           " WHERE gemeinde=".$data["gdecode"].
                        " ORDER BY name";
// echo "--".$sql;

                    $res_gmkg = $db -> query($sql);
                    $gmkg = "";
                    while ( $dat_gmkg = $db->fetch_array($res_gmkg,1) ){
                        if ( $gmkg != "" ) $gmkg .= ", ";
                        $gmkg .= $dat_gmkg["name"];
                    }

                    // tabellen farben wechseln
                    if ( $cfg["aemter"]["color"]["set"] == $cfg["aemter"]["color"]["a"]) {
                        $cfg["aemter"]["color"]["set"] = $cfg["aemter"]["color"]["b"];
                    } else {
                        $cfg["aemter"]["color"]["set"] = $cfg["aemter"]["color"]["a"];
                    }

                    $dataloop["gmd"][] = array(
                        "item" => $data["gemeinde"],
                        "gmkg" => $gmkg,
                        "color" => $cfg["aemter"]["color"]["set"]
                    );
                }
                break;

            case "info":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/info.html\">Informationen f&uuml;r behinderte Menschen</a>";
                $hidedata["info"]["inhalt"] = "#(handicap_".$amtid.")";
                if ( priv_check("/aemter/".$amtid,"edit") ) {
                    $hidedata["info"]["wizard"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,interbvv,amt-allg,handicap_".$amtid.".html\" class=\"button\">VA".$amtid.": Informationen bearbeiten</a>";
                }
                break;

            case "ansprech":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/ansprech.html\">Ansprechpartner</a>";
                $hidedata["ansprech"]["inhalt"] = "#(ansprech_".$amtid.")";
                if (priv_check("/aemter/".$amtid,"edit") ) {
                    $hidedata["ansprech"]["wizard"] = "<a href=\"".$pathvars["virtual"]."/wizard/show,interbvv,amt-allg,ansprech_".$amtid.".html\" class=\"button\">VA".$amtid.": Ansprechpartner</a>";
                }
                break;

            case "amtschronik":
                $environment["kekse"] .= $defaults["split"]["kekse"]."<a href=\"".$pathvars["virtual"]."/aemter/".$amtid."/amtschronik.html\">Amtschronik</a>";
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

        // wohin schicken
        #n/a

        // unzugaengliche #/g(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "g (open) g(open)<br />";
            $ausgaben["inaccessible"] .= "g (close) g(close)<br />";
            $ausgaben["inaccessible"] .= "# (error_dupe) #(error_dupe)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // +++
        // page basics

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
