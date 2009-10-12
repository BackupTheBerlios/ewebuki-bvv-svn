<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $script["name"] = "$Id: leer.inc.php 1355 2008-05-29 12:38:53Z buffy1860 $";
  $Script["desc"] = "short description";
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

    if ( $debugging["html_enable"] ) $debugging["ausgabe"] .= "[ ** ".$script["name"]." ** ]".$debugging["char"];

    if ( $cfg["leer"]["right"] == "" || $rechte[$cfg["leer"]["right"]] == -1 ) {

        ////////////////////////////////////////////////////////////////////
        // achtung: bei globalen funktionen, variablen nicht zuruecksetzen!
        // z.B. $ausgaben["form_error"],$ausgaben["inaccessible"]
        ////////////////////////////////////////////////////////////////////

        // page basics
        // ***

        // warnung ausgeben
        if ( get_cfg_var('register_globals') == 1 ) $debugging["ausgabe"] .= "Warnung: register_globals in der php.ini steht auf on, evtl werden interne Variablen ueberschrieben!".$debugging["char"];

        // path fuer die schaltflaechen anpassen
        if ( $cfg["leer"]["iconpath"] == "" ) $cfg["leer"]["iconpath"] = "/images/default/";

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

        if ( is_numeric($environment["parameter"][1]) && $environment["parameter"][1] != "" ) {
            setlocale(LC_ALL, "de_DE");
            $sql = "SELECT *
                      FROM ".$cfg["sapos"]["db"]["stationen"]["entries"]."
                     WHERE punktkennung_1=".$environment["parameter"][1];
            $result = $db -> query($sql);
            if ( $db -> num_rows($result) > 0 ) {
                $hidedata["station"]["link_back"] = $cfg["sapos"]["basis"].".html";
                $data = $db -> fetch_array($result,1);
                foreach ( $data as $key=>$value ) {
                    if ( $key == "punktkennung_1" ) {
                        $value = sprintf("%04d",$value);
                    } elseif ( $key == "punktkennung_2" ) {
                        $value = sprintf("%03d",$value);
                    } elseif ( preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/U",$value,$match) ) {
                        $value = strftime("%d.%m.%Y",mktime(0,0,0,$match[2],$match[3],$match[1] ));
                    } elseif ( $key == "etrs89_b" || $key == "etrs89_l" ) {
                        $koord  = $value;
                        $value  = floor($koord)."&deg;";
                        $koord  = ($koord-floor($koord))*60;
                        $value .= floor($koord)."'";
                        $koord  = ($koord-floor($koord))*60;
                        $value .= sprintf("%0.5f",$koord)."''";
                        if ( $key == "etrs89_b" ) {
                            $value .= " N";
                        } else {
                            $value .= " E";
                        }
                    } elseif ( $key == "etrs89_h" ) {
                        $value = sprintf("%0.3f",$value)." m";
                    } elseif ( strstr($key,"utm3") ) {
                        $value = sprintf("%0.3f",$value);
                    } elseif ( $key == "dh_vermark_arp" || strstr($key,"exz_") ) {
                        $value = sprintf("%0.3f",$value/1000)." m";
                    } elseif ( $key == "ant_radome" ) {
                        if ( $value == "Y" ) {
                            $value = "ja";
                        } else {
                            $value = "nein";
                        }
                    }
                    $hidedata["station"][$key] = $value;
                }
            }
        } else {
            $hidedata["uebersicht"] = array();

            // koordinaten-ausmasse berechnen
            $diff_h = $cfg["sapos"]["coord_limits"]["north"] - $cfg["sapos"]["coord_limits"]["south"];
            $diff_b = $cfg["sapos"]["coord_limits"]["east"]  - $cfg["sapos"]["coord_limits"]["west"];

            // bildhoehe aus max. bildbreite bestimmen
            $pic_height = ceil($cfg["sapos"]["pic_width"]/$diff_b*$diff_h)*$cfg["sapos"]["pic_scale"];

            // hintergrundbild holen
            $img_bg = imagecreatefrompng($pathvars["fileroot"]."images/html/sapos_bg.png");
            $src_width = imagesx($img_bg);
            $src_height = imagesy($img_bg);

            // neues bild erzeugen
            $img_dst = imagecreatetruecolor($cfg["sapos"]["pic_width"],$pic_height);
                    imageantialias($img_dst,true);
                    imagealphablending($img_dst, False);
                    imagesavealpha($img_dst, True);

            // hintergrundbild einfuegen
            imagecopyresampled($img_dst, $img_bg, 0, 0, 0, 0, $cfg["sapos"]["pic_width"], $pic_height, $src_width, $src_height);

            // farben festlegen
            $weiss = ImageColorAllocate ($img_dst, 255, 255, 255);
            $color_kreis = ImageColorAllocate ($img_dst, 40, 90, 147);
            $color_font = ImageColorAllocate ($img_dst, 40, 90, 147);

            $sql = "SELECT *
                      FROM ".$cfg["sapos"]["db"]["stationen"]["entries"]."
                  ORDER BY ".$cfg["sapos"]["db"]["stationen"]["order"];
            $result = $db -> query($sql);
            while ( $data = $db -> fetch_array($result,1) ) {

                // bildkoordinate der station berechnen
                $pic_x = ( $data["utm32_e"] - $cfg["sapos"]["coord_limits"]["west"] - 32000000 )/$diff_b*$cfg["sapos"]["pic_width"];
                $pic_y = ( $cfg["sapos"]["coord_limits"]["north"] - $data["utm32_n"] )/$diff_h*$pic_height;

                // station ins bild einfuegen
                imagefilledellipse  ( $img_dst  , $pic_x  , $pic_y  , 10  , 10  , $color_kreis  );
                imagefilledellipse  ( $img_dst  , $pic_x  , $pic_y  , 7  , 7  , $weiss  );
                imagettftext  ( $img_dst  , 6  , 0  , $pic_x+7  , $pic_y-2  , $color_font  , "../modules/customer/fonts/VeraMono.ttf"  , sprintf("%04d",$data["punktkennung_1"])  );
                imagettftext  ( $img_dst  , 6  , 0  , $pic_x+7  , $pic_y+10  , $color_font  , "../modules/customer/fonts/VeraMono.ttf"  , $data["stationsbezeichnung"]  );

                // dataloop-ausgabe
                $dataloop["stationen"][] = array(
                           "id" => $data["refst_obid"],
                      "kennung" => sprintf("%04d",$data["punktkennung_1"]),
                          "ort" => $data["stationsbezeichnung"],
                     "standort" => $data["standort"],
                        "pic_x" => floor($pic_x),
                        "pic_y" => floor($pic_y),
                         "link" => $cfg["sapos"]["basis"].",".$data["punktkennung_1"].".html",
                );
            }

            // bild ausgeben
            imagepng  ( $img_dst  , $pathvars["fileroot"]."images/html/sapos.png" );
        }


        // +++
        // funktions bereich


        // page basics
        // ***

        // navigation erstellen
        $ausgaben["add"] = $cfg["leer"]["basis"]."/add,".$environment["parameter"][1].",verify.html";
        #$mapping["navi"] = "leer";

        // hidden values
        #$ausgaben["form_hidden"] .= "";
        // was anzeigen
        $mapping["main"] = "saposref";
        #$mapping["navi"] = "leer";

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
