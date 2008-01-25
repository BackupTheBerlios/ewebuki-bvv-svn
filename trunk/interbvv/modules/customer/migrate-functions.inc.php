<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 921 2007-10-17 10:30:46Z krompi $";
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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    include $pathvars["moduleroot"]."admin/fileed2.cfg.php";
    $cfg["fileed"]["function"]["migrate"] = $cfg["migrate"]["function"]["migrate"];
// echo "<pre>".print_r($cfg,true)."</pre>";
    include $pathvars["moduleroot"]."admin/fileed2-functions.inc.php";
    include $pathvars["moduleroot"]."libraries/function_file_validate.inc.php";
    include $pathvars["moduleroot"]."libraries/function_zip_handling.inc.php";

    function get_mid($entry,$refid=0,$label="") {
        global $db, $cfg, $sql;

        if ( $label == "" ) $label = $entry;

        // wie lange duerfen die felder werden
        $buffer = $db->show_columns("site_menu");
        $maxlength = str_replace(array("(",")"),"",strstr($buffer[2]["Type"],"("));
        $entry = substr($entry,0,($maxlength));
        $buffer = $db->show_columns("site_menu_lang");
        $maxlength = str_replace(array("(",")"),"",strstr($buffer[3]["Type"],"("));
        $label = substr($label,0,($maxlength));



        $sql = "SELECT *
                    FROM site_menu
                    WHERE entry='".$entry."'
                    AND refid=".$refid;
        $result = $db -> query($sql);
        $num = $db->num_rows($result);
        if ( $num == 0 ) {
            $sql = "INSERT INTO site_menu (entry, refid, defaulttemplate, hide, mandatory, level) VALUES ('".$entry."', ".$refid.", '".$cfg["migrate"]["def_temp"]."', '0', '0', '')";
            $result = $db -> query($sql);
            $id = $db->lastid();
            $sql = "INSERT INTO site_menu_lang (mid,label) VALUES (".$id.", '".$label."')";
            $result = $db -> query($sql);
        } else {
            $data = $db -> fetch_array($result,1);
            $id = $data["mid"];
            $sql = "UPDATE site_menu_lang SET label='".$label."' WHERE mid=".$id;
            $result = $db -> query($sql);
        }
        return $id;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>