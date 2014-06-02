<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 21/05/14
 * Time: 09:58
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

define('PATH', $_SERVER['DOCUMENT_ROOT'].'/Nestbox/');

/**
 * Crée un objet DirectoryIterator
 * @see http://www.php.net/manual/en/class.directoryiterator.php
 * @author Alban Truc
 * @param string $path
 * @since 22/05/2014
 * @return array|DirectoryIterator
 */

function createDirectoryIterator($path)
{
    if(is_dir($path) || is_file($path))
    {
        $directoryIterator = new DirectoryIterator($path);
        return $directoryIterator;
    }
    else return array('error' => 'Directory or file not found.');
}

/**
 * Renomme un élément sur le système de fichier
 * @author Alban Truc
 * @param string $idUser
 * @param string $elementPath
 * @param string $oldName
 * @param string $newName
 * @since 22/05/2014
 * @return array|DirectoryIterator
 */

function renameFSElement($idUser, $elementPath, $oldName, $newName)
{
    $fileServerPath = PATH.$idUser.$elementPath;

    $oldCompletePath = $fileServerPath.$oldName;
    $newCompletePath = $fileServerPath.$newName;

    //L'élément à renommer existe et le nom voulu n'est pas déjà pris
    if (file_exists($oldCompletePath) && !(file_exists($newCompletePath)))
    {
        $directoryIterator = createDirectoryIterator($fileServerPath);

        if ($directoryIterator instanceof DirectoryIterator)
        {
            if ($directoryIterator->current()->isWritable())
            {
                //@link http://www.php.net/manual/fr/function.rename.php
                $renameSuccessful = rename($oldCompletePath, $newCompletePath);

                if ($renameSuccessful)
                {
                    //@link http://www.php.net/manual/fr/function.is-dir.php
                    if (is_dir($newCompletePath))
                    {
                        //@link http://www.php.net/manual/fr/function.sha1-file.php
                        $newHash = sha1_file($newCompletePath);
                        return $newHash;
                    }
                    else return TRUE;
                }
                else return array('error' => 'Could not rename element '.$oldName.' in '.$elementPath);
            }
            else return array('error' => 'You need write access on '.$elementPath);
        }
        else return $directoryIterator; //message d'erreur
    }
    else return array('error' => 'Invalid source element or name of element already taken');
}

function copyFSElement($idUser, $elementName, $sourcePath, $destinationPath)
{
    $sourceFSPath = PATH.$idUser.$sourcePath;
    $destinationFSPath = PATH.$idUser.$destinationPath;

    $sourceCompletePath = $sourceFSPath.$elementName;
    $destinationCompletePath = $destinationFSPath.$elementName;

    //le dossier et élément source existent
    if(file_exists($sourceCompletePath))
    {
        $noGo = FALSE;
        //le dossier de destination n'existe pas?
        if(!(file_exists($destinationFSPath)))
        {
            //on le crée {@link http://fr2.php.net/manual/fr/function.mkdir.php}
            $mkdirSuccessful = mkdir($destinationFSPath, 0777, TRUE);

            if($mkdirSuccessful)
                $noGo = TRUE;
            else
                return array('error' => 'Destination did not exist. We tried to create it but it failed');
        }

        //le nom de l'élément de destination est-il disponible?
        if($noGo === FALSE && file_exists($destinationCompletePath))
            return array('error' => 'Element already exists for this name in destination');


        //@link http://www.php.net/manual/fr/function.copy.php
        $copySuccessful = copy($sourceFSPath.$elementName, $destinationFSPath.$elementName);


    }
    else return array('error' => 'Source path not recognized');
}