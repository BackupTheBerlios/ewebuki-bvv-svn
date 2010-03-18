<?php

// Include this function on your pages
if ( !function_exists("print_gzipped_page") ) {
    function print_gzipped_page() {

        $HTTP_ACCEPT_ENCODING = getenv("HTTP_ACCEPT_ENCODING");
        if( headers_sent() ){
            $encoding = false;
        }elseif( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ){
            $encoding = 'x-gzip';
        }elseif( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ){
            $encoding = 'gzip';
        }else{
            $encoding = false;
        }

        if( $encoding ){
            $contents = ob_get_contents();
            ob_end_clean();
            header('Content-Encoding: '.$encoding);
            header("ETag: ".md5($contents)); // ETag im Header senden
            header("Expires: ".date("r",mktime(0,0,0,date("n"),date("j")+365)));
            print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
            $size = strlen($contents);
            $contents = gzcompress($contents, 9);
            $contents = substr($contents, 0, $size);
            print($contents);
//             exit();
        }else{
            ob_end_flush();
//             exit();
        }
    }
}

// At the beginning of each page call these two functions
ob_start();
ob_implicit_flush(0);

// Then do everything you want to do on the page
$path = dirname(dirname(__FILE__))."/";
$path = str_replace("/modules/","",$path);
$path = str_replace(".ext","",$path);

$file = $path.$_SERVER["REQUEST_URI"];
$ext_array = array("css","js");

$extension = substr(basename($file),(strrpos(basename($file),".")+1));

if ( in_array($extension,$ext_array) ) {
    header("Content-Type: text/".$extension);
    echo readfile($file);
} else {
    exit;
}

// Call this function to output everything as gzipped content.
print_gzipped_page();















?>
