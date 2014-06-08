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
/**
 * - Créer un nouveau dossier
 * @author Harry Bellod
 * @param $name | nom du nouveau dossier
 * @param $dir | dossier courant
 * @since 22/05/2014
 */
//function createNewFolder($name, $dir){
//
//    /** Gestion bdd **/
//
//    // récupère le nom du dossier ou l'on se trouve
//    if($dir == "/")
//        $directoryCurrent = $dir;
//    else
//    {
//        $explode = explode("/", $dir);
//        $directoryCurrent = $explode[sizeof($explode)-2];
//    }
//    // traitement pour avoir l'équivalent du serverPath afin de gérer les dossiers vides/non vides
//    $pattern = "#".$directoryCurrent."/#";
//    $path = preg_replace($pattern, "", $dir);
//
//    $elementManager = new ElementPdoManager();
//    $refElementManager = new RefElementPdoManager();
//
//    // test si il n'existe pas déjà un dossier du même nom
//    if($name !== "")
//    {
//        $element = $elementManager->findOne(array("name" => $name, "idOwner" => new MongoId("536749adedb5025416000029"), "serverPath" => $dir, "state" => 1));
//
//
//        if(is_array($element) && array_key_exists('error', $element))
//        {
//            // récupère l'id des refElements directory empty et directory not empty
//            // Not Empty
//            $refElementNotEmpty = $refElementManager->findOne(array("description" => "Directory File (not empty)"));
//            $idNotEmptyFolder = $refElementNotEmpty->getId();
//
//            // Empty
//            $refElementEmpty = $refElementManager->findOne(array("description" => "Directory File (empty)"));
//            $idEmptyFolder = $refElementEmpty->getId();
//
//            // on créer le dossier
//            $criteria = array("state" => 1, "name" => $name, "idOwner" => new MongoId("536749adedb5025416000029"), "idRefElement" => new MongoId($idEmptyFolder), "size" => null, "serverPath" => $dir, "hash" => "azeaze", "downloadLink" => null);
//            $elementManager->create($criteria);
//
//            // passage du dossier courant à not empty si le create a marché
//            $searchQuery = array('name' => $directoryCurrent, 'idOwner' => new MongoId("536749adedb5025416000029"), 'state' => 1, 'serverPath' => $path);
//            $updateCriteria = array(
//                '$set' => array('idRefElement' => new MongoId($idNotEmptyFolder))
//            );
//            $options = array('new' => true );
//            $elementManager->findAndModify($searchQuery, $updateCriteria, $options);
//
//            /** Gestion serveur de fichiers **/
//            $newFolderPath = $dir.$name;
//            //        mkdir($newFolderPath, 0777); // à voir pour les permissions <------------- <-------------
//
//            header('Location: /Nestbox');
//        }
//        else
//        {
//            echo '<script>alert("Sorry, there is already a folder with this name in this directory. Please enter an other name.");</script>';
//        }
//    }
//    else
//    {
//        echo '<script>alert("Sorry, please enter a name for the new folder");</script>';
//    }
//}



if( isset($_POST['var']) && !empty($_POST['var']) )
{
    echo '<div id="elementInformations">
            <ul>
                    <li>Current directory : '.$_GET['dir'].'</li>
            </ul>
            <label name="validationCreate">Are you sure you want to create this folder ?</label>
          </div>';

    ?>
    <!--  formulaire pour la création de dossier -->
    <form id="newFolder" method="POST">
        <?php echo '<input type="hidden" name="currentDirectory" value="'.$_GET['dir'].'" readonly>'; ?>
        <input type="text" name="nameNewFolder" placeholder="Folder name">
        <input type="submit" value="Create new folder" name="createNewFolder">
        <input type="button" onclick="parent.jQuery.fancybox.close();" value="Cancel">
    </form>
<?php
}
else
    echo "coucou";
?>
