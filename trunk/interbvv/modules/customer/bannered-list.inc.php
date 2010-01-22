<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer-list.inc.php 1678 2009-12-07 14:03:04Z chaot $";
// "leer - list funktion";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2010 Werner Ammon ( wa<at>chaos.de )

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

    // funktions bereich
    // ***

    ### put your code here ###

    /* z.B. db query */

    $sql = "SELECT *
              FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
          ORDER BY ".$cfg["bannered"]["db"]["banner"]["order"];
    if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];

    // seiten umschalter
    $inhalt_selector = inhalt_selector( $sql, $environment["parameter"][1], $cfg["bannered"]["db"]["banner"]["rows"], $parameter, 1, 3, $getvalues );
    $ausgaben["inhalt_selector"] = $inhalt_selector[0]."<br />";
    $sql = $inhalt_selector[1];
    $ausgaben["anzahl"] = $inhalt_selector[2];

    $result = $db -> query($sql);
    while ( $data = $db -> fetch_array($result,1) ) {

        // platz fuer vorbereitungen hier z.B.tabellen farben wechseln
        if ( $cfg["bannered"]["color"]["set"] == $cfg["bannered"]["color"]["a"]) {
            $cfg["bannered"]["color"]["set"] = $cfg["bannered"]["color"]["b"];
        } else {
            $cfg["bannered"]["color"]["set"] = $cfg["bannered"]["color"]["a"];
        }

        if ( $data["bpic"] != "" ) {
            $sql = "SELECT *
                    FROM site_file
                    WHERE fid IN (".$data["bpic"].")";
            $res_pic = $db -> query($sql);
            $i = 0;
            while ( $data_pic = $db -> fetch_array($res_pic,1) ) {
                if ( $i == 0 ) {
                    $pic_src = $cfg["file"]["base"]["webdir"].
                               $data_pic["ffart"]."/".
                               $data_pic["fid"]."/".
                               "o/".
                               $data_pic["ffname"];
                }
            }
        } else {
            $pic_src = "";
        }

        // wird der banner angezeigt
        if ( $data["bhide"] == -1 ) {
            $status = "<b>wird nicht angezeigt</b>";
        } else {
            $status = "aktiv";
        }

        // wie im einfachen modul k�nnten nur die marken !{0}, !{1} befuellt werden
        #$dataloop["list"][$data["id"]][0] = $data["field1"];
        #$dataloop["list"][$data["id"]][1] = $data["field2"];

        // der uebersicht halber fuellt das erweiterte modul aber einzeln benannte marken
        $dataloop["list"][$data[$cfg["bannered"]["db"]["banner"]["key"]]] = array(
                               "color" => $cfg["bannered"]["color"]["set"],
                                  "id" => $data[$cfg["bannered"]["db"]["banner"]["key"]],
                               "title" => $data[$cfg["bannered"]["db"]["banner"]["title"]],
                                "sort" => $data[$cfg["bannered"]["db"]["banner"]["sort"]],
                                "link" => $data[$cfg["bannered"]["db"]["banner"]["link"]],
                             "pic_src" => $pic_src,
                              "status" => $status,
                                "edit" => $cfg["bannered"]["basis"]."/edit,".$data[$cfg["bannered"]["db"]["banner"]["key"]].".html",
                              "delete" => $cfg["bannered"]["basis"]."/delete,".$data[$cfg["bannered"]["db"]["banner"]["key"]].".html",
                             "details" => $cfg["bannered"]["basis"]."/details,".$data[$cfg["bannered"]["db"]["banner"]["key"]].".html",
        );
        if ( $data["bhide"] != -1 ) {
            $dataloop["list_preview"][] = $dataloop["list"][$data[$cfg["bannered"]["db"]["banner"]["key"]]];
        }
    }
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

    // navigation erstellen
    if ( priv_check($cfg["bannered"]["qlink_url"],$cfg["bannered"]["right"]) ) {
echo "hallo";
        $hidedata["new"]["link"] = $cfg["bannered"]["basis"]."/add.html";
    }

    // hidden values
    #$ausgaben["form_hidden"] .= "";

    // was anzeigen
    $cfg["bannered"]["path"] = str_replace($pathvars["virtual"],"",$cfg["bannered"]["basis"]);
    $mapping["main"] = "bannered-list";
    #$mapping["navi"] = "leer";

    // unzugaengliche #(marken) sichtbar machen
    if ( isset($_GET["edit"]) ) {
        $ausgaben["inaccessible"] = "inaccessible values:<br />";
        $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
        $ausgaben["inaccessible"] .= "# (edittitel) #(edittitel)<br />";
        $ausgaben["inaccessible"] .= "# (deletetitel) #(deletetitel)<br />";
    } else {
        $ausgaben["inaccessible"] = "";
    }

    // wohin schicken
    #n/a

    // +++
    // page basics

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
