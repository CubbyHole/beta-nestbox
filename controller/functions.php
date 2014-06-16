<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 18/04/14
 * Time: 10:18
 */


if(isset($_SESSION['user']))
{
    $user = unserialize($_SESSION['user']);
    $userId = $user->getId();
}

//soumission du formulaire de download
if(isset($_POST['downloadElem']) && isset($_POST['idElement']))
{
    userDownload($userId, $_POST['idElement']);
}


// soumission du formulaire de download anonyme
if(isset($_POST['downloadAnonymousElem']) && isset($_POST['idElement']))
{
    userDownload($_POST['owner'], $_POST['idElement']);
}



// soumission du formulaire d'upload basique
if(isset($_POST['uploadBasicElem']) && isset($_FILES['fileExplorer']))
{
    $path = 'C:/wamp/www/Nestbox/'.$userId.'/Tmp-'.$userId.'';
    $folder = $path.'/';
    $file = $_FILES['fileExplorer']['name'];
    move_uploaded_file($_FILES['fileExplorer']['tmp_name'], $folder.$file);

    $returnMoveFS = moveFSElement($userId, '/Tmp-'.$userId.'/', $_FILES['fileExplorer']['name'], $_POST['destination'], $_FILES['fileExplorer']['name']);
    if($returnMoveFS == TRUE)
    {
        $newPath = $projectRoot.'/'.$userId.$_POST['destination'];
        $elementManager = new ElementPdoManager();
        $refElementManager = new RefElementPdoManager();

        $hash = sha1_file($newPath.$_FILES['fileExplorer']['name']);
        $size = fileSize64($newPath.$_FILES['fileExplorer']['name']);
        $pathInfo = pathinfo($newPath.$_FILES['fileExplorer']['name']);
        $refElement = $refElementManager->findOne(array('extension' => '.'.$pathInfo['extension']));

        if(is_array($refElement) && array_key_exists('error', $refElement))
            echo "Extension not found";
        else
        {
            $idRefElement = $refElement->getId();
            $criteria = array(
                'downloadLink' => '',
                'idOwner' => $userId,
                'idRefElement' => $idRefElement,
                'name' => $pathInfo['filename'],
                'state' => 1,
                'hash' => $hash,
                'serverPath' => $_POST['destination'],
                'size' => $size
            );

            $createElement = $elementManager->create($criteria);
            updateFolderStatus($_POST['destination'], $userId);
            header('Location : '.$_SERVER['PHP_SELF'].'');
        }
    }
    else
        echo "Error during upload.";
}




/**
 * @param $owner
 * @param $isOwner
 * @param $dir
 * todo modifier les paramètres de la fonction
 */
