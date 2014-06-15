<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */

/* Si l'utilisateur décide d'uploader un element */
if(isset($_POST['destination']))
{
    var_dump($_POST['destination']);
    echo "Your element has been successfully uploaded";
//    $elementManager->uploadElement($_POST['elementName'], $userId, $_POST['elementType'], $_POST['elementSize'], $_POST['destination']);
}
