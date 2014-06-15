<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 05/06/14
 * Time: 12:36
 */

header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';


if(isset($_SESSION['userId']))
    $userId = $_SESSION['userId'];
?>

    <div id="utils_fancybox">
        <div id="imageClose">
            <img src="./content/img/icon_close_box.png" onclick="parent.$.fancybox.close();"/>
        </div>
    </div>

<?php

if( isset($_POST['var']) && !empty($_POST['var']) )
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $userManager = new UserPdoManager();

    $refElementEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4002',
        'state' => 1
    ));
    if($refElementEmptyDirectory instanceof RefElement)
        $idRefElementEmptyDirectory = $refElementEmptyDirectory->getId();
    else
        return $refElementEmptyDirectory;

    $refElementNotEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4003',
        'state' => 1
    ));
    if($refElementNotEmptyDirectory instanceof RefElement)
        $idRefElementNotEmptyDirectory = $refElementNotEmptyDirectory->getId();
    else
        return $refElementNotEmptyDirectory;


    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());


    echo '<div id="elementInformations">
        <p><label name="description">Element information:</label></p>
        <ul>
            <li>Element name : '.$element->getName().'</li>
            <li>Current directory : '.$element->getServerPath().'</li>
            <li>Type : '.$refElement->getDescription().'</li>';
                if(!(preg_match('/^4/', $refElement->getCode())))
                {
                    if($element->getSize() < 1)
                        echo '<li>Size : < 1 KB</li>';
                    else
                        echo '<li>Size : '.$element->getSize().' KB</li>';
                }
       echo '<li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>
        </ul>
    </div>';

}
?>

