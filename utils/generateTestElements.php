<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 04/06/14
 * Time: 23:53
 */
/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

require_once $projectRoot.'/required.php';

$elementPdoManager = new ElementPdoManager();

//Chemin des fichiers de test
$path = 'D:/supinfo courses/Projets/M1/Drive/NestBox/Fichiers de test/';

/**
 *  /Nestbox/
 *          IMG/
 *              Croquis Nesbtox.jpg
 *          code/
 *              js/
 *                 front/
 *                       js.txt
 *              css/
 *                  test.txt
 *
 *          suivi de projet.gdoc
 *          liens upload.gdoc
 *          Explorateur de fichier.gdoc
 *  /Nestbox - test/
 */

//contient 12 éléments
$insert = array(
    // dossier Nestbox à la racine
    array(
        '_id' => new MongoId('5392061f09413a1021000041'),
        'state' => 1,
        'name' => 'Nestbox',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/',
    ),
    // dossier Nestbox - test à la racine
    array(
        'state' => 1,
        'name' => 'Nestbox - test',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000074'), //dossier vide
        'serverPath' => '/'
    ),
    //dossier IMG dans /Nestbox
    array(
        'state' => 1,
        'name' => 'IMG',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/Nestbox/',
    ),
    //fichier Croquis Nesbtox dans /Nestbox/IMG/
    array(
        'state' => 1,
        'name' => 'Croquis Nestbox',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb502180800006f'), //fichier jpg
        'serverPath' => '/Nestbox/IMG/',
        'size' => fileSize64($path.'Nestbox/IMG/Croquis Nestbox.jpg'),
        'hash' => sha1_file($path.'Nestbox/IMG/Croquis Nestbox.jpg'),
        'downloadLink' => ''
    ),
    //dossier code dans /Nestbox/
    array(
        'state' => 1,
        'name' => 'code',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/Nestbox/',
    ),
    //dossier js dans /Nestbox/code/
    array(
        'state' => 1,
        'name' => 'js',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/Nestbox/code/',
    ),
    //dossier front dans /Nestbox/code/js/
    array(
        'state' => 1,
        'name' => 'front',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/Nestbox/code/js/',
    ),
    //fichier js dans /Nestbox/code/js/front/
    array(
        'state' => 1,
        'name' => 'test',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000076'), //fichier non reconnu
        'serverPath' => '/Nestbox/code/js/front/',
        'size' => fileSize64($path.'Nestbox/code/js/front/js.txt'),
        'hash' => sha1_file($path.'Nestbox/code/js/front/js.txt'),
        'downloadLink' => ''
    ),
    //dossier css dans /Nestbox/code/
    array(
        'state' => 1,
        'name' => 'css',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000075'), //dossier non vide
        'serverPath' => '/Nestbox/code/',
    ),
    //fichier test dans /Nestbox/code/css/
    array(
        'state' => 1,
        'name' => 'test',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000076'), //fichier non reconnu
        'serverPath' => '/Nestbox/code/css/',
        'size' => fileSize64($path.'Nestbox/code/css/test.txt'),
        'hash' => sha1_file($path.'Nestbox/code/css/test.txt'),
        'downloadLink' => ''
    ),
    //fichier suivi de projet dans /Nestbox/
    array(
        'state' => 1,
        'name' => 'suivi de projet',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000076'), //fichier non reconnu
        'serverPath' => '/Nestbox/',
        'size' => fileSize64($path.'Nestbox/suivi de projet.gdoc'),
        'hash' => sha1_file($path.'Nestbox/suivi de projet.gdoc'),
        'downloadLink' => ''
    ),
    //fichier liens upload dans /Nestbox/
    array(
        'state' => 1,
        'name' => 'liens upload',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000076'), //fichier non reconnu
        'serverPath' => '/Nestbox/',
        'size' => fileSize64($path.'Nestbox/liens upload.gdoc'),
        'hash' => sha1_file($path.'Nestbox/liens upload.gdoc'),
        'downloadLink' => ''
    ),
    //fichier Explorateur de fichier dans /Nestbox/
    array(
        'state' => 1,
        'name' => 'Explorateur de fichier',
        'idOwner' => new MongoId('5350ece509413aec15000033'),
        'idRefElement' => new MongoId('53639f93edb5021808000076'), //fichier non reconnu
        'serverPath' => '/Nestbox/',
        'size' => fileSize64($path.'Nestbox/Explorateur de fichier.gdoc'),
        'hash' => sha1_file($path.'Nestbox/Explorateur de fichier.gdoc'),
        'downloadLink' => ''
    )
);

foreach($insert as $element)
{
    $elementPdoManager->create($element);
}

echo 'Insertion OK';
exit();