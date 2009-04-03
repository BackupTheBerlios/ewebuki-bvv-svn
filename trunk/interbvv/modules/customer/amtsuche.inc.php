<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: aemtersuche.inc.php 1131 2007-12-12 08:45:50Z chaot $";
  $Script["desc"] = "amt mithilfe von ort und plz suchen";
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

    // page basics
    // ***

    // label bearbeitung aktivieren
    if ( isset($_GET["edit"]) ) {
        $specialvars["editlock"] = 0;
    } else {
        $specialvars["editlock"] = -1;
    }

    // +++
    // page basics


    // funktions bereich
    // ***

    $ausgaben["search"] = $_GET["search"];

    if ( $_GET["search"] != "" || $_GET["amt"] != "" ) {
        if ( is_numeric($_GET["search"]) ) {
            $sql = "SELECT DISTINCT ".$cfg["amtsuche"]["db"]["plz"]["amt"].",
                                    ".$cfg["amtsuche"]["db"]["plz"]["plz"].",
                                    ".$cfg["amtsuche"]["db"]["plz"]["gmd"].",
                                      '' as gmdteil
                               FROM ".$cfg["amtsuche"]["db"]["plz"]["entries"]."
                              WHERE ".$cfg["amtsuche"]["db"]["plz"]["plz"]."='".$_GET["search"]."'
                           ORDER BY ".$cfg["amtsuche"]["db"]["plz"]["order"].";";
        } elseif ( $_GET["amt"] != "" ) {
            $sql = "SELECT ".$cfg["amtsuche"]["db"]["amt"]["akz"]." as ".$cfg["amtsuche"]["db"]["plz"]["amt"]."
                      FROM ".$cfg["amtsuche"]["db"]["amt"]["entries"]."
                     WHERE ".$cfg["amtsuche"]["db"]["amt"]["akz"]."='".$_GET["amt"]."'";
        } else {
            $sql = "SELECT DISTINCT ".$cfg["amtsuche"]["db"]["plz"]["amt"].",
                                      min(".$cfg["amtsuche"]["db"]["plz"]["plz"].") as plz,
                                    ".$cfg["amtsuche"]["db"]["plz"]["gmd"].",
                                    ".$cfg["amtsuche"]["db"]["plz"]["teil"]."
                               FROM ".$cfg["amtsuche"]["db"]["plz"]["entries"]."
                              WHERE ".$cfg["amtsuche"]["db"]["plz"]["teil"]." LIKE '".$_GET["search"]."%'
                           GROUP BY ".$cfg["amtsuche"]["db"]["plz"]["amt"].",
                                    ".$cfg["amtsuche"]["db"]["plz"]["gmd"].",
                                    ".$cfg["amtsuche"]["db"]["plz"]["teil"]."
                           ORDER BY ".$cfg["amtsuche"]["db"]["plz"]["order"].";";
        }
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $num = $db->num_rows($result);

        if ( $num == 0 ) {
            $hidedata["hit_nop"] = array();
        } elseif ( $num == 1 || $_GET["amt"] ) {
            $data = $db -> fetch_array($result,1);
            // amt-kennzahl
            $akz = $data[$cfg["amtsuche"]["db"]["plz"]["amt"]];
            header("Location: ".$pathvars["virtual"]."/aemter/".$akz."/index.html");
            exit;

            // gesuchter ort
            if ( $_GET["place"] != "" ) {
                $place = $_GET["place"];
            } else {
                $place = $data[$cfg["amtsuche"]["db"]["plz"]["plz"]]." ".$data[$cfg["amtsuche"]["db"]["plz"]["gmd"]];
                if ( $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] != "" && $data[$cfg["amtsuche"]["db"]["plz"]["gmd"]] != $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] ) {
                    $place .= " (".$data[$cfg["amtsuche"]["db"]["plz"]["teil"]].")";
                }
            }
            // amt finden
            $sql_amt = "SELECT *
                          FROM ".$cfg["amtsuche"]["db"]["amt"]["entries"]."
                          JOIN ".$cfg["amtsuche"]["db"]["kategorie"]["entries"]."
                            ON ( CAST (".$cfg["amtsuche"]["db"]["amt"]["kat"]." AS SIGNED ) =".$cfg["amtsuche"]["db"]["kategorie"]["key"].")
                         WHERE ".$cfg["amtsuche"]["db"]["amt"]["akz"]."='".$akz."'";
            $result_amt = $db -> query($sql_amt);
            $data_amt   = $db -> fetch_array($result_amt,1);
            // auf aussen-, service-stelle testen
            if ( $data_amt["adkate"] == 5 || $data_amt["adkate"] == 8 ) {
                $neben = " - ".$data_amt[$cfg["amtsuche"]["db"]["kategorie"]["lang"]]." ".$data_amt[$cfg["amtsuche"]["db"]["amt"]["amt"]]." - ";
                $sql_ha = "SELECT *
                             FROM ".$cfg["amtsuche"]["db"]["amt"]["entries"]."
                             JOIN ".$cfg["amtsuche"]["db"]["kategorie"]["entries"]."
                               ON ( CAST (".$cfg["amtsuche"]["db"]["amt"]["kat"]." AS SIGNED ) =".$cfg["amtsuche"]["db"]["kategorie"]["key"].")
                            WHERE ".$cfg["amtsuche"]["db"]["amt"]["key"]."='".$data_amt[$cfg["amtsuche"]["db"]["amt"]["parent"]]."'";
                $result_ha = $db -> query($sql_ha);
                $data_ha   = $db -> fetch_array($result_ha,1);
                $dienststelle = "Vermessungamt ".$data_ha[$cfg["amtsuche"]["db"]["amt"]["amt"]];
            } else {
                $neben = "";
                $dienststelle = "Vermessungamt ".$data_amt[$cfg["amtsuche"]["db"]["amt"]["amt"]];
            }
            // bayernviewer-link
            $bvlink = "http://www.geodaten.bayern.de/BayernViewer2.0/index.cgi?rw=".$data_amt["georef_rw"].
                                                                         "&amp;hw=".$data_amt["georef_hw"].
                                                                        "&amp;str=".$dienststelle." ".$neben.
                                                                        "&amp;ort=".$data_amt["adstr"].", ".$data_amt["adplz"]." ".$data_amt["adort"];

            $hidedata["hit_one"] = array(
                       "place" => $place,
                "beschreibung" => $dienststelle,
                      "zusatz" => $neben,
                     "strasse" => $data_amt[$cfg["amtsuche"]["db"]["amt"]["str"]],
                         "ort" => $data_amt[$cfg["amtsuche"]["db"]["amt"]["plz"]]." ".$data_amt[$cfg["amtsuche"]["db"]["amt"]["ort"]],
                         "fon" => $data_amt[$cfg["amtsuche"]["db"]["amt"]["fon"]],
                         "fax" => $data_amt[$cfg["amtsuche"]["db"]["amt"]["fax"]],
                       "email" => $data_amt[$cfg["amtsuche"]["db"]["amt"]["email"]],
                    "internet" => $pathvars["virtual"]."/aemter/".$akz."/index.html",
                     "bayview" => $bvlink,
            );
        } elseif ( $num < $cfg["amtsuche"]["db"]["plz"]["max"] ) {
            $hidedata["hit_list"] = array();
            while ( $data = $db -> fetch_array($result,1) ) {
                // suchbegriff hervorheben
                $highlighted = $data[$cfg["amtsuche"]["db"]["plz"]["plz"]]." - ".$data[$cfg["amtsuche"]["db"]["plz"]["gmd"]];
                if ( $data[$cfg["amtsuche"]["db"]["plz"]["gmd"]] != $data[$cfg["amtsuche"]["db"]["plz"]["teil"]]
                  && $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] != "" ) {
                    $highlighted .= " (".$data[$cfg["amtsuche"]["db"]["plz"]["teil"]].")";
                }
                $highlighted = preg_replace("/(".$_GET["search"].")/i","<b>".'$1'."</b>",$highlighted);

                // gesuchter ort
                $place = $data[$cfg["amtsuche"]["db"]["plz"]["plz"]]." ".$data[$cfg["amtsuche"]["db"]["plz"]["gmd"]];
                if ( $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] != "" && $data[$cfg["amtsuche"]["db"]["plz"]["gmd"]] != $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] ) {
                    $place .= " (".$data[$cfg["amtsuche"]["db"]["plz"]["teil"]].")";
                }

                // gemeindeteil
                $gmd_teil = "&nbsp;";
                if ( $data[$cfg["amtsuche"]["db"]["plz"]["gmd"]] != $data[$cfg["amtsuche"]["db"]["plz"]["teil"]] ) {
                    $gmd_teil = $data[$cfg["amtsuche"]["db"]["plz"]["teil"]];
                }

                // amt finden
                $sql_amt = "SELECT *
                              FROM ".$cfg["amtsuche"]["db"]["amt"]["entries"]."
                              JOIN ".$cfg["amtsuche"]["db"]["kategorie"]["entries"]."
                                ON ( CAST (".$cfg["amtsuche"]["db"]["amt"]["kat"]." AS SIGNED ) =".$cfg["amtsuche"]["db"]["kategorie"]["key"].")
                             WHERE ".$cfg["amtsuche"]["db"]["amt"]["akz"]."='".$data[$cfg["amtsuche"]["db"]["plz"]["amt"]]."'";
                $result_amt = $db -> query($sql_amt);
                $data_amt   = $db -> fetch_array($result_amt,1);
                // auf aussen-, service-stelle testen
                if ( $data_amt["adkate"] == 5 || $data_amt["adkate"] == 8 ) {
                    $neben = "<br />".$data_amt[$cfg["amtsuche"]["db"]["kategorie"]["lang"]]." ".$data_amt[$cfg["amtsuche"]["db"]["amt"]["amt"]];
                    $sql_ha = "SELECT *
                                 FROM ".$cfg["amtsuche"]["db"]["amt"]["entries"]."
                                 JOIN ".$cfg["amtsuche"]["db"]["kategorie"]["entries"]."
                                   ON ( CAST (".$cfg["amtsuche"]["db"]["amt"]["kat"]." AS SIGNED ) =".$cfg["amtsuche"]["db"]["kategorie"]["key"].")
                                WHERE ".$cfg["amtsuche"]["db"]["amt"]["key"]."='".$data_amt[$cfg["amtsuche"]["db"]["amt"]["parent"]]."'";
                    $result_ha = $db -> query($sql_ha);
                    $data_ha   = $db -> fetch_array($result_ha,1);
                    $dienststelle = "Vermessungamt ".$data_ha[$cfg["amtsuche"]["db"]["amt"]["amt"]];
                } else {
                    $neben = "";
                    $dienststelle = "Vermessungamt ".$data_amt[$cfg["amtsuche"]["db"]["amt"]["amt"]];
                }

                $dataloop["hits"][] = array(
                 "amtzahl" => $data[$cfg["amtsuche"]["db"]["plz"]["amt"]],
                     "amt" => $dienststelle.$neben,
                     "plz" => preg_replace("/(".$_GET["search"].")/i","<b>".'$1'."</b>",$data[$cfg["amtsuche"]["db"]["plz"]["plz"]]),
                     "gmd" => preg_replace("/(".$_GET["search"].")/i","<b>".'$1'."</b>",$data[$cfg["amtsuche"]["db"]["plz"]["gmd"]]),
                    "teil" => preg_replace("/(".$_GET["search"].")/i","<b>".'$1'."</b>",$gmd_teil),
           "link_amt_info" => "?amt=".$data[$cfg["amtsuche"]["db"]["plz"]["amt"]]."&place=".urlencode($place),
          "link_amt_seite" => $pathvars["virtual"]."/aemter/".$data[$cfg["amtsuche"]["db"]["plz"]["amt"]]."/index.html",
                      "hl" => $highlighted,
                );
            }
        } else {
            $hidedata["hit_toomuch"] = array();
            $ausgaben["num"] = $num;
        }
    }
    $hidedata["leer"][0] = "enable";
    // +++
    // funktions bereich


    // page basics
    // ***

    // fehlermeldungen
    if ( $_GET["error"] != "" ) {
        if ( $_GET["error"] == 1 ) {
            $ausgaben["form_error"] = "#(error1)";
        }
    } else {
        $ausgaben["form_error"] = "";
    }

    // hidden values
    #$ausgaben["form_hidden"] .= "";

    // was anzeigen
    $mapping["main"] = "aemtersuche";
    #$mapping["navi"] = "leer";

    // unzugaengliche #(marken) sichtbar machen
    if ( isset($_GET["edit"]) ) {
        $ausgaben["inaccessible"] = "inaccessible values:<br />";
        $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
    } else {
        $ausgaben["inaccessible"] = "";
    }

    // wohin schicken
    #n/a

    // +++
    // page basics

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
