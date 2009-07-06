<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2009 Werner Ammon ( wa<at>chaos.de )

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

    if ( $cfg["leer"]["right"] == "" || $rechte[$cfg["leer"]["right"]] == -1 ) {

        // funktions bereich
        // ***

        // label bearbeitung aktivieren
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        if ( /*$environment["parameter"][1] == "" || */$environment["parameter"][2] == "" ) {
            $hidedata["inhalt"] = array();
            $ausgaben["akz"] = $environment["parameter"][1];
            $ausgaben["inaccessible"] = "";
        } else {

            if ( $environment["parameter"][2] == "" ) {
                $environment["parameter"][2] = "lageplan";
            }

            // amtkennzahl bestimmen und ggf zur url MIT amtkennzahl springen
            if ( strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") ) {
                if ( $environment["parameter"][1] == "" ) {
                    preg_match("/.*(vermessungsamt-.*)[\.]{1}.*/U",$_SERVER["SERVER_NAME"],$match);
                    $sql = "SELECT *
                            FROM ".$cfg["katauszug"]["db"]["amt"]["entries"]."
                            WHERE ".$cfg["katauszug"]["db"]["amt"]["internet"]." LIKE '%".$match[1]."%'
                            AND ".$cfg["katauszug"]["db"]["amt"]["kate"]." IN ('3','4')";
                } else {
                    $sql = "SELECT *
                            FROM ".$cfg["katauszug"]["db"]["amt"]["entries"]."
                            WHERE ".$cfg["katauszug"]["db"]["amt"]["akz"]." = '".$environment["parameter"][1]."'
                            AND ".$cfg["katauszug"]["db"]["amt"]["kate"]." IN ('3','4')";
                }
                $result = $db -> query($sql);
                $data = $db -> fetch_array($result,1);
                if ( $environment["parameter"][1] == "" ) {
                    $header = $cfg["katauszug"]["basis"]."/".$cfg["katauszug"]["name"].",".$data[$cfg["katauszug"]["db"]["amt"]["akz"]].",".$environment["parameter"][2].".html";
                    header("Location: ".$header);
                }
                $ausgaben["akz"] = $environment["parameter"][1];
            }

            // PLZ-Suche
            if ( $_POST["s_plz"] != "" ) {
                $sql = "SELECT DISTINCT ".$cfg["katauszug"]["db"]["plz"]["akz"]."
                                   FROM ".$cfg["katauszug"]["db"]["plz"]["entries"]."
                                  WHERE ".$cfg["katauszug"]["db"]["plz"]["plz"]."=".$_POST["s_plz"];
                $result = $db -> query($sql);
                $num = $db->num_rows($result);
                if ( $num == 1 ) {
                    $data = $db -> fetch_array($result,1);
                    // auf aussenstelle pruefen
                    $sql = "SELECT amt.".$cfg["katauszug"]["db"]["amt"]["kate"]." as kategorie,
                                   parent.".$cfg["katauszug"]["db"]["amt"]["akz"]." as kennzahl
                              FROM ".$cfg["katauszug"]["db"]["amt"]["entries"]." as amt
                              JOIN ".$cfg["katauszug"]["db"]["amt"]["entries"]." as parent
                                ON (amt.".$cfg["katauszug"]["db"]["amt"]["parent"]."=parent.".$cfg["katauszug"]["db"]["amt"]["key"].")
                             WHERE amt.".$cfg["katauszug"]["db"]["amt"]["akz"]."='".$data[$cfg["katauszug"]["db"]["plz"]["akz"]]."'";
                    $res_test  = $db -> query($sql);
                    $data_test = $db -> fetch_array($res_test,1);
                    if ( $data_test["kategorie"] == 5 ) {
                        $akz = $data_test["kennzahl"];
                    } else {
                        $akz = $data[$cfg["katauszug"]["db"]["plz"]["akz"]];
                    }
                    $header = $cfg["katauszug"]["basis"]."/".$cfg["katauszug"]["name"].",".$akz.",".$environment["parameter"][2].".html";
                    header("Location: ".$header);
                } else {
                    $hidedata["form_error"] = array();
                    if ( $num == 0 ) {
                        $ausgaben["form_error"]  = "#(error_plz_nope)";
                    } else {
                        $ausgaben["form_error"]  = "#(error_plz_multi)";
                    }
                }
            }

            // falls das amt mit dropdown gewechselt wird
            if ( $_POST["amt_wechseln"] != "" && $environment["parameter"][1] != $_POST["amt_wechseln"] ) {
                $header = $cfg["katauszug"]["basis"]."/".$cfg["katauszug"]["name"].",".$_POST["amt_wechseln"].",".$environment["parameter"][2].".html";
                header("Location: ".$header);
            }

            // ueberschriftsmarke besetzen
            if ( !strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") ) {
                $hidedata["change_amt"] = array();
            } else {
                $hidedata["amt_page"]["amt"] = "VA ".$data[$cfg["katauszug"]["db"]["amt"]["name"]];
            }
            $hidedata["ueberschrift"]["label"] = "#(".$environment["parameter"][2].")";
            if ( $_POST["amt_wechseln"] != "" && $_POST["amt_wechseln"] != $environment["parameter"][1] ) {
                $environment["parameter"][1] = $_POST["amt_wechseln"];
            }

            // form options holen
            $form_options = form_options("katauszug");

            $person_data = array("surname", "forename", "strasse", "plz", "ort", "tel", "email");
            foreach ( $person_data as $value ) {
                $ausgaben[$value] = $_POST["person"][$value];
            }
            $ausgaben["sonder"] = $_POST["sonder"];
            $ausgaben["versand_adresse"] = $_POST["versand_adresse"];


            // dienststellen-dropdown
            $sql = "SELECT *
                    FROM ".$cfg["katauszug"]["db"]["amt"]["entries"]."
                    WHERE ".$cfg["katauszug"]["db"]["amt"]["kate"]." IN ('3','4')
                ORDER BY ".$cfg["katauszug"]["db"]["amt"]["order"];
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql (amt-dropdown): ".$sql.$debugging["char"];
            $result = $db -> query($sql);
            $amtkennzahl = "";
            while ( $data = $db -> fetch_array($result,1) ) {
                if ( $data[$cfg["katauszug"]["db"]["amt"]["akz"]] == $environment["parameter"][1] ) {
                    $sel = " selected=\"selected\"";
                    $amtkennzahl = $data[$cfg["katauszug"]["db"]["amt"]["akz"]];
                    $amt_poststelle = $data[$cfg["katauszug"]["db"]["amt"]["mail"]];
                    $dienststelle = $data[$cfg["katauszug"]["db"]["amt"]["name"]];
                } else {
                    $sel = "";
                }
                $dataloop["amt"][] = array(
                    "value" => $data[$cfg["katauszug"]["db"]["amt"]["akz"]],
                    "item" => "VA ".$data[$cfg["katauszug"]["db"]["amt"]["name"]],
                    "sel" => $sel,
                );
            }

            if ( $amtkennzahl != "" ) {
                $hidedata["formular"] = array();

                // aussenstellen mitnehmen
                $sql = "SELECT aussen.*
                          FROM db_aemter as haupt
                          JOIN db_aemter as aussen
                            ON (aussen.adparent=haupt.adid)
                         WHERE haupt.adakz = '".$amtkennzahl."'";
                $result = $db -> query($sql);
                $gmkg_akz = array();
                $dst_name = array();
                if ( $db->num_rows($result) > 0 ) {
                    while ( $data = $db -> fetch_array($result,1) ) {
                        $gmkg_akz[] = $data["adakz"];
                        $dst_name[] = $data["adststelle"];
                    }
                }
                $gmkg_akz[] = $amtkennzahl;

                $hidedata["amt_page"]["amt"] = "VA ".$dienststelle;
                if ( count($dst_name) > 0 ) $hidedata["amt_page"]["amt"] .= " mit Au&szlig;enstelle ".implode(", ",$dst_name);
                unset($hidedata["change_amt"]);

                // gemarkungs-dropdown
                $sql = "SELECT DISTINCT gmcode as gmkg_nr, name as gmkg
                        FROM gmn_gemeinden JOIN gmn_intranet ON (gmn=gmcode)
                        WHERE buort IN ('".implode("', '",$gmkg_akz)."')
                    ORDER BY name";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    $item = $data["gmkg"]." (".$data["gmkg_nr"].")";
                    for ( $i = 0; $i < 6; $i++) {
                        $sel = "";
                        if ( $_POST["order"][$i]["gmkg"] == $item
                        || $_POST["masszahlen"][$i]["gmkg"] == $item
                        || $_POST["koordinaten"][$i]["gmkg"] == $item ) {
                            $sel = " selected=\"selected\"";
                        }
                        $dataloop["gmkg_".$i][] = array(
                            "value" => $item,
                            "item" => $item,
                            "sel" => $sel,
                        );
                    }
                }

                for ( $i = 0; $i < 6; $i++ ) {
                    // anzahl-dropdowns
                    for ( $count = 1; $count <= 5; $count++ ) {
                        $sel = "";
                        if ( $_POST["order"][$i]["anzahl"] == $count
                        || $_POST["masszahlen"][$i]["anzahl"] == $count
                        || $_POST["koordinaten"][$i]["anzahl"] == $count ) {
                            $sel = " selected=\"selected\"";
                        }
                        $dataloop["anzahl_".$i][] = array(
                            "value" => $count,
                            "item" => $count,
                            "sel" => $sel,
                        );
                    }
                    // massstab-dropdowns
                    foreach ( $cfg["katauszug"]["massstab"] as $massstab ) {
                        $sel = "";
                        if ( $_POST["order"][$i]["massstab"] == $massstab
                        || $_POST["masszahlen"][$i]["massstab"] == $massstab
                        || $_POST["koordinaten"][$i]["massstab"] == $massstab ) {
                            $sel = " selected=\"selected\"";
                        }
                        $dataloop["massstab_".$i][] = array(
                            "value" => $massstab,
                            "item" => $massstab,
                            "sel" => $sel,
                        );
                    }
                    // format-dropdowns
                    foreach ( $cfg["katauszug"]["format"] as $format ) {
                        $sel = "";
                        if ( $_POST["order"][$i]["format"] == $format ) $sel = " selected=\"selected\"";
                        $dataloop["format_".$i][] = array(
                            "value" => $format,
                            "item" => $format,
                            "sel" => $sel,
                        );
                    }
                    // din-dropdowns
                    foreach ( $cfg["katauszug"]["din"] as $din ) {
                        $sel = "";
                        if ( $_POST["masszahlen"][$i]["din"] == $din ) $sel = " selected=\"selected\"";
                        $dataloop["din_".$i][] = array(
                            "value" => $din,
                            "item" => $din,
                            "sel" => $sel,
                        );
                    }
                    // beglaubigungs-radios
                    $check_yes = ""; $check_no = "";
                    if ( is_array($_POST["order"][$i]) && $_POST["order"][$i]["begl"] != "ja" ) {
                        $check_no = " checked=\"checked\"";
                    } else {
                        $check_yes = " checked=\"checked\"";
                    }
                    $dataloop["begl_".$i][] = array(
                        "value" => "ja",
                        "item" => "g(yes)",
                        "check" => $check_yes,
                    );
                    $dataloop["begl_".$i][] = array(
                        "value" => "nein",
                        "item" => "g(no)",
                        "check" => $check_no,
                    );
                    $check_yes = ""; $check_no = "";
                    if ( (is_array($_POST["order"][$i]) && $_POST["order"][$i]["add_begl"] != "nein")
                    || (is_array($_POST["masszahlen"][$i]) && $_POST["masszahlen"][$i]["add_begl"] != "nein") ) {
                        $check_yes = " checked=\"checked\"";
                    } else {
                        $check_no = " checked=\"checked\"";
                    }
                    $dataloop["add_begl_".$i][] = array(
                        "value" => "ja",
                        "item" => "g(yes)",
                        "check" => $check_yes,
                    );
                    $dataloop["add_begl_".$i][] = array(
                        "value" => "nein",
                        "item" => "g(no)",
                        "check" => $check_no,
                    );
                    // flst-nr
                    $hidedata[$environment["parameter"][2]]["flst_".$i] = $_POST["order"][$i]["flst"];
                    $hidedata[$environment["parameter"][2]]["umgriff_".$i] = $_POST["order"][$i]["umgriff"];
                    if ( $_POST["koordinaten"][$i]["flst"] != "" ) {
                        $hidedata[$environment["parameter"][2]]["koordFlst_".$i] = $_POST["koordinaten"][$i]["flst"];
                    } elseif ( $_POST["masszahlen"][$i]["flst"] != "" ) {
                        $hidedata[$environment["parameter"][2]]["flst_".$i] = $_POST["masszahlen"][$i]["flst"];
                    }
                    // checkboxen
                    $ausgaben["check_list_".$i] = "";$ausgaben["check_disk_".$i] = "";
                    if ( is_array($_POST["koordinaten"][$i]) ) {
                        if ( $_POST["koordinaten"][$i]["list"] != "" ) $ausgaben["check_list_".$i] = " checked=\"checked\"";
                        if ( $_POST["koordinaten"][$i]["disc"] != "" ) $ausgaben["check_disk_".$i] = " checked=\"checked\"";
                    }
                }

                // versand-radios
                $check_yes = ""; $check_no = "";
                if ( $_POST["versand"] != "" && $_POST["versand"] != -1 ) {
                    $hidedata[$environment["parameter"][2]]["versand_yes_check"] = "";
                    $hidedata[$environment["parameter"][2]]["versand_no_check"] = " checked=\"checked\"";
                } else {
                    $hidedata[$environment["parameter"][2]]["versand_yes_check"] = " checked=\"checked\"";
                    $hidedata[$environment["parameter"][2]]["versand_no_check"] = "";
                }

            } else {

            }
    // echo "<pre>".print_r($hidedata,true)."</pre>";


    //         $sql = "SELECT *
    //                   FROM ".$cfg["katauszug"]["db"]["leer"]["entries"]."
    //                  WHERE 1";
    //         if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
    //         $result = $db -> query($sql);
    //         while ( $data = $db -> fetch_array($result,1) ) {
    //             $dataloop["leer"][$data["id"]][0] = $data["field1"];
    //             $dataloop["leer"][$data["id"]][1] = $data["field2"];
    //         }
    //         $hidedata["leer"][0] = "enable";
            // +++
            // funktions bereich


            // page basics
            // ***

            // fehlermeldungen
//             if ( $HTTP_GET_VARS["error"] != "" ) {
//                 if ( $HTTP_GET_VARS["error"] == 1 ) {
//                     $ausgaben["form_error"] = "#(error1)";
//                 }
//             } else {
//                 $ausgaben["form_error"] = "";
//             }

            // navigation erstellen
            $ausgaben["form_aktion"] = $cfg["katauszug"]["basis"]."/".$cfg["katauszug"]["name"].",".$amtkennzahl.",".$environment["parameter"][2].",verify.html";
            #$mapping["navi"] = "leer";

            // hidden values
            #$ausgaben["form_hidden"] .= "";

            // was anzeigen
            $mapping["main"] = eCRC($environment["ebene"]).".katauszug";
            #$mapping["navi"] = "leer";

            // unzugaengliche #(marken) sichtbar machen
            if ( isset($HTTP_GET_VARS["edit"]) ) {
                $ausgaben["inaccessible"] = "inaccessible values:<br />";
                $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
                $ausgaben["inaccessible"] .= "# (error_plz_nope) #(error_plz_nope)<br />";
                $ausgaben["inaccessible"] .= "# (error_plz_multi) #(error_plz_multi)<br />";
                $ausgaben["inaccessible"] .= "# (success) #(success)<br />";
            } else {
                $ausgaben["inaccessible"] = "";
            }

            // wohin schicken
            #n/a

            // +++
            // page basics
            if ( $environment["parameter"][3] == "verify"
                &&  ( $_POST["send"] != ""
                    || $_POST["extension1"] != ""
                    || $_POST["extension2"] != "" ) ) {

                // form eingaben prüfen
                form_errors( $form_options, $_POST );
                form_errors( $form_options, $_POST["person"] );

                if ( $ausgaben["form_error"] == ""  ) {
                    foreach ( $_POST["person"] as $key=>$value ) {
                        $$key = $value;
                    }
                    if ( is_array($_POST["order"]) ) {
                        foreach ( $_POST["order"] as $key=>$value ){
                            if ( $value["flst"] == "" ) continue;
                            foreach ( $value as $label=>$order_data ) {
                                $dataloop["order"][$key][$label] = strtoupper($order_data);
                            }
                            if ( $value["add_begl"] == "nein" ) {
                                $dataloop["order"][$key]["massstab"] = "";
                                $dataloop["order"][$key]["anzahl"] = "";
                            }
                        }
                    }
                    if ( is_array($_POST["masszahlen"]) ) {
                        foreach ( $_POST["masszahlen"] as $key=>$value ){
                            if ( $value["flst"] == "" ) continue;
                            foreach ( $value as $label=>$order_data ) {
                                $dataloop["masszahlen"][$key][$label] = strtoupper($order_data);
                            }
                            if ( $value["add_begl"] == "nein" ) {
                                $dataloop["masszahlen"][$key]["massstab"] = "";
                                $dataloop["masszahlen"][$key]["anzahl"] = "";
                            }
                        }
                    }
                    if ( is_array($_POST["koordinaten"]) ) {
                        foreach ( $_POST["koordinaten"] as $key=>$value ){
                            if ( $value["flst"] == "" ) continue;
                            foreach ( $value as $label=>$order_data ) {
                                $dataloop["koordinaten"][$key][$label] = strtoupper($order_data);
                            }
                            $value["disc"] != "" ? $dataloop["koordinaten"][$key]["disc"] = "JA" : $dataloop["koordinaten"][$key]["disc"] = "NEIN";
                            $value["list"] != "" ? $dataloop["koordinaten"][$key]["list"] = "JA" : $dataloop["koordinaten"][$key]["list"] = "NEIN";
                        }
                    }
                    if ( $_POST["versand"] == -1 ) {
                        $hidedata["send"] = array();
                    } else {
                        $hidedata["nosend"] = array();
                    }
                    if ( $_POST["versand_adresse"] != "" ) $hidedata["versand_adresse"]["adresse"] = $_POST["versand_adresse"];
                    if ( $_POST["sonder"] != "" ) $hidedata["sonder"]["sonder"] = $_POST["sonder"];
                    $art = strtoupper($environment["parameter"][2]);
                    $message = parser("katauszug-email","");

                    if ( $environment["parameter"][2] == "lageplan" ) {
                        $subject = "Bestellung von Lageplaenen";
                    } elseif ( $environment["parameter"][2] == "vektor" ) {
                        $subject = "Bestellung von digitalen Flurkarten DFK";
                    } elseif ( $environment["parameter"][2] == "masszahlen" ) {
                        $subject = " Bestellung von Masszahlen und Koordinaten";
                    }
                    // mail an amt
                    $header_amt  = "From: ".$cfg["katauszug"]["email"]["robot"]."\r\n";
                    $header_amt .= "Reply-To: ".$_POST["person"]["email"]."\r\n";
                    $header_amt .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    $header_amt .= "Content-Transfer-Encoding: 8bit\r\n";
                    // mail an kunde
                    $header_kunde  = "From: ".$cfg["katauszug"]["email"]["owner"]."\r\n";
                    $header_kunde .= "Reply-To: ".$amt_poststelle."\r\n";
                    $header_kunde .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    $header_kunde .= "Content-Transfer-Encoding: 8bit\r\n";
                    $message_kunde = "Folgende Bestelldaten sind bei uns eingegangen:\r\n\r\n".$message."\r\n\r\nIhre Bayerische Vermessungsverwaltung";
                    if ( $cfg["katauszug"]["dry_run"] == -1 ) {
                        $ausgaben["form_error"]  = "<pre>";
                        $ausgaben["form_error"] .= "<i>";
                        $ausgaben["form_error"] .= date("r")."<br>";
                        $ausgaben["form_error"] .= "TESTLAUF!!!!<br>";
                        $ausgaben["form_error"] .= "############<br>";
                        $ausgaben["form_error"] .= "</i><br>";
                        $ausgaben["form_error"] .= "MAIL AN AMT:<br>";
                        $ausgaben["form_error"] .= "--------------<br>";
                        $ausgaben["form_error"] .= "Empfaenger: $amt_poststelle<br>";
                        $ausgaben["form_error"] .= "Betreff: $subject<br>";
                        $ausgaben["form_error"] .= "add.Header:<br> $header_amt<br>";
                        $ausgaben["form_error"] .= "Nachricht:<br>";
                        $ausgaben["form_error"] .= "~~~~~~~~~~<br>".$message."<br>";
                        $ausgaben["form_error"] .= "MAIL AN Kunde:<br>";
                        $ausgaben["form_error"] .= "--------------<br>";
                        $ausgaben["form_error"] .= "Empfaenger: ".$_POST["person"]["email"]."<br>";
                        $ausgaben["form_error"] .= "Betreff: $subject<br>";
                        $ausgaben["form_error"] .= "add.Header:<br> $header_kunde<br>";
                        $ausgaben["form_error"] .= "Nachricht:<br>";
                        $ausgaben["form_error"] .= "~~~~~~~~~~<br>".$message_kunde."<br>";
                        $ausgaben["form_error"] .= "</pre>";
                        $hidedata["form_error"] = array();
                    } else {
                        $result_amt = mail($amt_poststelle,$subject,$message,$header_amt);
                        $result_kunde = mail($_POST["person"]["email"],$subject,$message_kunde,$header_kunde);
                        if ( $result_amt && $result_kunde ) {
                            $ausgaben["form_error"]  = "#(success)";
                            $hidedata["form_error"] = array();
                        }
                    }


                } else {
                    $hidedata["form_error"] = array();
                }

            }

        }

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
