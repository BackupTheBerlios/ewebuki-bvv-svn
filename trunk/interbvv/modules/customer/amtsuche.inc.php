<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php,v 1.6 2006/09/22 06:16:23 chaot Exp $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2006 Werner Ammon ( wa<at>chaos.de )

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

    if ( $cfg["right"] == "" || $rechte[$cfg["right"]] == -1 ) {

        // page basics
        // ***

        $ausgaben["target"] = $cfg["name"];
#echo $ausgaben["target"];
        // warnung ausgeben
        if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

        // path fuer die schaltflaechen anpassen
        if ( $cfg["iconpath"] == "" ) $cfg["iconpath"] = "/images/default/";

        // label bearbeitung aktivieren
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }
        // +++
        // page basics

        // funktions bereich
        // ***

        $form_values = array_merge($_GET,$_POST);

        if ( isset($form_values["auto"]) && !is_numeric($form_values["auto_search"]) ){
            $buffer = "";
            $sql = str_replace("##values##",$form_values["auto_search"],$cfg["sql_auto"]["sql"]);
            $result = $db -> query($sql);
            while ( $data = $db->fetch_array($result,1) ) {
                // ausgabe ggf mit gemarkung
                $item = "<span class=\"informal\">".$data[$cfg["sql_auto"]["art"]]." </span>".$data[$cfg["sql_auto"]["ort"]];
                if ( $data[$cfg["sql_auto"]["ort"]] != $data[$cfg["sql_auto"]["gmkg"]] ) $item .= " (Gemarkung: ".$data[$cfg["sql_auto"]["gmkg"]].")";
                $buffer .= "<li id=\"".$data[$cfg["sql_auto"]["kz"]]."\">".$item."</li>";
            }
            if ( $buffer != "" ) echo "<ul>".$buffer."</ul>";
            die;
        }elseif ( isset($form_values["auto"]) && is_numeric($form_values["auto_search"]) ){
            $buffer = "";
            $sql = str_replace("##values##",$form_values["auto_search"],$cfg["sql_auto_plz"]["sql"]);
            $result = $db -> query($sql);
            while ( $data = $db->fetch_array($result,1) ) {
                $buffer .= "<li id=\"".$data[$cfg["sql_auto_plz"]["kz"]]."\">".$data[$cfg["sql_auto_plz"]["plz"]]."</li>";
            }
            if ( $buffer != "" ) echo "<ul>".$buffer."</ul>";
            die;
        }

        ## treffer wurde ausgewaehlt
        $ausgaben["auto_search"] = "";
        if ( $form_values["auto_search"] ){
            $ausgaben["auto_search"] = $form_values["auto_search"];
            $sql = str_replace("##values##",$form_values["auto_search"],$cfg["sql_selected"]["sql"]);
            $result = $db->query($sql);

            ## bei einem treffer
            if ( $db->num_rows($result) == 1 ){
                $data = $db->fetch_array($result,1);
                $ausgaben["auto_search"] = $form_values["auto_search"];
                $sql = "SELECT * FROM ".$cfg["db"]["main"]["entries"]." WHERE ".$cfg["db"]["main"]["key"]." ='".$data[$cfg["db"]["main"]["value"]]."'";


                $result = $db -> query($sql);
                if ( $db->num_rows($result) != 0 ){
                    $data = $db->fetch_array($result,1);

                    $zusatz = "";
                    $bezeichnung = $data[$cfg["db"]["main"]["name"]];
                    $kz = $data[$cfg["db"]["main"]["key"]];
                    if ( $data[$cfg["db"]["main"]["kategorie"]] == "5" || $data[$cfg["db"]["main"]["kategorie"]] == "8" ){
                        $zusatz = " - ".$cfg["db"]["main"]["zusatz_bez"]." ".$data[$cfg["db"]["main"]["name"]];
                        $sql = "SELECT * FROM ".$cfg["db"]["main"]["entries"]." WHERE ".$cfg["db"]["main"]["id"]." = '".$data[$cfg["db"]["main"]["parent"]]."'";

                        $result = $db -> query($sql);
                        $data_ha = $db->fetch_array($result,1);
                        $bezeichnung =  $data_ha[$cfg["db"]["main"]["name"]];
                        $kz = $data_ha[$cfg["db"]["main"]["key"]];
                    }

                    $hidedata["treffer"] = array(
                        "beschreibung"  => $cfg["kurz"]." ".$bezeichnung,
                        "zusatz"        => $zusatz,
                        "strasse"       =>  $data[$cfg["db"]["main"]["str"]],
                        "ort"           =>  $data[$cfg["db"]["main"]["plz"]]." ".$data[$cfg["db"]["main"]["ort"]],
                        "link"          =>  $pathvars["virtual"]."/".$cfg["link"]."/".$data[$cfg["db"]["main"]["key"]]."/index.html",
                    );
                }

            ## bei mehreren treffer
            }elseif( $db->num_rows($result) > 1 ){
                $hidedata["treffer_liste"][] = -1;
                while ( $data = $db->fetch_array($result,1) ) {
                    $ausgaben["auto_search"] = $form_values["auto_search"];
                    $zusatz = "";
                    $bezeichnung = $data[$cfg["db"]["main"]["name"]];
                    $amtakz = $data[$cfg["db"]["main"]["key"]];
                    if ( $data[$cfg["db"]["main"]["kategorie"]] == "5" || $data[$cfg["db"]["main"]["kategorie"]] == "8" ){
                        $zusatz = " - ".$cfg["db"]["main"]["zusatz_bez"]." ".$data[$cfg["db"]["main"]["name"]];
                        $sql = "SELECT * FROM ".$cfg["db"]["main"]["entries"]." WHERE ".$cfg["db"]["main"]["id"]." = '".$data[$cfg["db"]["main"]["parent"]]."'";
                        $result = $db -> query($sql);
                        $data_ha = $db->fetch_array($result,1);
                        $hauptamt =  $data_ha[$cfg["db"]["main"]["name"]];
                        $amtakz = $data_ha[$cfg["db"]["main"]["key"]];
                    }

                    $dataloop["treffer"][] = array(
                        "item" => $data["name"]." ( ".$cfg["kurz"]." ".$bezeichnung.$zusatz.")",
                        "link" =>  $pathvars["virtual"]."/".$cfg["link"]."/".$data[$cfg["db"]["main"]["value"]]."/index.html",
                    );
                }
            }
        }

        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];

        // +++
        // funktions bereich


        // page basics
        // ***

        // fehlermeldungen
        if ( $HTTP_GET_VARS["error"] != "" ) {
            if ( $HTTP_GET_VARS["error"] == 1 ) {
                $ausgaben["form_error"] = "#(error1)";
            }
        } else {
            $ausgaben["form_error"] = "";
        }

        // navigation erstellen
        $ausgaben["add"] = $cfg["basis"]."/add,".$environment["parameter"][1].",verify.html";
        #$mapping["navi"] = "leer";

        // hidden values
        #$ausgaben["form_hidden"] .= "";

        // was anzeigen
        $mapping["main"] = "aemtersuche";
        $mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // wohin schicken
        #n/a

        // +++
        // page basics

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>