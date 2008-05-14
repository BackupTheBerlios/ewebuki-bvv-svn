<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1131 2007-12-12 08:45:50Z chaot $";
  $Script["desc"] = "short description";
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

    if ( $cfg["admin"]["right"] == "" || $rechte[$cfg["admin"]["right"]] == -1 ) {

        ////////////////////////////////////////////////////////////////////
        // achtung: bei globalen funktionen, variablen nicht zuruecksetzen!
        // z.B. $ausgaben["form_error"],$ausgaben["inaccessible"]
        ////////////////////////////////////////////////////////////////////

        // page basics
        // ***

        include $pathvars["moduleroot"]."wizard/wizard-functions.inc.php";

        // +++
        // page basics


        // funktions bereich
        // ***

        // einzelne bereiche durchgehen (artikel, termine, ...)
        foreach ( $cfg["admin"]["specials"] as $url=>$bereich ) {
            // berechtigung checken
            if ( !priv_check($url,"admin;edit") ) continue;
            $hidedata[$bereich."_section"] = array(
                "heading" => "#(".$bereich."_heading)",
                "new" => "#(".$bereich."_new)",
            );
            // dataloop holen
            $buffer = find_marked_content( $url, $cfg["admin"], "inhalt" );
            $dataloop[$bereich."_edit"] = $buffer[-1];
            $dataloop[$bereich."_release"] = $buffer[-2];
            // bereiche sichtbar machen
            if ( count($dataloop[$bereich."_edit"]) > 0 && priv_check($url,"admin;edit") ) {
                $hidedata[$bereich."_edit"] = array();
            }
            if ( count($dataloop[$bereich."_release"]) > 0 && priv_check($url,"admin;publish") ) {
                $hidedata[$bereich."_release"] = array();
            }
        }

        // normalen content ausschliesslich spezielle bereiche durchgehen
        $buffer = find_marked_content( "/", $cfg["admin"], "inhalt", array("/aktuell"));
        $bereich = "content";
        if ( count($buffer) > 0 ) {
            $hidedata[$bereich."_section"] = array(
                "heading" => "#(".$bereich."_heading)",
                "new" => "#(".$bereich."_new)",
            );
            $dataloop[$bereich."_edit"] = $buffer[-1];
            $dataloop[$bereich."_release"] = $buffer[-2];
            if ( count($dataloop[$bereich."_edit"]) > 0 ) {
                $hidedata[$bereich."_edit"] = array();
            }
            if ( count($dataloop[$bereich."_release"]) > 0 ) {
                $hidedata[$bereich."_release"] = array();
            }
        }
        // +++
        // funktions bereich


        // page basics
        // ***

        // label bearbeitung aktivieren
        if ( isset($_GET["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // was anzeigen
        $mapping["main"] = "administration";

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
