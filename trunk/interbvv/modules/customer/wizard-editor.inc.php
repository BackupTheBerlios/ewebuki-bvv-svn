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

        $environment["parameter"][6] != "" ? $version = " AND version=".$environment["parameter"][6] : $version = "";

        #$sql = "SELECT *
        #          FROM ".$cfg["wizard"]["db"]["leer"]["entries"]."
        #         WHERE ".$cfg["wizard"]["db"]["leer"]["key"]."='".$environment["parameter"][1]."'";

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
        $tag_meat = cont_sections($form_values["content"]);

        if ( count($_POST) > 0 ) {
            $form_values = $_POST;
        }

        // form options holen
        #$form_options = form_options(crc32($environment["ebene"]).".".$environment["kategorie"]);

        // form elememte bauen
        #$element = form_elements( $cfg["wizard"]["db"]["leer"]["entries"], $form_values );

        // form elemente erweitern
        #$element["extension1"] = "<input name=\"extension1\" type=\"text\" maxlength=\"5\" size=\"5\">";
        #$element["extension2"] = "<input name=\"extension2\" type=\"text\" maxlength=\"5\" size=\"5\">";

        // +++
        // page basics


        // funktions bereich fuer erweiterungen
        // ***

        ### put your code here ###

        // funktion_content.inc.php zeile 181,182 reicht nicht (mehr)
        // eine funktion die nicht aufgerufen wird füllt auch die variablen nicht
        if ( $defaults["section"]["label"] == "" ) $defaults["section"]["label"] = "inhalt";
        if ( $defaults["section"]["tag"] == "" ) $defaults["section"]["tag"] = "[H";

        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "ebene: ".$_SESSION["ebene"].$debugging["char"];
        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "kategorie: ".$_SESSION["kategorie"].$debugging["char"];



        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "last edit: ".$_SESSION["cms_last_edit"].$debugging["char"];;
        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "last ebene: ".$_SESSION["cms_last_ebene"].$debugging["char"];;
        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "last kategorie: ".$_SESSION["cms_last_kategorie"].$debugging["char"];;

        if ( isset($_SESSION["cms_last_edit"]) && $_GET["referer"] != "" ) {
            unset($_SESSION["cms_last_edit"]);

            $_SESSION["ebene"] = $_SESSION["cms_last_ebene"];
            $_SESSION["kategorie"] = $_SESSION["cms_last_kategorie"];

            unset($_SESSION["cms_last_ebene"]);
            unset($_SESSION["cms_last_kategorie"]);
        }

        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "neue ebene    : ".$_SESSION["ebene"].$debugging["char"];;
        if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "neue kategorie: ".$_SESSION["kategorie"].$debugging["char"];;

        // status anzeigen
        $ausgaben["ce_tem_db"]      = "#(db): ".$environment["parameter"][1];
        $ausgaben["ce_tem_name"]    = "#(template): ".$environment["parameter"][2];
        $ausgaben["ce_tem_label"]   = "#(label): ".$environment["parameter"][3];
        $ausgaben["version"]        = "#(version): ".$form_values["version"];

        # $environment["parameter"][4] -> abschnitt bearbeiten -> war: datensatz in db gefunden

        $ausgaben["ce_tem_lang"]    = "#(language): ".$environment["language"];
        $ausgaben["ce_tem_convert"] = "#(convert): ".$environment["parameter"][5];


        // lock erzeugen, anzeigen
        $sql = "SELECT byalias, lockat
                    FROM site_lock
                   WHERE lang = '".$environment["language"]."'
                     AND label ='".$environment["parameter"][3]."'
                     AND tname ='".$environment["parameter"][2]."'";
        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        if ( $data = $db -> fetch_array($result, $nop) ) {
            $ausgaben["lock"] .= "lock by ".$data["byalias"]." @ ".$data["lockat"];
            $ausgaben["class"] = "ta_lock";
        } else {
            $sql = "INSERT INTO site_lock
                    (tname, lang, label, byalias, lockat)
            VALUES ('".$environment["parameter"][2]."',
                    '".$environment["language"]."',
                    '".$environment["parameter"][3]."',
                    '".$_SESSION["alias"]."',
                    '".date("Y-m-d H:i:s")."')";
            $result  = $db -> query($sql);
            $ausgaben["lock"] .= "lock by ".$_SESSION["alias"]." @ ".date("Y-m-d H:i:s");
            $ausgaben["class"] = "ta_norm";
        }


