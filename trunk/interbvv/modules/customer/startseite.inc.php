<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2008 Werner Ammon ( wa<at>chaos.de )

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

        $ausgaben["form_error"] = "";

        // weiterleitung zur login-seite
        if ( $_SERVER["REDIRECT_URL"] == "/" && ( $_SERVER["SERVER_NAME"] == "internetredakteur.bvv.bayern.de" || $_SERVER["SERVER_NAME"] == "lvg-entw-intertest") ) {
            header("Location: /login.html");
        }

        // Artikel anzeigen
        $sql = "SELECT Cast(SUBSTR(content,POSITION('[SORT]' IN content)+6,POSITION('[/SORT]' IN content)-POSITION('[SORT]' IN content)-6) as DATETIME) as date,
                        tname,
                        ebene,
                        kategorie,
                        content
                    FROM site_text
                    WHERE status='1'
                    AND tname LIKE '".eCRC("/aktuell/archiv").".%'
                    AND content LIKE '%[KATEGORIE]/aktuell/archiv[/KATEGORIE]%'
                ORDER BY date DESC";
        $result = $db -> query($sql);
        $count = 0;
        $today = mktime(23,59,59,date('m'),date('d'),date('Y'));
        $dataloop["list_new"] = array();
        while ( $data = $db->fetch_array($result,1) ) {

            // Datumskontrolle
            // Startdatum
            $startdatum = mktime(0,0,0,substr($data["date"],5,2),substr($data["date"],8,2),substr($data["date"],0,4));
            if ( $startdatum > $today ) continue;
            // Enddatum
            if ( preg_match("/\[ENDE\](.*)\[\/ENDE\]/Uis",$data["content"],$endmatch) ) {
                if ( $today >  mktime(0,0,0,substr($endmatch[1],5,2),substr($endmatch[1],8,2),substr($endmatch[1],0,4)) && ( $endmatch[1] != "1970-01-01" ) ) {
                    continue;
                }
            }

            // ueberschrift
            preg_match("/\[H1\](.*)\[\/H1\]/Uis",$data["content"],$match);
            $headline = $match[1];
            // teaser
            preg_match("/\[P=teaser\](.*)\[\/p\]/Uis",$data["content"],$match);
            $teaser = $match[1];
            // link
            $link = $pathvars["virtual"]."/aktuell/archiv/".$data["kategorie"].".html";
            // bild informationen holen
            preg_match("/\[IMG=(\/file\/.+\/)([0-9]+)(\/[a-z]+)(\/.+);/Uis",$data["content"],$match);
            $sql_img = "SELECT * FROM site_file WHERE fid='".$match[2]."'";
            $result_img = $db -> query($sql_img);
            $data_img = $db -> fetch_array($result_img,1);
            $pic_alt = $data_img["fdesc"];
            $pic_src = $match[1].$match[2]."/s".$match[4];

            // wieviele sollen angezeigt werden
            $count++;
            if ( $count > 4) break;

            $dataloop["startseite"][$count] = array(
                "headline"  => $headline,
                "teaser"    => $teaser,
                "link"      => $link,
                "pic_src"   => $pic_src,
                "pic_alt"   => $pic_alt,
            );

        }

        // zufalls-banner
        if ( is_array($cfg["startseite"]["pics"]) ) {
            $index = mt_rand( 0 , (count($cfg["startseite"]["pics"])-1) );
            if ( file_exists($pathvars["fileroot"]."images/html/".$cfg["startseite"]["pics"][$index]) ) {
                $hidedata["startbanner"]["src"] = $pathvars["images"].$cfg["startseite"]["pics"][$index];
            }
        }
        header("HTTP/1.0 200 OK");
        $mapping["main"] = "index";

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
