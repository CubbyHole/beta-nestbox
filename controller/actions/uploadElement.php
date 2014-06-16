<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 12/06/14
 * Time: 19:42
 */
$path = 'C:/wamp/www/Nestbox/'.$userId.'/Tmp-'.$userId.'';
/* Si l'utilisateur décide d'uploader un element */

if(isset($_POST['destination']))
{

    $returnMoveFS = moveFSElement($userId, '/Tmp-'.$userId.'/', $_SESSION['file']['name'], $_POST['destination'], $_SESSION['file']['name']);
    if($returnMoveFS == TRUE)
    {
        $newPath = $projectRoot.'/'.$userId.$_POST['destination'];
        $elementManager = new ElementPdoManager();
        $refElementManager = new RefElementPdoManager();

        $hash = sha1_file($newPath.$_SESSION['file']['name']);
        $size = fileSize64($newPath.$_SESSION['file']['name']);
        $pathInfo = pathinfo($newPath.$_SESSION['file']['name']);
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
        echo "Your element has been successfully uploaded.";
    }
}
else
        echo "Error during upload.";
}



function bytesToSize1024($bytes, $precision = 2) {
    $unit = array('B','KB','MB');
    return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}

//récupération des informations du fichier upload pour l'afficher à l'utilisateur
if (isset($_FILES['file']) && !isset($_POST['destination'])) {
    $_SESSION['file'] = $_FILES['file'];
    $sFileName = $_FILES['file']['name'];
    $sFileType = $_FILES['file']['type'];
    $sFileSize = bytesToSize1024($_FILES['file']['size'], 1);
    $dossier = $path.'/';
    move_uploaded_file($_FILES['file']['tmp_name'], $dossier.$sFileName);

    echo <<<EOF
<div class="s">
    <p>Your file: {$sFileName} has been successfully received.</p>
    <p>Type: {$sFileType}</p>
    <p>Size: {$sFileSize}</p>
</div>
EOF;
} elseif(!isset($_FILES['file']) && !isset($_POST['destination'])) {
    echo '<div class="f">An error occurred</div>';
}