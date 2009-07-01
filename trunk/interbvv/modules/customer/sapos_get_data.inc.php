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

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    if ( !in_array($_SERVER["REMOTE_ADDR"],$cfg["sapos_get_data"]["allowed_ip"]) ) exit;

    $oci_db   = "(DESCRIPTION =
                    (ADDRESS = (PROTOCOL = TCP)(HOST = ".$cfg["sapos_get_data"]["oracle"]["host"].")(PORT = ".$cfg["sapos_get_data"]["oracle"]["port"]."))
                    (CONNECT_DATA = (SERVICE_NAME =".$cfg["sapos_get_data"]["oracle"]["db"]."))
                )";

    $oci_conn = oci_connect($cfg["sapos_get_data"]["oracle"]["user"], $cfg["sapos_get_data"]["oracle"]["pass"], $oci_db);

    $sql = "SELECT * FROM ".$cfg["sapos_get_data"]["db"]["stationen"]["entries"];

    $statement = oci_parse ($oci_conn, $sql);
    oci_execute ($statement);

    $i = 0;
    $insert_sql = array();
    while ($row = oci_fetch_array ($statement, OCI_ASSOC)) {
    //     echo "<pre>".print_r($row,true)."</pre>";
        $insert_a = array();
        $insert_b = array();
        foreach ( $row as $key=>$value ) {

            switch ( oci_field_type($statement, $key) ) {
                case "VARCHAR2":
                    $psql_type = "character varying(200)";
                    $psql_value = "'".addslashes($value)."'";
                    break;
                case "CHAR":
                    $psql_type = "text";
                    $psql_value = "'".addslashes($value)."'";
                    break;
                case "NUMBER":
                    if ( strstr($value,".") ) {
                        $psql_type = "float";
                    } else {
                        $psql_type = "integer";
                    }
                    $psql_value = $value;
                    break;
                case "DATE":
                    $psql_type = "date";
                    $psql_value = "'".addslashes($value)."'";
                    break;
                default:
                    $psql_type = "";
                    $psql_value = $value;
                    break;
            }

            $insert_a[] = $key;
            $insert_b[] = $psql_value;


        }
        $insert_sql[] = "INSERT INTO sapos_referenz (".implode(", ",$insert_a).") VALUES (".implode(", ",$insert_b).");";
        $i++;
    }

    $delete_sql = "DELETE FROM sapos_referenz;";

    $sql = "BEGIN;\n".
           $delete_sql."\n".
           implode("\n",$insert_sql)."\n".
           "END;";
    $result = $db -> query($sql);

    header("HTTP/1.0 200 OK");
    header("Content-Type: text/plain");
    echo $sql."\n";
    die();



    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
