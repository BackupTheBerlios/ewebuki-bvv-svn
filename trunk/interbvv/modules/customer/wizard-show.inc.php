<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: contented-edit.inc.php 1242 2008-02-08 16:16:50Z chaot $";
// "contented - edit funktion";
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

    // parameter-verzeichnis:
    // 1: Datenbank
    // 2: tname
    // 3: label
    // 4: [leer]
    // 5: version

    // erlaubnis bei intrabvv speziell setzen
    $database = $environment["parameter"][1];
    if ( is_array($_SESSION["katzugriff"]) ) {
        if ( in_array("-1:".$database.":".$environment["parameter"][2],$_SESSION["katzugriff"]) ) $erlaubnis = -1;
    }
    if ( is_array($_SESSION["dbzugriff"]) ) {
        if ( in_array($database,$_SESSION["dbzugriff"]) ) $erlaubnis = -1;
    }

    // leere parameter abfangen
    $reload = 0;
    if ( $environment["parameter"][1] != "" ) {
        $db->selectDb($database,FALSE);
    } else {
        $reload = -1;
    }
    $environment["parameter"][1] = $db->getDb();
    if ( $environment["parameter"][2] == "" ) {
        $path = explode("/",str_replace($pathvars["menuroot"],"",$_SERVER["HTTP_REFERER"]));
        $kategorie = str_replace(".html","", array_pop($path));
        $ebene = implode("/",$path);
        if ( count($path) == 0 || (count($path) == 1 && $path[0]=="") ) {
            $environment["parameter"][2] = $kategorie;
        } else {
            $environment["parameter"][2] = crc32($ebene).".".$kategorie;
        }
        $reload = -1;
    }
    if ( $environment["parameter"][3] == "" ) {
        $environment["parameter"][3] = $cfg["wizard"]["wizardtyp"]["default"]["def_label"];
        $reload = -1;
    }
    if ( $reload == -1 ) header("Location: ".$cfg["wizard"]["basis"]."/".implode(",",$environment["parameter"]).".html");

    if ( $cfg["wizard"]["right"] == "" ||
        priv_check("/".$cfg["wizard"]["subdir"]."/".$cfg["wizard"]["name"],$cfg["wizard"]["right"]) ||
        priv_check_old("",$cfg["wizard"]["right"]) ||
        $rechte["administration"] == -1 ||
        $erlaubnis == -1 ) {


        // page basics
        // ***
        if ( $environment["parameter"][5] != "" ) {
            $version = " AND version=".$environment["parameter"][5];
        } else {
            $version = "";
        }

        $sql = "SELECT version, html, content, changed, byalias
                  FROM ". SITETEXT ."
                 WHERE lang = '".$environment["language"]."'
                   AND label ='".$environment["parameter"][3]."'
                   AND tname ='".$environment["parameter"][2]."'
                       $version
              ORDER BY version DESC
                 LIMIT 0,1";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);

        $form_values = $db -> fetch_array($result,1);

        // version
        $ausgaben["vaktuell"] = $form_values["version"];
        $sql = "SELECT version, html, content, changed, byalias
                  FROM ". SITETEXT ."
                 WHERE lang = '".$environment["language"]."'
                   AND label ='".$environment["parameter"][3]."'
                   AND tname ='".$environment["parameter"][2]."'
              ORDER BY version";
        $result_version = $db -> query($sql);
        $ausgaben["vgesamt"] = $db -> num_rows($result_version);
        $aktuell = 0; $back = ""; $next = "";
        while ( $data = $db -> fetch_array($result_version) ) {
            if ( $data["version"] == $form_values["version"] ) {
                $aktuell = -1;
                continue;
            }
            if ( $aktuell == 0 ) $back = $data["version"];
            if ( $aktuell == -1 ) {
                $next = $data["version"];
                break;
            }
        }
        $link = $environment["parameter"][0].",".
                $environment["parameter"][1].",".
                $environment["parameter"][2].",".
                $environment["parameter"][3].",".
                $environment["parameter"][4].",";
        if ( $back != "" ) {
            $hidedata["version_prev"]["link_prev"] = $link.$back.".html";
            $hidedata["version_prev"]["link_first"] = $link."1.html";
        }
        if ( $next != "" ) {
            $hidedata["version_next"]["link_next"] = $link.$next.".html";
            $hidedata["version_next"]["link_last"] = $link.$ausgaben["vgesamt"].".html";
        }

//             $result = $db -> query($sql);
//             if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
//             $data = $db -> fetch_array($result, $nop);
//             $content_exist = $db -> num_rows($result);

        // wizard-infos rausfinden (z.b. wizard-typ,..)
        preg_match("/^\[!\]wizard:(.+)\[\/!\]/i",$form_values["content"],$match);
        $wizard_name = "default";
        if ( $match[1] != "" ) {
            $info = explode(";",$match[1]);
            // typ
            if ( is_array($cfg["wizard"]["wizardtyp"][$info[0]]) ) $wizard_name = $info[0];
        }

        // bauen der zu bearbeitenden bereiche
        // * * *
        $tag_meat = cont_sections($form_values["content"]);
        $tag_order = $tag_meat["order"];
        unset($tag_meat["order"]);
        $tmp_tag_meat = $tag_meat;

        $content = $form_values["content"];
        foreach ( $tag_meat as $tag=>$sections ) {
            foreach ( $sections as $key=>$value ) {
                // links bauen
                $edit = $cfg["wizard"]["basis"]."/editor,".
                        $environment["parameter"][1].",".
                        $environment["parameter"][2].",".
                        $environment["parameter"][3].",".
                        $tag.":".$key.",".
                        $environment["parameter"][5].",".
                        $environment["parameter"][6].".html";
                $del = $cfg["wizard"]["basis"]."/modify,".
                        $environment["parameter"][1].",".
                        $environment["parameter"][2].",".
                        $environment["parameter"][3].",".
                        $tag.":".$key.",".
                        $environment["parameter"][5].",".
                        "delete.html";
                // bereiche vor oder nach den tag
                $pre_section  = substr($content,0,$tmp_tag_meat[$tag][$key]["start"]);
                $post_section = substr($content,$tmp_tag_meat[$tag][$key]["end"]);
                // test, inline-elemente als solche umzusetzen
                $display = "";
                $inline = array("LINK","IMG");
                if ( in_array($tag,$inline) ) {
                    $display = "display:inline;";
                }
                // bauen der "bereichsumrandung"
                $section = "<!--edit_begin--><div class=\"wiz_edit\" style=\"".$display."\">".
                           $tmp_tag_meat[$tag][$key]["complete"].
                           "<p style=\"clear:both;".$display."\" />".
                           "<div class=\"buttons\">
                                <a href=\"".$edit."\">edit</a>
                                <a href=\"".$del."\">delete</a>
                           </div>
                           </div><!--edit_end-->";
                // tag_meat-array neu durchzaehlen
                $content = $pre_section.$section.$post_section;
                $tmp_tag_meat = cont_sections($content);
            }
        }
        // + + +

        // bauen der "uebergeordneten" bereiche (keine verschachtelung)
        $allcontent = seperate_content($content);
// $ausgaben["output"] .= "<pre>".print_r($allcontent,true)."</pre>";

        // vorbereitung fuer die array-sortierung fuer das verschieben
        // * * *
        $i = 10;
        foreach ( $allcontent as $key=>$value ) {
            if ($key < $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][0]
              || (count($allcontent) - $key) <= $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][1]) {
                continue;
            } else {
                $sort_array[($key*10)] = "sort_content[]=".$key;
                $i = $i +10;
            }
        }
        function arrange_elements($sort_array, $key, $direction) {
            global $environment, $cfg;

            if ( $direction == "up" ) {
                $sort_array[($key*10)-11] = $sort_array[($key*10)];
            } elseif ( $direction == "down" ) {
                $sort_array[($key*10)+11] = $sort_array[($key*10)];
            }
            unset($sort_array[($key*10)]);
            ksort($sort_array);
            $link = $cfg["wizard"]["basis"]."/modify,".
                    $environment["parameter"][1].",".
                    $environment["parameter"][2].",".
                    $environment["parameter"][3].",".
                    "nop,".
                    $environment["parameter"][5].",".
                    "move.html?".implode("&",$sort_array);
            return $link;
        }
        // + + +

        // bereiche in eine liste pressen
        $buffer = "";
        foreach ( $allcontent as $key=>$value ) {
            if ( preg_match("/^\[!\].*\[\/!\]/i",$value) ) {
//                 echo $key.": ".$value;
                continue;
            }
            // links
            if ( $key < $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][0]
              || (count($allcontent) - $key) <= $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][1] ) {
                $ajax_class = "";
                $modify_class = " style=\"display:none;\"";
                $link_up = "";
                $link_down = "";
            } else {
                $ajax_class = "ajax_move";
                $modify_class = "";
                $link_up = arrange_elements($sort_array, $key, "up");
                $link_down = arrange_elements($sort_array, $key, "down");
            }
            // loeschen-link
            $del = $cfg["wizard"]["basis"]."/modify,".
                   $environment["parameter"][1].",".
                   $environment["parameter"][2].",".
                   $environment["parameter"][3].",".
                   "section:".$key.",".
                   $environment["parameter"][5].",".
                   "delete.html";

            $dataloop["sort_content"][] = array(
                            "key" => $key,
                          "value" => tagreplace($value),
                          "class" => $ajax_class,
                         "modify" => $modify_class,
                        "link_up" => $link_up,
                      "link_down" => $link_down,
                         "delete" => $del,
            );
        }

        // link-ziel fuer die ajax-verschieb-sache
        $ausgaben["ajax_request"] = $cfg["wizard"]["basis"]."/modify,".
                                    $environment["parameter"][1].",".
                                    $environment["parameter"][2].",".
                                    $environment["parameter"][3].",".
                                    "nop,".
                                    $environment["parameter"][5].",".
                                    "move.html";

        // add-buttons
        foreach ( $cfg["wizard"]["add_tags"] as $key=>$value ) {
            if ( !in_array($key,$cfg["wizard"]["wizardtyp"][$wizard_name]["add_tags"]) ) continue;
            $dataloop["add_buttons"][] = array(
                "link" => $cfg["wizard"]["basis"]."/modify,".
                          $environment["parameter"][1].",".
                          $environment["parameter"][2].",".
                          $environment["parameter"][3].",".
                          $key.":".strlen($form_values["content"]).",".
                          $environment["parameter"][5].",".
                          "add.html",
                "item" => $key
            );
        }

        // was anzeigen
        $mapping["main"] = "wizard-show";
        #$mapping["navi"] = "leer";


    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }



    $db -> selectDb(DATABASE,FALSE);



////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>