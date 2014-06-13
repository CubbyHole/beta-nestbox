<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 21:21
 */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */

/** Si l'utilisateur décide de créer un nouveau dossier => directory est un input caché dans le formulaire pour récupérer le dossier courant */
if(isset($_POST['directory']) && isset($_POST['nameNewFolder']))
{

    $returnCreate = createNewFolder($userId, $_POST['directory'], $_POST['nameNewFolder'], true);

    if(is_array($returnCreate) && array_key_exists('error', $returnCreate))
    {
        echo $returnCreate['error'];
    }
    else
        echo 'Your folder '.$_POST['nameNewFolder'].' has been successfully created. Please refresh the page.';
//    //$elementManager->createNewFolder($_POST['nameNewFolder'], $_POST['currentDirectory']);
}