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
        echo 'This element already has a download link. Copy the following link to your friend to give them possibility to download this element';
        echo "http://localhost/Nestbox/view/gouze.php?token=".$shareResult['downloadLink'];
    }
}