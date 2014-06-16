<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 15/06/14
 * Time: 16:04
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

if(isset($_POST['idElement']))
{
    $shareResult = shareWithAnonymous($_POST['idElement'], $userId);

    if(is_array($shareResult) && array_key_exists('error', $shareResult))
        echo $shareResult['error'];
    else
    {
        echo '<div id="downloadLink"  style="margin-top: 50px;text-align: center;">';
        echo 'Copy the following link to your friend to give them chance to download this element';
        echo '<br />';
        echo '<font color="green">http://localhost/Nestbox/view/grouse.php?token='.$shareResult['downloadLink'].'<font>';
        echo '</div>';
    }
}