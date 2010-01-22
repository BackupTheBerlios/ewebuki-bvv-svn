<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1678 2009-12-07 14:03:04Z chaot $";
  $Script["desc"] = "short description";
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

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];


        // page basics
        // ***

        // label bearbeitung aktivieren
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        // +++
        // page basics


        // funktions bereich
        // ***

        ### put your code here ###

        $bid = (int) $environment["kategorie"];

        $sql = "SELECT *
                  FROM ".$cfg["qlink"]["db"]["banner"]["entries"]."
                 WHERE ".$cfg["qlink"]["db"]["banner"]["key"]."=".$bid;
echo "<pre>";
echo $sql."\n";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $data = $db -> fetch_array($result,1);

        // bild holen
        if ( $data_margin["bpic"] != "" ) {
            $sql_pic = "SELECT *
                          FROM site_file
                         WHERE fid IN (".$data["bpic"].")";
            $result_pic = $db -> query($sql_pic);
            $data_pic = $db -> fetch_array($result_pic,1);
        }

        if ( !preg_match("/^http\:\/\//",$data["blink"]) ) $data["blink"] = "http://".$data["blink"];

        foreach ( $data as $key=>$value ) {
            $ausgaben[$key] = $value;
        }

        $hidedata["img"]["src"] = $cfg["file"]["base"]["webdir"].
                                  $data_pic["ffart"]."/".
                                  $data_pic["fid"]."/".
                                  "o/".
                                  $data_pic["ffname"];
        $hidedata["img"]["desc"] = $data["bdesc"];

        // auswertung
        $sql = "SELECT * 
                  FROM db_banner_count 
                 WHERE month=".date("n")." 
                   AND year=".date("Y")."
                   AND bid=".$bid;
echo $sql."\n";
        $result = $db -> query($sql);
        $num = $db->num_rows($result);
        if ( $num == 0 ) {
            $sql = "INSERT INTO db_banner_count
                                (bid,month,year,count)
                         VALUES (".$bid.",".date("n").",".date("Y").",1)";
echo $sql."\n";
            $result = $db -> query($sql);
        } else {
            $data_count = $db -> fetch_array($result,1);
            $sql = "UPDATE db_banner_count
                       SET count=".($data_count["count"] + 1)."
                     WHERE month=".date("n")."
                       AND year=".date("Y")."
                       AND bid=".$bid;
            $result = $db -> query($sql);
echo $sql."\n";
        }
echo $num."\n";
echo "</pre>";
//exit;

        header("Location:".$data["blink"]);
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

        // navigation erstellen
        $ausgaben["add"] = $cfg["qlink"]["basis"]."/add,".$environment["parameter"][1].",verify.html";
        #$mapping["navi"] = "leer";

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "qlink";
        #$mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($HTTP_GET_VARS["edit"]) ) {
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
