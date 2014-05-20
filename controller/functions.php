<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 18/04/14
 * Time: 10:18
 */

//// delete d'un fichier
//if(isset($_GET['delete']))
//{
//    unlink(utf8_decode($_GET['delete']));                                 /** Plus valable vu qu'on ne supprimer plus sur le serveur de fichier */
//}
//

//// delete d'un dossier
//if(isset($_GET['delete_folder']))
//{                                                                         /** Plus valable mais il faut penser que si l'user supprime un dossier, passer tout son contenu à state = 0 ? */
//    RepEfface(utf8_decode($_GET['delete_folder']));
//}
//

//// download
//if(isset($_GET['filepath']))
//{
//    download($_GET['filepath'], $_GET['filename']);
//}
//
                                                                                /** En récupérant les informations de l'élément la fonction download sur le serveur de fichier devrais marcher */
//function download($filepath, $filename){
//    if(file_exists($filepath) )
//    {
//        $fsize = filesize($filepath);
//
//        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
//        header('Content-type: application/x-download');
//        header('Content-Length: '.fsize);
//        readfile($filepath);
//        exit;
//
//    }
//}
//

/** Si l'utilisateur décide de créer un nouveau dossier => currentDirectory est un input caché dans le formulaire pour récupérer le dossier courant */
if(isset($_POST['createNewFolder']) && isset($_POST['currentDirectory']))
{
    createNewFolder($_POST['nameNewFolder'], $_POST['currentDirectory']);
}
elseif(isset($_POST['createNewFolder']) && !isset($_POST['currentDirectory']))
{
    createNewFolder($_POST['nameNewFolder'], "/");
}



/** Si l'utilisateur décide de supprimer un dossier ou un fichier */
if(isset($_POST['deleteElem']) && isset($_POST['currentDirectory']))
{
    deleteElement($_POST['elementToDelete'], $_POST['currentDirectory']);
}
elseif(isset($_POST['deleteElem']) && !isset($_POST['currentDirectory']))
{
    deleteElement($_POST['elementToDelete'], "/");
}


function arborescence($owner, $isOwner, $dir){
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);

    echo '<br />';
    echo '<br />';
    // Root
    if(!isset($_GET['dir']))
        echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_open.png" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'">Root</a></span></div></div>';
    else
        echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'">Root</a></span></div></div>';

    foreach($elementList as $element)
    {
        //$element->setRefElement($refElementManager->findById($element->getRefElement()));
        $codeElement = $element->getRefElement()->getCode();

        switch($codeElement)
        {
            case 4002: // dossier vide
                if(isset ($_GET['dir']) && $_GET['dir'] == "/".$element->getName()."/")
                {
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                }
                else
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:15px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                break;
            case 4003: // dossier non vide
                if(isset ($_GET['dir']) && (preg_match('#'.$element->getName().'/#', '#'.$_GET['dir'].'/#')))
                {
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                    $x = "/".$element->getName()."/";
                    under_arborescence($owner, $isOwner, $x);
                    /*if($_GET['dir'] != $x)
                        under_arborescence($owner, $isOwner, $_GET['dir']);*/
                }
                else
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:15px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                break;
        }
    };

}


