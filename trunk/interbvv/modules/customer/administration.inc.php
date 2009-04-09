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

        // page basics
        // ***

        if ( $_POST["ajax"] != "" ) {
            echo "<pre>".print_r($_POST,true)."</pre>";
            if ( $_POST["ajax"] == "blinddown" ) {
                $_SESSION["admin_toggle"][$_POST['id']] = $_POST['id'];
            } elseif ( $_POST["ajax"] == "blindup" ) {
                if ( $_SESSION["admin_toggle"][$_POST['id']] != "" ) {
                    unset($_SESSION["admin_toggle"][$_POST['id']]);
                }
            }
            die();
        }

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

        // benutzer und gruppen holen
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

        // lokale redakteure erkennen
        $halt = "";
        if ( is_array($dataloop["group_id"]) ) {
            foreach ( $dataloop["group_id"] as $gruppe ) {
                if ( $halt == -1 ) break;
                $sql = "SELECT *
                          FROM auth_content
                         WHERE gid='".$gruppe."'
                           AND ( pid='3' OR pid='2')"; // 2: edit; 3: publish
                $result = $db -> query($sql);
                // dataloop mit zugewiesenen aemtern fuellen
                while ( $data = $db -> fetch_array($result,1) ) {
                    if ( substr($data["tname"],0,8) == "/aemter/" ) {
                        $sql_amt = "SELECT *
                                      FROM db_aemter
                                     WHERE adakz='".substr($data["tname"],8,2)."'";
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
            $hidedata["lokal_presse_section"][0] = "on";
            $hidedata["lokal_artikel_section"][0] = "on";
            $hidedata["lokal_termine_section"][0] = "on";
        }
        // lokale redakteure erkennen

        // marginalspalten-bearbeitung
        if ( priv_check("/global","publish") ) {
            $hidedata["marginal"] = array();
            $hidedata["marginal"]["url"] = "/auth/wizard/show,devel0,global,marginal,,,.html";
            $hidedata["marginal"]["url"] = $pathvars["virtual"]."/wizard/show,".$db->getDb().",global,marginal.html";
        }

        function get_chefred($url) {
            global $db,$member_edit,$member_publish;

            // chefredakteure holen
            $infos = "";
            $priv_info = priv_info($url,$infos);
            $array_priv_edit = array();
            $member_edit = array();
            $member_publish = array();
            foreach ( $priv_info as $priv_url=>$value ) {
                foreach ( $value["add"] as $group=>$rights ) {
                    $sql = "SELECT *
                              FROM auth_group
                              JOIN auth_member
                                ON (auth_group.gid=auth_member.gid)
                              JOIN auth_user
                                ON (auth_member.uid=auth_user.uid)
                             WHERE ggroup='".$group."'";
                    $result = $db -> query($sql);
                    $member = array();
                    while ( $data = $db -> fetch_array($result,1) ) {
                        $member[$data["username"]] = "<a href=\"mailto:".$data["email"]."\">".$data["vorname"]." ".$data["nachname"]."</a>";
                        if ( strstr($rights,"edit") && !strstr($value["del"][$group],"edit") ) {
                            $member_edit[$data["username"]] = "<a href=\"mailto:".$data["email"]."\">".$data["vorname"]." ".$data["nachname"]."</a>";
                        }
                        if ( strstr($rights,"publish") && !strstr($value["del"][$group],"publish") ) {
                            $member_publish[$data["username"]] = "<a href=\"mailto:".$data["email"]."\">".$data["vorname"]." ".$data["nachname"]."</a>";
                        }
                    }
                }
            }
        }

        // einzelne bereiche durchgehen (artikel, termine, ...)
        foreach ( $cfg["admin"]["specials"] as $url=>$bereich ) {

            // chefredakteure holen
            get_chefred($url);

            // dataloop holen
            $buffer = find_marked_content( $url, $cfg, "inhalt", array(-2,-1), array(), FALSE );
            $dataloop[$bereich."_edit"] = $buffer[-1];
            $dataloop[$bereich."_release_queue"] = $buffer[-2];
            $dataloop[$bereich."_release_wait"] = $buffer[-2];
            $released_content = find_marked_content( $url, $cfg, "inhalt", array(1), array("max_age"=>30,"user"=>$_SESSION["username"]), FALSE );
            $dataloop[$bereich."_release_recent"] = $released_content[1];

            // unterschiedliche "toggle-bereiche" nachbearbeiten
            $toggle_fields = array(
                          "edit" => array("own","edit"),
                 "release_queue" => array("all","publish"),
                  "release_wait" => array("own","edit"),
                "release_recent" => array("own","edit"),
            );
            foreach ( $toggle_fields as $tog_key=>$tog_value ) {
                $ausgaben["toggle_".$bereich."_".$tog_key] = "none";
                if ( is_array ( $dataloop[$bereich."_".$tog_key] )  ) {
                    foreach ( $dataloop[$bereich."_".$tog_key] as $key => $value ) {
                        if ( $tog_value[0] == "own" &&
                             $value["author"] != $_SESSION["forename"]." ".$_SESSION["surname"] ) {
                            unset($dataloop[$bereich."_".$tog_key][$key]);
                            continue;
                        }
                        if ( priv_check($value["kategorie"],"admin;publish;edit") ) {
                            if ( $value["kategorie"] != "/aktuell/archiv"
                              && $value["kategorie"] != "/aktuell/presse"
                              && $value["kategorie"] != "/aktuell/termine" ) {
                                $sql_amt = "SELECT *
                                              FROM db_aemter
                                             WHERE adakz='".substr($value["kategorie"],8,2)."'";
                                $result_amt = $db -> query($sql_amt);
                                $data_amt = $db -> fetch_array($result_amt,1);
                                $value["amt"] = $data_amt["kat_kurz"]." ".$data_amt["adststelle"];
                                $dataloop["lokal_".$bereich."_".$tog_key][$key] = $value;
                                unset($dataloop[$bereich."_".$tog_key][$key]);
                                if ( $hidedata["lokal_".$bereich."_section"][0] == "on" ) {
                                    $hidedata["lokal_".$bereich."_".$tog_key] = array();
                                }
                                // tabellen farben wechseln
                                if ( $color["lokal_".$bereich."_".$tog_key] == $cfg["wizard"]["color"]["a"]) {
                                    $color["lokal_".$bereich."_".$tog_key] = $cfg["wizard"]["color"]["b"];
                                } else {
                                    $color["lokal_".$bereich."_".$tog_key] = $cfg["wizard"]["color"]["a"];
                                }
                                $dataloop["lokal_".$bereich."_".$tog_key][$key]["color"] = $color["lokal_".$bereich."_".$tog_key];
                                $dataloop["lokal_".$bereich."_".$tog_key][$key]["red"] = implode(", ",$member_edit);
                                $dataloop["lokal_".$bereich."_".$tog_key][$key]["chefred"] = implode(", ",$member_publish);
                            } else {
                                // tabellen farben wechseln
                                if ( $color[$bereich."_".$tog_key] == $cfg["wizard"]["color"]["a"]) {
                                    $color[$bereich."_".$tog_key] = $cfg["wizard"]["color"]["b"];
                                } else {
                                    $color[$bereich."_".$tog_key] = $cfg["wizard"]["color"]["a"];
                                }
                                $dataloop[$bereich."_".$tog_key][$key]["color"] = $color[$bereich."_".$tog_key];
                                $dataloop[$bereich."_".$tog_key][$key]["red"] = implode(", ",$member_edit);
                                $dataloop[$bereich."_".$tog_key][$key]["chefred"] = implode(", ",$member_publish);
                            }
                        } else {
                            unset($dataloop[$bereich."_".$tog_key][$key]);
                        }
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
            if ( count($dataloop[$bereich."_release"]) > 0 && !priv_check($url,"admin;publish") && priv_check($url,"admin;edit") ) {
                $hidedata[$bereich."_release_wait"]["num"] = count($dataloop[$bereich."_release"]);
            }
            if ( count($dataloop[$bereich."_release_queue"]) > 0 && priv_check($url,"admin;publish") ) {
                $hidedata[$bereich."_release_queue"]["num"] = count($dataloop[$bereich."_release_queue"]);
            }
            if ( count($dataloop[$bereich."_release_recent"]) > 0 ) {
                $hidedata[$bereich."_release_recent"]["num"] = count($dataloop[$bereich."_release_recent"]);
                $dataloop[$bereich."_release_recent"] = array_reverse($dataloop[$bereich."_release_recent"],TRUE);
            }
            if ( count($dataloop["lokal_".$bereich."_release_recent"]) > 0 ) {
                $hidedata["lokal_".$bereich."_release_recent"]["num"] = count($dataloop["lokal_".$bereich."_release_recent"]);
                $dataloop["lokal_".$bereich."_release_recent"] = array_reverse($dataloop["lokal_".$bereich."_release_recent"],TRUE);
            }

            // berechtigung checken
            if ( !priv_check($url,"admin;edit") ) continue;
            $hidedata[$bereich."_section"] = array(
                "heading" => "#(".$bereich."_heading)",
                    "new" => "#(".$bereich."_new)",
            );

        }

        // normalen content ausschliesslich spezielle bereiche durchgehen
        // * * *
        $bereich = "content";
        $buffer = find_marked_content( "/", $cfg, "inhalt", array(-2,-1), array(), FALSE, array("/aktuell","/service/fragen"));
        $dataloop[$bereich."_edit"] = $buffer[-1];
        $dataloop[$bereich."_release"] = $buffer[-2];
        $dataloop[$bereich."_release_wait"] = $buffer[-2];
        $toggle_fields = array(
                      "edit" => array("all","edit"),
             "release_queue" => array("all","publish"),
              "release_wait" => array("own","edit"),
            "release_recent" => array("own","edit"),
        );
        foreach ( $toggle_fields as $tog_key=>$tog_value ) {
            if ( is_array ( $dataloop[$bereich."_".$tog_key] )  ) {
                foreach ( $dataloop[$bereich."_".$tog_key] as $key => $value ) {
                    get_chefred($value["path"]);
                    if ( $tog_value[0] == "own" &&
                            $value["author"] != $_SESSION["forename"]." ".$_SESSION["surname"] ) {
                        unset($dataloop[$bereich."_".$tog_key][$key]);
                        continue;
                    }
                    if ( priv_check($value["path"],$tog_value[1]) ) {
                        // tabellen farben wechseln
                        if ( $color[$bereich."_".$tog_key] == $cfg["wizard"]["color"]["a"]) {
                            $color[$bereich."_".$tog_key] = $cfg["wizard"]["color"]["b"];
                        } else {
                            $color[$bereich."_".$tog_key] = $cfg["wizard"]["color"]["a"];
                        }
                        $dataloop[$bereich."_".$tog_key][$key]["color"] = $color[$bereich."_".$tog_key];
                        $dataloop[$bereich."_".$tog_key][$key]["red"] = implode(", ",$member_edit);
                        $dataloop[$bereich."_".$tog_key][$key]["chefred"] = implode(", ",$member_publish);
                    } else {
                        unset($dataloop[$bereich."_".$tog_key][$key]);
                    }
                }
                if ( count($dataloop[$bereich."_".$tog_key]) > 0 ) {
                    $hidedata[$bereich."_".$tog_key][0] = array();
                }
            }
        }

        $ausgaben["user"] = $_SESSION["username"];
        // ggf. toggles ausklappen
        if ( is_array($_SESSION["admin_toggle"]) ) {
            foreach ( $_SESSION["admin_toggle"] as $toggle ) {
    //             $ausgaben["toggle_".$toggle] = "block";
                $dataloop["toggles"][]["element"] = $toggle;
            }
        }
        // +++
        // funktions bereich

        // TEST MENUED
        if ( $_SESSION["uid"] ) $hidedata["menu_edit"]["on"] = "on";
        $design = "modern";
        $stop["nop"] = "nop";
        $positionArray["nop"] = "nop";
        include $pathvars["moduleroot"]."admin/menued2.cfg.php";
        $cfg["menued"]["function"]["login"] = array("locate","make_ebene");
        include $pathvars["moduleroot"]."admin/menued2-functions.inc.php";
        include $pathvars["moduleroot"]."libraries/function_menutree.inc.php";


        if ( $environment["parameter"][1] == "" ) {
            $_SESSION["menued_id"] = "";
            $_SESSION["menued_opentree"] = "";
            $_SESSION["menued_design"] = "";
        } else {
            $_SESSION["menued_id"] = $environment["parameter"][1];
        }

        if ( $_SESSION["menued_id"] != "" ) {
            // explode des parameters
            $opentree = explode("-",$_SESSION["menued_opentree"]);
            // was muss geschlossen werden ?!?!?
            foreach ( $opentree as $key => $value ) {
                if ( $value != "" ) {
                    delete($value,$value);
                }
                if ( $stop != "" ) {
                    if ( in_array($value,$stop) ) {
                        unset ($opentree[$key]);
                    }
                }
            }

            // punkt oeffnen
            if ( !in_array($_SESSION["menued_id"],$stop) ) {
                $opentree[] = $_SESSION["menued_id"];
            }

            // link bauen und positionArray bauen
            foreach ( $opentree as $key => $value ) {
                $treelink == "" ? $trenner = "" : $trenner = "-";
                $treelink .= $trenner.$value;
                if ( $value != "" ) {
                    locate($value);
                }
            }

            $_SESSION["menued_design"] = $design;
        } else {
            $positionArray[0] = 0;
        }

        // welche buttons sollen angezeigt werden
        $mod = array(
                    "edit"=> array("", "Seite editieren", "edit"),
                    "add"=> array("", "Seite add", "add")
                    );

        $blacklist = "/aktuell";
        $wizard_menu = sitemap(0, "admin", "wizard",$mod,"");
     #   $wizard_menu = preg_replace($preg,"",$wizard_menu);
        $ausgaben["display"] = "none";
        $ausgaben["zeichen"] = "pluszeichen.jpg";
        if ( $environment["parameter"][1] >= "0" ) {
            $ausgaben["zeichen"] = "minuszeichen.jpg";
            $ausgaben["display"] = "";
        }
        $test = explode("<li>",$wizard_menu);
        array_shift($test);
        $preg = '/<img.*\/img>/Ui';
        $preg_link = '/^<a (href)="\/auth\/edit,([0-9]*),[0-9]*\.html"/ui';
        $preg_black = '/(href="\/auth\/login,)([0-9]*)\.html"/ui';
//         $color = "#FFFFFF";
        $color = $cfg["wizard"]["color"]["a"];
        preg_match($preg_black,$line,$black);

        foreach ( $test as $line ) {
//             ( $color == "#FFFFFF" ) ? $color = "#EEEEEE" : $color = "#FFFFFF";
            ( $color == $cfg["wizard"]["color"]["a"] ) ? $color = $cfg["wizard"]["color"]["b"] : $color = $cfg["wizard"]["color"]["a"];
            preg_match($preg_black,$line,$black);
            preg_match($preg_link,$line,$regis);

            if ( $black[2] == 263 ) continue;

            if ( $regis[2] ) {
                if ( eCrc(substr(make_ebene($regis[2]),0,strrpos(make_ebene($regis[2]),"/"))) == "0" ) {
                    $make_crc = "";
                } else {
                    $make_crc = eCrc(substr(make_ebene($regis[2]),0,strrpos(make_ebene($regis[2]),"/"))).".";
                }
                $edit_crc = $make_crc.substr(make_ebene($regis[2]),strrpos(make_ebene($regis[2]),"/")+1);
                $line = preg_replace($preg_link,"<a href=/auth/wizard/show,".DATABASE.",".$edit_crc.",inhalt,,,.html",$line);
                $line = preg_replace("/<a href=\"\/auth\/add,[0-9]*,[0-9]*.html\"/","<a href=/auth/wizard/add,,".$edit_crc.".html",$line);
                $ausgaben["edmenu"] .= "<li style=\"background-color:".$color.";margin:0;padding:0.5em;\">".$line."</li>";
            } else {
                $ausgaben["edmenu"] .= "<li style=\"background-color:".$color.";margin:0;padding:0.5em;\">".$line."</li>";
            }
        }

        // TEST MENUED

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
echo "<pre>".print_r($dataloop["lokal_artikel_release"],true)."</pre>";
// echo "<pre>".print_r($hidedata,true)."</pre>";

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
