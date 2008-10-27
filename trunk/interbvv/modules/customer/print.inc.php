<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: print.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
  $Script["desc"] = "print";
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

        $sql = "SELECT content FROM site_text WHERE tname = '".eCRC(dirname($environment["ebene"])).".".basename($environment["ebene"])."' AND status = '1'";
        include $pathvars["moduleroot"]."wizard/wizard.cfg.php";
        $cfg["wizard"]["function"]["print"][] = "makece";
        include $pathvars["moduleroot"]."wizard/wizard-functions.inc.php";

    if ( ( $cfg["print"]["path"] == "" || strstr($environment["ebene"],$cfg["print"]["path"]) ) && class_exists('PDFlib') ) {

        $sql = "SELECT content FROM site_text WHERE tname = '".eCRC(dirname($environment["ebene"])).".".basename($environment["ebene"])."' AND status = '1'";

        if ( $debugging["sql_enable"] ) $debugging["ausgabe"] .= "sql: ".$sql.$debugging["char"];
        $result = $db -> query($sql);
        $data = $db -> fetch_array($result,1);

        $test = content_split_all($data["content"]);

        $mutate["H"] = array("H");
        #$mutate["B"] = array("");
        $mutate["P"] = array("P");

        $pdf = new PDFlib();
        $pdf -> begin_document("", "lang=de tagged=true");

        $pdf->set_info("Creator", "hello.php");
        $pdf->set_info("Author", "STI");
        $pdf->set_info("Title", "Hello world (PHP)!");

        $pdf -> set_parameter("autospace","true");

        $doc=$pdf->begin_item("Document","Title=Buffy");

        $pdf->begin_page_ext(0, 0, "width=a4.width height=a4.height");
        $font = $pdf->load_font("Helvetica", "unicode", "");
        $pdf-> setfont($font,10);
        $fontname = $pdf->get_parameter( "fontname", 0);

        $count = 0;
        $i = 0;
        foreach ( $tag_sort as $key => $value ) {
            if ( array_key_exists($value["para"][0],$mutate) ) {
                if ( $value["start"] > $count ) {
                    $out[$i] = substr($data["content"],$value["start"],$value["end"]-$value["start"]);
                    $count = $value["end"];
                    $buffy = 0;
                } else {
                    if ( $buffy == -1 ) {
                        $out[$i-1] = substr($data["content"],$ende,$value["start"]-$ende);
                    } else {
                        $out[$i-1] = substr($data["content"],$anfang,$value["start"]-$anfang);
                    }
                    $out[$i] = substr($data["content"],$value["start"],$value["end"]-$value["start"]);
                    $i++;
                    $out[$i] = substr($data["content"],$value["end"],$count-$value["end"]);
                    $buffy = -1;
                }
                $i++;
                $anfang = $value["start"];
                $ende = $value["end"];
            }
        }

        $preg_begin = "\[(.+)";
        $preg_end = "\[\/(.+)";
        $count_tag = 0;
        $stand = "";

        foreach ( $out as $inhalt ) {
            $count_tag++;
            $endtag = 0;
            if ( preg_match_all("/".$preg_begin."/Us",$inhalt,$match)  ) {
                if ( preg_match_all("/\[\/".$match[1][0].".*\]/Us",$inhalt,$match_end) ) {
                    $endtag = -1;
                }
                $item = $match[1][0].$count_tag;
                $$item = $pdf -> begin_item($match[1][0],"");
                $inhalt = preg_replace("/\[[\/A-Za-z0-9=]*\]/","",$inhalt); 
                #$inhalt = preg_replace("/\[.*\]/","",$inhalt); 
                $textflow = $pdf-> create_textflow($inhalt,"fontname=".$fontname." fontsize=10 textformat=utf8 encoding=unicode ");
                if ( $stand == "" ) {
                    $pdf->fit_textflow( $textflow, 50, 120, 550, 820, "");

                } else {
                    $pdf->fit_textflow( $textflow, 50, 120, 550, $stand, "");
                }
                $stand = $pdf->info_textflow ( $textflow,"textendy")-20;
            }
            if ( $endtag == -1 ) {
                $pdf -> end_item($$match[1][0].$count_tag);
            }
        }

        $pdf-> end_item($doc);

        $pdf->end_page_ext("");

        $pdf->end_document("");
        $buf = $pdf->get_buffer();
        $len = strlen($buf);

        header("Content-type: application/pdf");
        header("Content-Length: $len");
        header("Content-Disposition: inline; filename=hello.pdf");
        echo $buf;

    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