function contenu($owner,$isOwner,$dir){
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    echo "<br />";
    echo "<br />";

    $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);

    if(is_array($elementList) && array_key_exists('error', $elementList))
    {
        echo '<div class="row"><span class="cell">Empty folder</span></div>';
    }
    else
    {
        foreach($elementList as $element)
        {
//        $element->setRefElement($refElementManager->findById($element->getRefElement()));
        $codeElement = $element->getRefElement()->getCode();

            switch($codeElement)
            {
                case 4002:
                    if(isset($_GET['dir']))
                        echo '<div id="'.$element->getName().'" data-element-type="folder"><div class="row"><div id="arbo"><span class="cell"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div></div>';
                    else
                        echo '<div id="'.$element->getName().'" data-element-type="folder"><div class="row"><div id="arbo"><span class="cell"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div></div>';
                    break;
                case 4003:
                    if(isset($_GET['dir']))
                        echo '<div id="'.$element->getName().'" data-element-type="folder"><div class="row"><div id="arbo"><span class="cell"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div></div>';
                    else
                        echo '<div id="'.$element->getName().'" data-element-type="folder"><div class="row"><div id="arbo"><span class="cell"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" /><a href="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">'.$element->getName().'</a></span></div></div></div>';
                    break;
                default:
                    echo '<div id="'.$element->getName().'" data-element-type="file"><div class="row"><div id="arbo"><span class="cell"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></div></div></div>';

            }
        };
    }

}

function under_arborescence($owner, $isOwner, $dir)
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    //$criteria = array("idOwner" => $owner, "state" => (int)$state, "serverPath" => $dir);
//    $elementList = $elementManager->find($criteria);
//                    var_dump($elementList);
    $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);

    foreach($elementList as $element)
    {
        //$element->setRefElement($refElementManager->findById($element->getRefElement()));
        $codeElement = $element->getRefElement()->getCode();
        //var_dump($codeElement);
        switch($codeElement)
        {
            case 4002:
                echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                break;
            case 4003:
                echo $element->getName();
                echo $_GET['dir'];
                echo $dir;
                if(isset ($_GET['dir']) && (preg_match('#'.$element->getName().'/#', '#'.$_GET['dir'].'/#')))
                {
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                    under_arborescence($owner, $isOwner, $_GET['dir']);
                }
                else
                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
                break;
        }
    };
}


// retourne la taille du fichier
function fileSize64($file)
{
    //Le système d'exploitation est-il un windows?
    static $isWindows;
    if (!isset($isWindows))
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');

    //La commande exec est-elle disponible?
    static $execWorks;
    if (!isset($execWorks))
        $execWorks = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');

    //Essaye une commande shell
    if ($execWorks)
    {
        $cmd = ($isWindows) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
        @exec($cmd, $output);

        /*
         * ctype_digit vérifie si tous les caractères de la chaîne sont des chiffres
         * @link https://php.net/manual/fr/function.ctype-digit.php
         */
        if (is_array($output) && ctype_digit($size = trim(implode("\n", $output))))
            return round($size / 1024, 0); //conversion en Kb
    }

    //Essaye l'interface Windows COM
    if ($isWindows && class_exists("COM"))
    {
        try
        {
            //@link https://php.net/manual/fr/class.com.php
            $fileSystemObject = new COM('Scripting.FileSystemObject'); //accès au système de fichier de l'ordinateur

            //retourne un objet fichier
            $fileObject = $fileSystemObject->GetFile(realpath($file));

            //retourne la taille du fichier en octets
            $size = $fileObject->Size;

        } catch (Exception $e)
        {
            $size = null;
        }

        if (ctype_digit($size))
            return round($size / 1024, 0); //conversion en Kb
    }

    //En dernier recours utilise filesize (qui ne fonctionne pas correctement pour des fichiers de plus de 2 Go.
    return round(filesize($file) / 1024, 0);
}



