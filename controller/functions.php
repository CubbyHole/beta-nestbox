<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
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

//$elementManager = new ElementPdoManager();

if(isset($_SESSION['user']))
{
    $user = unserialize($_SESSION['user']);
    $userId = $user->getId();
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
                            //under_arborescence($owner, $isOwner, $x);
                            /*if($_GET['dir'] != $x)
                                under_arborescence($owner, $isOwner, $_GET['dir']);*/
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
    echo '<div class="col-md-3 contenu">';

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
            echo '<div class="row"><span class="cell">No elements shared with you</span></div>';
        }
        else
        {
            foreach($sharedElementList as $sharedElement)
            {
                $codeRightElement = $sharedElement['right']['idRefRight']['code'];
                $codeElement = $sharedElement['idRefElement']['code'];
//                var_dump($sharedElement['_id']);
//                var_dump($sharedElement);
                switch($codeElement)
                {
                    case 4002:
                        if(isset($_GET['shared']))
                            echo '<div  onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                        <div class="row">
                                            <div id="arbo">
                                                <span class="cell">
                                                    <img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />
                                                    <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared='.$_GET['shared'].$sharedElement['name'].'/">
                                                        '.$sharedElement['name'].'
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                     </div>';
                        else
                            echo '<div  onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                        <div class="row">
                                            <div id="arbo">
                                                <span class="cell">
                                                    <img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />
                                                    <span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/">
                                                    '.$sharedElement['name'].'
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                      </div>';
                        break;
                    case 4003:
                        if(isset($_GET['dir']))
                            echo '<div  onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                        <div class="row">
                                            <div id="arbo">
                                                <span class="cell">
                                                    <img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />
                                                    <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared='.$_GET['shared'].$sharedElement['name'].'/">
                                                    '.$sharedElement['name'].'
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                      </div>';
                        else
                            echo '<div  onclick="selectFolder(this)" id="'.$sharedElement['_id'].'" data-element-type="folder" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                        <div class="row">
                                            <div id="arbo">
                                                <span class="cell">
                                                    <img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />
                                                    <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/'.$sharedElement['name'].'/">
                                                    '.$sharedElement['name'].'
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                      </div>';
                        break;
                    default:
                        echo '<div  onclick="selectFile(this)" id="'.$sharedElement['_id'].'" data-element-type="file" name="'.$sharedElement['name'].'" class="'.$codeRightElement.'">
                                    <div class="row">
                                        <div id="arbo">
                                            <span class="cell">
                                                <img src="'.$sharedElement['idRefElement']['imagePath'].'" width="18px" height="18px" />
                                                '.$sharedElement['name'].'
                                            </span>
                                        </div>
                                    </div>
                                  </div>';

                }
            };
        }
    }
    else
    {
        $refElementManager = new RefElementPdoManager();

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
                                                <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">
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
                                                <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
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
                                                <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir='.$_GET['dir'].$element->getName().'/">
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
                                                <span class="nameElement" onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$element->getName().'/">
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



/**
 * fil d'ariane
 * @author Harry Bellod
 * @param string $separator | permet de choisir un délimiteur entre les noms
 * @param string $home | nom du dossier racine dans le fil d'ariance
 * @param string $directory | shared ou dir
 * @param bool $share
 * @return string
 * http://stackoverflow.com/questions/2594211/php-simple-dynamic-breadcrumb
 * //@todo commenter et variables propres
 */
function breadcrumbs($directory, $home = 'My Files', $share = false, $separator = ' &raquo; ') {
    // This gets the REQUEST_URI (/path/to/file.php), splits the string (using '/') into an array, and then filters out any empty values
    $path = array_filter(explode('/', parse_url($directory, PHP_URL_PATH)));


    // This will build our "base URL"
    $base = $_SERVER['PHP_SELF'];

    // Initialize a temporary array with our breadcrumbs. (starting with our home page, which I'm assuming will be the base URL)
    if($share == false)
        $breadcrumbs = Array("<span class='nameElement' onclick='clickable(this)' data-tree=\"$base\">$home</span>");
    else
        $breadcrumbs = Array("<span class='nameElement' onclick='clickable(this)' data-tree=\"$base?shared=/\">$home</span>");

    // Find out the index for the last value in our path array
    $keys = array_keys($path);
    $last = end($keys);

    $fullPath = '';
    // Build the rest of the breadcrumbs
    foreach ($path AS $x => $crumb)
    {
        $fullPath .= '/'.$crumb;
        // Our "title" is the text that will be displayed (strip out .php and turn '_' into a space)
        $title = ucwords(str_replace(Array('.php', '_'), Array('', ' '), $crumb));

        // If we are not on the last index, then display an <a> tag
        if ($x != $last)
        {
            if($share == false)
                $breadcrumbs[] = "<span class='nameElement' onclick='clickable(this)' data-tree='$base?dir=$fullPath/'>$title</span>";
            else
                $breadcrumbs[] = "<span class='nameElement' onclick='clickable(this)' data-tree='$base?shared=$fullPath/'>$title</span>";
            // Otherwise, just display the title (minus)
        }
        else
            $breadcrumbs[] = $title;
    }

    // Build our temporary array (pieces of bread) into one big string :)
//            var_dump(implode($separator, $breadcrumbs));
    return implode($separator, $breadcrumbs);
}
?>
