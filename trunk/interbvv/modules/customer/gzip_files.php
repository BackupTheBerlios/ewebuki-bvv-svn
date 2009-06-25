<?php
    function my_obstart() {
        $encode = getenv("HTTP_ACCEPT_ENCODING");
            if(ereg("gzip",$encode)) {
            ob_start("ob_gzhandler");
        } else {
            ob_start();
        }
    }
    my_obstart(); // Führt die Funktion nun aus

    $pathvars["fileroot"] = dirname(dirname(__FILE__))."/";

    $file = "/srv/www/htdocs/internet/interbvv".$_SERVER["REQUEST_URI"];
    $ext_array = array("css","js");

    $extension = substr(basename($file),(strrpos(basename($file),".")+1));


    if ( in_array($extension,$ext_array) ) {
        header("Content-Type: text/".$extension);
        readfile($file);
    } else {
        exit;
    }
?>
