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
            <label name="validationDownload">Are you sure you want to dowload this element ?</label>
                <ul>
                    <li>Element name : '.$element->getName().'</li>
                    <li>Current directory : '.$element->getServerPath().'</li>
                    <li>Type : '.$refElement->getDescription().'</li>
                    <li>Size : '.$element->getSize().' KB</li>
                    <li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>
                </ul>
          </div>';
    ?>

    <!-- formulaire pour déplacer -->
    <form id="submitDownload" method="POST">
        <?php
            echo '<input type="hidden" name="idElement" value="'.$_GET['id'].'" read-only>';
        ?>
        <input type="submit" value="Download" name="downloadElem">
        <input type="button" onclick="parent.jQuery.fancybox.close();" value="Cancel">
    </form>
<?php
}?>