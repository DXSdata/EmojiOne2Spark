<?php
namespace Emojione;

function getFilename($shortname)
{
    // include the PHP library (if not autoloaded)
    require('emojione-master/lib/php/autoload.php');
            
    $client = new Client(new Ruleset());

    // ###############################################
    // Optional:
    // default is PNG but you may also use SVG
    $client->imageType = 'png'; // or png (default)      
    
    $client->ascii = true;
                             
    $html = $client -> shortnameToImage($shortname);
                      
    //strip all tags to get filename      
    $doc = new \DOMDocument();
    $doc->loadHTML($html);
    $xpath = new \DOMXPath($doc);
    $src = $xpath->evaluate("string(//img/@src)"); # "/images/image.jpg"        
    $file = basename($src);
    $file = substr($file, 0, strpos($file, "?"));
    
    return $file;     
}



?>