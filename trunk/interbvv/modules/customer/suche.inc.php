<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  "$Id: suche.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
//  "htdig-suche";
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

    // steuerung der reiter
    $hidedata["search"]["on"] = "on";
    $ausgaben["aktion"] = "service/suche.html";
    $ausgaben["displaysite"] = "";

    $ausgaben["10"] = "";
    $ausgaben["25"] = "";
    $ausgaben["50"] = "";
    if ( $_POST["restrict"] ) {
        $ausgaben["esearch"] = "";
    } else {
        $ausgaben["esearch"] = "display:none";
    }

    // restrict dropdown bauen
    $sql_restrict = "SELECT * FROM site_menu INNER JOIN site_menu_lang ON (site_menu.mid=site_menu_lang.mlid)WHERE refid='0' AND ( hide is Null or hide = '0' ) ORDER by sort";
    $result_restrict = $db -> query($sql_restrict);
    while ( $data = $db -> fetch_array($result_restrict) ){
        if ( $data["entry"] == $_POST["restrict"] ) {
            $dataloop["restrict"][$data["entry"]]["selected"] = "selected";
        } else {
            $dataloop["restrict"][$data["entry"]]["selected"] = "";
        }
        $dataloop["restrict"][$data["entry"]]["entry"] = $data["entry"];
        $dataloop["restrict"][$data["entry"]]["label"] = $data["label"];
    }

    $ausgaben["suchbegriff"] = "";
    if ( $_POST["matchesperpage"] != "" ) {
        $ausgaben[$_POST["matchesperpage"]] = "selected";
    }

    if ( $environment["fqdn"][1] != "" ) {
        $fqdn = $environment["fqdn"][0].".".$environment["fqdn"][1];
    } else {
        $fqdn = $environment["fqdn"][0];
    }

    $network_adress = $fqdn;
    if ( is_array($cfg["suche"]["alien_index"][$fqdn]) ) {
        $network_adress = $cfg["suche"]["alien_index"][$fqdn][1];
    }
    $index = $network_adress;
    if ( $cfg["suche"]["index_build"] != "" ) $index=$cfg["suche"]["index_build"];

    $suchanfrage = urlencode(utf8_decode($_POST["words"]));

    if ( $suchanfrage != "" ) $ausgaben["suchbegriff"] = $_POST["words"];

    if ( $_POST["matchesperpage"] != "" ) {
        $matchesperpage = "&matchesperpage=".$_POST["matchesperpage"];
        $hits_per_site = $_POST["matchesperpage"];
    } else {
        $hits_per_site = 10;
        $matchesperpage = "";
    }

    $pages = "&page=1";
    $page_org_site = 1;

    // page-schalter auswerten
    if ( $_POST["page"]  ) {
        preg_match("/(\|)([0-9]*)/",$_POST["page"],$matches);
        $page_org_site = round($matches[2]/$hits_per_site)+1;
        $pages = "&page=".$page_org_site;
    }

    if ( $_POST["words"] ) {
        $counter = 0;

        // eingaben absichern
        if ( get_magic_quotes_gpc() ) {
            $restrict = $_POST["restrict"];
        } else {
            $suchanfrage = addslashes($suchanfrage);
            $restrict = addslashes($_POST["restrict"]);
            $matchesperpage = addslashes($matchesperpage);
            $pages = addslashes($pages);
        }

        // html-seiten suche
        $fp=fopen("http://".$network_adress."/cgi-bin/htsearch?words=".$suchanfrage."&restrict=".$restrict."&method=and&config=".$cfg["suche"]["config"].$matchesperpage.$pages,"r");
        while ( $line = fgets($fp,1000) ){
            $line = preg_replace("/<a href=\"[A-Za-z0-9#-_:\/\"\.]*>/U","",$line);
            $line = str_replace("</a>","",$line);
            if ( preg_match("/^http:\/\/(.*)/",$line,$match) ) {
                $counter++;
                if ( $cfg["suche"]["alien_index"][$fqdn][0] != ""  ) {
                    $fqdn = $cfg["suche"]["alien_index"][$fqdn][0];
                }
                $line = preg_replace("/^http:\/\/".substr($match[1],0,strpos($match[1],"/"))."/","http://".$fqdn.$pathvars["virtual"],$line);$pathvars["virtual"];
                $dataloop["treffer"][$counter] = explode("##",$line);
                if (strrchr($dataloop["treffer"][$counter][0],".") == ".pdf") {
                    $dataloop["treffer"][$counter]["art"] = "[PDF]";
                    $dataloop["treffer"][$counter][2] = "";
                } else {
                    $dataloop["treffer"][$counter]["art"] = "";
                }
            }
        }
    }

    // wieviele treffer-seiten ?
    $site_count = floor(($dataloop["treffer"][1][5]-0.0001) / $hits_per_site) +1;

    if ( !$dataloop["treffer"][1][5] ) {
        $ausgaben["gesamt"] = "Keine Treffer";
    } elseif ( $dataloop["treffer"][1][5] == 1 ) {
        $ausgaben["gesamt"] = "Ein Treffer";
    } else {
        $begin = ($page_org_site-1)*$hits_per_site+1;
        if ( $site_count > 0 ) {
            $end = ($page_org_site-1)*$hits_per_site+$hits_per_site;
            if ( $end > $dataloop["treffer"][1][5] ) $end = $dataloop["treffer"][1][5];
        } else {
            $end = $dataloop["treffer"][1][5];
        }

        $ausgaben["gesamt"] = "Anzahl der Treffer: ".$dataloop["treffer"][1][5];
    }

    if ( !$_POST ) $ausgaben["gesamt"] = "Bitte geben Sie einen Suchbegriff ein";

    $anfang = $page_org_site -2;
    $ende = $page_org_site +2;
    if ( $anfang <= 0 ) $anfang = 1;
    if ( $ende > $site_count ) $ende = $site_count;

    for ( $i = $anfang; $i <= $ende;$i++ ) {
        // gibts den schalter zurueck?
        if ( $page_org_site > 1 && $i == $page_org_site) {
            $dataloop["site_switch"][0]["font"] = "bold";
            $dataloop["site_switch"][0]["site"] = $i-1;
        }

        $start = $i*$hits_per_site-($hits_per_site-1);
        $aus = ($i-1)*$hits_per_site+$hits_per_site;
        if ( $aus > $dataloop["treffer"][1][5] ) $aus = $dataloop["treffer"][1][5];
        if ( $i == $page_org_site ) {
            $font = "bold";
        } else {
            $font = "";
        }
        $dataloop["site_switch"][$i]["font"] = $font;
        $dataloop["site_switch"][$i]["site"] = $i;
        $dataloop["site_switch"][$i]["anzeige"] = "|".$start."-".$aus."|";
    }
    if ( is_array($dataloop["site_switch"]) ) {
        ksort($dataloop["site_switch"]);
    }

    // warnung ausgeben
    if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

    // label bearbeitung aktivieren
    if ( isset($HTTP_GET_VARS["edit"]) ) {
        $specialvars["editlock"] = 0;
    } else {
        $specialvars["editlock"] = -1;
    }

    // was anzeigen
    $mapping["main"] = "htdig";
    #$mapping["navi"] = "leer";

    // unzugaengliche #(marken) sichtbar machen
    if ( isset($HTTP_GET_VARS["edit"]) ) {
        $ausgaben["inaccessible"] = "inaccessible values:<br />";
        $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
    } else {
        $ausgaben["inaccessible"] = "";
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
