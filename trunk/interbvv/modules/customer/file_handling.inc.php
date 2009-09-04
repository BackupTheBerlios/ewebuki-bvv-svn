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
            // ist datei schon einmal gezaehlt
            $sql = "SELECT *
                      FROM db_count_files
                     WHERE fid=".$value[3];
            $result = $db -> query($sql);
            $num = $db -> num_rows($result);
            if ( $num == 0 ) {
                $sql = "INSERT INTO db_count_files
                                    (fid,index,first)
                             VALUES (".$value[3].",1,'".date("Y-m-d")."')";
            } else {
                $sql = "UPDATE db_count_files
                           SET index=index+1
                         WHERE fid=".$value[3];
            }
            $result = $db -> query($sql);
        }
        // datei ausgeben
        include_once $pathvars["fileroot"]."/basic/wrapper.php";
        die();
    }

    if ( $_POST["fid"] != "" || $_GET["fid"] ) {
        $fid = trim($_POST["fid"]).trim($_GET["fid"]);
        $sql = "SELECT *
                  FROM db_count_files
                 WHERE fid=".preg_replace("/[a-z,]/","",$fid);
        $result = $db -> query($sql);
        $num = $db -> num_rows($result);
        if ( $num == 0 ) {
            $downloads = 0;
        } else {
            $data = $db -> fetch_array($result,1);
            $downloads = $data["index"];
        }
        echo $downloads;
        die();
    } else {
        $sql = "SELECT *
                  FROM db_count_files
                  JOIN site_file
                    ON (db_count_files.fid=site_file.fid)
              ORDER BY index DESC";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $i = 0;
        while ( $data = $db -> fetch_array($result,1) ) {
            foreach ( $data as $key=>$value ) {
                $dataloop["count"][$i][$key] = $value;
            }
            $i++;
        }
echo "hallo";
        $mapping["main"] = "file_handling_tem";
    }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
