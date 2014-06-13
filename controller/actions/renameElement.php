<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:59
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

if(!empty($_POST['newName']) && isset($_POST['idElement']))
{
    $renameResult = renameHandler($_POST['idElement'], $userId, $_POST['newName']);

    echo 'The element has been successfully renamed to '.$_POST['newName'].'.Please refresh the page';
}