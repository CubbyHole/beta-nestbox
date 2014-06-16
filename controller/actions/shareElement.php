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
        if($shareResult['error'] == 'You cannot share an element with yourself')
             echo $shareResult['error'].'. Please enter an another email.';
        else
            echo $shareResult['error'].' Please enter an another email or choose anonymous share.';
    }
    else
        echo 'Right has been successfully applied for element and user with email '.$_POST['email'];
}