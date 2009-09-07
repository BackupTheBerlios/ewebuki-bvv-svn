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


    if (  $environment["ebene"] == "/admin" && $environment["kategorie"] == "site_counting" ) {
        if ( $_SESSION["uid"] != "" ) {

            if ( $_GET["path"] != "" ) {
                include_once $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";
                $menu = make_id(str_replace("www.vermessung.bayern.de","",urldecode($_GET["path"])));
                if ( $menu["mid"] != 0 ) {
                    $sql = "SELECT *
                              FROM site_menu_lang
                             WHERE mid=".$menu["mid"]."
                               AND lang='".$environment["language"]."'";
                    $result = $db -> query($sql);
                    $data = $db -> fetch_array($result,1);
                    $title = $data["label"]." (".urldecode($_GET["path"]).")";
                } else {
                    $title = urldecode($_GET["path"]);
                }
                $hidedata["detail"]["title"] = $title;
                $hidedata["detail"]["back"] = $pathvars["requested"];
                // monats-auswertung
                $sql = "SELECT *
                          FROM db_count_sites
                         WHERE path='".urldecode($_GET["path"])."'
                      ORDER BY month DESC";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    $dataloop["monthly"][] = array(
                        "month" => $data["month"],
                        "hits"  => $data["count"],
                    );
                }
                // referer auswertung
                $sql = "SELECT sum(count) as hits, referer
                          FROM db_count_sites_referer
                         WHERE path='".urldecode($_GET["path"])."'
                      GROUP BY referer
                      ORDER BY hits DESC
                         LIMIT 0,10";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    $dataloop["referer"][] = array(
                        "referer" => $data["referer"],
                        "hits"  => $data["hits"],
                    );
                }
                // follower auswertung
                $sql = "SELECT sum(count) as hits, path, referer
                          FROM db_count_sites_referer
                         WHERE referer LIKE '%".str_replace("www.","",urldecode($_GET["path"])).".html'
                      GROUP BY referer,path
                      ORDER BY hits DESC
                         LIMIT 0,10";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    $dataloop["follows"][] = array(
                        "follows" => $data["path"],
                        "hits"  => $data["hits"],
                    );
                }
            } else {
                $hidedata["list"] = array();
                $sql = "SELECT sum(count) as hits,path
                          FROM db_count_sites
                         WHERE (path NOT LIKE '%rss'
                            AND path NOT LIKE '%index'
                            AND path NOT LIKE '%favicon%'
                            AND path NOT LIKE 'internetredakteur.bvv.bayern.de/admin%'
                            AND path NOT LIKE 'internetredakteur.bvv.bayern.de/wizard%')
                         GROUP BY path
                      ORDER BY hits DESC";

                // seiten umschalter
                if ( $environment["parameter"][2] != "" && is_numeric($environment["parameter"][2]) ) {
                    $rows = $environment["parameter"][2];
                } else {
                    $rows = 50;
                }
                $inhalt_selector = inhalt_selector( $sql, $environment["parameter"][1], $rows, $parameter, 1, 3, $getvalues );
                $ausgaben["inhalt_selector"] = $inhalt_selector[0]."<br />";
                $sql = $inhalt_selector[1];
                $ausgaben["anzahl"] = $inhalt_selector[2];

                $result = $db -> query($sql);
                $i = 0; $csv = "";
                while ( $data = $db -> fetch_array($result,1) ) {
                    foreach ( $data as $key=>$value ) {
                        $dataloop["count"][$i][$key] = $value;
                    }
                    $csv .= $data["hits"].";".$data["path"];
                    $dataloop["count"][$i]["link"] = "?path=".urlencode($data["path"]);
                    $i++;
                }

                if ( $environment["parameter"][3] == "csv" ) {
                    header("Content-type: text/csv");
                    header("Content-Disposition: attachment; filename=\"bvv_seiten_statistik_top_".$rows.".csv\"");
                    echo $csv;
                    die();
                }
            }
            $mapping["main"] = "site_counting_tem";
        }
    } else {
        $domain = $_SERVER["HTTP_X_FORWARDED_SERVER"];
        if ( $domain == "" ) $domain = $_SERVER["SERVER_NAME"];
        $path = $domain.$environment["ebene"]."/".$environment["allparameter"];
        $month = date("Y-m");
        // ist seite schon einmal gezaehlt
        $sql = "SELECT *
                FROM db_count_sites
                WHERE month='".$month."'
                AND path='".$path."'";
        $result = $db -> query($sql);
        $num = $db -> num_rows($result);
        if ( $num == 0 ) {
            $sql = "INSERT INTO db_count_sites "."
                                (month,path,count)
                        VALUES ('".$month."','".$path."',1)";
        } else {
            $data = $db -> fetch_array($result,1);
            $site_index = $data["site_index"];
            $sql = "UPDATE db_count_sites
                       SET count=count+1
                     WHERE month='".$month."'
                       AND path='".$path."'";
        }
        $result = $db -> query($sql);

        $referer = $_SERVER["HTTP_REFERER"];
        // ist der referer schon einmal gezaehlts
        $sql = "SELECT *
                  FROM db_count_sites_referer
                 WHERE month='".$month."'
                   AND path='".$path."'
                   AND referer='".$referer."'";
        $result = $db -> query($sql);
        $num = $db -> num_rows($result);
        if ( $num == 0 ) {
            $sql = "INSERT INTO db_count_sites_referer
                                (month,path,referer,count)
                        VALUES ('".$month."','".$path."','".$referer."',1)";
        } else {
            $sql = "UPDATE db_count_sites_referer
                    SET count=count+1
                    WHERE month='".$month."'
                    AND path='".$path."'
                    AND referer='".$referer."'";
        }
        $result = $db -> query($sql);
    }



////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>