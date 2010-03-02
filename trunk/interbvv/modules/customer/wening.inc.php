<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1678 2009-12-07 14:03:04Z chaot $";
  $Script["desc"] = "short description";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    eWeBuKi - a easy website building kit
    Copyright (C)2001-2010 Werner Ammon ( wa<at>chaos.de )

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

    86343 K�nigsbrunn

    URL: http://www.chaos.de
*/
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];
echo "<pre>";

    $path_html = "/misc/platte_1/sicherung-internetauftritt/altes_internet/bvv_web_090812/produkte/wening/popup_wening";
    $path_pics = "/misc/platte_1/sicherung-internetauftritt/altes_internet/bvv_web_090812/produkte/wening/gfx_wening";
    $path_html = "/home/krom/Desktop/wening/popup_wening";
    $path_pics = "/home/krom/Desktop/wening/gfx_wening";



    if ( $handle = opendir($path_html) ) {

        echo "Directory handle: $handle\n";

            $environment["kategorie"] = "add";
            include $pathvars["moduleroot"]."admin/fileed2.cfg.php";
            include $pathvars["moduleroot"]."admin/fileed2-functions.inc.php";

        $i = 0; $array_formate = array(); $array_zeit = array(); $missing_pic = array();
        while (false !== ($file = readdir($handle))) {
            if ( !strstr($file, "html") ) continue;
//             if ( $i > 1 ) break;
            $zeit_start = array_sum(explode(' ', microtime()));

echo "<b>".$file."</b>\n";

            // gibt es ein bild dazu
            $pic = str_replace(".html",".jpg",$file);
            if ( file_exists($path_pics."/".$pic) ) {
                echo "  ".str_pad($pic,15,".").": vorhanden\n";

                // informationen rausgrepen
                // --------------------------------------
                $buffer = file_get_contents($path_html."/".$file);
                $buffer = utf8_encode($buffer);
                // titel
                preg_match_all("/<td colspan=\"3\" class=\"subhead\".*><img.*>(.*)<\/td>/Uis", $buffer, $match);
                $titel = trim($match[1][1]);
                $titel = html_entity_decode($titel);
                $titel = utf8_encode($titel);
                echo "  ".str_pad("Titel",15,".").": ".$titel."\n";
                // beschreibung
                preg_match_all("/Bezeichnung:.*<img.*>(.*)<\/td>/Uis", $buffer, $match);
                $desc = trim($match[1][0]);
                $desc = str_replace(" &nbsp;&nbsp;&nbsp;",", ",$desc);
                echo "  ".str_pad("Beschreibung",15,".").": ".$desc."\n";
                // seriennummer
                preg_match_all("/Nr. ([A-Z]{1} [0-9]{3}).*Planquadrat ([A-Z]{1} [0-9]{3})/Uis", $desc, $match);
                $serial = trim($match[1][0])."-".trim($match[2][0]);
                $serial = str_replace(" ","",$serial);
                echo "  ".str_pad("Seriennr",15,".").": ".$serial."\n";
                // groesse
                preg_match_all("/Origina.*<img.*>(.*)<\/td>/Uis", $buffer, $match);
                $size = trim($match[1][0]);
                echo "  ".str_pad("Groesse",15,".").": ".$size."\n";
                // format
                preg_match_all("/Ausf.*<img.*>(.*)<\/td>/Uis", $buffer, $match);
                $format = trim($match[1][0]);
                echo "  ".str_pad("Format",15,".").": ".$format."\n";
                $array_formate[$format]++;
                // preis
                if ( strstr($format,"Normalblatt") ) {
                    $preis = "12,80";
                } elseif ( strstr($format,"Doppelblatt") ) {
                    $preis = "20,50";
                } elseif ( strstr($format,"Dreifachblatt") ) {
                    $preis = "30,70";
                } else {
                    $preis = "";
                }
                echo "  ".str_pad("Preis",15,".").": ".$preis."\n";

                // site_file: sql bauen
                $sql = "SELECT * FROM site_file WHERE ffname='".$pic."' AND funder='Wening-Stich ".$serial."'";
                echo "  ".str_pad("Ueberpruefung",15,".").": ".$sql."\n";
                $result  = $db -> query($sql);
                echo "  ".str_pad("Num_Rows",15,".").": ".$db->num_rows($result)."\n";
                if ( $db->num_rows($result) == 0 ) {
                    $sql = "INSERT INTO site_file (ffname,
                                                   fdesc,
                                                   funder,
                                                   fhit,
                                                   ffart,
                                                   fuid,
                                                   fdid)
                                           VALUES ('".$pic."',
                                                   'Wening-Stiche:\n".$desc."\n".$size."\n".$format."',
                                                   'Wening-Stich ".$serial."',
                                                   'Wening-Stiche:\n".$desc."\n".$size."\n".$format."',
                                                   'jpg',
                                                   '1',
                                                   '')";
//                     echo "  ".str_pad("Einfuegen",15,".").": ".$sql."\n";

                    if ( $result  = $db -> query($sql) ) {
                        echo "  ".str_pad("Einfuegen",15,".").": erfolgreich\n";
                        $pic_source = $path_pics."/".$pic;
                        echo "  ".str_pad("Quelle",15,".").": ".$pic_source."\n";

                        $file_id = $db->lastid();
                        echo "  ".str_pad("last-id",15,".").": ".$file_id."\n";
                        arrange( $file_id, $path_pics."/".$pic, $pic, 0 );
                    }


                }
                // db_produkte: sql bauen
                $sql = "SELECT * FROM db_produkte WHERE seriennr='".$serial."' AND typ='wening' AND titel='".$titel."'";
                echo "  ".str_pad("Ueberpruefung",15,".").": ".$sql."\n";
                $result  = $db -> query($sql);
                echo "  ".str_pad("Num_Rows",15,".").": ".$db->num_rows($result)."\n";
                if ( $db->num_rows($result) == 0 ) {
                    $sql = "INSERT INTO db_produkte (seriennr,
                                                     typ,
                                                     titel,
                                                     beschreibung,
                                                     preis,
                                                     changed,
                                                     created,
                                                     pics)
                                             VALUES ('".$serial."',
                                                     'wening',
                                                     '".$titel."',
                                                     '".$desc."\n".$size."\n".$format."',
                                                     '".$preis."',
                                                     '".date("Y-m-d")."',
                                                     '".date("Y-m-d")."',
                                                     '".$file_id."')";
//                     echo "  ".str_pad("Einfuegen",15,".").": ".$sql."\n";
//                     $result  = $db -> query($sql);
                    if ( $result  = $db -> query($sql) ) {
                        echo "  ".str_pad("Einfuegen",15,".").": erfolgreich\n";
                    }
                }

            } else {
                echo "  ".str_pad($pic,15,".").": <b>fehlt</b>\n";
                $missing_pic[$file] = $pic;
            }

            $zeit = array_sum(explode(' ', microtime())) - $zeit_start;
            $array_zeit[] = $zeit;
            echo "  ".str_pad("ZEIT",15,".").": ".$zeit." Sekunden\n";

            $i++;

        }


echo "<hr />";
// asort($missing_pic);
// echo print_r($missing_pic,true);
// echo print_r($array_formate,true);
// echo "$i\n";

        echo "  ".str_pad("Durchlaeufe",20,".").": ".$i."\n";
        echo "  ".str_pad("Gesamtzeit",20,".").": ".( array_sum($array_zeit) )." Sekunden\n";
        echo "  ".str_pad("Durchschnittszeit",20,".").": ".( array_sum($array_zeit)/count($array_zeit) )." Sekunden\n";
        closedir($handle);
    }

echo "</pre>";
exit;
    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ++ ".$script["name"]." ++ ]".$debugging["char"];

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
