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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    if ( $cfg["right"] == "" || $rechte[$cfg["right"]] == -1 ) {

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
                  FROM ".$cfg["db"]["dst"]["entries"]."
                  JOIN db_adrd_kate on (adkate=katid)
                 WHERE adakz=".$amtid;
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
                      JOIN db_adrd_kate ON (adkate=katid)
                     WHERE adid=".$id." AND adkate IN (3,4,5,8)";
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
                        JOIN db_adrd_kate ON (adkate=katid)
                       WHERE adparent=".$id." AND adkate IN (5,8)";
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
                  JOIN db_adrd_kate ON (adkate=katid)
                 WHERE adparent=".$form_values["adid"]."
                   AND adkate IN (5,8)";
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            $ausgaben["amt"] .= " mit Au&szlig;enstelle";
            aussenstellen($form_values["adid"]);
        }else{
//             echo "Keine Aussenstelle";
        }

        // ist das amt eine aussenstelle?
        $sql = "SELECT * FROM db_adrd WHERE adkate IN (3,4) AND adid=".$form_values["adparent"];
// echo "Aussenstelle".$sql;
        $result = $db -> query($sql);
        if ( $db->num_rows($result) > 0 ){
            $data = $db->fetch_array($result,1);
            $ausgaben["amt"] = "Vermessungsamt ".$data["adststelle"]." - ".$form_values["kat_lang"]." ".$form_values["adststelle"];
            aussenstellen($data["adid"]);
        }
//         echo "--".$db->num_rows($result);

        switch ($environment["parameter"][0]){
            // startseite
            case "index":

                // nachrichten
                $sql = "SELECT * FROM db_info WHERE ifqdn0 IN ('www','intra".$amtid."') ORDER BY ivon";
                $result = $db -> query($sql);
                while ( $data = $db->fetch_array($result,1) ) {
                    if ( $data["ifqdn0"] == "www" ){
                        // bayernweit
                        $dataloop["bayern"][] = array(
                            "link" => "news,".$data["iid"].".html",
                            "item" => $data["ititel"]." (".substr($data["ivon"],8,2).".".substr($data["ivon"],5,2).".".substr($data["ivon"],0,4).")"
                        );
                        $hidedata["bayern"][0] = "enable";
                    }else{
                        // lokal
                        $dataloop["lokal"][] = array(
                            "link" => "news/details,".$data["iid"].".html",
                            "item" => $data["ititel"]." (".substr($data["ivon"],8,2).".".substr($data["ivon"],5,2).".".substr($data["ivon"],0,4).")"
                        );
                        $hidedata["lokal"][0] = "enable";
                    }

                    // alle
                    $dataloop["news"][] = array(
                        "link" => "news,".$data["iid"].".html",
                        "item" => $data["ititel"]." (".substr($data["ivon"],8,2).".".substr($data["ivon"],5,2).".".substr($data["ivon"],0,4).")"
                    );
                }
                break;

            case "standort":

                for ($i=1;$i<4;$i++){
                    $dataloop["gallery"][] = array(
                        "id"     => $i,
                        "amtakz" => $amtid
                    );
                }
                if ( $environment["parameter"][1] != "" ){
// echo "hallo1";
                    $hidedata["skizze"]["id"] = $environment["parameter"][1];
                    $hidedata["skizze"]["amtakz"] = $amtid;
                    $hidedata["skizze"]["viewer"] = $form_values["adviewer"];
                }else{
// echo "hallo2";
                    $hidedata["gallery"][0] = "enable";
                    $hidedata["gallery"]["viewer"] = $form_values["adviewer"];
                }


                break;

            case "amtsbezirk";
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
                    if ( $cfg["color"]["set"] == $cfg["color"]["a"]) {
                        $cfg["color"]["set"] = $cfg["color"]["b"];
                    } else {
                        $cfg["color"]["set"] = $cfg["color"]["a"];
                    }

                    $dataloop["gmd"][] = array(
                        "item" => $data["gemeinde"],
                        "gmkg" => $gmkg,
                        "color" => $cfg["color"]["set"]
                    );
                }
                break;
        }


        // +++
        // funktions bereich


        // page basics
        // ***

        // navigation erstellen
        $ausgaben["add"] = $cfg["basis"]."/add,".$environment["parameter"][1].",verify.html";
        #$mapping["navi"] = "leer";

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "amt-allg";
        #$mapping["navi"] = "leer";

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