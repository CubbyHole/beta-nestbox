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
?>

<!--    <script type="text/javascript">-->
<!--        jQuery(function($)-->
<!--        {-->
<!--            $('.dropfile').dropfile({-->
<!---->
<!--            });-->
<!--        });-->
<!--    </script>-->
<?php
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
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

     echo '<div id="elementInformations">
            <ul>
                    <li>Current directory : '.$_GET['dir'].'</li>
            </ul>
            <label name="validationUpload">Are you sure you want to upload this file ?</label>
          </div>';

function cmp($a,$b)
{
    return strcmp($a, $b);
}

/**
 * Fonction d'upload
 * @author Harry Bellod
 * @param array|Element $name de l'élément qu'on veut copier/couper
 * @param array|Element $dir dossier courant ou l'on veut coller
 * @since 28/05/2014
 */
//function uploadElement($filename, $userId, $filetype, $filesize, $dir)
//{
//    $refElementManager = new RefElementPdoManager();
//    $explode = explode("/", $filetype);
//    $ext = $explode[sizeof($explode)-1];
//
//    $refElement = $refElementManager->findOne(array('extension' => '.'.$ext));
//    var_dump($filename);
//    var_dump($ext);
//    var_dump($dir);
//
//}


?>

    <!--  formulaire pour la création de dossier -->
        <form id="submitUpload" method="POST" enctype="multipart/form-data">
        <?php echo '<input type="hidden" name="currentDirectory" value="'.$_GET['dir'].'" readonly>'; ?>
<!--        <p><label>Select a file :</label>-->
<!--        <input type="file" name="element"></p>-->
<!--        <div class="dropfile"></div>-->
            <div id="drop_zone" class="alert alert-block alert-success pagination-centered">
                <h1>Drop files here or click for select</h1> To hash them all
            </div>
            <div>
                <input type="checkbox" id="hash_md5" title="Check this to calculate MD5 file hash" checked/>
                MD5&nbsp;
                <input type="checkbox" id="hash_sha1" title="Check this to calculate SHA1 file hash" checked/>
                SHA1&nbsp;
                <input type="checkbox" id="hash_sha256" title="Check this to calculate SHA2 (SHA-256) file hash" checked/>
                SHA-256&nbsp;

                <div style="float: right">
                    <input type="file" id="files" name="files[]" multiple/>
                </div>
            </div>
            <div id="list"></div>
        <?php
          echo '<select name="destination" id="destination">
                <option>/</option>';

                $elementList = $elementManager->find(array(
                'serverPath'=> new MongoRegex("/^/"),
                'state' => 1,
                '$or' => array(
                array('idRefElement' => $idRefElementEmptyDirectory),
                array('idRefElement' => $idRefElementNotEmptyDirectory)
                )
                ),
                array(
                'serverPath' => TRUE,
                'name' => TRUE,
                '_id' => FALSE
                ));

                $f = function($array){return $array['serverPath'].$array['name'];};

                $elementList = array_map($f, $elementList);
                $result = array_unique($elementList);

                usort($result, "cmp");
                foreach($result as $element)
                {
                echo '<option>'.$element.'/</option>';
                }
                echo '</select>';
        ?>
        <input type="submit" value="Upload" name="uploadElem">
        <input type="button" onclick="parent.jQuery.fancybox.close();" value="Cancel">
        <div id="informationElementToUpload"></div>
    </form>
