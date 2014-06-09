<?php
//session_start();
header('Content-Type: text/html; charset=utf-8');


require_once 'required.php';
//unset($_SESSION['user']);

if(isset($_SESSION['user']))
{
$user = unserialize($_SESSION['user']);
$userId = $user->getId();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nestbox - File explore23r</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel=stylesheet type="text/css" href="content/css/style.css">
    <link rel=stylesheet type="text/css" href="content/bootstrap/css/bootstrap.css">
    <link rel=stylesheet type="text/css" href="content/bootstrap/css/bootstrap-theme.css">
    <link rel="shortcut icon" href="content/img/logo/logoNestBox.png">

    <!-- Add fancyBox -->
    <link rel="stylesheet" href="content/css/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />

    <!-- Optionally add helpers - button, thumbnail and/or media -->
    <link rel="stylesheet" href="content/css/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />

    <link rel="stylesheet" href="content/css/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />

    <script src="content/js/jquery.js"></script>
    <script src="content/js/navigation.js"></script>
    <script src="content/js/script.js"></script>

    <!-- Add mousewheel plugin (this is optional) -->
    <script type="text/javascript" src="content/js/jquery.mousewheel-3.0.6.pack.js"></script>

    <!-- Fichiers js fancybox actions-->
    <script  src="content/js/fancybox/createFolder.js"></script>
    <script  src="content/js/fancybox/renameElement.js"></script>
    <script  src="content/js/fancybox/disableElement.js"></script>
    <script  src="content/js/fancybox/copyElement.js"></script>
    <script  src="content/js/fancybox/moveElement.js"></script>
    <script  src="content/js/fancybox/downloadElement.js"></script>
    <script  src="content/js/fancybox/uploadElement.js"></script>


    <script src="content/js/dropfile.js"></script>
    <!-- FancyBox-->
    <script type="text/javascript" src="content/js/jquery.fancybox.pack.js?v=2.1.5"></script>
    <script type="text/javascript" src="content/js/jquery.fancybox-buttons.js?v=1.0.5"></script>
    <script type="text/javascript" src="content/js/jquery.fancybox-media.js?v=1.0.6"></script>
    <script type="text/javascript" src="content/js/jquery.fancybox-thumbs.js?v=1.0.7"></script>
    <script src="content/bootstrap/js/bootstrap.min.js"></script>


</head>

<?php
include 'header/menu.php';
?>

<div id="contenu">
    <div id="actions">
        <?php

        if(isset($_GET['dir']))
        {
            $directoryCurrent = urlencode($_GET['dir']);
            echo '<span>
            <div id="addFolder" class="actionButton" data-toggle="tooltip" title data-original-title="Create new folder">
                <a class="addFolder fancybox.ajax " href="controller/fancybox/createFolder.php?dir='.$directoryCurrent.'">
                <img src="content/img/icon_add.png">
                </a>
            </div>

            <div id="uploadElement" class="actionButton" data-toggle="tooltip" title="Upload file">
                <a class="uploadElement fancybox.ajax" href="controller/fancybox/uploadElement.php?dir='.$directoryCurrent.'">
                <img src="content/img/icon_upload.png">
                </a>
            </div>';
        }
        else
        {
            echo '<span>
            <div id="addFolder" class="actionButton" data-toggle="tooltip" title data-original-title="Create new folder">
                <a class="addFolder fancybox.ajax" href="controller/fancybox/createFolder.php?dir=/" >
                <img  id="actionButton" src="content/img/icon_add.png">
                </a>
            </div>
            <div id="uploadElement" class="actionButton" data-toggle="tooltip" title="Upload file">
                <a class="uploadElement fancybox.ajax" href="controller/fancybox/uploadElement.php?dir=/">
                <img src="content/img/icon_upload.png" title="Upload">
                </a>
            </div>';
        }


        echo '<div id="renameElement" class="actionButton" data-toggle="tooltip" title="Rename"></div>
          <div id="disableElement" class="actionButton" data-toggle="tooltip" title="Delete"></div>
          <div id="copyElement" class="actionButton" data-toggle="tooltip" title="Copy"></div>
          <div id="moveElement" class="actionButton" data-toggle="tooltip" title="Move"></div>
          <div id="downloadElement" class="actionButton" data-toggle="tooltip" title="Download file"></div></span>';

        ?>
    </div>
    <div id="fil_ariane" style="margin-top: 50px">
        <?php
        if(isset($_GET['dir']))
        {
            $explode = explode('/', $_GET['dir']);
            $d = $explode[sizeof($explode)-2];
            var_dump($d);
            echo '<span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'">
                    Root
                  </span>';
            echo '/';
            echo '<span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$d.'/">
                    '.$d.'
                  </span>';
            echo '/';
            echo '<span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?dir=/'.$d.'/">
                    '.$d.'
                  </span>';
        }
        else
        {
            echo '<span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'">
                    Root
                  </span>';
        }
        ?>
    </div>

    <!--        <!-- liste des répertoires-->
    <!--        et des sous-répertoires -->
    <?php
    echo '<div class="content-arbo">';
        arborescence($userId,"1","/");    // owner, isOwner, dir (à voir pour le dir pour mettre la base ) + owner = idOwner en fonction de l'user qui se connecte

        if(!isset($_GET['dir']) || $_GET['dir'] == "/")
            contenu($userId,"1","/");
        else
        {
            contenu($userId,"1",$_GET['dir']);
        }
    echo '</div>';
    }
    else
    {
        header('Location: /Nestbox/view/login.php');
    }
    ?>

    <!---->
    <!--    <!-- liste des fichiers -->
    <!--    -->

</div>
<?php include 'footer/footer.php'; ?>
