<?php
    function my_obstart() {
        $encode = getenv("HTTP_ACCEPT_ENCODING");
            if(ereg("gzip",$encode)) {
            ob_start("ob_gzhandler");
        } else {
            ob_start();
        }
    }
    my_obstart(); // F�hrt die Funktion nun aus

    $path = dirname(dirname(__FILE__))."/";
    $path = str_replace("/modules/","",$path);
    $path = str_replace(".ext","",$path);

    $file = $path.$_SERVER["REQUEST_URI"];
    $ext_array = array("css","js");

    $extension = substr(basename($file),(strrpos(basename($file),".")+1));


    if ( in_array($extension,$ext_array) ) {
        header("Content-Type: text/".$extension);
        readfile($file);
    } else {
        exit;
    }
?>
