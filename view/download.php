<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 18/04/14
 * Time: 16:51
 */

if(isset($_GET['filepath']))
{
    $filepath = $_GET['filepath'];
    if(file_exists($filepath) )
    {

        /* header("Pragma: public");
         header("Expires: 0");
         //header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
         //header("Cache-Control: public");
         header("Content-Description: File Transfer");*/

        //Use the switch-generated Content-Type
        header('Content-Type: application/x-download');

        //Force the download
        header("Content-Disposition: attachment; filename=".basename($filepath)."");

        //header("Content-Transfer-Encoding: binary");
        //header("Content-Length: ".$fsize);

        // parse Info / obtenir l'extension
        $fsize = filesize($filepath);
        $path_parts = pathinfo($filepath);
        $ext = strtolower($path_parts["extension"]);

        // determine le Content Type
        /*switch ($ext)
        {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "doc": $ctype="application/msword"; break;
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            default: $ctype="application/force-download";
        }*/
        readfile($filepath);
        exit;
    }
}
?>
<html>
<head>
    <body>
        <a href="download.php?filepath=functions.php" target="_blank">dl</a>
    </body>
</head>
</html>