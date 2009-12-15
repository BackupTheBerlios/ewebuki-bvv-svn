<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script_name = "$Id: linking.inc.php-dist 256 2004-11-08 14:53:46Z chaot $";
  $Script_desc = "webdesigner kann mit dieser datei das laden der templates beinflussen";
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

  if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** $script_name ** ]".$debugging["char"];
  if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "linking path: ".$linking_path.$debugging["char"];

  // mit diesem script kann ansatt der jeweiligen marke
  // abhaengig vom $environment["ebene"] und/oder $environment["kategorie"]
  // mit dem wert der variable $mapping["markenbezeichnung"]
  // jedes beliebige template und deren content geladen werden.

  // beispiel 1:
  // der rechte bereich #{news} soll in der kategorie special
  // das template "meldung.tem.html" laden.
  #switch( $environment["kategorie"] ) {
  #  case "special":
  #    $mapping["news"] = "meldung";
  #    break;
  #}

  // beispiel 2:
  // in der ebene /cms soll im haupbereich ein script geladen werden
  // welches ueber die ganze breite der website geht.
  #switch( $environment["ebene"] ) {
  #  case "/cms":
  #    $mapping["screen"] = "cms";
  #    break;
  #}

  // besipiel 3:
  // natuerlich kann das ebenfalls gelichzeitig mit beiden werten gesteuert werden
  // so wird ueberall unterhalb von /bereich/seite.html
  // das template un der content von intern.tem.html geladen
  #if (  ( $environment["ebene"] == "/bereich" && $environment["kategorie"] == "seite" )
  #   || ( $environment["ebene"] == "/bereich/seite" ))
  #{
  #  $maping["screen"] = "intern";
  #}

  // bespiel 4:
  // eigene steuer variablen
  #if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "meine variable: ".$meine.$debugging["char"];
  #switch( $meine ) {
  #  case "wert1":
  #    $mapping["zusatz"] = "wert1";
  #    break;
  #}

    if ( strstr($environment["ebene"],"/admin/")
      || ($environment["ebene"] == "/keywords" && strstr($environment["kategorie"],"edit") )
      /*|| $environment["kategorie"] == "sitemap"*/){
        $mapping["screen"] = "screen_admin";
        $mapping["navi"] = "leer";
        $mapping["foot"] = "leer";
        $mapping["margin"] = "leer";
        $ausgaben["user"] = $_SESSION["username"];
    }

    if ( preg_match("/^\/m_/",$environment["ebene"]."/".$environment["kategorie"]) ) {
        $hidedata["hidden_menu"] = array();
    }

    if ( $environment["ebene"] == "/wizard" ){
        $mapping["navi"] = "leer";
        $mapping["foot"] = "leer";
    }

    // banner-steuerung
    $ausgaben["banner"] = "banner.jpg";
    $ausgaben["display_banner"] = "";
    if ( $_GET["change_banner"] != "" ) {
        $banner = explode(":",$_GET["change_banner"]);
        if ( file_exists(rtrim($pathvars["fileroot"],"/").$pathvars["images"].$banner[0]) ) {
            $ausgaben["banner"] = $banner[0];
            if ( count($banner) > 1 ) $ausgaben["display_banner"] = "display:none;";
        }
    }

    // wizard-test
    if ( $environment["ebene"] == "" && $environment["kategorie"] == "login" ){
        $mapping["navi"] = "leer";
        $mapping["foot"] = "leer";
    }

    // sub_menu wird ausgeblendet wenn man tiefer als level 2 steht
    if ( $pathvars["level_depth"] - ($pathvars["virtual_depth"] - 1) >= 3 ) {
        unset($hidedata["level3"]);
        $hidedata["level_up"] = array();
    }

    // einige ausgabe-definitionen
    if ( preg_match("/(vermessungsamt-[^.]+)\./U",$pathvars["menuroot"],$match) ) {
        $url = "http://www.".$match[1].".de".$pathvars["requested"];
    } else {
        $url = "http://www.vermessung.bayern.de".$pathvars["requested"];
    }
    $ausgaben["recommend_link"] = htmlentities("mailto:?subject=Linkempfehlung&body=".$url);

  if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ $script_name ++ ]".$debugging["char"];

//////////////////////////////////////////////////////////////////////////////////////
?>
