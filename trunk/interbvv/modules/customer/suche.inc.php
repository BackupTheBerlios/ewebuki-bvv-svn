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

    // steuerung der reiter
    $ausgaben["search_sel"] = "selected";
    $ausgaben["esearch_sel"] = "";
    $hidedata["search"]["on"] = "on";
    $ausgaben["aktion"] = "suche.html";
    $ausgaben["displaysite"] = "";
    $ausgaben["displayfile"] = " display:none";
    $ausgaben["datresult"] = " display:none";

    if ( $_POST["fpage"] ) {
        $ausgaben["displaysite"] = " display:none";
        $ausgaben["displayfile"] = "";
    }

    if ( !$_POST["words"] ) {
        $ausgaben["displaysite"] = " display:none";
        $ausgaben["displayfile"] = " display:none";
    }


    if ( $environment["parameter"][1] ) {
        $ausgaben["aktion"] = "suche,esearch.html";
        $ausgaben["search_sel"] = "";
        $hidedata["esearch"]["on"] = "";
        $ausgaben["esearch_sel"] = "selected";
        unset($hidedata["search"]);
        // restrict dropdown bauen
        $sql_restrict = "SELECT * FROM site_menu INNER JOIN site_menu_lang ON (site_menu.mid=site_menu_lang.mlid)WHERE refid='0' AND ( hide is Null or hide = '0' ) ORDER by sort";
        $result_restrict = $db -> query($sql_restrict);
        while ( $data = $db -> fetch_array($result_restrict) ){
            $dataloop["restrict"][$data["entry"]]["entry"] = $data["entry"];
            $dataloop["restrict"][$data["entry"]]["label"] = $data["label"];
        }

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

    $suchanfrage2 = preg_replace("/%F6/","oe",$suchanfrage);


    if ( $suchanfrage != "" ) $ausgaben["suchbegriff"] = $_POST["words"];
    if ( $_POST["matchesperpage"] != "" ) {
        $matchesperpage = "&matchesperpage=".$_POST["matchesperpage"];
        $hits_per_site = $_POST["matchesperpage"];
    } else {
        $hits_per_site = 10;
        $matchesperpage = "";
    }

    $page = "";
    if ( $_POST["spage"] != "" || $_POST["fpage"] != "" ) {
        $page_org = $_POST["spage"]+1;
        if ( $_POST["fpage"] != "" ) $page_org = $_POST["fpage"]+1;
        $page = "&page=".$page_org;
    }
    $fp=fopen("http://".$network_adress."/cgi-bin/htsearch?words=".$suchanfrage."&restrict=".$_POST["restrict"]."&exclude=".$index."/file/&method=and&config=".$cfg["suche"]["config"].$matchesperpage.$page,"r");

    while ( $line = fgets($fp,1000) ){
        $line = preg_replace("/<a href=\"[A-Za-z0-9#-_:\/\"\.]*>/U","",$line);
        $line = str_replace("</a>","",$line);
        if ( preg_match("/^http:\/\/(.*)/",$line,$match) ) {
            if ( $cfg["suche"]["alien_index"][$fqdn][0] != ""  ) {
                $fqdn = $cfg["suche"]["alien_index"][$fqdn][0];
            }
            $line = preg_replace("/^http:\/\/".substr($match[1],0,strpos($match[1],"/"))."/","http://".$fqdn.$pathvars["virtual"],$line);$pathvars["virtual"];
            $dataloop["treffer"][] = explode("##",$line);
        }
    }

    $i = 0;
    $fp1=fopen("http://".$network_adress."/cgi-bin/htsearch?words=".$suchanfrage2."&restrict=".$index."/file/&".$_POST["restrict"]."&method=any&config=".$cfg["suche"]["config"].$matchesperpage.$page,"r");
    while ( $line = fgets($fp1,1000) ){
        $i++;
        $line = preg_replace("/<a href=\"[A-Za-z0-9#-_:\/\"\.]*>/U","",$line);
        $line = str_replace("</a>","",$line);
        if ( preg_match("/^http:\/\/(.*)/",$line,$match) ) {
            if ( $cfg["suche"]["alien_index"][$fqdn][0] != ""  ) {
                $fqdn = $cfg["suche"]["alien_index"][$fqdn][0];
            }

            $line = preg_replace("/^http:\/\/".substr($match[1],0,strpos($match[1],"/"))."/","http://".$fqdn.$pathvars["virtual"],$line);$pathvars["virtual"];
            $dataloop["files_treffer"][] = explode("##",$line);
        }
    }

    if ( $i > 0 && $environment["parameter"][1] == "") $ausgaben["datresult"] = "";

    if ( $page_org == 0 ) $page_org = 1;
    $page_org = $page_org-1;

    // anzeige der trefferanzahl
    $site_count = floor($dataloop["treffer"][0][5] / $hits_per_site);
    if ( !$dataloop["treffer"][0][5] ) {
        $ausgaben["result"] = "Keine Treffer f&uuml;r: ".$suchanfrage;
    } elseif ( $dataloop["treffer"][0][5] == 1 ) {
        $ausgaben["result"] = "Ein Treffer";
    } else {
        $begin = $page_org*$hits_per_site+1;
        if ( $site_count > 0 ) {
            $end = $page_org*$hits_per_site+$hits_per_site;
            if ( $end > $dataloop["treffer"][0][5] ) $end = $dataloop["treffer"][0][5];
        } else {
            $end = $dataloop["treffer"][0][5];
        }

        $ausgaben["result"] = "Ergebnis ".$begin." bis ".$end." von ".$dataloop["treffer"][0][5];
    }

    // anzeige der trefferanzahl
    $site_count_files = floor($dataloop["files_treffer"][0][5] / $hits_per_site);
    if ( !$dataloop["files_treffer"][0][5] ) {
        $ausgaben["filesresult"] = "Keine Treffer f&uuml;r: ".$suchanfrage;
    } elseif ( $dataloop["files_treffer"][0][5] == 1 ) {
        $ausgaben["filesresult"] = "Ein Treffer";
    } else {
        $begin = $page_org*$hits_per_site+1;
        if ( $site_count > 0 ) {
            $end = $page_org*$hits_per_site+$hits_per_site;
            if ( $end > $dataloop["files_treffer"][0][5] ) $end = $dataloop["files_treffer"][0][5];
        } else {
            $end = $dataloop["files_treffer"][0][5];
        }

        $ausgaben["filesresult"] = "Ergebnis ".$begin." bis ".$end." von ".$dataloop["files_treffer"][0][5];
    }

    // buffy-umschalter
    if ( $site_count > 0 ) {
        $dataloop["site_switch"][0]["site"] = 0;
        $counter = 0;
        while ( $counter < $site_count ) {
           // bei 20 seiten ist schluss
            if ( $counter == 20 ) break;
            $counter++;
            $dataloop["site_switch"][$counter]["site"] = $counter;
        }
        $dataloop["site_switch"][0]["site"] = 0;
    }

    // buffy-umschalter
    if ( $site_count_files > 0 ) {
        $dataloop["files_site_switch"][0]["site"] = 0;
        $counter = 0;
        while ( $counter < $site_count_files ) {
           // bei 20 seiten ist schluss
            if ( $counter == 20 ) break;
            $counter++;
            $dataloop["files_site_switch"][$counter]["site"] = $counter;
        }
        $dataloop["files_site_switch"][0]["site"] = 0;
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
