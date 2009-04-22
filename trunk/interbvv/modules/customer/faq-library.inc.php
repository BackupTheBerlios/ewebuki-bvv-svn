<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: faq-library.inc.php 1131 2007-12-12 08:45:50Z chaot $";
  $Script["desc"] = "faq-library";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2009 Werner Ammon ( wa<at>chaos.de )

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

        include $pathvars["moduleroot"]."libraries/function_menu_convert.inc.php";

        // label bearbeitung aktivieren
        if ( isset($_GET["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }
        $ausgaben["faq"] = "";
        if ( $environment["parameter"][1] ) {
            $ebe =  make_ebene($environment["parameter"][1]);
            $tn = eCrc(substr($ebe,0,strrpos($ebe,"/"))).".".substr($ebe,strrpos($ebe,"/")+1);
            $sql = "Select * from site_text where tname = '".$tn."' and status = 1 and label = 'inhalt'";
            $result = $db -> query($sql);
            $data = $db -> fetch_array($result,1);
            preg_match_all("/\[LIST=DEF\](.*)\[\/LIST\]/Us",$data["content"],$match);
            foreach ( $match[0] as $key => $value ) {
                $buffer .= tagreplace($value);
            }
            $buffer = str_replace("<dl>","",$buffer);
            $ausgaben["faq"] = str_replace("</dl>","",$buffer);
        } 

        // LISTE
        $sql = "Select * from site_text where content like '%[LIST=DEF]%' and status = 1";
        $result = $db -> query($sql);
        while ( $data = $db -> fetch_array($result,1) ) {
            $id = make_id(tname2path($data["tname"]));
            $name = "";
            foreach ( url2Loop(tname2path($data["tname"])) as $value ) {
                $name .= "|".$value["label"];
            }
            $dataloop["faq_sites"][$id["mid"]]["id"] = $id["mid"];
            $dataloop["faq_sites"][$id["mid"]]["name"] = $name;
        }

        // was anzeigen
        $mapping["main"] = "faq-library";

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