//         // eWeBuKi tag schutz - sections 1
//         if ( strpos( $form_values["content"], "[/E]") !== false ) {
//             $preg = "|\[E\](.*)\[/E\]|Us";
//             preg_match_all($preg, $form_values["content"], $match, PREG_PATTERN_ORDER );
//             $mark = $defaults["section"]["tag"];
//             $hide = "++";
//             foreach ( $match[0] as $key => $value ) {
//                 $escape = str_replace( $mark, $hide, $match[1][$key]);
//                 $form_values["content"] = str_replace( $value, "[E]".$escape."[/E]", $form_values["content"]);
//             }
//         }


        // evtl. spezielle section
        $tag_marken = explode(":",$environment["parameter"][4]);


//         if ( count($tag_marken) == 1 ) {
//             if ( is_array($defaults["section"]["tag"]) ) {
//                 $preg_search = str_replace(
//                                 array("[", "]", "/"),
//                                 array("\[","\]","\/"),
//                                 implode("|",$defaults["section"]["tag"])
//                 );
//                 $allcontent = preg_split("/(".$preg_search.")/",$form_values["content"],-1,PREG_SPLIT_DELIM_CAPTURE);
//                 $i = 0;
//                 foreach ( $allcontent as $key=>$value ) {
//                     if ( in_array($value,$defaults["section"]["tag"]) ) {
//                         $join[$i] = "{".$i."}".$value;
//                     } else {
//                         $join[$i] .= $value;
//                         $i++;
//                     }
//                 }
//
//                 if ( $environment["parameter"][4] != "" ) {
//                     $form_values["content"] = preg_replace("/\{[0-9]+\}/U","",$join[$environment["parameter"][4]]);
//                 }
//             } else {
//                 $alldata = explode($defaults["section"]["tag"], $form_values["content"]);
//                 if ( $environment["parameter"][4] != "" ) {
//                     $form_values["content"] = $defaults["section"]["tag"].$alldata[$environment["parameter"][4]];
//                 }
//             }
//         } elseif ( count($_POST) == 0 ) {
            $form_values["content"] = $tag_meat[$tag_marken[0]][$tag_marken[1]]["meat"];
//         }


