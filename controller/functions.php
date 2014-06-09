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
$elementManager = new ElementPdoManager();
if(isset($_SESSION['user']))
{
    $user = unserialize($_SESSION['user']);
    $userId = $user->getId();
}


/** Si l'utilisateur décide de créer un nouveau dossier => currentDirectory est un input caché dans le formulaire pour récupérer le dossier courant */
if(isset($_POST['createNewFolder']) && isset($_POST['currentDirectory']))
{

    $returnCreate = createNewFolder($userId, $_POST['currentDirectory'], $_POST['nameNewFolder'], true);

    if(is_array($returnCreate) && array_key_exists('error', $returnCreate))
    {
        if('error' == 'Folder name not available.')
            echo "error";
    }
//    //$elementManager->createNewFolder($_POST['nameNewFolder'], $_POST['currentDirectory']);
}

/** Si l'utilisateur veut renommer un élement */
if(isset($_POST['renameElem']) && !empty($_POST['renameElem']) && isset($_POST['idElement']))
{
    $elementManager->renameElement($_POST['idElement'],$_POST['newName'], $userId);
}

/** Si l'utilisateur décide de supprimer un dossier ou un fichier */
if(isset($_POST['disableElem']) && isset($_POST['idElement']))
{
    $idElement = new MongoId($_POST['idElement']);
    disableHandler($idElement, $userId);
}


/* Si l'utilisateur décide de copier un fichier ou un dossier */
if(isset($_POST['copyElem']) && isset($_POST['destination']))
{
    copyHandler($_POST['idElement'], $userId, $_POST['destination']);
}

/* Si l'utilisateur décide de couper un fichier ou un dossier */
if(isset($_POST['moveElem']) && isset($_POST['destination']))
{
   if(isset($_POST['keepRights']))
       $keepRights = true;
   else
       $keepRights = false;

    if(isset($_POST['keepDownloadLink']))
        $keepDownloadLink = true;
    else
        $keepDownloadLink = false;

    $options = array('returnImpactedElements' => true, 'returnMovedElements' => true, 'keepRights' => $keepRights, 'keepDownloadLinks' => $keepDownloadLink);
    moveHandler($_POST['idElement'], $userId, $_POST['destination'], $options);
}

/* Si l'utilisateur décide d'uploader un element */
if(isset($_POST['uploadElem']) && isset($_POST['destination']) && isset($_POST['elementName']) && isset($_POST['elementSize']) && isset($_POST['elementType']))
{
    // si upload basique
//    $elementManager->uploadElement($_FILES['element']['name'], $userId, $_FILES['element']['type'], $_POST['currentDirectory']);
    //si upload drag

    $elementManager->uploadElement($_POST['elementName'], $userId, $_POST['elementType'], $_POST['elementSize'], $_POST['destination']);
}



///* Si l'utilisateur décide de coller un élement */
//if(isset($_POST['pasteElem']) && isset($_POST['currentDirectory']))
//{
//    $elementManager->pasteElement($_POST['elementToPaste'], $_POST['currentDirectory'], $userId);
//}
//elseif(isset($_POST['pasteElem']) && !isset($_POST['currentDirectory']))
//{
//    $elementManager->pasteElement($_POST['elementToPaste'], "/", $userId);
//}



function arborescence($owner, $isOwner, $dir)
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);


    echo '<br />';
    echo '<br />';

    echo "<!-- Arborescence -->";
    echo '<div class="col-md-3 arborescence">';
    // Root
    if(!isset($_GET['dir']))
        echo '
                <div class="row" data-element-type="folder">
                    <div id="arbo">
                        <span class="cell">
                            <img src="content/img/icon_dir_open.png" width="18px" height="18px" />
                            <a href="'.$_SERVER['PHP_SELF'].'">Root</a>
                        </span>
                    </div>
                </div>';
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
                    echo '<div class="row" data-element-type="folder">
                            <div id="arbo">
                                <span class="cell">
                                    <img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/>
                                    <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/" style="cursor:pointer;">
                                        '.$element->getName().'
                                    </span>
                                </span>
                            </div>
                         </div>';
                }
                else
                    echo '<div class="row" data-element-type="folder">
                            <div id="arbo">
                                <span class="cell">
                                    <img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:15px;"/>
                                    <span onclick="clickable(this)"  data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                        '.$element->getName().'
                                    </span>
                                </span>
                            </div>
                          </div>';
                break;
            case 4003: // dossier non vide
                if(isset ($_GET['dir']) && (preg_match('#'.$element->getName().'/#', '#'.$_GET['dir'].'/#')))
                {
                    echo '<div class="row" data-element-type="folder">
                            <div id="arbo">
                                <span class="cell">
                                    <img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/>
                                    <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                        '.$element->getName().'
                                    </span>
                                </span>
                            </div>
                          </div>';
                    $x = "/".$element->getName()."/";
                    //under_arborescence($owner, $isOwner, $x);
                    /*if($_GET['dir'] != $x)
                        under_arborescence($owner, $isOwner, $_GET['dir']);*/
                }
                else
                    echo '<div class="row" data-element-type="folder">
                            <div id="arbo">
                                <span class="cell">
                                    <img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:15px;"/>
                                    <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                    '.$element->getName().'
                                    </span>
                                </span>
                            </div>
                          </div>';
                break;
        }
    };
