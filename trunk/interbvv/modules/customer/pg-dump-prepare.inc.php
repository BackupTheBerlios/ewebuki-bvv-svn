<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id$";
  $Script["desc"] = "mach aus umbruechen \r\n";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001, 2002, 2003 Werner Ammon <wa@chaos.de>

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

    // dieses skript bereitet einen postgres-dump so auf damit er ins svn eingelesen werden kann
    // es macht aus umbruechen die mit dem editor erzeugt werden die zeichenfolge \r\n
    // somit kann er problemlos ins svn eingelesen werden

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

        $pfad="/tmp/nunaber.sql";
        $pfad_aus="/tmp/nunaber_clean.sql";
        $fp2 = fopen($pfad_aus,"w");
        $fp = fopen($pfad,"r");

        $preg = "^INSERT.*(\r\n)+$";
        $preg2 = "\r\n";
        $preg3 = "^INSERT";
        $buffer = "";
        $merker = "";
        while ( $line = fgets($fp, 15000)) {
            if ( preg_match("/$preg/",$line,$regs) && $merker != "an"  ){
                $merker = "an";
                $buffer = preg_replace("/$preg2/","\\r\\n",$line);
                continue;
            } elseif  ( preg_match("/$preg/",$line,$regs) && $merker == "an"  ){
                //speichern
                fwrite($fp2,$buffer);
                $buffer = "";

            } elseif ( preg_match("/$preg3/",$line,$regs) && $merker == "an"  ){
                fwrite($fp2,$buffer);
                $buffer = "";
                // speichern
                $merker = "aus";
            } else {
            }
            if ( $merker != "an" ) {
                fwrite($fp2,$line);
            } else {
                $buffer .= preg_replace("/$preg2/","\\r\\n",$line);
            }
        }
        fclose($fp);
        fclose($fp2);

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
