<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1131 2007-12-12 08:45:50Z chaot $";
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

    if ( $cfg["admin"]["right"] == "" || $rechte[$cfg["admin"]["right"]] == -1 ) {

        ////////////////////////////////////////////////////////////////////
        // achtung: bei globalen funktionen, variablen nicht zuruecksetzen!
        // z.B. $ausgaben["form_error"],$ausgaben["inaccessible"]
        ////////////////////////////////////////////////////////////////////

        // page basics
        // ***

        include $pathvars["moduleroot"]."wizard/wizard.cfg.php";
        unset($cfg["wizard"]["function"]);
        include $pathvars["moduleroot"]."wizard/wizard-functions.inc.php";
        // +++
        // page basics


        // funktions bereich
        // ***

        // banner einbinden
        if ( $pathvars["virtual"] == "" || $_GET["edit"] ) {
            $hidedata["adminbild"] = array();
        }

        // benutzer und gruppen
        $ausgaben["user"] = $_SESSION["username"];
        if ( $_SESSION["username"] != "" ) {
            $sql = "SELECT *
                      FROM auth_member
                      JOIN auth_group
                        ON (auth_member.gid=auth_group.gid)
                     WHERE uid=".$_SESSION["uid"];
            $result = $db -> query($sql);
            while ( $data = $db -> fetch_array($result,1) ) {
                $dataloop["groups"][]["groups"] = $data["beschreibung"];
                $dataloop["group_id"][] = $data["gid"];
            }
        }

        $halt = "";
        // lokale redakteure erkennen
        if ( is_array($dataloop["group_id"]) ) {
            foreach ( $dataloop["group_id"] as $gruppe ) {
                if ( $halt == -1 ) break;
                $sql = "SELECT * FROM auth_content WHERE gid='".$gruppe."' AND pid='3'";
                $result = $db -> query($sql);
                while ( $data = $db -> fetch_array($result,1) ) {
                    if ( substr($data["tname"],0,8) == "/aemter/" ) {
                        $sql_amt = "SELECT * FROM db_adrd INNER JOIN db_adrd_kate ON ( cast (adkate as INTEGER )=katid)WHERE adakz='".substr($data["tname"],8,2)."'";
                        $result_amt = $db -> query($sql_amt);
                        $data_amt = $db -> fetch_array($result_amt,1);
                        $halt = -1;
                        $dataloop["artikel_aemter"][substr($data["tname"],8,2)]["url"] = substr($data["tname"],0,10);
                        $dataloop["artikel_aemter"][substr($data["tname"],8,2)]["name"] = $data_amt["kat_kurz"]." ".$data_amt["adststelle"];
                    }
                }
            }
        }

        if ( $halt == -1 ) {
            $hidedata["lokal_presse_section"][] = "on";
            $hidedata["lokal_artikel_section"][] = "on";
            $hidedata["lokal_termine_section"][] = "on";
        }
        // lokale redakteure erkennen

        // einzelne bereiche durchgehen (artikel, termine, ...)
        foreach ( $cfg["admin"]["specials"] as $url=>$bereich ) {
            // dataloop holen
            $buffer = find_marked_content( $url, $cfg, "inhalt" );
            $dataloop[$bereich."_edit"] = $buffer[-1];
            $dataloop[$bereich."_release"] = $buffer[-2];
            if ( is_array ( $dataloop[$bereich."_edit"] )  ) {
                foreach ( $dataloop[$bereich."_edit"] as $key => $value ) {
                    if ( $value["author"] != $_SESSION["forename"]." ".$_SESSION["surname"] ) {
                        unset($dataloop[$bereich."_edit"][$key]);
                        continue;
                    }
                    if ( priv_check($value["kategorie"],"admin;publish;edit") ) {
                        if ( $value["kategorie"] != "/aktuell/archiv" ) {
                            $sql_amt = "SELECT * FROM db_adrd INNER JOIN db_adrd_kate ON ( cast (adkate as INTEGER )=katid)WHERE adakz='".substr($value["kategorie"],8,2)."'";
                            $result_amt = $db -> query($sql_amt);
                            $data_amt = $db -> fetch_array($result_amt,1);
                            $value["amt"] = $data_amt["kat_kurz"]." ".$data_amt["adststelle"];
                            $dataloop["lokal_".$bereich."_edit"][] = $value;
                            unset($dataloop[$bereich."_edit"][$key]);
                            $hidedata["lokal_".$bereich."_edit"] = array();
                        }
                    } else {
                        unset($dataloop[$bereich."_edit"][$key]);
                    }
                }
            }

            // bereiche sichtbar machen
            if ( count($dataloop["lokal_".$bereich."_edit"]) > 0 && priv_check($url,"admin;edit") ) {
                $hidedata["lokal_".$bereich."_edit"]["num"] = count($dataloop["lokal_".$bereich."_edit"]);
            }
            if ( count($dataloop[$bereich."_edit"]) > 0 && priv_check($url,"admin;edit") ) {
                $hidedata[$bereich."_edit"]["num"] = count($dataloop[$bereich."_edit"]);
            }
            if ( count($dataloop[$bereich."_release"]) > 0 && priv_check($url,"admin;publish") ) {
                $hidedata[$bereich."_release"]["num"] = count($dataloop[$bereich."_release"]);
            }

            // berechtigung checken
            if ( !priv_check($url,"admin;edit") ) continue;
            $hidedata[$bereich."_section"] = array(
                "heading" => "#(".$bereich."_heading)",
                    "new" => "#(".$bereich."_new)",
            );

        }

        // normalen content ausschliesslich spezielle bereiche durchgehen
        $buffer = find_marked_content( "", $cfg, "inhalt", array("/aktuell","/service/fragen"));
        $bereich = "content";
        if ( count($buffer) > 0 ) {
            $hidedata[$bereich."_section"] = array(
                "heading" => "#(".$bereich."_heading)",
                    "new" => "#(".$bereich."_new)",
            );
            $dataloop[$bereich."_edit"] = $buffer[-1];
            $dataloop[$bereich."_release"] = $buffer[-2];
            if ( count($dataloop[$bereich."_edit"]) > 0 ) {
                $hidedata[$bereich."_edit"] = array();
            }
            if ( count($dataloop[$bereich."_release"]) > 0 ) {
                $hidedata[$bereich."_release"] = array();
            }
        }

        $ausgaben["user"] = $_SESSION["username"];
        // +++
        // funktions bereich


        // page basics
        // ***

        // label bearbeitung aktivieren
        if ( isset($_GET["edit"]) ) {
            $specialvars["editlock"] = 0;
        } else {
            $specialvars["editlock"] = -1;
        }

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($_GET["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (login) #(login)<br />";
            $ausgaben["inaccessible"] .= "# (adminbild) #(adminbild)<br />";
            $ausgaben["inaccessible"] .= "# (error1) #(error1)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // was anzeigen
        $mapping["main"] = "administration";

        // wohin schicken
        $backlink = "";
        if ( $_SERVER["HTTP_REFERER"] != "" ) {
            if ( strstr($_SERVER["HTTP_REFERER"],"/login.html" )
              || strstr($_SERVER["HTTP_REFERER"],"/wizard/")
              || strstr($_SERVER["HTTP_REFERER"],"/admin/") ) {
                if ( $_SESSION["admin_back_link"] != "" ) {
                    $backlink = $_SESSION["admin_back_link"];
                } else {
                    $backlink = "/index.html";
                }
            } else {
                $backlink = $_SERVER["HTTP_REFERER"];
            }
        } else {
            if ( $_SESSION["admin_back_link"] != "" ) {
                $backlink = $_SESSION["admin_back_link"];
            } else {
                $backlink = "/index.html";
            }
        }
        $backlink = preg_replace(
                        array("/^(".str_replace("/","\/",$pathvars["webroot"]).")/","/^\/auth/"),
                        "",
                        $backlink
                    );
        if ( $_SESSION["uid"] != "" ) {
            $backlink = "/auth".$backlink;
        }
        session_start();
        $_SESSION["admin_back_link"] = $backlink;
        $ausgaben["back_link"] = $backlink;

        // +++
        // page basics

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
