<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */


if(isset($_POST['idElement']))
{
  /**
   *  appel de la fonction download
   */
    userDownload($userId,$_POST['idElement']);
    echo "You have successfully download this element.";
}