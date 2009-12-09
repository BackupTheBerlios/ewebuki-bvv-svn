<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer-list.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
// "leer - list funktion";
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

    if ( $cfg["autoform"]["right"] == "" || $rechte[$cfg["autoform"]["right"]] == -1 ) {

        if ( $_GET["eintragen"] ) {
            $preg = "^(-)?([0-9])*$";
            if ( preg_match("/$preg/",$_GET["eintragen"],$regs) ) {
                $sql = "SELECT * FROM ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["key"]."='".$regs[0]."' AND confirm !='-1'";
                $result = $db -> query($sql);
                if ( $db -> num_rows($result) > 0 ) {
                    $sql = "UPDATE ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." SET time=".mktime().", confirm='-1' WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["key"]."='".$regs[0]."' AND confirm !='-1'";
                    $result = $db -> query($sql);
                    header("Location: ".$cfg["register"]["sites"]["signin_akt"]);
                    exit;
                } else {
                    header("Location: ".$cfg["register"]["sites"]["no"]);
                    exit;
                }
            } else {
                header("Location: ".$cfg["register"]["sites"]["no"]);
                exit;
            }
        }
        if ( $_GET["austragen"] ) {
            $preg = "^(-)?([0-9])*$";
            if ( preg_match("/$preg/",$_GET["austragen"],$regs) ) {
                $sql = "SELECT * FROM ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["key"]."='".$regs[0]."' AND confirm ='-1'";
                $result = $db -> query($sql);
                if ( $db -> num_rows($result) > 0 ) {
                    $sql = "DELETE FROM ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["key"]."='".$regs[0]."' AND confirm ='-1'";
                    $result = $db -> query($sql);
                    header("Location: ".$cfg["register"]["sites"]["signout_akt"]);
                    exit;
                } else {
                    header("Location: ".$cfg["register"]["sites"]["no"]);
                    exit;
                }
            } else {
                header("Location: ".$cfg["register"]["sites"]["no"]);
                exit;
            }
        }
        // page basics
        // ***


        $hidedata["newsletter"] = array();
        // warnung ausgeben
        if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

        // path fuer die schaltflaechen anpassen
        if ( $cfg["kontakt"]["iconpath"] == "" ) $cfg["kontakt"]["iconpath"] = "/images/default/";

        // label bearbeitung aktivieren
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        if ( count($HTTP_POST_VARS) == 0 ) {
        } else {
            $form_values = $HTTP_POST_VARS;
        }

        // form options holen
        $form_options = form_options(eCRC($environment["ebene"]).".".$environment["kategorie"]);

        // form elememte bauen
        $element = form_elements( $cfg["register"]["db"][$environment["kategorie"]]["entries"], $form_values );


        $hidedata["form"] = array();

        // +++
        // page basics



        // funktions bereich
        // ***

        if ( is_array($cfg["register"]["captcha"]) ) {

            // zufaellige zeichen erzeugen
            $captcha_text = captcha_randomize($cfg["register"]["captcha"]["length"],$cfg["register"]["captcha"]);

            // bild erzeugen
            captcha_create($captcha_text,$cfg["register"]["captcha"]);
            // captcha-info erzeugen
            $captcha_crc = crc32($captcha_text.$cfg["register"]["captcha"]["randomize"]);
            $captcha_name = "captcha-".$captcha_crc.".png";
            $captcha_path_web = $cfg["file"]["base"]["webdir"].$cfg["file"]["base"]["new"];
            $captcha_path_srv = $cfg["file"]["base"]["maindir"].$cfg["file"]["base"]["new"];
            // ausgeben
            $hidedata["captcha"]["url"] = $captcha_path_web.$captcha_name;
            $hidedata["captcha"]["proof"] = $captcha_crc;
            // alte, unnuetze bilder entfernen
            foreach ( glob($captcha_path_srv."captcha-*.png") as $captcha_file) {
                if ( (mktime() - filemtime($captcha_file)) > 600 ) unlink($captcha_file);
            }
        }

 
        // +++
        // funktions bereich


        // page basics
        // ***



        // fehlermeldungen
        if ( $HTTP_GET_VARS["error"] != "" ) {
            if ( $HTTP_GET_VARS["error"] == 1 ) {
                $ausgaben["form_error"] = "#(error1)";
            }
        } else {
            $ausgaben["form_error"] = "";
        }

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $cfg["leer"]["path"] = str_replace($pathvars["virtual"],"",$cfg["leer"]["basis"]);
        $mapping["main"] = eCRC($environment["ebene"]).".".$environment["kategorie"];
        #$mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
            $ausgaben["inaccessible"] .= "# (error_mail) g(error_email)<br />";
            $ausgaben["inaccessible"] .= "# (error_captcha) g(error_captcha)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        if ( $environment["parameter"][2] == "verify"
            &&  $HTTP_POST_VARS["btn"] != "" ) {

            // form eigaben pruefen
            form_errors( $form_options, $HTTP_POST_VARS );

            if ( is_array($cfg["register"]["captcha"]) ) {
                if ( $_POST["captcha_proof"] != crc32($_POST["captcha"].$cfg["register"]["captcha"]["randomize"])
                  || !file_exists($captcha_path_srv."captcha-".$_POST["captcha_proof"].".png") ) {
                    $ausgaben["form_error"] .= "g(error_captcha)<br>";
                    $dataloop["form_error"]["captcha"]["text"] = "g(error_captcha)";
                    $hidedata["captcha"]["class"] = "form_error";
                }
                if (file_exists($captcha_path_srv."captcha-".$_POST["captcha_proof"].".png")) unlink($captcha_path_srv."captcha-".$_POST["captcha_proof"].".png");
            }

            $preg =  "^(.+)@(.+)\.([a-zA-z]{2,8})$";
            if ( $_POST["email"] != "" && !preg_match("/$preg/",$_POST["email"],$regs) ) {
                    $ausgaben["form_error"] .= "g(error_email)";
            }

            if ( $ausgaben["form_error"] != "") $hidedata["error"] = array();

            if ( $ausgaben["form_error"] == "" ) {

                $sqla = "";
                $sqlb = "";
                if ( is_array($cfg["register"]["db"][$environment["kategorie"]]["addon"]) ) {
                        foreach ( $cfg["register"]["db"][$environment["kategorie"]]["addon"] as $value ) {
                            $sqla .= $value.",";
                            $sqlb .= "'".$_POST[$value]."',";
                        }
                }
                //pruefen ob email schon registriert
                if ( $_POST["ac"] == "eintragen") {
                    $sql = "SELECT * FROM ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]."='".$_POST[$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]]."' AND confirm ='-1'";
                    $result = $db -> query($sql);
                    if ( $db -> num_rows($result) > 0 ) {
                        header("Location: ".$cfg["register"]["sites"]["twice"]);
                        exit;
                    }
                    $sql = "INSERT INTO ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]. " (".$sqla."email,key,time) VALUES ( ".$sqlb."'".$_POST["email"]."','".$_POST["captcha_proof"]."','".mktime()."')";
                    $result = $db -> query($sql);
                    mail($_POST[$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]],"Ihre Anmeldung in unserem BVV-Kundeninformations-System",str_replace("###bestaetigungslink###","http://".$environment["fqdn"][0].$environment["ebene"]."/".$environment["kategorie"].".html"."?eintragen=".$_POST["captcha_proof"],$cfg["register"]["email_text"]["anmelde_plus"]),"FROM: ".$cfg["register"]["from"]."\r\nContent-Type: text/plain; charset=UTF-8\r\n");
                    header("Location: ".$cfg["register"]["sites"]["signin"]);
                }
                // pruefen ob man noch eingetragen ist
                if ( $_POST["ac"] == "austragen") {
                    $sql = "SELECT * FROM ".$cfg["register"]["db"][$environment["kategorie"]]["entries"]." WHERE ".$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]."='".$_POST[$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]]."' AND confirm ='-1'";
                    $result = $db -> query($sql);
                    $data = $db -> fetch_array($result,1);
                    if ( $db -> num_rows($result) == 0 ) {
                        header("Location: ".$cfg["register"]["sites"]["no"]);
                        exit;
                    }
                    mail($_POST[$cfg["register"]["db"][$environment["kategorie"]]["e-mail"]],"Ihre Abmeldung in unserem BVV-Kundeninformations-System",str_replace("###bestaetigungslink###","http://".$environment["fqdn"][0].$environment["ebene"]."/".$environment["kategorie"].".html"."?austragen=".$data["key"],$cfg["register"]["email_text"]["abmelde_plus"]),"Content-Type: text/plain; charset=UTF-8\r\n");
                    $result = $db -> query($sql);
                    header("Location: ".$cfg["register"]["sites"]["signout"]);
                }



            }
            
        }



        // wohin schicken
        $ausgaben["form_aktion"] = "965077567.newsletter";
        $ausgaben["form_break"] = "list.html";

        // +++
        // page basics

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
