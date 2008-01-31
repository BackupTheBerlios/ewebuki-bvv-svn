<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 921 2007-10-17 10:30:46Z krompi $";
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

    if ( $cfg["right"] == "" || $rechte[$cfg["right"]] == -1 ) {

        // max. feldlängen von bestimmten tabellen rausfinden
        $length_check = array("site_menu","site_menu_lang");
        foreach ( $length_check as $table ) {
            $buffer = $db->show_columns($table);
            foreach ( $buffer as $column ) {
                if ( strstr($column["Type"],"char") ) {
                    $len_max[$table][$column["Field"]] = str_replace(array("(",")"),"",strstr($column["Type"],"("));
                }
            }
        }

        // utf-kodierung?
        $preg_mod = "Uusi";
        if ( $cfg["migrate"]["utf-8"] == False ) $preg_mod = "Usi";

        // menue-namen holen
        if ( file_exists($cfg["migrate"]["path"]."menue_struktur.csv") ) {
            $fd = fopen($cfg["migrate"]["path"]."menue_struktur.csv", "r");
            while (!feof($fd)) {
                $line = fgets($fd,1024);
                $array = explode(";",$line);
                $menu_csv[trim($array[0]," \"")] = str_replace("\"","",trim($array[2]));
            }
        }


        //  1. Sub-Dirs durchgehen
        foreach ( $cfg["migrate"]["subdirs"] as $subdir_entry=>$subdir_label ) {
            $ausgaben["output"] .= "<h2>Subdir: $subdir_entry</h2>";

            // anlegen des Sub-Dir-Menueeintrags
            $refid_1 = get_mid($subdir_entry,0,$subdir_label);

            if ( !is_dir($cfg["migrate"]["path"].$subdir_entry."/txt") ) continue;

            $dir = opendir($cfg["migrate"]["path"].$subdir_entry."/txt");
            while ( $file = readdir($dir) ) {

                if ( preg_match("/\.odt$/i",$file) == TRUE ) {
                    $ausgaben["output"] .= "<b>File: $file</b><br>";

                    //  2. Menue-Punkte checken
                    if ( $file == "start.odt" ) {
                        $ebene = "";
                        $kategorie = $subdir_entry;
                    } else {
                        $menu = explode("_",str_replace(".odt","",$file),3);

                        $ebene = "/".substr(rawurlencode($subdir_entry),0,$len_max["site_menu"]["entry"]);
                        $kategorie = "";

                        foreach ( $menu as $key=>$part ) {
                            if ( $kategorie != "" ) $ebene .= "/".$kategorie;
                            $kategorie = substr(rawurlencode($part),0,$len_max["site_menu"]["entry"]);
                            $index_child = $key + 2;
                            $var_child = "refid_".$index_child;
                            $index_parent = $key + 1;
                            $var_parent = "refid_".$index_parent;
                            $$var_child = get_mid($kategorie,$$var_parent,$menu_csv[$ebene."/".$kategorie]);
                    }

                    //  3.1 odt auspacken
                    $zip = new ZipArchive;
                    if ($zip->open($cfg["migrate"]["path"].$subdir_entry."/txt/".$file) == TRUE) {
                        $content =  $zip->getFromName('content.xml');
                        if ( $cfg["migrate"]["utf-8"] == False ) $content = utf8_decode($content);
                        $style   =  $zip->getFromName('styles.xml');
                    }

                    // 3.2 ggf weitere tags aus dem odt-header holen
                    preg_match_all("/(\<style:style )(.*)(>)/".$preg_mod,$content,$match);
                    foreach ( $match[0] as $key=>$value ) {
                        preg_match("/style:parent-style-name=\"(.*)\"/".$preg_mod,$value,$parent);
                        $cfg_tag = @iconv("UTF-8","ISO-8859-15",$parent[1]);
                        if ( is_array($cfg["migrate"]["tags"][$cfg_tag]) ) {
                            preg_match("/style:name=\"(.*)\"/".$preg_mod,$value,$child);
                            $cfg["migrate"]["tags"][$child[1]] = $cfg["migrate"]["tags"][$cfg_tag];
                        }
                    }

                    //  4.  Marken finden, Tags setzen
                    //  4.1.  Listen
                    preg_match_all("/(\<text:list .*>)(.*)(\<\/text:list\>)/".$preg_mod,$content,$match);
                    foreach ( $match[0] as $key=>$lists ) {
                        preg_match_all("/(\<text:list-item><text:p.*>)(.*)(\<\/text:p.*><\/text:list-item\>)/".$preg_mod,$match[0][$key],$items);
                        $merker = 0;$buffer = "";
                        foreach ( $items[0] as $index=>$value ) {
                            if ( $merker == 0 ) {
                                $buffer = "[LIST]".$items[2][$index]."\n";
                                $merker = 1;
                            } else {
                                $buffer .= "[*]".$items[2][$index]."\n";
                            }
                        }
                        if ( $buffer != "" ) $buffer .= "[/LIST]\n";
                        $content = str_replace($lists,$buffer,$content);
                    }

                    //  4.2.  Standard-Tags einfuegen
                    /* leere zeilen werden entfernt */
                    preg_match_all("/<text\:[^>]*\/>/".$preg_mod,$content,$tmp);
                    while ( preg_match("/<text\:[^>]*\/>/".$preg_mod,$content,$tmp) ) {
                        $content = preg_replace("/<text\:[^>]*\/>/".$preg_mod,"",$content);
                    }
                    preg_match_all("/(\<text\:[hp] text\:style-name=\")(.*)(\".*>)(.*)(\<\/text\:[hp]\>)/".$preg_mod,$content,$match);
                    $merker = 0;
                    foreach ( $match[0] as $key=>$value ) {
                        $tag = utf8_decode($match[2][$key]);
                        if ( $cfg["migrate"]["utf-8"] == False ) $tag = $match[2][$key];
                        /* evtl menu-eintrag ergaenzen */
//                         if ( ($tag == "BVV-Überschrift" || $cfg["migrate"]["tags"][$tag]["start"] == "[H1]" ) && $merker == 0 ) {
//                             if ( $menu[1] != "" ) {
//                                 $refid_3 = get_mid(str_replace(" ","_",$menu[1]),$refid_2,$match[4][$key]);
//                             } else {
//                                 $refid_2 = get_mid(str_replace(" ","_",$menu[0]),$refid_1,$match[4][$key]);
//                             }
//                             $merker = 1;
//                         }
                        if ( is_array($cfg["migrate"]["tags"][$tag]) ) {
                            $ersetzung = $cfg["migrate"]["tags"][$tag]["start"].$match[4][$key].$cfg["migrate"]["tags"][$tag]["end"]."\n";
                        } else {
                            $ersetzung = $cfg["migrate"]["tags"]["Standard"]["start"].$match[4][$key].$cfg["migrate"]["tags"]["Standard"]["end"]."\n";
                        }
                        $content = str_replace($value,$ersetzung,$content);
                    }

                    //  4.4.  die restlichen xml-tags entfernen
                    $content = preg_replace("/(\<)(.*)(\>)/".$preg_mod,"",$content);

                    //  5. Daten-Handling
                    //  5.1. Bilder, Archive, Dokumente
                    preg_match_all("/##(bild|doc|zip)_(.*)##/".$preg_mod,$content,$match);
                    foreach ( $match[0] as $key=>$value ) {
                        // falls keine beschreibung vorhanden ist
                        $extended = explode(";",$match[2][$key],2);
                        $match[3][$key] = $match[2][$key];
                        if ( count($extended) > 0 ) {
                            $match[2][$key] = $extended[0];
                            $match[3][$key] = $extended[1];
                        }
                        $file2insert = $cfg["migrate"]["path"].$subdir_entry."/".$cfg["migrate"]["filedirs"][$match[1][$key]]."/".$match[2][$key];
                        if ( file_exists($file2insert) ) {
                            /* file ueberpruefen */
                            $error = file_validate($file2insert, filesize($file2insert), $cfg["migrate"]["filesize"], $cfg["migrate"]["filetyp"]);
                            if ( $error == 0 ) {
                                /* db-eintrag machen */
                                $extension = strtolower(substr(strrchr($match[2][$key],"."),1));
                                /* testen, ob schon ein identischer eintrag vorhanden ist */
                                $sql = "SELECT *
                                          FROM site_file
                                         WHERE fuid=1
                                           AND ffname='".$match[2][$key]."'
                                           AND ffart='".$extension."'
                                           AND fdesc='".$match[3][$key]."'
                                           AND funder='".$match[3][$key]."'
                                           AND fhit LIKE '%from ".$file."%'";
                                $result  = $db -> query($sql);
                                $num = $db->num_rows($result);
                                if ( $num == 0 ) {
                                    $sql = "INSERT INTO site_file (fuid,
                                                                   ffname,
                                                                   ffart,
                                                                   fdesc,
                                                                   funder,
                                                                   fhit)
                                                           VALUES (1,
                                                                   '".$match[2][$key]."',
                                                                   '".$extension."',
                                                                   '".$match[3][$key]."',
                                                                   '".$match[3][$key]."',
                                                                   'from ".$file."')";
                                    $result  = $db -> query($sql);
                                    /* zu dateiablage hinzufuegen */
                                    if ( $result ) {
                                        $file_id = $db->lastid();
                                        arrange( $file_id, $file2insert, $match[2][$key], 0 );
                                    }
                                } else {
                                    $data = $db -> fetch_array($result,1);
                                    $file_id = $data["fid"];
                                    if ( $cfg["migrate"]["replace_files"] == True ) arrange( $file_id, $file2insert, $match[2][$key], 0 );
                                }
                                /* content ersetzen */
                                $ersetzung="";
                                $link = $cfg["file"]["base"]["webdir"].
                                        $extension."/".
                                        $file_id."/".
                                        $match[2][$key];
                                if ( $match[1][$key] == "bild" ) {
                                    $link = $cfg["file"]["base"]["webdir"].
                                            $extension."/".
                                            $file_id."/".
                                            $cfg["migrate"]["tags"]["image"]["size"]."/".
                                            $match[2][$key];
                                    $ersetzung = str_replace("link",$link,$cfg["migrate"]["tags"]["image"]["start"].$match[3][$key].$cfg["migrate"]["tags"]["image"]["end"]);
                                } elseif ( $match[1][$key] == "doc" ) {
                                    $ersetzung = str_replace("link",$link,$cfg["migrate"]["tags"]["link_pdf"]["start"].$match[3][$key].$cfg["migrate"]["tags"]["link_pdf"]["end"]);
                                } elseif ( $match[1][$key] == "zip" ) {
                                    $ersetzung = str_replace("link",$link,$cfg["migrate"]["tags"]["link_zip"]["start"].$match[3][$key].$cfg["migrate"]["tags"]["link_zip"]["end"]);
                                } else {
                                    $ersetzung = str_replace("link",$link,$cfg["migrate"]["tags"]["link"]["start"].$match[3][$key].$cfg["migrate"]["tags"]["link"]["end"]);
                                }
                                $content = str_replace($value,$ersetzung,$content);
                            }
                        } else {
                            $ausgaben["output"] .=  $file2insert." nicht vorhanden<br>";
                            $content = str_replace($value,"",$content);
                        }
                    }

                    // 5.2.  Tabellen
                    preg_match_all("/##(tab)_(.*)##/".$preg_mod,$content,$match);
                    foreach ( $match[0] as $key=>$tabs ) {
                        $tab_file_name = str_replace(strstr(";",$match[2][$key]),"",$match[2][$key]);
                        $tab_file = $cfg["migrate"]["path"].$subdir_entry."/".$cfg["migrate"]["filedirs"][$match[1][$key]]."/".$tab_file_name;
                        if ( file_exists($tab_file) ) {
                            $lines = file($tab_file);
                            /* 1. Durchlauf: bestimmung der maximalen Spaltenzahl */
                            $max = 0;
                            foreach ( $lines as $value ){
                                $buffer = explode(";",$value);
                                if ( count($buffer) == 1 ) $buffer = explode('","',$value);
                                if ( count($buffer) > $max ) $max = count($buffer);
                            }
                            /* 2. Durchlauf: bauen der tabelle */
                            $table = "";
                            foreach ( $lines as $value ){

                                $buffer = explode(";",$value);
                                if ( count($buffer) == 1 ) $buffer = explode('","',$value);
                                $row = "";
                                for( $i=0 ; $i<$max ; $i++ ){
                                    $cell = preg_replace("/^(\")(.*)/i",'${2}',$buffer[$i]);
                                    $cell = preg_replace("/(.*)(\")$/i",'${1}',$cell);
                                    $cell = trim($cell);
                                    if ( $cell == "" ) {
                                        $row .= "[COL]&nbsp;[/COL]\n";
                                    } else {
                                        $row .= "[COL]".$cell."[/COL]\n";
                                    }
                                }
                                $table .= "[ROW]\n".$row."[/ROW]\n";
                            }
                            if ( $table != "" ) {
                                $content = str_replace($tabs,
                                                       $cfg["migrate"]["tags"]["table"]["start"].$table.$cfg["migrate"]["tags"]["table"]["end"],
                                                       $content
                                );
                            }
                        } else {
                            $ausgaben["output"] .=  $tab_file." nicht vorhanden<br>";
                            $content = str_replace($tabs,"",$content);
                        }
                    }

                    // 5.3.  Galerien
                    unset($_SESSION["zip_extracted"]);
                    preg_match_all("/##(gal)_(.*);(.*)##/".$preg_mod,$content,$match);
                    foreach ( $match[0] as $key=>$group ) {
                        $gal_file = $cfg["migrate"]["path"].$subdir_entry."/".$cfg["migrate"]["filedirs"][$match[1][$key]]."/".$match[2][$key];
                        if ( file_exists($gal_file) ) {
                            /* naechste Selektionsnummer finden */
                            $buffer = compilation_list();
                            reset($buffer);
                            $compid = key($buffer) + 1;
                            /* entpacken */
                            $not_extracted = zip_handling($gal_file,
                                                          $cfg["file"]["base"]["maindir"].$cfg["file"]["base"]["new"],
                                                          $cfg["migrate"]["filetyp"],
                                                          $cfg["migrate"]["filesize"],
                                                          "",
                                                          $compid,
                                                          $cfg["migrate"]["zip_handling"]["sektions"]
                            );
                            $i = 0;$sort=0;
                        if ( count($_SESSION["zip_extracted"]) == 0 ) continue;
                            foreach ( $_SESSION["zip_extracted"] as $name=>$value ) {
                                /* ueberpruefen */
                                $file2insert = $cfg["file"]["base"]["maindir"].$cfg["file"]["base"]["new"].$name;
                                $error = file_validate($file2insert, filesize($file2insert), $cfg["migrate"]["filesize"], $cfg["migrate"]["filetyp"]);
                                if ( $error == 0 ) {
                                    /* db-eintrag machen */
                                    $extension = strtolower(substr(strrchr($name,"."),1));
                                    $sort++;
                                    $comp_tag = "#p".$compid.",".($sort*10)."#";
                                    /* testen, ob schon ein identischer eintrag vorhanden ist */
                                    $sql = "SELECT *
                                              FROM site_file
                                             WHERE fuid=1
                                               AND ffname='".str_replace($_SESSION["uid"]."_","",$name)."'
                                               AND ffart='".$extension."'
                                               AND fdesc='".$value["fdesc"]."'
                                               AND funder='".$value["funder"]."'
                                               AND fhit LIKE '%from ".$file."%'";
                                    $result  = $db -> query($sql);
                                    $num = $db->num_rows($result);
                                    if ( $num == 0 ) {
                                        $sql = "INSERT INTO site_file (fuid,
                                                                       ffname,
                                                                       ffart,
                                                                       fdesc,
                                                                       funder,
                                                                       fhit)
                                                               VALUES (1,
                                                                       '".str_replace($_SESSION["uid"]."_","",$name)."',
                                                                       '".$extension."',
                                                                       '".$value["fdesc"]."',
                                                                       '".$value["funder"]."',
                                                                       '".$comp_tag." from ".$file."')";
                                        $result  = $db -> query($sql);
                                        /* zu dateiablage hinzufuegen */
                                        if ( $result ) {
                                            $file_id = $db->lastid();
                                            arrange( $file_id, $file2insert, str_replace($_SESSION["uid"]."_","",$name) );
                                        }
                                    } else {
                                        $data = $db -> fetch_array($result,1);
                                        $file_id = $data["fid"];
                                        if ( $cfg["migrate"]["replace_files"] == True ) arrange( $file_id, $file2insert, str_replace($_SESSION["uid"]."_","",$name), 0 );
                                        preg_match("/#p([0-9]*),[0-9]*/",$data["fhit"],$match_compid);
                                        $compid = $match_compid[1];
                                    }
                                    if ( $i < $cfg["migrate"]["tags"]["selektion"]["pics"] ) {
                                        $pics[] = $file_id;
                                    }
                                    unlink($file2insert);
                                }
                            }
                            /* vorschaubilder suchen */
                            if ( count($pics) > 0 ) {
                                $ersetzung = str_replace(array("compid","pics"),
                                                               array($compid,implode(":",$pics)),
                                                               $cfg["migrate"]["tags"]["selektion"]["start"]
                                             ).$match[3][$key].$cfg["migrate"]["tags"]["selektion"]["end"]."\n";
                                $content = str_replace($group,
                                                       $ersetzung,
                                                       $content
                                );
                                $content = preg_replace("/\[P\](\[SEL.*\[\/SEL\])\n\[\/P\]/".$preg_mod,'${1}',$content);
                            }
                        }
                    }

                    // leere marken werden entfernt
                    if ( preg_match("/##.*##/".$preg_mod,$content) ) $content = preg_replace("/##.*##/".$preg_mod,"",$content);
                    if ( preg_match("/\[P[^\[]*\][\s]*\[\/P\]/".$preg_mod,$content) ) $content = preg_replace("/\[P[^\[]*\][\s]*\[\/P\]/".$preg_mod,"",$content);



                    // sonderkonstellationen werden bereinigt
                    // text und ueberschriften laufen um bild
                    $content = preg_replace("/\[P\](\[IMG=[^\]]*\][^\[]*\[\/IMG\])\[\/P\]([\n]?\[H[0-9]{1}\])/".$preg_mod,
                                            $cfg["migrate"]["tags"]["clear"].'${1}${2}',
                                            $content
                    );
                    // umbruch vor ueberschrift
                    $content = preg_replace("/(\[H[0-9]{1}\][^\[]+\[\/H[0-9]{1}\])([\n]?\[IMG=[^\]]*\][^\[]*\[\/IMG\])/".$preg_mod,
                                            $cfg["migrate"]["tags"]["clear"].'${1}${2}',
                                            $content
                    );
                    // kein umlauf
                    $content = preg_replace("/(\[\/H[0-9]{1}\][\n]?)\[P\](\[IMG=[^\]]*\/)(".$cfg["migrate"]["tags"]["image"]["size"].")(\/[^\]]*;)([lr])(;[^\]]*\][^\[]*\[\/IMG\])\[\/P\]/".$preg_mod,
                                            '${1}${2}'.$cfg["migrate"]["tags"]["image"]["size_banner"].'${4}${6}',
                                            $content
                    );
                    // umbruch vor ueberschrift mit bild einfuegen
                    $content = preg_replace("/(\[H2\][^\[]+\[\/H2\]\n?\[P\]\[IMG)/".$preg_mod,
                                            $cfg["migrate"]["tags"]["clear"].'${1}',
                                            $content
                    );
                    // menue der 3. ebene anzeigen
                    $sec_menu = "\n[DIV=sub_menu]\n[H3]Zum Thema[/H3]\n#{sub_menu}\n[/DIV]\n";
                    if ( $index_child > 1 ) {
                        $content = preg_replace("/(\[H1\].*\[\/H1\])/".$preg_mod,
                                                '${1}'."\n".$sec_menu,
                                                $content
                        );
                    }
                    // menuepunkte der 4. ebene unten einblenden
                    $add_links = "\n[DIV=aktuell]\ng(additional_news)\n[M2=l][/M2]\n[/DIV]\n";
                    if ( $index_child == 3 ) {
                        $content .= $add_links;
                    }
                    // bei menuepunkte der 4. ebene wird M1-Tag eingefuegt
                    $add_m1 = "\n[DIV=aktuell]\ng(additional_news)\n[M1]g(back)[/M1]\n[/DIV]\n";
                    if ( $index_child > 3 ) {
                        $content .= $add_m1;
                    }


                    // 6.  Content einfuegen
                    // wie lange duerfen die felder werden
                    $buffer = $db->show_columns("site_text");
                    $maxlength = str_replace(array("(",")"),"",strstr($buffer[2]["Type"],"("));

                    // content bereinigen
                    $content = trim($content);
                    $content = addslashes($content);

                    if ( $ebene == "" ) {
                        $tname = $kategorie;
                    } else {
                        $tname = crc32($ebene).".".$kategorie;
                    }
                    $tname = substr($tname,0,$maxlength);
                    $sql = "SELECT *
                              FROM site_text
                             WHERE lang='".$cfg["migrate"]["db"]["text"]["lang"]."'
                               AND label='".$cfg["migrate"]["db"]["text"]["label"]."'
                               AND tname='".substr($tname,0,$maxlength)."'
                          ORDER BY version DESC
                             LIMIT 0,1";
                    $result  = $db -> query($sql);
                    $num = $db->num_rows($result);

                    if ( $num == 0 ) {
                        $ausgaben["output"] .= "Noch kein Content vorhanden, eingefuegt<br>";
                        $sql = "INSERT INTO site_text (lang,
                                                       label,
                                                       tname,
                                                       ebene,
                                                       kategorie,
                                                       html,
                                                       content,
                                                       changed,
                                                       bysurname,
                                                       byforename,
                                                       byemail,
                                                       byalias,
                                                       version)
                                               VALUES ('".$cfg["migrate"]["db"]["text"]["lang"]."',
                                                       '".$cfg["migrate"]["db"]["text"]["label"]."',
                                                       '".$tname."',
                                                       '".$ebene."',
                                                       '".$kategorie."',
                                                       0,
                                                       '".$content."',
                                                       '".date("Y-m-d")."',
                                                       '".$_SESSION["surname"]."',
                                                       '".$_SESSION["forename"]."',
                                                       '".$_SESSION["email"]."',
                                                       '".$_SESSION["alias"]."',
                                                       '0')";
                        $result  = $db -> query($sql);
                    } else {
                        $data = $db -> fetch_array($result,1);
                        if ( $content != $data["content"] ) {
                            $ausgaben["output"] .= "neuer Content eingefuegt (v. ".($data["version"]+1).")<br>";
                            $sql = "INSERT INTO site_text (lang,
                                                           label,
                                                           tname,
                                                           ebene,
                                                           kategorie,
                                                           html,
                                                           content,
                                                           changed,
                                                           bysurname,
                                                           byforename,
                                                           byemail,
                                                           byalias,
                                                           version)
                                                   VALUES ('".$cfg["migrate"]["db"]["text"]["lang"]."',
                                                           '".$cfg["migrate"]["db"]["text"]["label"]."',
                                                           '".$tname."',
                                                           '".$ebene."',
                                                           '".$kategorie."',
                                                           0,
                                                           '".$content."',
                                                           '".date("Y-m-d")."',
                                                           '".$_SESSION["surname"]."',
                                                           '".$_SESSION["forename"]."',
                                                           '".$_SESSION["email"]."',
                                                           '".$_SESSION["alias"]."',
                                                           '".($data["version"]+1)."')";
                            $result  = $db -> query($sql);
                        } else {
                            $ausgaben["output"] .= "Content schon vorhanden!<br>";
                        }
                    }


                }

            }

        }

    } else {
        header("Location: ".$pathvars["virtual"]."/");
    }

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