echo '</div>';//fin div container
}


function contenu($owner,$isOwner,$dir)
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();

    echo "<br />";
    echo "<br />";
    echo "<!-- Contenu -->";
    echo '<div class="col-md-9 contenu">';

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
                        echo '<div  onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'">
                                <div class="row">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />
                                            <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">
                                                '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                </div>
                             </div>';
                    else
                        echo '<div  onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'">
                                <div class="row">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />
                                            <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                            '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                </div>
                              </div>';
                    break;
                case 4003:
                    if(isset($_GET['dir']))
                        echo '<div  onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'">
                                <div class="row">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />
                                            <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">
                                            '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                </div>
                              </div>';
                    else
                        echo '<div  onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'">
                                <div class="row">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />
                                            <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                            '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                </div>
                              </div>';
                    break;
                default:
                    echo '<div  onclick="selectFile(this)" id="'.$element->getId().'" data-element-type="file" name="'.$element->getName().'">
                            <div class="row">
                                <div id="arbo">
                                    <span class="cell">
                                        <img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />
                                        '.$element->getName().'
                                    </span>
                                </div>
                            </div>
                          </div>';

            }
        };
    }
    echo '</div>';
}

//function under_arborescence($owner, $isOwner, $dir)
//{
//    $elementManager = new ElementPdoManager();
//    $refElementManager = new RefElementPdoManager();
//
//    //$criteria = array("idOwner" => $owner, "state" => (int)$state, "serverPath" => $dir);
////    $elementList = $elementManager->find($criteria);
////                    var_dump($elementList);
//    $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);
//
//    foreach($elementList as $element)
//    {
//        //$element->setRefElement($refElementManager->findById($element->getRefElement()));
//        $codeElement = $element->getRefElement()->getCode();
//        //var_dump($codeElement);
//        switch($codeElement)
//        {
//            case 4002:
//                echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
//                break;
//            case 4003:
////                echo $element->getName();
////                echo $_GET['dir'];
////                echo $dir;
//                if(isset ($_GET['dir']) && (preg_match('#'.$element->getName().'/#', '#'.$_GET['dir'].'/#')))
//                {
//                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
//                    under_arborescence($owner, $isOwner, $_GET['dir']);
//                }
//                else
//                    echo '<div class="row"><div id="arbo"><span class="cell"><img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:30px;"/><a href="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">'.$element->getName().'</a></span></div></div>';
//                break;
//        }
//    };
//}


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
$bytes = $kiloBytes * 1024; //transforme en bytes

// Format string
$format = ($format === NULL) ? '%01.2f' : (string) $format;

$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
$mod = 1024;

/*
 * Déterminer l'unité à utiliser
 * http://php.net/manual/en/function.array-search.php
 */
if (($power = array_search((string) $outputUnit, $units)) === FALSE)
{
    //http://php.net/manual/en/function.floor.php
    $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
}

/*
 * http://php.net/manual/en/function.sprintf.php
 * http://php.net/manual/en/function.pow.php
 */
return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

/**
 * Récupère le gravatar correspondant en fonction de l'adresse mail.
 *
 * @author Kentucky Sato
 * @param string $email=> l'adresse mail
 * @param string $size=> Taille en pixel, valeur par defaut à 80px [ 1 - 2048 ]
 * @param string $default=> Image par defaut [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $rating=> Note Maximum [ g | pg | r | x ]
 * @param boole $img=> True => retourne l'image complete, False=> retourne l'URL
 * @param array $atts=> Optionnel, Attribut pour l'inclusion d' tag img
 * @return Chaîne contenant seulement une URL ou un tag d'image complète
 * @source http://gravatar.com/site/implement/images/php/
 */

function getGravatar($email, $size = 60, $default = 'mm', $rating = 'g', $img = false, $atts = array())
{
    $url = 'http://www.gravatar.com/avatar/';
    $url.= md5( strtolower( trim( $email ) ) );//http://www.php.net/manual/en/function.strtolower.php
    $url.= "?s=$size&d=$default&r=$rating";

    if ( $img )
    {
        $url = '<img src="' . $url . '"';
        foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }

    return $url;
}
?>
