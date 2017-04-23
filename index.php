<?php

/**
* http://www.dxsdata.com
* 2017-04 ds
* This script generates a modern IconSet from EmojiOne.com for Openfire messenger Spark.
* Put the generated zip file under %appdata%\Spark\xtra\emoticons and choose DXSdata under Settings -> Appearance. 
* 
*/

$iconsetName = 'DXSdata.AdiumEmoticonSet';
$zipfilename = 'DXSdata.AdiumEmoticonSet.zip';
$sourceJsonFile = 'emojione-master/emoji.json';
$categoriesJsonFile = 'emojione-master/categories.json';
$plistTemplate = 'template.plist';
$tmpDir = '/tmp';
$tmpFilePrefix = 'tmpEmoticons';
$title="DXSdata EmojiOne2Spark - IconSet Builder";
$blogUrl = "http://www.dxsdata.com/2017/04/emoticons-for-openfire-spark-messenger/";

if ($_POST['action'] == 'generate')
{               
    $emoticons = json_decode(file_get_contents($sourceJsonFile));
    $xml = new SimpleXMLElement(file_get_contents($plistTemplate));     
    $base = $xml -> dict -> dict;       
    
    $categories = $_POST['categories'];  
    $displayOption = @$_POST['displayOption'];
    $diversityOption = @$_POST['diversityOption']; 
    $iconsize = $_POST['iconsize'];       
                                                         
    $zip = new ZipArchive();                        
    $tmpzipfilename = tempnam($tmpDir, $tmpFilePrefix);
    $zip -> open($tmpzipfilename, ZipArchive::CREATE); 
    $zip -> addEmptyDir($iconsetName);                 
       
    foreach($emoticons as $filename => $emoticon)
    {
        //echo $emoticon -> name;
        
        //Filtering
        if (!in_array($emoticon -> category, $categories))
            continue;
     
        if ($displayOption != null)
            if ($emoticon -> display != 1) //would otherwise show also numbers etc. in "people" category, ...
                continue;
            
        if ($diversityOption != null)
            if ($emoticon -> diversity != null) //would otherwise show "redundant" emoticons (only different colors etc.)
                continue;
        //Filtering End
            
            
        //Add XML nodes
        $base -> addChild('key', $filename . '.png'); //Filename
        $dict = $base -> addChild('dict');
        $dict -> addChild('key', 'Equivalents');
        $arr = $dict -> addChild('array');            
        $arr -> addChild('string', $emoticon -> shortname);
        foreach($emoticon -> ascii as $ascii)
            $arr -> addChild('string', $ascii);            
        $dict -> addChild('key', 'Name');
        $dict -> addChild('string', $emoticon -> name); 
                                   
        //Import into zip
        $zip->addFile('emoticons'.$iconsize.'/'.$filename . '.png', $iconsetName.'/'.$filename . '.png');           
    }
     
     
    //echo "processed $i items";

    //beautify and save
    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false; 
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $c = $dom->createComment($blogUrl);
    $dom->insertBefore($c, $dom->firstChild);
    //file_put_contents("Emoticons.plist", $dom->saveXML());


    $zip->addFromString($iconsetName.'/Emoticons.plist', $dom->saveXML());
    $zip->addFile('template_readme.txt', $iconsetName.'/readme.txt');
    $zip->addFile('template_blogLink.url', 'DXSdataBlog.url');
    
    //echo "numfiles: " . $zip->numFiles . "\n";
    //echo "status:" . $zip->status . "\n";
    $zip->close();    
         
    //file_put_contents("out.plist", $xml -> asXML());
      

    //publish zip
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=$zipfilename");
    header("Content-Length: " . filesize($tmpzipfilename));     
    readfile($tmpzipfilename);  
    exit;
}

$categories = json_decode(file_get_contents($categoriesJsonFile));

?>
<html>
<head>
    <title><?=$title?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    
    <script>
    function check_frame() {
     if( top === self ) { // not in a frame
          location.href = "<?=$blogUrl?>";
     }
}
</script>
<body onLoad="check_frame()">
</head>
<body>

<div class="col-md-4">

    <h1>DXSdata EmojiOne2Spark - IconSet Builder</h1>

    <h3>Recommendations</h3>
    <p class="bg-info">
        Select as few categories as possible. Spark emoticon popup might freeze for seconds if it has too many icon files to load.<br>
    </p>


    <form method="post" action="<?=$_SERVER['PHP_SELF']?>">
    <input type="hidden" name="action" value="generate">

    <h3>Get IconSet as ZIP file</h3>

    Which emoticon categories do you want to include in your IconSet?
    <select name="categories[]" multiple="multiple" class="form-control">
        <?php
        foreach($categories as $category)
        {
            ?>
            <option value="<?=$category -> category?>" selected="selected"><?=$category -> category_label?></option>        
            <?php
        }
        ?>
    </select>
    
    <p>
    Icon size
        <select name="iconsize" class="form-control">
            <option value="">32px (original)</option>       
            <option value="_24px">24px</option>
            <option value="_20px" selected="selected">20px (recommended)</option>
        </select>
        <p class="help-block">
            The 32px sized files are the original ones provided by EmojiOne.<br>
            However, 20px seem to fit better to the Spark chat window.
        </p>
    </p>

    <div class="checkbox">
        <label>
            <input type="checkbox" checked="checked" name="displayOption" value="1">
            Only items with display value 1 
        </label>
        <p class="help-block">
            Recommended. Otherwise, e.g. in people category, also digits icons etc. would be integrated.
        </p>
    </div>


    <div class="checkbox">
        <label>
            <input type="checkbox" checked="checked" name="diversityOption" value="1">
            Only items without diversity value 
        </label>
        <p class="help-block">
            Recommended. Otherwise, many "redundant" icons would be integrated (only different colours).
        </p>
    </div>

            
    <input type="submit" value="Generate IconSet" class="btn btn-default">
    </form>

    <div>
    <h3>Installation</h3>
        <ol>
            <li>Download the zip file.</li>
            <li>Extract it to %AppData%\Spark\xtra\emoticons.</li>
            <li>So the folder structure would be e.g. C:\Users\me\AppData\Roaming\Spark\xtra\emoticons\DXSdata.AdiumEmoticonSet.</li>
            <li>In Spark settings, open the Appearance tab and select DXSdata Iconset.</li>
        </ol>
    </div>

    <footer>
        <p>
            IconSet builder provided by <a href="http://www.dxsdata.com">www.dxsdata.com</a>
        </p>

        <p>
            Thanks to <a href="http://www.emojione.com">EmojiOne</a> for providing free emoji icons.
        </p>
    </footer>
</div>

</body>
</html>
    