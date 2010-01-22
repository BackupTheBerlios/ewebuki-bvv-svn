<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer-edit.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
// "leer - edit funktion";
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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo "<pre>";
    if ( priv_check($cfg["bannered"]["basis"],$cfg["bannered"]["right"]) ) {

        // page basics
        // ***

        if ( count($_POST) == 0 ) {
            $sql = "SELECT *
                      FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
                     WHERE ".$cfg["bannered"]["db"]["banner"]["key"]."='".$environment["parameter"][1]."'";
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $result = $db -> query($sql);
            $form_values = $db -> fetch_array($result,1);
        } else {
            $form_values = $_POST;
        }

        // form options holen
        $form_options = form_options(eCRC($environment["ebene"]).".".$environment["kategorie"]);

        // form elememte bauen
        $element = form_elements( $cfg["bannered"]["db"]["banner"]["entries"], $form_values );

        // form elemente erweitern
        $element["extension1"] = "<input name=\"extension1\" type=\"text\" maxlength=\"5\" size=\"5\">";
        $element["extension2"] = "<input name=\"extension2\" type=\"text\" maxlength=\"5\" size=\"5\">";

        // +++
        // page basics


        // funktions bereich fuer erweiterungen
        // ***

        // Banner-Graphiken
        $pic_array = array();
        if ( $cfg["bannered"]["multi_pics"] == false ) {
//echo "hallo\n";
            // aus posts
            if ( $_POST["pic_value"] != "" ) {
                foreach ( $_POST["pic_value"] as $value ) {
                    $pic_array[0] = $value;
                    break;
                }
            }
            // aus db
            if ( $form_values["bpic"] != "" ) {
                $tmp = explode(",",$form_values["bpic"]);
                foreach ( $tmp as $value ) {
                    $pic_array[0] = $value;
                    break;
                }
            }
            // aus session
            if ( is_array($_SESSION["file_memo"]) ) {
                foreach ( $_SESSION["file_memo"] as $value ) {
                    $pic_array[0] = $value;
                    break;
                }
            }
        } else {
            // aus session
            if ( is_array($_SESSION["file_memo"]) ) {
                $pic_array = $_SESSION["file_memo"];
            }
            // aus posts
            if ( $_POST["pic_value"] != "" ) {
                foreach ( $_POST["pic_value"] as $value ) {
                    $pic_array[$value] = $value;
                }
            }
            
        }
//echo print_r($form_values,true);
//echo print_r($tmp,true);
//echo print_r($pic_array,true);
        // bilder holen
        $sql = "SELECT * 
                  FROM site_file
                 WHERE fid IN (".implode(", ",$pic_array).")";
//echo $sql."\n";
        $result = $db -> query($sql);
        while ( $data = $db -> fetch_array($result,1) ) {
            $dataloop["pics"][] = array(
                "id"     => $data["fid"],
                "src_tn" => $cfg["file"]["base"]["webdir"].
                            $data["ffart"]."/".
                            $data["fid"]."/".
                            "tn/".
                            $data["ffname"],
                "src_o"  => $cfg["file"]["base"]["webdir"].
                            $data["ffart"]."/".
                            $data["fid"]."/".
                            "o/".
                            $data["ffname"],
            );

        }

        // kategorien
        if ( is_array($cfg["bannered"]["kategorien"]) ) {
            foreach ( $cfg["bannered"]["kategorien"] as $key=>$value ) {
                $sel = "";
                if ( is_array($form_values["kat"]) ) {
                    if ( in_array($key, $form_values["kat"]) ) $sel = " selected=\"true\"";
                } else {
                    if ( strstr($form_values["bkat"],$key) ) $sel = " selected=\"true\"";
                }
                $dataloop["bkat"][] = array(
                    "value" => $key,
                    "item"  => $value,
                    "sel"   => $sel
                );
            }
        } else {
            // aus menue holen
            $sql = "SELECT * 
                      FROM site_menu
                     WHERE (hide!='-1' OR hide IS NULL)
                       AND refid=0
                  ORDER BY sort";
            $result = $db -> query($sql);
            while ( $data = $db -> fetch_array($result,1) ) {
                $kat = "/".$data["entry"];
                $sel = "";
                if ( is_array($form_values["kat"]) ) {
                    if ( in_array($kat, $form_values["kat"]) ) $sel = " selected=\"true\"";
                } else {
                    if ( strstr($form_values["bkat"],$kat) ) $sel = " selected=\"true\"";
                }
                $dataloop["bkat"][] = array(
                    "value" => $kat,
                    "item"  => "/".$data["entry"],
                    "sel"   => $sel
                );
            }
        }

        // +++
        // funktions bereich fuer erweiterungen


        // page basics
        // ***

        // fehlermeldungen
        $ausgaben["form_error"] = "";

        // navigation erstellen
        $ausgaben["form_aktion"] = $cfg["bannered"]["basis"]."/edit,".$environment["parameter"][1].",verify.html";
        $ausgaben["form_break"] = $cfg["bannered"]["basis"]."/list.html";

        // hidden values
        $ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "bannered-edit";
        #$mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error_result) #(error_result)<br />";
            $ausgaben["inaccessible"] .= "# (error_dupe) #(error_dupe)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // wohin schicken
        #n/a

        // +++
        // page basics

        if ( $environment["parameter"][2] == "verify"
            &&  $_POST["abort"] != "" ) {
            header("Location:".$ausgaben["form_break"]);
        }

        if ( $environment["parameter"][2] == "verify"
            &&  ( $_POST["send"] != ""
               || $_POST["pic"] != ""
                || $_POST["extension2"] != "" ) ) {

            // form eingaben pr�fen
            form_errors( $form_options, $_POST );

            // evtl. zusaetzliche datensatz aendern
            if ( $ausgaben["form_error"] == ""  ) {

                // funktions bereich fuer erweiterungen
                // ***

                ### put your code here ###

                if ( $error ) $ausgaben["form_error"] .= $db -> error("#(error_result)<br />");
                // +++
                // funktions bereich fuer erweiterungen
            }

            // datensatz aendern
            if ( $ausgaben["form_error"] == ""  ) {

                $kick = array( "PHPSESSID", "form_referer", "send", "pic", "pic_value", "kat" );
                foreach($_POST as $name => $value) {
                    if ( !in_array($name,$kick) && !strstr($name, ")" ) ) {
                        if ( $sqla != "" ) $sqla .= ",
                               ";
                        $sqla .= $name."='".$value."'";
                    }
                }

                // Sql um spezielle Felder erweitern
                if ( $_POST["pic_value"] != "" ) {
                    $sqla .= ",
                               bpic='".implode(",",$_POST["pic_value"])."'";
                }
                if ( is_array($_POST["kat"]) ) {
                    foreach ( $_POST["kat"] as $key=>$value ) {
                        if ( $value == "" ) unset($_POST["kat"][$key]);
                    }
                    $sqla .= ",
                               bkat='".implode(",",$_POST["kat"])."'";
                }
                #$ldate = $_POST["ldate"];
                #$ldate = substr($ldate,6,4)."-".substr($ldate,3,2)."-".substr($ldate,0,2)." ".substr($ldate,11,9);
                #$sqla .= ", ldate='".$ldate."'";

                $sql = "UPDATE ".$cfg["bannered"]["db"]["banner"]["entries"]."
                           SET ".$sqla."
                         WHERE ".$cfg["bannered"]["db"]["banner"]["key"]."='".$environment["parameter"][1]."'";
//echo $sql."\n";
                if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
                $result  = $db -> query($sql);
                if ( !$result ) {
                    $ausgaben["form_error"] .= $db -> error("#(error_result)<br />");
                } else {
                    unset($_SESSION["file_memo"]);
                    if ( $_POST["pic"] ) {

                        $_SESSION["cms_last_edit"] = str_replace(",verify", "", $pathvars["requested"]);

                        $_SESSION["cms_last_referer"] = $ausgaben["form_referer"];
                        $_SESSION["cms_last_ebene"] = $_SESSION["ebene"];
                        $_SESSION["cms_last_kategorie"] = $_SESSION["kategorie"];

                        header("Location: ".$pathvars["virtual"]."/admin/fileed/list.html");
                        exit;

                    }
                }
                if ( $header == "" ) $header = $cfg["bannered"]["basis"]."/list.html";
            }

            // wenn es keine fehlermeldungen gab, die uri $header laden
            if ( $ausgaben["form_error"] == "" ) {
                header("Location: ".$header);
            }
        }
    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }
echo "</pre>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