//         // eWeBuKi tag schutz - sections 2
//         $form_values["content"] = str_replace( $hide, $mark, $form_values["content"]);


        // einzelne sektionen
        // * * *
        if ( count($tag_marken) > 0 ) {
            switch ($tag_marken[0]) {
                case "IMG":
                    $hidedata["img"]["meat"] = $tag_meat[$tag_marken[0]][$tag_marken[1]]["meat"];
                    if ( $_POST["description"] != "" ) $hidedata["img"]["meat"] = $_POST["description"];
                    $opentag = str_replace(array("[","]"),"",$tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"]);
                    $tag_werte = explode(";",trim(strstr($opentag,"="),"="));
                    for ($i = 0; $i <= 6; $i++) {
                        if ( is_array($_POST["tagwerte"]) ) {
                            $ausgaben["tagwerte".$i] = $_POST["tagwerte"][$i];
                        } elseif ( $tag_werte[$i] != "" ) {
                            $ausgaben["tagwerte".$i] = $tag_werte[$i];
                        } else {
                            $ausgaben["tagwerte".$i] = "";
                        }
                    }
                    // preview-bild
                    $pic_info = str_replace($cfg["file"]["base"]["webdir"],"",$ausgaben["tagwerte0"]);
                    $pic_array = explode("/",$pic_info);
                    if ( is_array($_SESSION["file_memo"]) || $pic_array[1] != "" ) {
                        if ( is_array($_SESSION["file_memo"]) ) {
                            $fid = current($_SESSION["file_memo"]);
                        } else {
                            $fid = $pic_array[1];
                        }
                        $sql = "SELECT * FROM site_file WHERE fid=".$fid;
                        $result = $db -> query($sql);
                        if ( $db -> num_rows($result) == 1 ) {
                            $data = $db -> fetch_array($result);
                            $hidedata["imgpreview"]["src"] = $cfg["file"]["base"]["webdir"].
                                                             $data["ffart"]."/".
                                                             $fid."/s/".
                                                             $data["ffname"];
                            $target_src = $cfg["file"]["base"]["webdir"].
                                          $data["ffart"]."/".
                                          $fid."/".
                                          $pic_array[count($pic_array)-2]."/".
                                          $data["ffname"];
                            if ( is_array($_SESSION["file_memo"]) && $hidedata["img"]["meat"] == "" ) $hidedata["img"]["meat"] = $data["funder"];
                        }
                        unset($_SESSION["file_memo"]);
                    }
                    // anzeigen-groesse-radiobutton
                    if ( count($cfg["wizard"]["img_edit"]["cb_show_size"]) >0 ) {
                        foreach ( $cfg["wizard"]["img_edit"]["cb_show_size"] as $value=>$label ) {
                            $pic_url = $cfg["file"]["base"]["webdir"].$data["ffart"]."/".$fid."/".$value."/".$data["ffname"];
                            $check = "";
                            if ( strstr($ausgaben["tagwerte0"],"/".$value."/") ) $check = " checked=\"checked\"";
                            $dataloop["show"][] = array(
                                "value" => $pic_url,
                                "label" => $label,
                                "check" => $check,
                            );
                        }
                    } else {
                        $dataloop["show"][] = array(
                            "value" => $target_src,
                            "label" => "not changeable",
                            "check" => " checked=\"checked\"",
                        );
                    }
                    // align-radiobutton
                    if ( count($cfg["wizard"]["img_edit"]["cb_align"]) >0 ) {
                        foreach ( $cfg["wizard"]["img_edit"]["cb_align"] as $value=>$label ) {
                            $check = "";
                            if ( $ausgaben["tagwerte1"] == $value ) $check = " checked=\"checked\"";
                            $dataloop["align"][] = array(
                                "value" => $value,
                                "label" => $label,
                                "check" => $check,
                            );
                        }
                    } else {
                        $dataloop["align"][] = array(
                            "value" => $ausgaben["tagwerte1"],
                            "label" => "not changeable",
                            "check" => " checked=\"checked\"",
                        );
                    }
                    // size-radiobutton
                    if ( count($cfg["wizard"]["img_edit"]["cb_link_size"]) > 0 ) {
                        foreach ( $cfg["wizard"]["img_edit"]["cb_link_size"] as $value=>$label ) {
                            $check = "";
                            if ( $ausgaben["tagwerte3"] == $value ) $check = " checked=\"checked\"";
                            $dataloop["size"][] = array(
                                "value" => $value,
                                "label" => $label,
                                "check" => $check,
                            );
                        }
                    } else {
                        $dataloop["size"][] = array(
                            "value" => $ausgaben["tagwerte3"],
                            "label" => "not changeable",
                            "check" => " checked=\"checked\"",
                        );
                    }
                    break;

                case "TAB":
                    $hidedata["tab"] = array();
                    $opentag = str_replace(array("[","]"),"",$tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"]);
                    $tag_werte = explode(";",trim(strstr($opentag,"="),"="));
                    for ($i=0;$i<5;$i++){
                        if ( is_array($_POST["tagwerte"]) ) {
                            $ausgaben["tagwerte".$i] = $_POST["tagwerte"][$i];
                        } elseif ( $tag_werte[$i] != "" ) {
                            $ausgaben["tagwerte".$i] = $tag_werte[$i];
                        } else {
                            $ausgaben["tagwerte".$i] = "";
                        }
                    }
                    // daten auflisten
                    preg_match_all("/\[ROW\](.*)\[\/ROW\]/Us",$tag_meat[$tag_marken[0]][$tag_marken[1]]["meat"],$rows);
                    $ausgaben["tabelle"] = "<table width=\"100%\">\n";
                    $row_index = 0; $ausgaben["num_row"] = 0; $ausgaben["num_col"] = 0;
                    foreach ( $rows[1] as $row ) {
                        $ausgaben["tabelle"] .= "<tr>";
                        preg_match_all("/\[COL\](.*)\[\/COL\]/Us",$row,$cells);
                        $col_index = 0; $ausgaben["num_col"] = 0;
                        foreach ( $cells[1] as $cell ) {
                            $ausgaben["tabelle"] .= "<td>
                                                    <input type=\"text\" value=\"".$cell."\" name=\"cells[".$row_index."][".$col_index."]\" />
                                                    </td>";
                            $col_index++; $ausgaben["num_col"]++;
                        }
                        $ausgaben["tabelle"] .= "</tr>";
                        $row_index++; $ausgaben["num_row"]++;
                    }
                    $ausgaben["tabelle"] .= "</table>";

// echo "<pre>".print_r($rows,true)."</pre>";
                    break;

                case "LIST":
                    $hidedata["default"] = array();
                    $hidedata["list"] = array();
                    $buffer = explode("[*]",$form_values["content"]);
                    $form_values["content"] = "";
                    foreach ( $buffer as $value ) {
                        if ( $form_values["content"] != "" ) $form_values["content"] .= chr(13).chr(10).chr(13).chr(10);
                        $form_values["content"] .= trim($value);
                    }
                    break;

                case "SEL":
                    $hidedata["sel"] = array();
                    $ausgaben["desc"] = $tag_meat[$tag_marken[0]][$tag_marken[1]]["meat"];
echo "<pre>".print_r($tag_meat["SEL"][0],true)."</pre>";
                    break;

                default:
                    $hidedata["default"] = array();
                    break;
            }
        }
        // + + +



        /*
        / wenn preview gedrueckt wird, hidedata erzeugen und $form_values["content"] aendern
        /
        / so funktioniert das ganze nicht
        / (es wird nie gespeichert -> "edit" anstatt "save" in der aktion url)
        / der extra parameter in der aktion url und
        / die if abfrage die den save verhindert
        / hat mir nicht gefallen!
        */
        if ( $_POST["PREVIEW"]  ){
            $hidedata["preview"]["content"] = "#(preview)";
            $preview = intelilink($_POST["content"]);
            $preview = tagreplace($preview);
            $hidedata["preview"]["content"] .= nlreplace($preview);
            $form_values["content"] = $_POST["content"];
        }



        // convert tag 2 html
        switch ( $environment["parameter"][5] ) {
            case "html":
                // content nach html wandeln
                $form_values["content"] = tagreplace($form_values["content"]);
                // intelligenten link tag bearbeiten
                $form_values["content"] = intelilink($form_values["content"]);
                // newlines nach br wandeln
                $form_values["content"] = nlreplace($form_values["content"]);
                // html db value aendern
                $form_values["html"] = -1;
                break;
            case "tag":
                // content nach cmstag wandeln
                ###
                // html db value aendern
                $form_values["html"] = 0;
                break;
            default:
                $form_values["html"] = 0;
        }


//         // eWeBuKi tag schutz part 3
//         $mark_o = array( "#(", "g(", "#{", "!#" );
//         $hide_o = array( "::1::", "::2::", "::3::", "::4::" );
//         $form_values["content"] = str_replace( $mark_o, $hide_o, $form_values["content"]);


//         // wie wird content verarbeitet
//         if ( $form_values["html"] == "-1" ) {
//             $ausgaben["ce_name"] = "content";
//             $ausgaben["ce_inhalt"] = $form_values["content"];
//
//             // epoz fix
//             if ( $specialvars["wysiwyg"] == "epoz" ) {
//                 $sea = array("\\","\n","\r","'");
//                 $rep = array("\\\\","\\n","\\r","\\'");
//                 $ausgaben["ce_inhalt"] = str_replace( $sea, $rep, $ausgaben["ce_inhalt"]);
//             }
//
//             // template version
//             $art = "-".$specialvars["wysiwyg"];
//         } else {
            // ce editor bauen

            $ausgaben["name"] = "content";
            if ( $cfg["wizard"]["letters"] != "" ) {
                $ausgaben["charakters"] = "#(charakters)";
                $ausgaben["eventh2"] = "onKeyDown=\"count('content',".$cfg["wizard"]["letters"].");\" onChange=\"chk('content',".$cfg["wizard"]["letters"].");\"";
            } else {
                $ausgaben["charakters"] = "";
            }
            $ausgaben["inhalt"] = $form_values["content"];


            // feststellen, welche Tags erlaub sind
            $allowed_tags = $cfg["wizard"]["allowed_tags"][$tag_marken[0]];
            if ( count($tag_marken) > 1 ) {
                $tag_compl = str_replace(array("[","]"),"",$tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"]);
                if ( is_array($cfg["wizard"]["allowed_tags"][$tag_compl]) ) {
                    $allowed_tags = $cfg["wizard"]["allowed_tags"][$tag_compl];
                }
            }
            $ausgaben["tn"] = makece("ceform", "content", $form_values["content"], $allowed_tags);


//             // vogelwilde regex die alte & neue file links findet
//             // und viel arbeit erspart
//             preg_match_all("/[_\/]([0-9]+)[.\/]/",$form_values["content"],$found);
//             $debugging["ausgabe"] .= "<pre>".print_r($found,True)."</pre>";
//
//             // file memo auslesen und zuruecksetzen
//             if ( is_array($_SESSION["file_memo"]) ) {
//                 $array = array_merge($_SESSION["file_memo"],$found[1]);
// //                 unset($_SESSION["file_memo"]);
//             } else {
//                 $array = $found[1];
//             }

//             // wenn es thumbnails gibt, anzeigen
//             if ( count($array) >= 1 ) {
//
//                 $merken = $db -> getDb();
//                 if ( $merken != DATABASE ) {
//                     $db -> selectDB( DATABASE ,"");
//                 }
//
//                 foreach ( $array as $value ) {
//                     if ( $where != "" ) $where .= " OR ";
//                     $where .= "fid = '".$value."'";
//                 }
//                 $sql = "SELECT * FROM site_file WHERE ".$where." ORDER BY ffname, funder";
//                 $result = $db -> query($sql);
//
//
//                 if ( $merken != DATABASE ) {
//                     $db -> selectDB($merken,"");
//                 }
//
//                 filelist($result, "contented");
//             }

//             if ( is_array($_SESSION["compilation_memo"]) ) {
//                 foreach ( $_SESSION["compilation_memo"] as $compid=>$value ) {
//                     $dataloop["selection"][] = array(
//                         "id" => $compid,
//                         "pics" => implode(":",$value)
//                     );
//                 }
//                 if ( count($dataloop["selection"]) > 0 ) $hidedata["selection"] = array();
//             }


            // template version
            $art = "";
//         }



        // referer im form mit hidden element mitschleppen
        if ( $HTTP_GET_VARS["referer"] != "" ) {
            $ausgaben["form_referer"] = $HTTP_GET_VARS["referer"];
            $ausgaben["form_break"] = $HTTP_GET_VARS["referer"];
        } elseif ( $_POST["form_referer"] == "" ) {
            $ausgaben["form_referer"] = $_SERVER["HTTP_REFERER"];
        } else {
            $ausgaben["form_referer"] = $_POST["form_referer"];
        }



        // +++
        // funktions bereich fuer erweiterungen


        // page basics
        // ***

        // fehlermeldungen
        $ausgaben["form_error"] = "";

        // navigation erstellen
        #$ausgaben["form_aktion"] = $cfg["wizard"]["basis"]."/edit,".$environment["parameter"][1].",verify.html";
        #$ausgaben["form_break"] = $cfg["wizard"]["basis"]."/list.html";

        #$ausgaben["form_aktion"] = $cfg["wizard"]["basis"]."edit/save,".$environment["parameter"][1].",".$environment["parameter"][2].",".$environment["parameter"][3].",".$environment["parameter"][4].".html";
        $ausgaben["form_aktion"] = $cfg["wizard"]["basis"]."/editor,".$environment["parameter"][1].",".$environment["parameter"][2].",".$environment["parameter"][3].",".$environment["parameter"][4].",,,verify.html";
        #$ausgaben["form_abbrechen"] = $_SESSION["page"];
        $ausgaben["form_break"] = $cfg["wizard"]["basis"]."/editor,".$environment["parameter"][1].",".$environment["parameter"][2].",".$environment["parameter"][3].",".$environment["parameter"][4].",,,unlock.html";


        // hidden values
        #$ausgaben["form_hidden"] .= "";
        $ausgaben["form_hidden"] .= $form_values["html"];

        // was anzeigen
        $mapping["main"] = "wizard-edit".$art;
        #$mapping["navi"] = "leer";

        // unzugaengliche #(marken) sichtbar machen
        if ( isset($HTTP_GET_VARS["edit"]) ) {
            $ausgaben["inaccessible"] = "inaccessible values:<br />";
            $ausgaben["inaccessible"] .= "# (error_result) #(error_result)<br />";
            $ausgaben["inaccessible"] .= "# (error_dupe) #(error_dupe)<br />";
            $ausgaben["inaccessible"] .= "# (upload) #(upload)<br />";
            $ausgaben["inaccessible"] .= "# (file) #(file)<br />";
            $ausgaben["inaccessible"] .= "# (files) #(files)<br />";
        } else {
            $ausgaben["inaccessible"] = "";
        }

        // wohin schicken
        #n/a


        // +++
        // page basics

        // lock aufheben
        if ( $environment["parameter"][7] != "" ) {
            $sql = "DELETE FROM site_lock
                          WHERE label ='".$environment["parameter"][3]."'
                            AND tname ='".$environment["parameter"][2]."'
                            AND lang = '".$environment["language"]."'";
            $result  = $db -> query($sql);
//             header("Location: ".$_SESSION["page"]."");
        }

        if ( $environment["parameter"][7] == "verify"
            &&  ( $_POST["send"] != ""
                || $_POST["add"] != ""
                || $_POST["sel"] != ""
                || $_POST["col_resize"] != ""
                || $_POST["upload"] != "" ) ) {


            // form eingaben prüfen
            form_errors( $form_options, $_POST );


            // gibt es bereits content?
            $sql = "SELECT version, html, content
                      FROM ". SITETEXT ."
                     WHERE tname='".$environment["parameter"][2]."'
                       AND lang='".$environment["language"]."'
                       AND label='".$environment["parameter"][3]."'
                  ORDER BY version DESC
                     LIMIT 0,1";
            $result = $db -> query($sql);
            if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $data = $db -> fetch_array($result, $nop);
            $content_exist = $db -> num_rows($result);

            // evtl. spezielle section
            if ( $environment["parameter"][4] != "" ) {

                // eWeBuKi tag schutz - sections 1
                if ( strpos( $data["content"], "[/E]") !== false ) {
                    $preg = "|\[E\](.*)\[/E\]|Us";
                    preg_match_all($preg, $data["content"], $match, PREG_PATTERN_ORDER );
                    $mark = $defaults["section"]["tag"];
                    $hide = "++";
                    foreach ( $match[0] as $key => $value ) {
                        $escape = str_replace( $mark, $hide, $match[1][$key]);
                        $data["content"] = str_replace( $value, "[E]".$escape."[/E]", $data["content"]);
                    }
                }

                if ( count($tag_marken) == 1 ) {

                    if ( is_array($defaults["section"]["tag"]) ) {

                        $preg_search = str_replace(
                                        array("[", "]", "/"),
                                        array("\[","\]","\/"),
                                        implode("|",$defaults["section"]["tag"])
                        );
                        $allcontent = preg_split("/(".$preg_search.")/",$data["content"],-1,PREG_SPLIT_DELIM_CAPTURE);
                        $i = 0;
                        foreach ( $allcontent as $key=>$value ) {
                            if ( in_array($value,$defaults["section"]["tag"]) ) {
                                $join[$i] = "{".$i."}".$value;
                            } else {
                                $join[$i] .= $value;
                                $i++;
                            }
                        }

                        $content = "";
                        foreach ( $join as $key=>$value ) {
                            if ( $key == $environment["parameter"][4] ) {
                                $content .= $_POST["content"];
                            } elseif ( $key > 0 ) {
                                $content .= preg_replace("/\{[0-9]+\}/U","",$value);
                            } else {
                                $content .= $value;
                            }
                        }
                        // eWeBuKi tag schutz - sections 2
                        $content = str_replace( $hide, $mark, $content );

                    } else {
                        $allcontent = explode($defaults["section"]["tag"], addslashes($data["content"]) );
                        $content = "";
                        foreach ($allcontent as $key => $value) {
                            if ( $key == $environment["parameter"][4] ) {
                                $length = strlen( $defaults["section"]["tag"] );
                                if ( substr($_POST["content"],0,$length) == $defaults["section"]["tag"] ) {
                                    $content .= $defaults["section"]["tag"].substr($_POST["content"],$length);
                                } else {
                                    $content .= $_POST["content"];
                                }
                            } elseif ( $key > 0 ) {
                                $content .= $defaults["section"]["tag"].$value;
                            } else {
                                $content .= $value;
                            }

                        // eWeBuKi tag schutz - sections 2
                        $content = str_replace( $hide, $mark, $content );

                        }
                    }
                } else {
                    $tag_meat = cont_sections($data["content"]);
                    if ( $tag_marken[0] == "IMG" ) {
                        $tag_werte = array();
                        for ($i = 0; $i <= 6; $i++) {
                            $tag_werte[] = $_POST["tagwerte"][$i];
                        }
                        $to_insert = "[IMG=".implode(";",$tag_werte)."]".$_POST["description"]."[/IMG]";
                    } elseif ( $tag_marken[0] == "TAB" ) {
                        if ( $_FILES["csv_upload"]["type"] == "text/csv" ) {
                            $handle = fopen ($_FILES["csv_upload"]["tmp_name"],"r");
                            $tab = "[TAB=".implode(";",$_POST["tagwerte"])."]\n";
                            while ( ($csv_data = fgetcsv ($handle, 1000, ";")) !== FALSE ) {
                                $tab .= "[ROW]\n";
                                foreach ( $csv_data as $cell ) {
                                    $tab .= "[COL]".trim($cell)."[/COL]\n";
                                }
                                $tab .= "[/ROW]\n";
                            }
                            $tab .= "[/TAB]";
                            fclose ($handle);
                            $to_insert = $tab;
                        } else {
                            $tab = "[TAB=".implode(";",$_POST["tagwerte"])."]\n";
                            for ($i=0;$i<$_POST["num_row"];$i++) {
                                $tab .= "[ROW]\n";
                                for ($k=0;$k<$_POST["num_col"];$k++) {
                                    $tab .= "[COL]".trim($_POST["cells"][$i][$k])."[/COL]\n";
                                }
                                $tab .= "[/ROW]\n";
                            }
//                             foreach ( $_POST["cells"] as $row ) {
//                                 $tab .= "[ROW]\n";
//                                 foreach ( $row as $cell ) {
//                                     $tab .= "[COL]".trim($cell)."[/COL]\n";
//                                 }
//                                 $tab .= "[/ROW]\n";
//                             }
                            $tab .= "[/TAB]";
                            $to_insert = $tab;
                        }
                    } elseif ( $tag_marken[0] == "LIST" ) {
                        // trennen nach leerzeilen
                        $buffer = explode(chr(13).chr(10).chr(13).chr(10),$_POST["content"]);
                        $to_insert = implode("\n[*]",$buffer);
                        // verbotenen tags rausfiltern
                        $buffer = array();
                        foreach ( $allowed_tags as $value ) {
                            $buffer[] = "[/".strtoupper($value)."]";
                        }
                        $to_insert = $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"].
                                     tagremove($to_insert,False,$buffer).
                                     $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_end"];
echo "hallo<br>";
echo $to_insert;
echo "<pre>".print_r($tag_meat["LIST"],true)."</pre>";
echo "<pre>".print_r($tag_marken,true)."</pre>";
// die;
                    } else {
                        // verbotenen tags rausfiltern
                        foreach ( $allowed_tags as $value ) {
                            $buffer[] = "[/".strtoupper($value)."]";
                        }
                        $to_insert = $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"].
                                     tagremove($_POST["content"],False,$buffer).
                                     $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_end"];
                    }
                    $pre_content = substr($data["content"],0,$tag_meat[$tag_marken[0]][$tag_marken[1]]["start"]);
                    $post_content = substr($data["content"],$tag_meat[$tag_marken[0]][$tag_marken[1]]["end"]);

                    $content = $pre_content.
                               $to_insert.
                               $post_content;



//                     $tag_meat = cont_sections($data["content"]);
//                     $pre_content = substr($data["content"],0,$tag_meat[$tag_marken[0]][$tag_marken[1]]["start"]);
//                     $post_content = substr($data["content"],$tag_meat[$tag_marken[0]][$tag_marken[1]]["end"]);
//
//                     $content = $pre_content.
//                                $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_start"].
//                                $_POST["content"].
//                                $tag_meat[$tag_marken[0]][$tag_marken[1]]["tag_end"].
//                                $post_content;
                }

            } else {
                $content = $_POST["content"];
            }


            // html killer :)
            if ( $specialvars["denyhtml"] == -1 ) {
                $content = strip_tags($content);
            }


            // space killer
            if ( $specialvars["denyspace"] == -1 ) {
                $pattern = "  +";
                while ( preg_match("/".$pattern."/", $content, $tag) ) {
                    $content = str_replace($tag[0]," ",$content);
                }
            }

            // evtl. zusaetzliche datensatz aendern
            if ( $ausgaben["form_error"] == ""  ) {

                // funktions bereich fuer erweiterungen
                // ***

                ### put your code here ###

                if ( $error ) $ausgaben["form_error"] .= $db -> error("#(error_result)<br />");
                // +++
                // funktions bereich fuer erweiterungen
            }

            // datensatz aendern
            if ( $ausgaben["form_error"] == ""  ) {

                if ( $content_exist == 1 && !in_array($environment["parameter"][3], $cfg["wizard"]["archive"]) ) {
                    if ( $environment["parameter"][4] == "" && $_POST["content"] == "" ) {
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
                                       html = '".$_POST["html"]."',
                                       content = '".$content."',
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
                                         '".++$data["version"]."',
                                         '".$_SESSION["ebene"]."',
                                         '".$_SESSION["kategorie"]."',
                                         '".$specialvars["crc32"]."',
                                         '".$_POST["html"]."',
                                         '".$content."',
                                         '".date("Y-m-d H:i:s")."',
                                         '".$_SESSION["surname"]."',
                                         '".$_SESSION["forename"]."',
                                         '".$_SESSION["email"]."',
                                         '".$_SESSION["alias"]."')";
                }


//                 $kick = array( "PHPSESSID", "form_referer", "send" );
//                 foreach($_POST as $name => $value) {
//                     if ( !in_array($name,$kick) && !strstr($name, ")" ) ) {
//                         if ( $sqla != "" ) $sqla .= ", ";
//                         $sqla .= $name."='".$value."'";
//                     }
//                 }

                // Sql um spezielle Felder erweitern
                #$ldate = $_POST["ldate"];
                #$ldate = substr($ldate,6,4)."-".substr($ldate,3,2)."-".substr($ldate,0,2)." ".substr($ldate,11,9);
                #$sqla .= ", ldate='".$ldate."'";

//                 $sql = "update ".$cfg["wizard"]["db"]["leer"]["entries"]." SET ".$sqla." WHERE ".$cfg["wizard"]["db"]["leer"]["key"]."='".$environment["parameter"][1]."'";
                if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
echo "\$sql: $sql<br>";
                $result  = $db -> query($sql);
                #if ( !$result ) die($db -> error("DB ERROR: "));
                if ( !$result ) $ausgaben["form_error"] .= $db -> error("#(error_result)<br />");
                if ( $header == "" ) $header = $cfg["wizard"]["basis"]."/list.html";
            }

            // wenn es keine fehlermeldungen gab, die uri $header laden
            if ( $ausgaben["form_error"] == "" ) {
                if ( $_POST["add"] || $_POST["sel"] || $_POST["upload"] > 0 ) {

                    $_SESSION["cms_last_edit"] = str_replace(",verify", "", $pathvars["requested"]);

                    $_SESSION["cms_last_referer"] = $ausgaben["form_referer"];
                    $_SESSION["cms_last_ebene"] = $_SESSION["ebene"];
                    $_SESSION["cms_last_kategorie"] = $_SESSION["kategorie"];

                    if ( $_POST["upload"] > 0 ) {
                        header("Location: ".$pathvars["virtual"]."/admin/fileed/upload.html?anzahl=".$_POST["upload"]);
                    } elseif ( $_POST["sel"] != "" ) {
                        header("Location: ".$pathvars["virtual"]."/admin/fileed/compilation.html");
                    } else {
                        header("Location: ".$pathvars["virtual"]."/admin/fileed/list.html");
                    }

                } elseif ( $_POST["col_resize"] != "" ) {
                    header("Location: ".$ausgaben["form_aktion"]."");
                } else {
                    $pattern = ",v[0-9]*\.html$";
                    $ausgaben["form_referer"] = preg_replace("/".$pattern."/",".html",$ausgaben["form_referer"] );
//                     header("Location: ".$ausgaben["form_referer"]."");
                    $header = $cfg["wizard"]["basis"]."/show,".$environment["parameter"][1].",".
                                                        $environment["parameter"][2].",".
                                                        $environment["parameter"][3].",".
                                                        ",".
                                                        $environment["parameter"][5].".html";
                    header("Location: ".$header);
                }

#                header("Location: ".$header);
            }
        }
    } else {
//         header("Location: ".$pathvars["virtual"]."/");
    }



    $db -> selectDb(DATABASE,FALSE);



////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>