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

        if ( $_POST["wsearch"] != "" && $_POST["ajax"] != "update" ) {
            // user-eingabe absichern
            if ( get_magic_quotes_gpc() ) {
                $search_strings = $_POST["wsearch"];
            } else {
                $search_strings = addslashes($_POST["wsearch"]);
            }
            $sql = "SELECT DISTINCT ".$cfg["wening"]["db"]["produkte"]["title"]."
                               FROM ".$cfg["wening"]["db"]["produkte"]["entries"]."
                              WHERE ".$cfg["wening"]["db"]["produkte"]["typ"]."='wening'
                                AND ".$cfg["wening"]["db"]["produkte"]["title"]." LIKE '".$search_strings."%'
                           ORDER BY ".$cfg["wening"]["db"]["produkte"]["order"];
            $result = $db -> query($sql);
            $buffer = array();
            while ( $data = $db -> fetch_array($result,1) ) {
                $buffer[] = "<li>".preg_replace("/^(".$search_strings.")/i",'<b>$1</b>',$data[$cfg["wening"]["db"]["produkte"]["title"]])."</li>";
            }
            if ( count($buffer) > 0 ) {
                header("HTTP/1.0 200 Ok");
                echo "<ul>".implode("\n",$buffer)."</ul>";
                exit;
            } else {
                header("HTTP/1.0 404 Not Found");
                exit;
            }
        }

        $ausgaben["wsearch"] = htmlspecialchars($_GET["wsearch"]);
        $ausgaben["result"] = "";

        $where = "";
        if ( $_GET["wsearch"] != "" ) {
            // user-eingabe absichern
            if ( get_magic_quotes_gpc() ) {
                $search_strings = $_GET["wsearch"];
            } else {
                $search_strings = addslashes($_GET["wsearch"]);
            }
            $where = " AND titel LIKE '".$search_strings."%'";
            $ausgaben["result"] = " #(for) <b>".$ausgaben["wsearch"]."</b>";
        }

        /* z.B. db query */

        $sql = "SELECT *
                  FROM ".$cfg["wening"]["db"]["produkte"]["entries"]."
                 WHERE ".$cfg["wening"]["db"]["produkte"]["typ"]."='wening'".$where."
              ORDER BY ".$cfg["wening"]["db"]["produkte"]["order"];
// echo "<pre>$sql</pre>";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];

        // seiten umschalter
        $get_vars = trim(str_replace("ajax=update","",$_SERVER["QUERY_STRING"]),"& ");
        $inhalt_selector = inhalt_selector( $sql, $environment["parameter"][1], $cfg["wening"]["db"]["produkte"]["rows"], $parameter, 1, 5, $get_vars );
        $ausgaben["inhalt_selector"] = $inhalt_selector[0]."<br />";
        $sql = $inhalt_selector[1];
        $ausgaben["anzahl"] = $inhalt_selector[2];
        $ausgaben["inhalt_selected"] = $inhalt_selector[3];

        $result = $db -> query($sql);
        while ( $data = $db -> fetch_array($result,1) ) {

            // abgabe-format
            if ( strstr($data[$cfg["wening"]["db"]["produkte"]["desc"]],"Doppelblatt") ) {
                $abgabe = "Doppelblatt";
            } elseif ( strstr($data[$cfg["wening"]["db"]["produkte"]["desc"]],"Dreifachblatt") ) {
                $abgabe = "Dreifachblatt";
            } else {
                $abgabe = "Normalblatt";
            }

            // bild holen
            if ( $data[$cfg["wening"]["db"]["produkte"]["pic"]] != "" ) {
                $sql = "SELECT *
                          FROM site_file
                         WHERE fid=".$data[$cfg["wening"]["db"]["produkte"]["pic"]];
                if ( $res_pic = $db -> query($sql) ) {
                    $dat_pic = $db -> fetch_array($res_pic,1);
                    $pic_src = $cfg["file"]["base"]["webdir"].
                               $dat_pic["ffart"]."/".
                               $dat_pic["fid"]."/".
                               "tn/".
                               $dat_pic["ffname"];
                    $pic_lb  = $cfg["file"]["base"]["webdir"].
                               $dat_pic["ffart"]."/".
                               $dat_pic["fid"]."/".
                               "o/".
                               $dat_pic["ffname"];
                }
            }

            $dataloop["liste"][] = array(
                "key"          => $data[$cfg["wening"]["db"]["produkte"]["key"]],
                "serial"       => $data[$cfg["wening"]["db"]["produkte"]["serial"]],
                "typ"          => $data[$cfg["wening"]["db"]["produkte"]["typ"]],
                "title"        => $data[$cfg["wening"]["db"]["produkte"]["title"]],
                "desc"         => nl2br($data[$cfg["wening"]["db"]["produkte"]["desc"]]),
                "price"        => $data[$cfg["wening"]["db"]["produkte"]["price"]],
                "changed"      => $data[$cfg["wening"]["db"]["produkte"]["changed"]],
                "created"      => $data[$cfg["wening"]["db"]["produkte"]["created"]],
                "abgabe"       => $abgabe,
                "pic_desc"     => $data[$cfg["wening"]["db"]["produkte"]["title"]],
                "pic_src"      => $pic_src,
                "pic_lb"       => $pic_lb,
                "link_details" => $cfg["wening"]["basis"]."/details,".$data[$cfg["wening"]["db"]["produkte"]["key"]].".html",
                "link_edit"    => $cfg["wening"]["basis"]."/edit,".$data[$cfg["wening"]["db"]["produkte"]["key"]].".html",
            );
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
        $ausgaben["form_aktion"] = $cfg["wening"]["basis"]."/list.html";
        $ausgaben["link_new"] = $cfg["wening"]["basis"]."/add.html";

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "wening-list";
        if ( priv_check($cfg["wening"]["basis"],$cfg["wening"]["right"]) ) {
            $hidedata["modus_edit"] = array();
        } else {
            $hidedata["modus_view"] = array();
        }
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

        if ( $_GET["ajax"] == "update" ) {
            echo parser("wening-list-ajax","");
            die();
        }

        // wohin schicken
        #n/a

        // +++
        // page basics


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
