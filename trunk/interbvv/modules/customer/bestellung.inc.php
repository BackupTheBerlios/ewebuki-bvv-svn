<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: bestellung.inc.php 1131 2007-12-12 08:45:50Z chaot $";
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

    86343 Königsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    ////////////////////////////////////////////////////////////////////
    // achtung: bei globalen funktionen, variablen nicht zuruecksetzen!
    // z.B. $ausgaben["form_error"],$ausgaben["inaccessible"]
    ////////////////////////////////////////////////////////////////////

    // warnung ausgeben
    if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

    // path fuer die schaltflaechen anpassen
    if ( $cfg["leer"]["iconpath"] == "" ) $cfg["leer"]["iconpath"] = "/images/default/";

    // label bearbeitung aktivieren
    if ( isset($HTTP_GET_VARS["edit"]) ) {
        $specialvars["editlock"] = 0;
    } else {
        $specialvars["editlock"] = -1;
    }
    $ausgaben["form_error"] = "";

    $hidedata["order_form"]["on"] = "on";

    // pruefen der pflichtfelder
    foreach ( $cfg["bestellung"]["data_fields"]  as $key => $value ) {

        if ( $_POST["send"] ) {
            $shopper .= $value[0]."\t\t".$_POST[$key]."\n";
            if ( $value[1] == -1 ) {
                if ( $_POST[$key] == "" ) {
                    $ausgaben["form_error"] .= "Bitte ".$value[0]." eingeben<br>";
                }
            }
            $hidedata["order_form"][$key] = $_POST[$key];
        } else {
            $hidedata["order_form"][$key] = "";
        }
    }

    $order = "";
    foreach ( $cfg["bestellung"]["order_fields"]  as $key => $value ) {
        $hidedata["order_form"][$key] = $_POST[$key];
        if ( $ausgaben["form_error"] == "" ) {
            if ( $_POST[$key] > 0 ) {
                $order .= $_POST[$key]." Stück ".$value."\n";
            }
        }
    }
    if ( $ausgaben["form_error"] != "" ) {
        $ausgaben["form_error"] = "<p class=\"error\">".$ausgaben["form_error"]."</p>";
    } else {
        if (  $order != "" ) {
            $shopper = utf8_decode($shopper);
            mail($cfg["bestellung"]["email"]["owner"],$cfg["bestellung"]["email"]["subject"],$cfg["bestellung"]["email"]["text"]."\n\n".$order."\n\n".$shopper,"Mime-Version: 1.0 Content-Type: text/plain; charset=ISO-8859-1");
            $hidedata["order_success"]["text"] = "#(success)";
           unset($hidedata["order_form"]);
        } else {
            if ( $_POST["send"] ) {
                $ausgaben["form_error"] = "<p class=\"error\">#(no_order)</p>"; 
            }
        }
    }

if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
