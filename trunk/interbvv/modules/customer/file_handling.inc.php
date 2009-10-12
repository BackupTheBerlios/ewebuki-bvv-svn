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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( preg_match("/^\/file\//",$_SERVER["REQUEST_URI"]) ) {

        // subdir support
        $specialvars["subdir"] = trim(dirname(dirname($_SERVER["SCRIPT_NAME"])),"/");
        if ( $specialvars["subdir"] != "" ) {
            $value = str_replace( $specialvars["subdir"]."/", "", $_SERVER["REQUEST_URI"] );
        } else {
            $value = $_SERVER["REQUEST_URI"];
        }

        $value = explode("/",$value);

        // typen, die gezählt werden sollen
        $types = array("pdf","arc","odf");

        if ( in_array($cfg["file"]["filetyp"][$value[2]],$types) ) {
            $period = date("Y-m");
            // ist datei schon einmal gezaehlt
            $sql = "SELECT *
                      FROM db_count_files
                     WHERE fid=".$value[3]."
                       AND period='".$period."'";
            $result = $db -> query($sql);
            $num = $db -> num_rows($result);
            if ( $num == 0 ) {
                $sql = "INSERT INTO db_count_files
                                    (fid,count,period,month)
                             VALUES (".$value[3].",1,'".$period."','".date("Y-m-d")."')";
            } else {
                $sql = "UPDATE db_count_files
                           SET count=count+1
                         WHERE fid=".$value[3]."
                           AND period='".$period."'";
            }
            $result = $db -> query($sql);
            // ist datei schon einmal gezaehlt
            $sql = "SELECT *
                      FROM db_count_files_referer
                     WHERE fid=".$value[3]."
                       AND period='".$period."'
                       AND referer='".$_SERVER["HTTP_REFERER"]."'";
            $result = $db -> query($sql);
            $num = $db -> num_rows($result);
            if ( $num == 0 ) {
                $sql = "INSERT INTO db_count_files_referer
                                    (fid,count,period,referer)
                             VALUES (".$value[3].",1,'".$period."','".$_SERVER["HTTP_REFERER"]."')";
            } else {
                $sql = "UPDATE db_count_files_referer
                           SET count=count+1
                         WHERE fid=".$value[3]."
                           AND period='".$period."'
                           AND referer='".$_SERVER["HTTP_REFERER"]."'";
            }
            $result = $db -> query($sql);
        }
        // datei ausgeben
        include_once $pathvars["fileroot"]."/basic/wrapper.php";
        die();
    }

    if ( ($_POST["fid"] != "" && $_POST["ajax"] != "") || ($_GET["fid"] != "" && $_GET["ajax"]) ) {
        // ajax-ausgabe
        $fid = trim($_POST["fid"]).trim($_GET["fid"]);
        $sql = "SELECT sum(count) as count
                  FROM db_count_files
                 WHERE fid=".preg_replace("/[a-z,]/","",$fid);
        $result = $db -> query($sql);
        $num = $db -> num_rows($result);
        $data = $db -> fetch_array($result,1);
        if ( $num == 0 || $data["count"] == "" ) {
            $downloads = 0;
        } else {
            $downloads = $data["count"];
        }
        echo $downloads;
        die();
    } else {
        if ( $_SESSION["uid"] != "" ) {
            if ( $_GET["fid"] != "" ) {
                $hidedata["detail"]["back"] = $pathvars["requested"];
                // datei feststellen
                $sql = "SELECT *
                          FROM site_file
                         WHERE fid=".$_GET["fid"];
                $result = $db -> query($sql);
                $data = $db -> fetch_array($result,1);
                $hidedata["detail"] = array_merge($hidedata["detail"],$data);
                // monatsstatistiken
                $sql = "SELECT *
                          FROM db_count_files
                         WHERE fid=".$_GET["fid"]."
                      ORDER BY period DESC";
                $result = $db -> query($sql);
                $hits = 0;
                while ( $data = $db -> fetch_array($result,1) ) {
                    $period = $data["period"];
                    $dataloop["monthly"][] = array(
                        "period" => $period,
                        "hits"   => $data["count"],
                        "link"   => "?fid=".$_GET["fid"]."&period=".$period,
                    );
                    $hits += $data["count"];
                }
                $dataloop["monthly"][] = array(
                    "period" => "&sum;",
                    "hits"   => $hits,
                    "link"   => "?fid=".$_GET["fid"],
                );
                // referer
                $where = "WHERE fid=".$_GET["fid"];
                if ( $_GET["period"] != "" ) $where .= " AND period='".$_GET["period"]."'";
                $sql = "SELECT sum(count) as count, referer
                          FROM db_count_files_referer
                         ".$where."
                      GROUP BY referer
                      ORDER BY count DESC";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    if ( $data["referer"] != "" ) {
                        $referer = $data["referer"];
                    } else {
                        $referer = "(direkter Aufruf)";
                    }
                    $dataloop["referer"][] = array(
                        "referer" => $referer,
                        "hits"    => $data["count"],
                    );
                }
            } else {
                $hidedata["list"] = array();
                $sql = "SELECT sum(count) as count, site_file.fid, ffname, funder, fdesc
                          FROM db_count_files
                          JOIN site_file
                            ON (db_count_files.fid=site_file.fid)
                      GROUP BY site_file.fid,ffname, funder, fdesc
                      ORDER BY count DESC";
                if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];

                // seiten umschalter
                $inhalt_selector = inhalt_selector( $sql, $environment["parameter"][1], 20, $parameter, 1, 3, $getvalues );
                $ausgaben["inhalt_selector"] = $inhalt_selector[0]."<br />";
                $sql = $inhalt_selector[1];
                $ausgaben["anzahl"] = $inhalt_selector[2];

                $result = $db -> query($sql);
                $i = 0;
                while ( $data = $db -> fetch_array($result,1) ) {
                    foreach ( $data as $key=>$value ) {
                        $dataloop["count"][$i][$key] = $value;
                    }
                    $dataloop["count"][$i]["pos"] = $i;
                    $dataloop["count"][$i]["link"] = "?fid=".$data["fid"];
                    $i++;
                }
            }
            $mapping["main"] = "file_handling_tem";
        } else {
            header("Location:".$pathvars["pretorian"]);
        }
    }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
