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


        // funktions bereich
        // ***

        function rss_walk_path($refid,$hide=0,$sub=0) {
            global $db, $environment, $dataloop, $cfg;

            // content dieser ebene suchen
            $ebene = make_ebene($refid);

            // das menue durchgehen
            $sql = "SELECT  *
                      FROM  ".$cfg["xml-sm"]["db"]["menu"]["entries"]."
                      JOIN  ".$cfg["xml-sm"]["db"]["lang"]["entries"]."
                        ON  (".$cfg["xml-sm"]["db"]["menu"]["entries"].".".$cfg["xml-sm"]["db"]["menu"]["key"]."=".$cfg["xml-sm"]["db"]["lang"]["entries"].".".$cfg["xml-sm"]["db"]["menu"]["key"].")
                     WHERE  ".$cfg["xml-sm"]["db"]["menu"]["ref"]."=".$refid."
                       AND  ".$cfg["xml-sm"]["db"]["lang"]["lang"]."='".$environment["language"]."'
                  ORDER BY sort";
// echo "<pre>".$sql."</pre>";
            $result = $db -> query($sql);
            while ( $data = $db -> fetch_array($result,1) ) {
                if ( $hide == 0 && $data["hide"] == -1 ) continue;

                if ( preg_match("/\/$/",$ebene) ) {
                    $url = $ebene."index.html";
                } else {
                    $url = $ebene.".html";
                }
                // aus content aenderungsdatum holen
                $array = explode("/",$ebene);
                $kategorie = array_pop($array);
                if ( count($array) > 1 ) {
                    $ebene = implode("/",$array);
                    $tname = eCRC($ebene).".".$kategorie;
                } else {
                    $ebene = "";
                    $tname = $kategorie;
                }
                $sql = "SELECT  *
                          FROM  ".$cfg["xml-sm"]["db"]["text"]["entries"]."
                         WHERE tname='".$tname."'
                           AND label='".$cfg["xml-sm"]["def_label"]."'
                           AND lang='".$environment["language"]."'
                           AND status=1";
                $res_content = $db -> query($sql);
                $data_content = $db -> fetch_array($res_content,1);
                $date = $data_content["changed"];

                // <changefreq>
                if ( preg_match("/^\/aktuell/",$url) || preg_match("/^\/index/",$url) ) {
                    $changefreq = "daily";
                    $priority = "1.0";
                } else {
                    $changefreq = "monthly";
                    $priority = "0.5";
                }

                $ebene = make_ebene($data["mid"]);
                $dataloop["urls"][$url] = array(
                    "url" => $url,
                    "pubDate" => $date,
                    "changefreq" => $changefreq,
                    "priority" => $priority,
                );

                if ( preg_match("/^\/aktuell/",$url) ) {
                    $sql = "SELECT  *
                              FROM  ".$cfg["xml-sm"]["db"]["text"]["entries"]."
                             WHERE tname LIKE '".eCRC($ebene)."%'
                               AND label='".$cfg["xml-sm"]["def_label"]."'
                               AND lang='".$environment["language"]."'
                               AND status=1
                          ORDER BY changed DESC";
                    $res_content = $db -> query($sql);
                    while ( $data_content = $db -> fetch_array($res_content,1) ) {
                        if ( !is_numeric($data_content["kategorie"]) ) continue;
                        if ( preg_match("#\[SORT\](.*)\[/SORT\]#U",$data["content"],$match) ) {
                            $date_start = $match[1];
                            if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})/",$date_start,$date_match) ) {
                                $timestamp_start = mktime("0","0","0",$date_match[2],$date_match[3],$date_match[1]);
                            } else {
                                // falls nicht angegeben, ganz frueher annehmen
                                $timestamp_start = mktime(0,0,0,1,1,1971);
                            }
                            if ( $timestamp_start > mktime() ) continue;
                        }
                        $changefreq = "never";
                        $priority = "0.7";
                        $url_sub = str_replace(".html","",$url)."/".$data_content["kategorie"].".html";
                        $dataloop["urls"][$url_sub] = array(
                            "url" => $url_sub,
                            "pubDate" => $data_content["changed"],
                            "changefreq" => $changefreq,
                            "priority" => $priority,
                        );
                    }
                }

                rss_walk_path($data["mid"]);
            }
        }

        include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
        $menu_item = make_id($path);
        rss_walk_path($menu_item["mid"]);
        $menu_item = make_id("/aktuell");
        rss_walk_path($menu_item["mid"],-1,-1);

        if ( count($dataloop["urls"]) > 0 ) {
            $hidedata["sitemap"] = array();
        }

        header("HTTP/1.0 200 OK");
        header("Content-Type: text/xml");
        echo parser("xml-sitemap","");
        die();


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
