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

    86343 Koenigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    // funktions bereich
    // ***

    include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";

    function rss_walk_path($refid) {
        global $db, $environment, $dataloop, $cfg;

        // content dieser ebene suchen
        $ebene = make_ebene($refid);
        if ( preg_match("/^\/aktuell/",$ebene) ) {
            rss_get_content($ebene,-1);
        } else {
            rss_get_content($ebene);
        }
        // das menue durchgehen
        $sql = "SELECT  *
                  FROM  ".$cfg["rss"]["db"]["menu"]["entries"]."
                  JOIN  ".$cfg["rss"]["db"]["lang"]["entries"]."
                    ON (".$cfg["rss"]["db"]["menu"]["entries"].".".$cfg["rss"]["db"]["menu"]["key"]."=".$cfg["rss"]["db"]["lang"]["entries"].".".$cfg["rss"]["db"]["menu"]["key"].")
                 WHERE  ".$cfg["rss"]["db"]["menu"]["ref"]."=".$refid."
                   AND  ".$cfg["rss"]["db"]["lang"]["lang"]."='".$environment["language"]."'
                   AND (".$cfg["rss"]["db"]["menu"]["hide"]."='0'
                    OR  ".$cfg["rss"]["db"]["menu"]["hide"]." IS NULL)";
        $result = $db -> query($sql);
        while ( $data = $db -> fetch_array($result,1) ) {
            $ebene = make_ebene($data["mid"]);
            rss_walk_path($data["mid"]);
        }
    }

    function rss_get_content($path,$only_sub=0,$label="") {
        global $db, $pathvars, $environment, $dataloop, $hidedata, $cfg;

        // ebene und kategorie bauen
        $array = explode("/",$path);
        $kategorie = array_pop($array);
        if ( count($array) > 1 ) {
            $ebene = implode("/",$array);
            $tname = eCRC($ebene).".".$kategorie;
        } else {
            $ebene = "";
            $tname = $kategorie;
        }
        if ( $only_sub == -1 ) {
            $sql = "SELECT  *
                      FROM  ".$cfg["rss"]["db"]["text"]["entries"]."
                     WHERE tname LIKE '".eCRC($path)."%'
                       AND label='".$cfg["rss"]["def_label"]."'
                       AND lang='".$environment["language"]."'
                       AND status=1";
        } else {
            $sql = "SELECT  *
                      FROM  ".$cfg["rss"]["db"]["text"]["entries"]."
                     WHERE (tname='".$tname."' OR tname LIKE '".eCRC($path)."%')
                       AND label='".$cfg["rss"]["def_label"]."'
                       AND lang='".$environment["language"]."'
                       AND status=1";
        }
        $result = $db -> query($sql);
        while ( $data = $db -> fetch_array($result,1) ) {
            // kategorieueberpruefung fuer amts-artikel
            preg_match("#\[KATEGORIE\](.*)\[/KATEGORIE\]#U",$data["content"],$match);
            if ( $_GET["kategorie"] != "" ) {
                if ( $match[1] != "" && !strstr($match[1],$_GET["kategorie"]) ) continue;
            } else {
                if ( $match[1] != "" && !strstr($match[1],$environment["ebene"]) ) continue;
            }
            // titel
            $title = "---";
            preg_match("/\[H[0-9]{1}\](.+)\[\/H/U",$data["content"],$match);
            if ( count($match) > 1 ) {
                $title = $match[1];
            }
            if ( $label != "" ) $title = $label.": ".$title;
            if ( $cfg["rss"]["utf_encoding"] == -1 ) $title = utf8_encode($title);

            // link
            if ( $cfg["rss"]["webroot"] != "" ) {
                $link = $cfg["rss"]["webroot"].$data["ebene"]."/".$data["kategorie"].".html";
                if ( strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") || strstr($_SERVER["SERVER_NAME"],"krompi") ) {
                    $link = str_replace(
                        array(
                            $cfg["rss"]["webroot"],
                            ".int-dmz.bayern",
                            "/aktuell",
                            "archiv",
                        ),
                        array(
                            "http://www.".$_SERVER["SERVER_NAME"],
                            "",
                            "",
                            "artikel",
                        ),
                        $link
                    );
                    $link = preg_replace("/\/([0-9]+)\.html$/Ui",',,$1.html',$link);
                }
            } else {
                $link = $pathvars["webroot"].$data["ebene"]."/".$data["kategorie"].".html";
            }

            // teaser
            $teaser = "";
            preg_match("/\[P=teaser\](.+)\[\/P/U",$data["content"],$match);
            if ( count($match) > 1 ) {
                $teaser = tagremove($match[1]);
            }
            if ( $cfg["rss"]["utf_encoding"] == -1 ) $teaser = utf8_encode($teaser);

            // pubDate. RFC2822-formatierte Datum
            // mit ausnahme, wenn bei artikel ein datum mitgeben wurde
            if ( preg_match("/\[SORT\](.+)\[\/SORT\]/U",$data["content"],$match) ) {
                $date = $match[1];
            } else {
                $date = $data["changed"];
            }
            $pubDate = date("r",
                            mktime(
                            substr($date,11,2),
                            substr($date,14,2),
                            substr($date,17,2),
                            substr($date,5,2),
                            substr($date,8,2),
                            substr($date,0,4)
                            )
            );

            // lastBuildDate
            if ( $pubDate > $hidedata["rss"]["lastBuildDate"] ) {
                $hidedata["rss"]["lastBuildDate"] = $pubDate;
            }

            $dataloop["items"][$date." - ".$data["ebene"]."/".$data["kategorie"]] = array(
                 "title" => $title,
                "teaser" => $teaser,
                  "link" => $link,
                  "guid" => $link,
               "pubDate" => $pubDate,
            );
        }
        if ( is_array($dataloop["items"]) ) krsort($dataloop["items"]);
    }

    $menu_item = make_id($path);

    // aktuellen menue-punkt holen
    $mid = $menu_item["mid"];
    $sql = "SELECT *
              FROM ".$cfg["rss"]["db"]["lang"]["entries"]."
             WHERE ".$cfg["rss"]["db"]["menu"]["key"]."=".$mid;
    $result = $db -> query($sql);
    $data = $db -> fetch_array($result,1);

    if ( preg_match("/^\/aemter/",$environment["ebene"]."/".$environment["kategorie"]) || strstr($_SERVER["SERVER_NAME"],"vermessungsamt-") ) {
        $link = "http://".str_replace(
            array(
                ".int-dmz.bayern",
            ),
            array(
                "",
            ),
            $_SERVER["SERVER_NAME"]
        );
    } else {
        $link = "http://www.geodaten.bayern.de";
    }

    $hidedata["rss"] = array(
           "lang" => $environment["language"],
           "link" => $link,
          "label" => $data["label"],
        "pubDate" => date("r"),
    );
    rss_walk_path($menu_item["mid"]);

    // anzahl der eintraege limitieren
    if ( $cfg["rss"]["max_items"] != "" && count($dataloop["items"]) > 0 ) $dataloop["items"] = array_slice($dataloop["items"], 0, $cfg["rss"]["max_items"]);

    // was anzeigen
//     header("Content-type: application/rss+xml");
    $HTTP_POST_VARS["print"][2] = "rss";
    #$mapping["navi"] = "leer";

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