function arborescence($owner, $isOwner, $dir)
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();



    echo "<!-- Arborescence -->";
    echo '<div class="col-md-3 arborescence">';

    if((!isset($_GET['dir']) && !isset($_GET['shared'])))
        echo '
                <div class="row" data-element-type="folder">
                    <div id="arbo">
                        <span class="cell">
                            <img src="content/img/icon_dir_open.png" width="18px" height="18px" />
                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'">My Files</span>
                        </span>
                    </div>
                </div>';
    else
        echo '<div class="row">
                <div id="arbo">
                    <span class="cell">
                        <img src="content/img/icon_dir_close.png" width="18px" height="18px" />
                        <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'">My Files</span>
                    </span>
                </div>
              </div>';

    if((!isset($_GET['dir']) && !isset($_GET['shared'])) || isset($_GET['dir']))
    {
        $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);
        if(is_array($elementList) && !array_key_exists('error', $elementList))
        {
            foreach($elementList as $element)
            {
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
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
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
                                            <span class="nameElement" onclick="clickable(this)"  data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
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
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                                '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                  </div>';
                            $x = "/".$element->getName()."/";
                        }
                        else
                            echo '<div class="row" data-element-type="folder">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="content/img/icon_dir_close.png" width="18px" height="18px" style="margin-left:15px;"/>
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
                                            '.$element->getName().'
                                            </span>
                                        </span>
                                    </div>
                                  </div>';
                        break;
                }
            };
        }
    }

    if(!isset($_GET['shared']))
        echo '
                <div class="row" data-element-type="folder">
                    <div id="arbo">
                        <span class="cell">
                            <img src="content/img/icon_dir_close.png" width="18px" height="18px" />
                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/">Shared with me</span>
                        </span>
                    </div>
                </div>';
    elseif(isset($_GET['shared']) && $_GET['shared'] == "/")
        echo '<div class="row">
                <div id="arbo">
                    <span class="cell">
                        <img src="content/img/icon_dir_open.png" width="18px" height="18px" />
                        <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/">Shared with me</span>
                    </span>
                </div>
              </div>';
    else
        echo '<div class="row">
                <div id="arbo">
                    <span class="cell">
                        <img src="content/img/icon_dir_close.png" width="18px" height="18px" />
                        <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/">Shared with me</span>
                    </span>
                </div>
              </div>';

    if((!isset($_GET['dir']) && isset($_GET['shared'])))
    {
        $options = array('returnUserInfo' => true, 'returnRefElementInfo' => true, 'returnRightInfo' => true, 'returnRefRightInfo' => true);
        /**
         * ['idOwner'] => infos de l'user propriétaire
         * ['idRefElement'] => infos refElement de l'élément
         * ['right'] => infos droits appliqué à l'élément
         * ['right']['idRefRight'] => infos du refRight du droit
         *
         */
        $sharedElementList = $elementManager->findSharedElements($owner, $dir, $options);
        if(is_array($sharedElementList) && !array_key_exists('error', $sharedElementList))
        {
            foreach($sharedElementList as $sharedElement)
            {
                $codeRefRightElement = $sharedElement['right']['idRefRight']['code'];
                $codeRefElement = $sharedElement['idRefElement']['code'];

                switch($codeRefElement)
                {
                    case 4002: // dossier vide
                        if(isset ($_GET['shared']) && $_GET['shared'] == "/".$sharedElement['name']."/")
                        {
                            echo '<div class="row" data-element-type="folder">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/>
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/" style="cursor:pointer;">
                                                '.$sharedElement['name'].'
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
                                            <span class="nameElement" onclick="clickable(this)"  data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/">
                                                '.$sharedElement['name'].'
                                            </span>
                                        </span>
                                    </div>
                                  </div>';
                        break;
                    case 4003: // dossier non vide
                        if(isset ($_GET['shared']) && (preg_match('#'.$sharedElement['name'].'/#', '#'.$_GET['shared'].'/#')))
                        {
                            echo '<div class="row" data-element-type="folder">
                                    <div id="arbo">
                                        <span class="cell">
                                            <img src="content/img/icon_dir_open.png" width="18px" height="18px" style="margin-left:15px;"/>
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/">
                                                '.$sharedElement['name'].'
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
                                            <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/">
                                            '.$sharedElement['name'].'
                                            </span>
                                        </span>
                                    </div>
                                  </div>';
                        break;
                }
            };
        }
    }
    echo '</div>';//fin div container
}


function contenu($owner,$isOwner,$dir, $sharedElements = false)
{
    echo "<!-- Contenu -->";
    echo '<div class="col-md-7 contenu">';


    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="infoTitle" style="width:400px;">Name</th>';
    echo '<th class="infoTitle" style="width:400px;">Size(Kb)</th>';
    echo '<th class="infoTitle" style="width:400px;">Extension</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    $elementManager = new ElementPdoManager();
    if($sharedElements == true)
    {
        $options = array('returnUserInfo' => true, 'returnRefElementInfo' => true, 'returnRightInfo' => true, 'returnRefRightInfo' => true);
        /**
         * ['idOwner'] => infos de l'user propriétaire
         * ['idRefElement'] => infos refElement de l'élément
         * ['right'] => infos droits appliqué à l'élément
         * ['right']['idRefRight'] => infos du refRight du droit
         *
         */

        $sharedElementList = $elementManager->findSharedElements($owner, $dir, $options);
        if(is_array($sharedElementList) && array_key_exists('error', $sharedElementList))
        {
            echo '<td><span class="cell">No elements shared with you</span></td>';
        }
        else
        {

            foreach($sharedElementList as $sharedElement)
            {
                $extensionSharedElement = str_replace('.','',$sharedElement['idRefElement']['extension']);
                $codeRightElement = $sharedElement['right']['idRefRight']['code'];
                $codeElement = $sharedElement['idRefElement']['code'];
                switch($codeElement)
                {

                    case 4002:
                        echo '<tr onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared='.$_GET['shared'].$sharedElement['name'].'/"><img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />'.$sharedElement['name'].'</span></td>
                                <td ></td>
                                <td ></td>
                               </tr>';
                        break;
                    case 4003:
                        echo '<tr onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared='.$_GET['shared'].$sharedElement['name'].'/"><img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />'.$sharedElement['name'].'</span></td>
                                <td ></td>
                                <td ></td>
                               </tr>';
                        break;
                    default:
                        echo '<tr onclick="selectFile(this)" id="'.$sharedElement['_id'].'" data-element-type="file" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                <td ><span class="cell" ><img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />'.$sharedElement['name'].'</span></td>
                                <td ><span class="cell" >'.$sharedElement['size'].' KB</span></td>
                                <td ><span class="cell" >'.$extensionSharedElement.'</span></td>
                               </tr>';
                }
            }

        }
    }
    else
    {
        $elementList = $elementManager->returnElementsDetails($owner, $isOwner, $dir);

        if(is_array($elementList) && array_key_exists('error', $elementList))
        {
            echo '<td><span class="cell">Empty folder</span></td>';
        }
        else
        {

            foreach($elementList as $element)
            {
                $extensionElement = str_replace('.','',$element->getRefElement()->getExtension());
                $codeElement = $element->getRefElement()->getCode();

                switch($codeElement)
                {
                    case 4002:
                        if(isset($_GET['dir']))
                            echo '<tr onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'>
                                     <td ><span class="cell"></span></td>
                                     <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></td>
                                     <td></td>
                                     <td></td>
                                 </tr>';
                        else
                            echo '<tr onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'>
                                     <td ><span class="cell"></span></td>
                                     <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></td>
                                     <td></td>
                                     <td></td>
                                 </tr>';
                        break;
                    case 4003:
                        if(isset($_GET['dir']))
                            echo '<tr onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'>
                                     <td ><span class="cell"></span></td>
                                     <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></td>
                                     <td></td>
                                     <td></td>
                                 </tr>';
                        else
                            echo '<tr onclick="selectFolder(this)" id="'.$element->getId().'" data-element-type="folder" name="'.$element->getName().'>
                                     <td ><span class="cell"></span></td>
                                     <td ><span class="cell nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/"><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></td>
                                     <td></td>
                                     <td></td>
                                 </tr>';
                        break;
                    default:
                        echo '<tr onclick="selectFile(this)" id="'.$element->getId().'" data-element-type="file" name="'.$element->getName().'>
                                <td ><span class="cell"></span></td>
                                <td ><span class="cell" ><img src="'.$element->getRefElement()->getImagePath().'" width="18px" height="18px" />'.$element->getName().'</span></td>
                                <td ><span class="cell" >'.$element->getSize().' KB</span></td>
                                <td ><span class="cell" >'.$extensionElement.'</span></td>
                               </tr>';
                }
            }
        }
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
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



/**
 * fil d'ariane
 * @author Harry Bellod
 * @param string $separator | permet de choisir un délimiteur entre les noms
 * @param string $home | nom du dossier racine dans le fil d'ariance
 * @param string $directory | shared ou dir
 * @param bool $share
 * @return string
 * http://stackoverflow.com/questions/2594211/php-simple-dynamic-breadcrumb
 */
function breadcrumbs($directory, $home = 'My Files', $share = false, $separator = ' &raquo; ') {

    // on enlève les '/' de la chaine de caractère directory passée en paramètre avec l'explode, puis on supprime les index du tableau qui sont égaux à ''
    $path = array_filter(explode('/', parse_url($directory, PHP_URL_PATH)));


    // on définit la racine
    $base = $_SERVER['PHP_SELF'];

    // on vérifie la valeur du paramètre $share, il permet de distinguer la partie navigation standard de la partie navigation dans les dossiers partagés
    if($share == false)
        $breadcrumbs = Array("<span class='nameElement' onclick='clickable(this)' data-tree=\"$base\">$home</span>");
    else
        $breadcrumbs = Array("<span class='nameElement' onclick='clickable(this)' data-tree=\"$base?shared=/\">$home</span>");

    // on récupère l'index de la dernière valeur du tableau path
    $keys = array_keys($path);
    $lastIndex = end($keys);

    $fullPath = '';
    // on parcours le tableau
    foreach ($path AS $x => $crumb)
    {
        $fullPath .= '/'.$crumb;

        // Lors de l'affichage du fil d'ariane, on utilise des liens sauf pour le dernier index pour pouvoir naviguer en arrière
        if ($x != $lastIndex)
        {
            // encore une fois on distingue les deux navigations possibles
            if($share == false) // navigation de base
                $breadcrumbs[] = "<span class='nameElement' onclick='clickable(this)' data-tree='$base?dir=$fullPath/'>$crumb</span>";
            else // navigation dans les dossiers partagés
                $breadcrumbs[] = "<span class='nameElement' onclick='clickable(this)' data-tree='$base?shared=$fullPath/'>$crumb</span>";

        }
        else
            $breadcrumbs[] = $crumb;
    }

    // Build our temporary array (pieces of bread) into one big string :)
//            var_dump(implode($separator, $breadcrumbs));
    return implode($separator, $breadcrumbs);
}


/** Permet de vérifier si l'utilisateur possède les droits maximums dans le dossier courant
 * @author Harry Bellod
 * @param $serverPath | path actuel
 * @param $idUser | id de l'user connecté
 * @return bool | true si l'user à les droits maximums (écriture/lecture)
 */
function checkRightOnCurrentDirectory($serverPath, $idUser)
{
    $elementManager = new ElementPdoManager();
    $rightManager = new RightPdoManager();
    $refRightManager = new RefRightPdoManager();

    if($serverPath != "/")
    {
        // on récupère le nom du dossier ou l'on se trouve
        $explode = explode("/", $serverPath);
        $currentDirectory = $explode[sizeof($explode)-2];

        // on récupère son serverPath
        $pattern = "#".$currentDirectory."/#";
        $path = preg_replace($pattern, "", $serverPath, 1);

        $criteria = array('name' => $currentDirectory, 'serverPath' => $path, 'state' => 1);
        $element = $elementManager->findOne($criteria);


        $rightCriteria = array('idElement' => $element->getId(), 'idUser' => $idUser);
        $right = $rightManager->findOne($rightCriteria);

        $refRight = $refRightManager->findById($right->getRefRight());

        //si l'utilisateur n'a que les droits de lecture alors return false, sinon true
        if($refRight->getCode() == '01')
            return false;
        else
            return true;
    }
}
?>