// fonction de création de dossier
function createNewFolder($name, $dir){

    /** Gestion bdd **/

    // récupère le nom du dossier ou l'on se trouve
    if($dir == "/")
        $directoryCurrent = $dir;
    else
    {
        $explode = explode("/", $dir);
        $directoryCurrent = $explode[sizeof($explode)-2];
    }
    // traitement pour avoir l'équivalent du serverPath afin de gérer les dossiers vides/non vides
    $pattern = "#".$directoryCurrent."/#";
    $path = preg_replace($pattern, "", $dir);

    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    // test si il n'existe pas déjà un dossier du même nom
    if($name !== "")
    {
        $element = $elementManager->findOne(array("name" => $name, "idOwner" => new MongoId("536749adedb5025416000029"), "serverPath" => $dir, "state" => 1));


        if(is_array($element) && array_key_exists('error', $element))
        {
            // récupère l'id des refElements directory empty et directory not empty
            // Not Empty
            $refElementNotEmpty = $refElementManager->findOne(array("description" => "Directory File (not empty)"));
            $idNotEmptyFolder = $refElementNotEmpty->getId();

            // Empty
            $refElementEmpty = $refElementManager->findOne(array("description" => "Directory File (empty)"));
            $idEmptyFolder = $refElementEmpty->getId();

            // on créer le dossier
            $criteria = array("state" => 1, "name" => $name, "idOwner" => new MongoId("536749adedb5025416000029"), "idRefElement" => new MongoId($idEmptyFolder), "size" => null, "serverPath" => $dir, "hash" => "azeaze", "downloadLink" => null);
            $elementManager->create($criteria);

            // passage du dossier courant à not empty si le create a marché
            $searchQuery = array('name' => $directoryCurrent, 'idOwner' => new MongoId("536749adedb5025416000029"), 'state' => 1, 'serverPath' => $path);
            $updateCriteria = array(
                '$set' => array('idRefElement' => new MongoId($idNotEmptyFolder))
            );
            $options = array('new' => true );
            $elementManager->findAndModify($searchQuery, $updateCriteria, $options);

            /** Gestion serveur de fichiers **/
            $newFolderPath = $dir.$name;
    //        mkdir($newFolderPath, 0777); // à voir pour les permissions <------------- <-------------

            header('Location: /Nestbox');
        }
        else
        {
            echo '<script>alert("Sorry, there is already a folder with this name in this directory. Please enter an other name.");</script>';
        }
    }
    else
    {
            echo '<script>alert("Sorry, please enter a name for the new folder");</script>';
        }
}

//fonction de suppression d'élément
function deleteElement($name, $dir)
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    $searchQuery = array('name' => $name, 'idOwner' => new MongoId("536749adedb5025416000029"), 'state' => 1, 'serverPath' => $dir);
    $updateCriteria = array(
        '$set' => array('state' => 0)
    );
    $options = array('new' => true );
    $elementManager->findAndModify($searchQuery, $updateCriteria, $options);

    if($dir == "/")
        $regex = new MongoRegex('/^/'.$name.'/*$/');
    else
        $regex = new MongoRegex('/^'.$dir.$name.'/');

    $searchQuery1 = array('serverPath' => $regex);
    $updateCriteria1 = array(
        '$set' => array('state' => 0)
    );
    $elementManager->findAndModify($searchQuery1, $updateCriteria1, $options);

    header('Location: /Nestbox');

}

/**
 * Convertit des kilobytes (unité enregistrée en BDD) dans l'unité voulue).
 * Attention: retourne une chaîne de caractère.
 * @author Alban Truc
 * @param int|float $kiloBytes
 * @param NULL|string $outputUnit
 * @param null|string $format
 * @since 16/05/2014
 * @return string
 */

function convertKilobytes($kiloBytes, $outputUnit = NULL, $format = NULL)
{
    $kiloBytes = $kiloBytes * 1024; //transforme en bytes

    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    $units = array('B', 'MB', 'GB', 'TB', 'PB');
    $mod = 1000;

    /*
     * Déterminer l'unité à utiliser
     * http://php.net/manual/en/function.array-search.php
     */
    if (($power = array_search((string) $outputUnit, $units)) === FALSE)
    {
        //http://php.net/manual/en/function.floor.php
        $power = ($kiloBytes > 0) ? floor(log($kiloBytes, $mod)) : 0;
    }

    /*
     * http://php.net/manual/en/function.sprintf.php
     * http://php.net/manual/en/function.pow.php
     */
    return sprintf($format, $kiloBytes / pow($mod, $power), $units[$power]);
}
?>
