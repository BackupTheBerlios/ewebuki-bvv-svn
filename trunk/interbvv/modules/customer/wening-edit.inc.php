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

    if ( priv_check($cfg["wening"]["basis"],$cfg["wening"]["right"]) ) {

        // page basics
        // ***

        if ( count($_POST) == 0 ) {
            $sql = "SELECT *
                      FROM ".$cfg["wening"]["db"]["produkte"]["entries"]."
                     WHERE ".$cfg["wening"]["db"]["produkte"]["key"]."='".$environment["parameter"][1]."'";
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $result = $db -> query($sql);
            $form_values = $db -> fetch_array($result,1);
        } else {
            $form_values = $_POST;
        }

        if ( is_array($_SESSION["file_memo"]) ) {
            $form_values[$cfg["wening"]["db"]["produkte"]["pic"]] = current($_SESSION["file_memo"]);
            unset($_SESSION["file_memo"]);
        }

        // form options holen
        $form_options = form_options("wening-edit");

        // form elememte bauen
        $element = form_elements( $cfg["wening"]["db"]["produkte"]["entries"], $form_values );
        foreach ( $cfg["wening"]["db"]["produkte"] as $key=>$value ) {
            if ( $element[$value] != "" ) $element[$key] = $element[$value];
        }

        // form elemente erweitern
        $element["extension1"] = "<input name=\"extension1\" type=\"text\" maxlength=\"5\" size=\"5\">";
        $element["extension2"] = "<input name=\"extension2\" type=\"text\" maxlength=\"5\" size=\"5\">";

        // +++
        // page basics


        // funktions bereich fuer erweiterungen
        // ***

        // bild holen
        if ( $form_values[$cfg["wening"]["db"]["produkte"]["pic"]] != "" ) {
            $sql = "SELECT *
                        FROM site_file
                        WHERE fid=".$form_values[$cfg["wening"]["db"]["produkte"]["pic"]];
            if ( $res_pic = $db -> query($sql) ) {
//                 if ( $db->num_rows($res_pic) > 1 ) $hidedata["pic"] = array();
// echo $db->num_rows($res_pic)."<br>";
                if ( $db->num_rows($res_pic) > 0 ) {
                    $dat_pic = $db -> fetch_array($res_pic,1);
                    $pic_src = $cfg["file"]["base"]["webdir"].
                               $dat_pic["ffart"]."/".
                               $dat_pic["fid"]."/".
                               "tn/".
                               $dat_pic["ffname"];
// echo "hallo";
                    $hidedata["pic"]["src"] = $pic_src;
                }
            }
        }

        // +++
        // funktions bereich fuer erweiterungen


        // page basics
        // ***

        // fehlermeldungen
        $ausgaben["form_error"] = "";

        // navigation erstellen
        $ausgaben["form_aktion"] = $cfg["wening"]["basis"]."/edit,".$environment["parameter"][1].",verify.html";
        $ausgaben["form_break"] = $cfg["wening"]["basis"]."/list.html";

        // hidden values
        $ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "wening-edit";
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
            &&  ( $_POST["send"] != ""
                || $_POST["get_pic"] != ""
                || $_POST["extension2"] != "" ) ) {

            // form eingaben pruefen
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

                $kick = array( "PHPSESSID", "form_referer", "send", "get_pic" );
                foreach($_POST as $name => $value) {
                    if ( !in_array($name,$kick) && !strstr($name, ")" ) ) {
                        if ( $sqla != "" ) $sqla .= ",
                               ";
                        $sqla .= $name."='".$value."'";
                    }
                }

                // Sql um spezielle Felder erweitern
                $sqla .= ",
                               changed='".date("Y-m-d")."'";
                #$ldate = $_POST["ldate"];
                #$ldate = substr($ldate,6,4)."-".substr($ldate,3,2)."-".substr($ldate,0,2)." ".substr($ldate,11,9);
                #$sqla .= ", ldate='".$ldate."'";

                $sql = "UPDATE ".$cfg["wening"]["db"]["produkte"]["entries"]."
                           SET ".$sqla."
                         WHERE ".$cfg["wening"]["db"]["produkte"]["key"]."='".$environment["parameter"][1]."'";
// echo "<pre>".$sql."</pre>";
                if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
                $result  = $db -> query($sql);
                if ( !$result ) {
                    $ausgaben["form_error"] .= $db -> error("#(error_result)<br />");
                } else {
                    if ( $_POST["get_pic"] ) {

                        $_SESSION["cms_last_edit"] = str_replace(",verify", "", $pathvars["requested"]);

                        $_SESSION["cms_last_referer"] = $ausgaben["form_referer"];
                        $_SESSION["cms_last_ebene"] = $_SESSION["ebene"];
                        $_SESSION["cms_last_kategorie"] = $_SESSION["kategorie"];

                        header("Location: ".$pathvars["virtual"]."/admin/fileed/list.html");
                        exit;

                    }
                }
                if ( $header == "" ) $header = $cfg["wening"]["basis"]."/list.html";
            }

            // wenn es keine fehlermeldungen gab, die uri $header laden
            if ( $ausgaben["form_error"] == "" ) {
                header("Location: ".$header);
            }
        }
    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
