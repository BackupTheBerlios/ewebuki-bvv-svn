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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    function sitemap_xml ($refid,$path = "") {
        global $ausgaben,$cfg, $environment, $db, $pathvars, $specialvars;

        $sql = "SELECT *
                  FROM site_menu
                  JOIN site_menu_lang
                    ON (site_menu.mid=site_menu_lang.mid)
                 WHERE refid=".$refid."
                   AND (hide='0' OR hide='' OR hide IS NULL)
              ORDER BY sort";
// echo "$sql<br>";

        $result  = $db -> query($sql);

        while ( $data = $db -> fetch_array($result,1) ) {
//             echo $data["entry"]."::".$data["label"]."<br>";

            $url = $path."/".$data["entry"];

            if ( $path != "" ) {
                $tname = eCRC($path).".".$data["entry"];
            } else {
                $tname = $data["entry"];
            }

            $sql_text = "SELECT *
                           FROM site_text
                          WHERE tname='".$tname."'
                            AND status=1
                       ORDER BY version DESC";
            $res_text  = $db -> query($sql_text);
            $data_text = $db -> fetch_array($res_text,1);

            echo "<url>\n";
            echo "<loc>http://www.vermessung.bayern.de".$url.".html</loc>\n";
            echo "<tname>".$tname."</tname>\n";
            echo "<lastmod>".$data_text["changed"]."</lastmod>\n";
            echo "</url>\n";
            sitemap_xml ($data["mid"],$url);
        }
    }

    header("HTTP/1.0 200 OK");
    header("content-type: text/xml; charset=utf-8");
//     header('Content-Disposition: attachment; filename="sitemap.xml"');

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
  <urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

    sitemap_xml(0);

    echo '</urlset>';

    die();

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
