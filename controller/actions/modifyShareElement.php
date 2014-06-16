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
if(isset($_POST['email']) && isset($_POST['refRight']) && isset($_POST['idElement']))
{
    $shareResult = shareWithUser($_POST['idElement'], $userId, $_POST['email'], $_POST['refRight']);
    if(is_array($shareResult) && array_key_exists('error', $shareResult))
    {
        echo $shareResult['error'];
    }
    else
        echo 'Right has been successfully updated for element and user with email '.$_POST['email'];
}