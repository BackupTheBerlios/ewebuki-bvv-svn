<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: info-details.inc.php,v 1.8 2006/10/06 14:38:44 chaot Exp $";
// "info - details funktion";
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

    if ( $cfg["right"] == "" || $rechte[$cfg["right"]] == -1 ) {

        // funktions bereich fuer erweiterungen
        // ***

        ### put your code here ###


        // +++
        // funktions bereich fuer erweiterungen

        // datensatz holen
        $sql = "SELECT *
                  FROM ".$cfg["db"]["info"]["entries"]."
                 WHERE ".$cfg["db"]["info"]["key"]."='".$environment["parameter"][1]."'";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db->query($sql);
        $form_values = $db->fetch_array($result,1);

        // form elemente erweitern
        $ausgaben["titel"] = $form_values[$cfg["db"]["info"]["titel"]];
        $ausgaben["lead"]  = $form_values[$cfg["db"]["info"]["teaser"]];
        $ausgaben["text"]  = tagreplace(nlreplace($form_values[$cfg["db"]["info"]["text"]]));

        $ausgaben["breite"] = "";
        if ( $form_values[$cfg["db"]["info"]["bilder"]] ){
            $arrPics = explode(",",$form_values[$cfg["db"]["info"]["bilder"]]);
            if ( $cfg["gal_len"] == 0 ){
                $gal_len = count($arrPics);
            }else{
                $gal_len = $cfg["gal_len"];
            }
            foreach ( array_slice($arrPics,0,$cfg["gal_len"]) as $value ){
                $sql = "SELECT * FROM ".$cfg["db"]["file"]["entries"]." WHERE ".$cfg["db"]["file"]["key"]."=".$value;
                $result = $db->query($sql);
                $data = $db->fetch_array($result,1);
                $dataloop["pics"][] = array(
                    "id"   => $data[$cfg["db"]["file"]["key"]],
                    "view" => "details,".$environment["parameter"][1]."/view,o,".$data[$cfg["db"]["file"]["key"]].".html",
                    "desc" => $data[$cfg["db"]["file"]["name"]]
                );
            }
            if ( count($arrPics) > 0 && $cfg["gal_len"] > 0 ){
                $hidedata["pics"][] = -1;
                $ausgaben["breite"] = ' style="width:400px;"';
                }
        }

        // page basics
        // ***

        // fehlermeldungen
        $ausgaben["form_error"] = "";

        // navigation erstellen
        $ausgaben["form_aktion"] = $cfg["basis"]."/delete,".$environment["parameter"][1].".html";
        $ausgaben["form_break"] = $cfg["basis"]."/list.html";

        // hidden values
        $ausgaben["form_hidden"] = "";
        $ausgaben["form_delete"] = "true";

        // was anzeigen
        $mapping["main"] = "info-details";

        // unzugaengliche #(marken) sichtbar machen
        // ***
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error_result1) #(error_result1)<br />";
            $ausgaben["inaccessible"] .= "# (error_result2) #(error_result2)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }
        // +++
        // unzugaengliche #(marken) sichtbar machen

        // wohin schicken
        #n/a

        // +++
        // page basics
        // +++
        // das loeschen wurde bestaetigt, loeschen!
    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
