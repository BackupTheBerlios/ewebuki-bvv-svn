<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: leer-functions.inc.php 1131 2007-12-12 08:45:50Z chaot $";
// "funktion loader";
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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /* um funktionen z.b. in der kategorie add zu laden, leer.cfg.php wie folgt aendern
    /*
    /*    "function" => array(
    /*                 "add" => array( "function1_name", "function2_name"),
    */

    // beschreibung der funktion
//    if ( in_array("function_name", $cfg["bannered"]["function"][$environment["kategorie"]]) ) {
//
//         function function_name(  $var1, $var2 = "") {
//            ### put your code here ###
//         }
//    }

    if ( !function_exists("build_banner") ) {
        function build_banner($kat = "",$id="") {
            global $db,$pathvars,$cfg;

            include $pathvars["moduleroot"]."customer/bannered.cfg.php";

//            $where = $cfg["bannered"]["db"]["banner"]["kat"]."=''
//                               OR ".$cfg["bannered"]["db"]["banner"]["kat"]." IS NULL
//                               ";
            $where_array = array();

            if ( $kat != "" ) {
                $buffer = array();
                $buffer[] = $cfg["bannered"]["db"]["banner"]["kat"]."=''";
                $buffer[] = $cfg["bannered"]["db"]["banner"]["kat"]." IS NULL";
                if ( is_array($kat) ) {
                    foreach ( $kat as $value ) {
                        $buffer[] = $cfg["bannered"]["db"]["banner"]["kat"]." LIKE '%".$value."%'";
                    }
                } else {
                    $buffer[] = $cfg["bannered"]["db"]["banner"]["kat"]." LIKE '%".$kat."%'";
                }
                $where_array[] = "(".implode(" OR ",$buffer).")";
            }
            if ( $id != "" ) {
                $where_array[] = $cfg["bannered"]["db"]["banner"]["key"]."=".$id;
            } else {
                $where_array[] = "(".$cfg["bannered"]["db"]["banner"]["hide"]."=0 OR ".$cfg["bannered"]["db"]["banner"]["hide"]." IS NULL)";
            }

            // marginalspalte fuellen
            $sql_margin = "SELECT *
                             FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
                            WHERE ".implode(" AND ",$where_array)."
                         ORDER BY ".$cfg["bannered"]["db"]["banner"]["order"];
//echo "<pre>".print_r($where_array,true)."</pre>";
//echo "<pre>$sql_margin</pre>";
//echo "<pre>$where</pre>";


//            if ( $kat != "" ) {
//                $where_kat = $cfg["bannered"]["db"]["banner"]["kat"]."=''
//                               OR ".$cfg["bannered"]["db"]["banner"]["kat"]." IS NULL
//                               ";
//                if ( is_array($kat) ) {
//                    foreach ( $kat as $value ) {
//                        $where_kat .= "OR ".$cfg["bannered"]["db"]["banner"]["kat"]." LIKE '%".$value."%'
//                               ";
//                    }
//                } else {
//                    $where_kat .= "OR ".$cfg["bannered"]["db"]["banner"]["kat"]." LIKE '%".$kat."%'";
//                }
//                $where_kat = " AND (".$where.")";
//            }
//            if ( $id != "" ) {
//                $where_def = "";
//                $where_id = "
//                              AND ".$cfg["bannered"]["db"]["banner"]["key"]."=".$id;
//            } else {
//                $where_def = "(".$cfg["bannered"]["db"]["banner"]["hide"]."=0
//                               OR  ".$cfg["bannered"]["db"]["banner"]["hide"]." IS NULL)";
//                $where_id = "";
//            }

//            // marginalspalte fuellen
//            $sql_margin = "SELECT *
//                             FROM ".$cfg["bannered"]["db"]["banner"]["entries"]."
//                            WHERE ".$where_def."
//                             ".$where_kat."".$where_id."
//                         ORDER BY ".$cfg["bannered"]["db"]["banner"]["order"];
//echo "<pre>".print_r($where_array,true)."</pre>";
//echo "<pre>$sql_margin</pre>";
//echo "<pre>$where</pre>";
            $result_margin = $db -> query($sql_margin);
            while ( $data_margin = $db -> fetch_array($result_margin,1) ) {
                // bild holen
                if ( $data_margin["bpic"] == "" ) continue;
                $sql_pic = "SELECT *
                              FROM site_file
                             WHERE fid IN (".$data_margin[$cfg["bannered"]["db"]["banner"]["pic"]].")";
                $result_pic = $db -> query($sql_pic);
                $data_pic = $db -> fetch_array($result_pic,1);

                $desc = $data_margin[$cfg["bannered"]["db"]["banner"]["desc"]];
                if ( $desc == "" ) $desc = $data_pic["funder"];

                $link = $pathvars["virtual"].$cfg["bannered"]["qlink_url"]."/".str_pad($data_margin[$cfg["bannered"]["db"]["banner"]["key"]],6,"0",STR_PAD_LEFT).".html";

                if ( $data_margin[$cfg["bannered"]["db"]["banner"]["window"]] == -1 ) {
                    $target = "_blank";
                } else {
                    $target = "";
                }

                $loop[] = array(
                    "id"   => $data_margin[$cfg["bannered"]["db"]["banner"]["key"]],
                    "desc" => $desc,
                    "link" => $link,
                    "kat"  => $data_margin[$cfg["bannered"]["db"]["banner"]["kat"]],
                 "target"  => $target,
                    "src"  => $cfg["file"]["base"]["webdir"].
                              $data_pic["ffart"]."/".
                              $data_pic["fid"]."/".
                              "o/".
                              $data_pic["ffname"],
                );
            }
//echo "<pre>".print_r($loop,true)."</pre>";
            return $loop;
        }
    }

    ### platz fuer weitere funktionen ###

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
