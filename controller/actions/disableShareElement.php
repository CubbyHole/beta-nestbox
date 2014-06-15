<?php
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
* Created by PhpStorm.
* User: Harry
* Date: 12/06/14
* Time: 19:42
*/

/* Si l'utilsateur décide de partager un élément à un utilisateur */
if(isset($_POST['idUser']) && isset($_POST['idElement']))
{

    $disableShareResult = disableShareRights($_POST['idElement'], $_POST['idUser'], $_POST['idOwner']);

    if(is_array($disableShareResult) && array_key_exists('error', $disableShareResult))
    {
        echo $disableShareResult['error'];
    }
    else
        echo 'Right has been successfully disabled.';

}