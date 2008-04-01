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
    // 4: marke
    // 5: version
    // 6: modus

    // erlaubnis bei intrabvv speziell setzen
    $database = $environment["parameter"][1];
    if ( is_array($_SESSION["katzugriff"]) ) {
        if ( in_array("-1:".$database.":".$environment["parameter"][2],$_SESSION["katzugriff"]) ) $erlaubnis = -1;
    }

    if ( is_array($_SESSION["dbzugriff"]) ) {
        if ( in_array($database,$_SESSION["dbzugriff"]) ) $erlaubnis = -1;
    }

    $db->selectDb($database,FALSE);



    if ( $cfg["wizard"]["right"] == "" ||
        priv_check("/".$cfg["wizard"]["subdir"]."/".$cfg["wizard"]["name"],$cfg["wizard"]["right"]) ||
        priv_check_old("",$cfg["wizard"]["right"]) ||
        $rechte["administration"] == -1 ||
        $erlaubnis == -1 ) {


        // page basics
        // ***

        $environment["parameter"][5] != "" ? $version = " AND version=".$environment["parameter"][5] : $version = "";

        if ( count($_POST) == 0 ) {

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

            #$data = $db -> fetch_array($result, $nop);
            $form_values = $db -> fetch_array($result,1);

        } else {
            $form_values = $_POST;
        }

        // wizard-typ rausfinden
        preg_match("/^\[!\]wizard:(.*)\[\/!\]/i",$form_values["content"],$match);
        if ( $match[1] != "" && is_array($cfg["wizard"]["wizardtyp"][$match[1]]) ) {
            $wizard_name = $match[1];
        } else {
            $wizard_name = "default";
        }

        // evtl. spezielle section
        $tag_marken = explode(":",$environment["parameter"][4]);
        $tag_meat = cont_sections($form_values["content"]);

        if ( ( count($tag_marken) >  1 || $environment["parameter"][4] == "nop" )
          && strstr($_SERVER["HTTP_REFERER"],$cfg["wizard"]["basis"]) ) {
            switch ( $environment["parameter"][6] ) {
                case "add":
                    $allcontent = seperate_content($form_values["content"]);
                    foreach ( $allcontent as $key=>$value ) {
                        if ( (count($allcontent) - $key) <= $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][1] ) {
                            $buffer[] = preg_replace("/^[ ]+/m","",$cfg["wizard"]["add_tags"][$tag_marken[0]]);
                        }
                        $buffer[] = trim($value);
                    }
                    if ( $cfg["wizard"]["wizardtyp"][$wizard_name]["section_block"][1] == 0 ) $buffer[] = $cfg["wizard"]["add_tags"][$tag_marken[0]];
                    $content = implode(chr(13).chr(10).chr(13).chr(10),$buffer);
// echo $content;
// die;
                    break;
                case "delete":
                    if ( $tag_marken[0] == "section" ) {
                        $allcontent = seperate_content($form_values["content"]);
                        foreach ( $allcontent as $key=>$value ) {
                            if ( $key == $tag_marken[1] ) continue;
                            $buffer[] = trim($value);
                        }
                        $content = implode(chr(13).chr(10).chr(13).chr(10),$buffer);
                    } else {
                        $content = substr($form_values["content"],0,$tag_meat[$tag_marken[0]][$tag_marken[1]]["start"]).
                                   substr($form_values["content"],$tag_meat[$tag_marken[0]][$tag_marken[1]]["end"]);
                    }
                    break;
                case "move":
                    $allcontent = seperate_content($form_values["content"]);
                    $i = 0;
                    foreach ( $allcontent as $key=>$value ) {
                        if ( in_array($key,$_GET["sort_content"]) ) {
                            $buffer[] = trim($allcontent[$_GET["sort_content"][$i]]);
                            $i++;
                        } else {
                            $buffer[] = trim($value);
                        }
                    }
                    $content = implode(chr(13).chr(10).chr(13).chr(10),$buffer);
// echo "--".$content."<br>";
                    break;
                default:
                    header("Location: ".$_SERVER["HTTP_REFERER"]);
                    break;
            }

            if ( $content_exist == 1 && !in_array($environment["parameter"][3], $cfg["wizard"]["archive"]) ) {
                if ( $environment["parameter"][4] == "" && $HTTP_POST_VARS["content"] == "" ) {
                    $sql = "DELETE FROM ". SITETEXT ."
                                    WHERE lang = '".$environment["language"]."'
                                    AND label ='".$environment["parameter"][3]."'
                                    AND tname ='".$environment["parameter"][2]."'";
                } else {
                    $sql = "UPDATE ". SITETEXT ." set
                                    version = ".++$data["version"].",
                                    ebene = '".$_SESSION["ebene"]."',
                                    kategorie = '".$_SESSION["kategorie"]."',
                                    crc32 = '".$specialvars["crc32"]."',
                                    html = '".$HTTP_POST_VARS["html"]."',
                                    content = '".addslashes($content)."',
                                    changed = '".date("Y-m-d H:i:s")."',
                                    bysurname = '".$_SESSION["surname"]."',
                                    byforename = '".$_SESSION["forename"]."',
                                    byemail = '".$_SESSION["email"]."',
                                    byalias = '".$_SESSION["alias"]."'
                                WHERE lang = '".$environment["language"]."'
                                AND label ='".$environment["parameter"][3]."'
                                AND tname ='".$environment["parameter"][2]."'";
                }
            } else {
                $sql = "INSERT INTO ". SITETEXT ."
                                    (lang, label, tname, version,
                                    ebene, kategorie,
                                    crc32, html, content,
                                    changed, bysurname, byforename, byemail, byalias)
                                VALUES (
                                        '".$environment["language"]."',
                                        '".$environment["parameter"][3]."',
                                        '".$environment["parameter"][2]."',
                                        '".++$form_values["version"]."',
                                        '".$_SESSION["ebene"]."',
                                        '".$_SESSION["kategorie"]."',
                                        '".$specialvars["crc32"]."',
                                        '0',
                                        '".addslashes($content)."',
                                        '".date("Y-m-d H:i:s")."',
                                        '".$_SESSION["surname"]."',
                                        '".$_SESSION["forename"]."',
                                        '".$_SESSION["email"]."',
                                        '".$_SESSION["alias"]."')";
            }
// echo "$sql";
// die;

            // notwendig fuer die artikelverwaltung alle artikel des gleichen tname's werden auf inaktiv gesetzt
            if ( preg_match("/^\[!\]/",$content,$regs) ) {
                $sql_regex = "UPDATE ". SITETEXT ." SET content=regexp_replace(content,'^\\\[!]1','[!]0') WHERE tname like '".$environment["parameter"][2]."'";
                $result_regex  = $db -> query($sql_regex);
            }

            $result  = $db -> query($sql);
        }

        if ( strstr($_SERVER["HTTP_REFERER"],$cfg["wizard"]["basis"]) ) {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        } else {
            header("Location: ".$cfg["wizard"]["basis"]."/show,".$environment["parameter"][1].",".$environment["parameter"][2].",".$environment["parameter"][3].".html");
        }
    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    $db -> selectDb(DATABASE,FALSE);



////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>