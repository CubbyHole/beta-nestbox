<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */


/* Si l'utilisateur décide de couper un fichier ou un dossier */
if(isset($_POST['destination']))
{
    if(isset($_POST['keepRights']) && $_POST['keepRights'] != "undefined")
        $keepRights = true;
    else
        $keepRights = false;

    if(isset($_POST['keepDownloadLink']) && $_POST['keepDownloadLink'] != "undefined")
        $keepDownloadLink = true;
    else
        $keepDownloadLink = false;

    $options = array('returnImpactedElements' => true, 'returnMovedElements' => true, 'keepRights' => $keepRights, 'keepDownloadLinks' => $keepDownloadLink);
    $moveResult = moveHandler($_POST['idElement'], $userId, $_POST['destination'], $options);

    if(is_array($moveResult) && array_key_exists('error', $moveResult))
        echo $moveResult['error'];
    else
        echo 'The element has been successfully moved to '.$_POST['destination'];
}