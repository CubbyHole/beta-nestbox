<?php
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */


/* Si l'utilisateur décide de copier un fichier ou un dossier */
if(isset($_POST['destination']))
{
    if(isset($_POST['keepRights']) && $_POST['keepRights'] != "undefined")
        $keepRights = true;
    else
        $keepRights = false;


    $options = array('returnImpactedElements' => true, 'returnMovedElements' => true, 'keepRights' => $keepRights);
    copyHandler($_POST['idElement'], $userId, $_POST['destination'], $options);

    echo 'The element has been successfully copied to '.$_POST['destination'].'. Please refresh the page';
}