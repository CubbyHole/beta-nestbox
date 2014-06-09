<?php
header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 05/06/14
 * Time: 12:36
 */

if( isset($_POST['var']) && !empty($_POST['var']) )
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $userManager = new UserPdoManager();


    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());

    echo '<div id="elementInformations">
          <label name="validationDisable">Are you sure you want to disable this element ?</label>
            <ul>
                <li>Element name : '.$element->getName().'</li>
                <li>Current directory : '.$element->getServerPath().'</li>
                <li>Type : '.$refElement->getDescription().'</li>
                <li>Size : '.$element->getSize().' KB</li>
                <li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>
            </ul>
          </div>';


    ?>
    <!-- formulaire pour renommer -->
    <form id="submitDisable" method="POST">
        <?php echo '<input type="hidden" name="idElement" value="'.$_GET['id'].'" read-only>'; ?>
        <input type="submit" value="Disable" name="disableElem">
        <input type="button" onclick="parent.jQuery.fancybox.close();" value="Cancel">
    </form>
<?php
}
?>
