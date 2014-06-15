<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/06/14
 * Time: 15:12
 */

/* Téléchargement anonyme */


header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

if(isset($_GET['token']))
{
    $elementManager = new ElementPdoManager();
    $userManager = new UserPdoManager();
    $refElementManager = new RefElementPdoManager();

    $element = $elementManager->findOne(array('downloadLink' => $_GET['token']));
    $user = $userManager->findById($element->getOwner());
    $refElement = $refElementManager->findById($element->getRefElement());

    echo '<div id="elementInformations">
             <p><label name="description">Element information:</label></p>
        <ul>
            <li>Element name : '.$element->getName().'</li>
            <li>Extension : '.$refElement->getExtension().'</li>
            <li>Type : '.$refElement->getDescription().'</li>';
            if(!(preg_match('/^4/', $refElement->getCode())))
            {
                if($element->getSize() < 1)
                    echo '<li>Size : < 1 KB</li>';
                else
                    echo '<li>Size : '.$element->getSize().' KB</li>';
            }
            echo '<li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>

          </div>';
    ?>
        <!-- formulaire pour déplacer -->
        <form id="downloadLinkElement" method="POST">
            <?php
            echo '<p><label name="createDownloadLink">Do you want to download this element:</label></p>';
            echo '<input type="hidden" name="idElement" id="idElement" value="'.$element->getId().'" read-only>';
            ?>
    <p style="text-align: center;"><input type="submit" class="btn-success btn" value="Download" name="downloadAnonymousElem" id="downloadAnonymousElem"></p>
    </form>

<?php
}