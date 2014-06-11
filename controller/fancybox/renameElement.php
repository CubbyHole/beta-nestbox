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

/** - Renomme un fichier ou un dossier
 * @author Harry Bellod
 * @param $name | nom du fichier/dossier qu'on renomme
 * @param $dir | dossier courant
 * @since 03/06/2014
 */
//function renameElement($elementId, $newName, $userId)
//{
//    $elementManager = new ElementPdoManager();
//    $elementId = new MongoId($elementId);
//    $searchQuery = array('_id' => $elementId, 'idOwner' => $userId, 'state' => 1);
//    $updateCriteria = array(
//        '$set' => array('name' => $newName)
//    );
//    $options = array('new' => true);
//    $elementManager->findAndModify($searchQuery, $updateCriteria, $options);
////        var_dump($newName);
////        var_dump($e);
////        var_dump($searchQuery);
////        header('Location: /Nestbox');
//}

if( isset($_POST['var']) && !empty($_POST['var']) )
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $userManager = new UserPdoManager();


    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());

    echo '<p><label name="description">Element information:</label></p>';
    echo '<div id="elementInformations">
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
    <form id="submitRename" method="POST">
        <?php
        echo '<p><label name="nameRename">Enter a new name:</label></p>';
        echo '<input type="hidden" name="idElement" value="'.$_GET['id'].'" read-only>
              <input type="text" name="newName" value="'.$element->getName().'">';
        ?>
        <br /><br />
        <p style="text-align: center;"><input type="submit" class="btn-success btn" value="Rename" name="renameElem">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel"></p>
    </form>
<?php
}
?>
