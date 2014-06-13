<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 20:10
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';


/** Si l'utilisateur décide de supprimer un dossier ou un fichier */
if(isset($_POST['idElement']))
{
    $idElement = new MongoId($_POST['idElement']);
    $disableResult = disableHandler($idElement, $userId);
    if(is_array($disableResult) && array_key_exists('error', $disableResult))
    {
        echo $disableResult['error'];
    }
    else
        echo 'The element has been successfully disabled. Please refresh the page.';

}