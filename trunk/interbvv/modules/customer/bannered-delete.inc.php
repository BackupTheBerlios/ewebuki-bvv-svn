<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer-delete.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
// "leer - delete funktion";
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

    if ( priv_check($cfg["bannered"]["basis"],$cfg["bannered"]["right"]) ) {

        // funktions bereich fuer erweiterungen
        // ***

        ### put your code here ###

        /* z.B. evtl. auf verknuepften datensatz pruefen
        $sql = "SELECT ".$cfg["bannered"]["db"]["menu"]["key"]."
                  FROM ".$cfg["bannered"]["db"]["menu"]["entries"]."
                 WHERE refid='".$environment["parameter"][1]."'";
        $result = $db -> query($sql);
        $num_rows = $db -> num_rows($result);
        */

        // +++
        // funktions bereich fuer erweiterungen

        if ( $num_rows > 0 ) {

            // was anzeigen
            $mapping["main"] = eCRC($environment["ebene"]).".list";
            $mapping["navi"] = "leer";

            // wohin schicken
            header("Location: ".$cfg["bannered"]["basis"]."/list.html?error=1");

        } else {

            // datensatz holen
            $sql = "SELECT *
                      FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
                     WHERE ".$cfg["bannered"]["db"]["banner"]["key"]."='".$environment["parameter"][1]."'";
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $result = $db -> query($sql);
            $data = $db -> fetch_array($result,$nop);

            foreach ( $data as $key => $value ) {
                $index = array_search($key, $cfg["bannered"]["db"]["banner"]);
                if ( $index ) {
                    $ausgaben[$index] = $value;
                    $ausgaben1[$index] = $value;
                }
            }

            if ( is_array($cfg["bannered"]["kategorien"]) ) {
                $search = array();$replace = array();
                foreach ( $cfg["bannered"]["kategorien"] as $key=>$value ) {
                    $search[]  = $key;
                    $replace[] = $value;
                }
            }
            $ausgaben["kat"] = str_replace($search,$replace,$data[$cfg["bannered"]["db"]["banner"]["kat"]]);

            $ausgaben["form_id1"] = $data["id"];
            $ausgaben["field1"] = $data["field1"];
            $ausgaben["field2"] = $data["field2"];

            // funktions bereich fuer erweiterungen
            // ***

//            echo "<pre>";
//            echo "<h1>kat</h1>";
//            echo print_r(build_banner( "global" ),true);
//            echo "<h1>kat/id</h1>";
//            echo print_r(build_banner( "global", $environment["parameter"][1] ),true);
//            echo "<h1>id</h1>";
//            echo print_r(build_banner( "", $environment["parameter"][1] ),true);
//            echo "</pre>";

            $dataloop["del_banner"] = build_banner( "", $environment["parameter"][1] ) ;

            /* z.B. evtl. verknuepfte datensatze holen
            $sql = "SELECT *
                      FROM ".$cfg["bannered"]["db"]["more"]["entries"]."
                     WHERE ".$cfg["bannered"]["db"]["more"]["key"]." ='".$environment["parameter"][1]."'";
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $result = $db -> query($sql);
            while ( $data2 = $db -> fetch_array($result,$nop) ) {
                if ( $ids != "" ) $ids .= ",";
                $ids .= $data2["id"];
                $ausgaben["field3"] .= $data2["field1"]." ";
                $ausgaben["field3"] .= $data2["field2"]."<br />";
            }
            $ausgaben["form_id2"] = $ids;
            */

            // +++
            // funktions bereich fuer erweiterungen


            // page basics
            // ***

            // fehlermeldungen
            $ausgaben["form_error"] = "";

            // navigation erstellen
            $ausgaben["form_aktion"] = $cfg["bannered"]["basis"]."/delete,".$environment["parameter"][1].".html";
            $ausgaben["form_break"] = $cfg["bannered"]["basis"]."/list.html";

            // hidden values
            $ausgaben["form_hidden"] = "";
            $ausgaben["form_delete"] = "true";

            // was anzeigen
            $mapping["main"] = "bannered-delete";
            #$mapping["navi"] = "leer";

            // unzugaengliche #(marken) sichtbar machen
            // ***
            if ( isset($_GET["edit"]) ) {
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


            // das loeschen wurde bestaetigt, loeschen!
            // ***
            if ( $_POST["abort"] != "" ) {
                header("Location: ".$cfg["bannered"]["basis"]."/list.html");
            }
            if ( $_POST["send"] != "" ) {

                // evtl. zusaetzlichen datensatz loeschen
                if ( $_POST["id2"] != "" ) {
                    // funktions bereich fuer erweiterungen
                    // ***

                    ### put your code here ###

                    /* z.B. evtl. verknuepfte datensatze loeschen
                    $sql = "DELETE FROM ".$cfg["bannered"]["db"]["more"]["entries"]."
                                  WHERE ".$cfg["bannered"]["db"]["more"]["key"]." = '".$_POST["id2"]."'";
                    if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
                    $result  = $db -> query($sql);
                    if ( !$result ) $ausgaben["form_error"] = $db -> error("#(error_result2)<br />");
                    */

                    // +++
                    // funktions bereich fuer erweiterungen
                }

                // datensatz loeschen
                if ( $ausgaben["form_error"] == "" ) {
                    $sql = "DELETE FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
                                  WHERE ".$cfg["bannered"]["db"]["banner"]["key"]."='".$environment["parameter"][1]."';";
                    if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
                    $result  = $db -> query($sql);
                    if ( !$result ) $ausgaben["form_error"] = $db -> error("#(error_result1)<br />");
                }

                // wohin schicken
                if ( $ausgaben["form_error"] == "" ) {
                    header("Location: ".$cfg["bannered"]["basis"]."/list.html");
                }
            }
            // +++
            // das loeschen wurde bestaetigt, loeschen!
        }
    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
