<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// "$Id: contented-functions.inc.php 1252 2008-02-25 11:46:56Z krompi $";
// "funktion loader";
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

    /* um funktionen z.b. in der kategorie add zu laden, leer.cfg.php wie folgt aendern
    /*
    /*    "function" => array(
    /*                 "add" => array( "function1_name", "function2_name"),
    */

//     if ( in_array("makece", $cfg["wizard"]["function"][$environment["kategorie"]]) ) {
//          function function_name(  $var1, $var2 = "") {
//             ### put your code here ###
//          }
//     }

    // content editor erstellen
    if ( in_array("makece", $cfg["wizard"]["function"][$environment["kategorie"]]) ) {

        function makece($ce_formname, $ce_name, $ce_inhalt,$allowed_tags=array()) {
            global $debugging, $environment, $db, $cfg, $pathvars, $ausgaben, $specialvars, $defaults;

            // label fuer neue buttons fuellen
            $sql = "SELECT label, content
                      FROM ". SITETEXT ."
                     WHERE tname='-141347382.modify'
                       AND lang='".$environment["language"]."'";
            if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
            $result  = $db -> query($sql);
            while ( $data = $db -> fetch_array($result) ) {
                $label[$data["label"]] = $data["content"];
            }

            $cms_old_mode = False;
            $tag_marken = explode(":",$environment["parameter"][4]);
            foreach( $cfg["wizard"]["tags"] as $key => $value ) {

                // feststellen, ob der tag erlaubt ist
                if ( count($allowed_tags) > 0 && !in_array($key,$allowed_tags) ) {
                    continue;
                }

//                 // js code erstellen
//                 if ( $ausgaben["js"] == "" ) {
//                     $c = "if";
//                 } else {
//                     $c = "else if";
//                 }
//
                if ( $value[1] != "" ) {
                    $k = " [KEY-".$value[1]."]";
                } else {
                    $k = "";
                }

                if ( $value[2] == False ) {
                    $s = "' + selText + '";
                } else {
                    $s = "";
                }

                if ( $value[3] != "" ) {
                    $l = $value[3];
                } else {
                    $l = "]";
                }

                if ( $value[6] == "" ) {
                    $keyX = $key;
                } else {
                    $keyX = $value[6];
                }

//              else if (st=='b')
//              st='[B]' + selText + '[\/B]';
//
//                 $ausgaben["js"] .= "    ".$c." (st=='".$key."')\n";
//                 $ausgaben["js"] .= "        st='[".strtoupper($key).$l.$s.$value[4]."[\/".strtoupper($key)."]'\n";



                if ( $value[0] == "" && $cfg["wizard"]["debug"] == True ) $value[0] = "T";

                // position (T=top, B=bottom), access key, no select, links, rechts, disable
                //                                                     ebButtons[ebButtons.length] = new ebButton(
                // id           used to name the toolbar button           'eb_h1'
                // key          label on button                          ,'H1'
                // tit          button title                             ,'Überschrift [Alt-1]'
                // position     position (top, bot)                      ,'T'
                // access       access key                               ,'1'
                // noSelect                                              ,'-1'
                // tagStart     open tag                                 ,'[H1]'
                // tagMid       mid tag                                  ,''
                // tagEnd       close tag                                ,'[/H1]'
                //                                                     );

                $ausgaben["njs"] .= "ebButtons[ebButtons.length] = new ebButton(\n";
                $ausgaben["njs"] .= "'eb_".$key."'
                                    ,'".strtoupper($key)."'
                                    ,'".$label[$key].$k."'
                                    ,'".$value[0]."'
                                    ,'".$value[1]."'
                                    ,'noSelect'
                                    ,'[".strtoupper($keyX).$l."'
                                    ,'".$value[4]."'
                                    ,'".$value[5]."[/".strtoupper($keyX)."]'\n";
                $ausgaben["njs"] .= ");\n";



//                 // buttons bauen
//                 if ( $value[0] == "T" ) {
//                     if ( $cms_old_mode == True ) {
//                         #$ausgaben["ce_button"] .= "<a href=\"#\" onclick=\"INSst('".$key."','".$ce_formname."','".$ce_name."')\" onMouseOver=\"status='".$value[3]."';return true;\" onMouseOut=\"status='';return true;\"><img src=\"".$defaults["cms-tag"]["path"]."cms-tag-".$key.".png\" alt=\"".$value[3]."\" title=\"".$value[3]."\" width=\"23\" height=\"22\" border=\"0\" /></a>\n ";
//                         $ausgaben["ce_button"] .= "<a href=\"#\" onclick=\"INSst('".$key."','".$ce_formname."','".$ce_name."')\" onMouseOver=\"status='#(".$key.")';return true;\" onMouseOut=\"status='';return true;\"><img src=\"".$defaults["cms-tag"]["path"]."cms-tag-".$key.".png\" alt=\"#(".$key.")\" title=\"#(".$key.")\" width=\"23\" height=\"22\" border=\"0\" /></a>\n ";
//                     } else {
//                         $ausgaben["ce_button"] .= "<a class=\"buttag\" href=\"#\" onclick=\"INSst('".$key."','".$ce_formname."','".$ce_name."')\" alt=\"#(".$key.")\" title=\"#(".$key.")\" onMouseOver=\"status='#(".$key.")';return true;\" onMouseOut=\"status='';return true;\">".strtoupper($key)."</a>\n ";
//                     }
//                 } elseif ( $value[0] == "B" ) {
//                     $ausgaben["ce_bottom_button"] .= "<a class=\"buttag\" href=\"#\" onclick=\"INSst('".$key."','".$ce_formname."','".$ce_name."')\" alt=\"#(".$key.")\" title=\"#(".$key.")\" onMouseOver=\"status='#(".$key.")';return true;\" onMouseOut=\"status='';return true;\">".strtoupper($key)."</a>\n ";
//                 }

//                 // dropdown bauen
//                 if ( $value[5] == "" ) {
//                     $ausgaben["ce_dropdown"] .= "<option value=\"".$key."\">".strtoupper($key)." #(".$key.")</option>\n";
//                 }
//                 #ce_anker
            }

#echo "<pre>".$ausgaben["njs"]."</pre>";

//             $ausgaben["ce_dropdown"] .= "</select>";

            // script in seite parsen
            #echo "<pre>".$ausgaben["js"]."</pre>";
            $ausgaben["ce_script"] = parser($cfg["wizard"]["tagjs"],"");

//             if ( $cms_old_mode == True ) {
//                 $ausgaben["ce_button"] .= "<input name=\"add[]\" type=\"image\" id=\"image\" value=\"add\" src=\"".$defaults["cms-tag"]["path"]."cms-tag-imgb.png\" title=\"#(add)\" width=\"23\" height=\"22\">";
//             } else {
//                 $ausgaben["ce_button"] .= "<input type=\"submit\" name=\"add[]\" value=\"FILE\" title=\"#(add)\" class=\"butoth\">";
//             }

//             $ausgaben["ce_upload"] .= "<select style=\"width:95px;font-family:Helvetica, Verdana, Arial, sans-serif;font-size:12px;\" name=\"upload\" onChange=\"submit()\">";
//             $ausgaben["ce_upload"] .= "<option value=\"\">#(upload)</option>";
//             $ausgaben["ce_upload"] .= "<option value=\"1\">1 #(file)</option>";
//             $ausgaben["ce_upload"] .= "<option value=\"2\">2 #(files)</option>";
//             $ausgaben["ce_upload"] .= "<option value=\"3\">3 #(files)</option>";
//             $ausgaben["ce_upload"] .= "<option value=\"4\">4 #(files)</option>";
//             $ausgaben["ce_upload"] .= "<option value=\"5\">5 #(files)</option>";
//             $ausgaben["ce_upload"] .= "</select>";

            return $tn;
        }

        function cont_sections($content) {
            $preg_sections = array(
                    "H"    => "(\[H[0-9]{1}\])(.*)(\[\/H[0-9]{1}\])",        // ueberschriften
                    "P"    => "(\[P.*\])(.*)(\[\/P\])",                      // absaetze
                    "LINK" => "(\[LINK.*\])(.*)(\[\/LINK\])",                // links
                    "IMG"  => "(\[IMG.*\])(.*)(\[\/IMG\])",                  // bilder
                    "SEL"  => "(\[SEL.*\])(.*)(\[\/SEL\])",                  // gruppierungen
                    "TAB"  => "(\[TAB.*\])(.*)(\[\/TAB\])",                  // tabellen
                    "LIST" => "(\[LIST.*\])(.*)(\[\/LIST\])",                // listen
            );
            $tag_meat = array();
            foreach ( $preg_sections as $tag=>$preg ) {
                preg_match_all("/".$preg."/Us",$content,$match,PREG_OFFSET_CAPTURE);
                foreach ( $match[0] as $key=>$value ) {
                    $tag_meat[$tag][] = array(
                                "tag_start" => $match[1][$key][0],
                                "tag_end"   => $match[3][$key][0],
                                "meat"      => $match[2][$key][0],
                                "complete"  => $match[0][$key][0],
                                "start"     => $match[0][$key][1],
                                "end"       => $match[0][$key][1] + strlen($match[0][$key][0]),
                    );

                    $tag_meat["order"][$match[0][$key][1]] = $tag;
                }
            }
            if (is_array($tag_meat["order"])) ksort($tag_meat["order"]);
            return $tag_meat;
        }

        function seperate_content($content) {

            // tags in verschiedenen ebenen
            $array = array(
                                "[H",
                                "[P",
                                "[LIST",
                                "[SEL",
                                "[TAB",
                                "[DIV",
            );
            // suchmuster bauen und open- und close-tags finden
            $preg = array();
            foreach ( $array as $tag ) {
                $end_tag = str_replace("[","[/",$tag);
                $split_tags["open"][] = $tag;
                $split_tags["close"][$tag] = $end_tag;
                $preg[] = str_replace(array("[","/"),array("\[","\/"),$tag);
                $preg[] = str_replace(array("[","/"),array("\[","\/"),$end_tag);
            }
            $separate = preg_split("/(".implode("|",$preg).")|(<!--edit_begin-->)|(<!--edit_end-->)/",$content,-1,PREG_SPLIT_DELIM_CAPTURE);

            $end = "--"; $i = 0;
            $allcontent = array();
            foreach ( $separate as $line ) {
                if ( trim($line) == "" ) continue;
                if ( $close == 1 ) {
                    $buffer = explode("]",$line,2);
                    $allcontent[$i] .= $buffer[0]."]";
    //                 $i++;
                    $close = 0;
                    $line = $buffer[1];
                    if ( trim($line) == "" ) continue;
                }
                if ( in_array($line,$split_tags["open"]) && $end == "--" ) {
                    $i++;
                    $end = $split_tags["close"][$line];
                } elseif ( $line == "<!--edit_begin-->" && $end == "--" ) {
                    $i++;
                    $end = "<!--edit_end-->";
                }
                $allcontent[$i] .= trim($line);
                if ( $line == $end ) {
                    if ( $end != "<!--edit_end-->" ) $close = 1;
                    $end = "--";
                }
            }

            return array_merge($allcontent);
        }

    }

    ### platz fuer weitere funktionen ###

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
