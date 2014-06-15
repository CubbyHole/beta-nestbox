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
        <title>Nestbox - File explorer</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="content/img/logo/logoNestBox.png">

        <link rel=stylesheet type="text/css" href="content/css/style.css">
        <link rel=stylesheet type="text/css" href="content/bootstrap/css/bootstrap.css">
        <link rel=stylesheet type="text/css" href="content/bootstrap/css/bootstrap-theme.css">

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
        <script src="content/js/fancybox/shareElement.js"></script>
        <script src="content/js/fancybox/infoElement.js"></script>


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
        if(isset($_GET['dir']) && !isset($_GET['shared']))
        {
            $directoryCurrent = urlencode($_GET['dir']);
            echo '<span>
            <div id="addFolder" class="actionButton" data-toggle="tooltip" title data-original-title="Create new folder">
                <a class="addFolder fancybox.ajax " href="controller/fancybox/createFolder.php?dir='.$directoryCurrent.'">
                <img class="imgButton" src="content/img/icon_add.png">
                </a>
            </div>

            <div id="uploadElement" class="actionButton" data-toggle="tooltip" title="Upload file">
                <a class="uploadElement fancybox.ajax" href="controller/fancybox/uploadElement.php?dir='.$directoryCurrent.'">
                <img class="imgButton" src="content/img/icon_upload.png">
                </a>
            </div>';
        }
        elseif(!isset($_GET['dir']) && !isset($_GET['shared']))
        {
            echo '<span>
            <div id="addFolder" class="actionButton" data-toggle="tooltip" title data-original-title="Create new folder">
                <a class="addFolder fancybox.ajax" href="controller/fancybox/createFolder.php?dir=/" >
                <img class="imgButton" id="actionButton" src="content/img/icon_add.png">
                </a>
            </div>
            <div id="uploadElement" class="actionButton" data-toggle="tooltip" title="Upload file">
                <a class="uploadElement fancybox.ajax" href="controller/fancybox/uploadElement.php?dir=/">
                <img class="imgButton" src="content/img/icon_upload.png" title="Upload">
                </a>
            </div>';
        }
        elseif(!isset($_GET['dir']) && isset($_GET['shared']))
        {
            $check = checkRightOnCurrentDirectory($_GET['shared'], $userId);
            if($check == TRUE)
            {
                $directoryCurrent = urlencode($_GET['shared']);
                echo '<span>
                <div id="addFolder" class="actionButton" data-toggle="tooltip" title data-original-title="Create new folder">
                    <a class="addFolder fancybox.ajax " href="controller/fancybox/createFolder.php?shared='.$directoryCurrent.'">
                    <img class="imgButton" src="content/img/icon_add.png">
                    </a>
                </div>

                <div id="uploadElement" class="actionButton" data-toggle="tooltip" title="Upload file">
                    <a class="uploadElement fancybox.ajax" href="controller/fancybox/uploadElement.php?shared='.$directoryCurrent.'">
                    <img class="imgButton" src="content/img/icon_upload.png">
                    </a>
                </div>';
            }

        }


        echo '<div id="renameElement" class="actionButton" data-toggle="tooltip" title="Rename"></div>
          <div id="disableElement" class="actionButton" data-toggle="tooltip" title="Delete"></div>
          <div id="copyElement" class="actionButton" data-toggle="tooltip" title="Copy"></div>
          <div id="moveElement" class="actionButton" data-toggle="tooltip" title="Move"></div>
          <div id="downloadElement" class="actionButton" data-toggle="tooltip" title="Download file"></div>
          <div id="shareElement" class="actionButton" data-toggle="tooltip" title="Share element"></div>
          <div id="infoElement" class="actionButton" data-toggle="tooltip" title="Info element"></div></span>';

        ?>
    </div>
    <div id="fil_ariane" style="margin-left: 50px">


        <?php

        if(isset($_GET['dir']))
        {
            echo breadcrumbs($_GET['dir'], "My Files");
        }
        elseif(isset($_GET['shared']) && $_GET['shared'] != "/")
        {
            echo breadcrumbs($_GET['shared'], "Shared with me", true);
        }
        elseif(!isset($_GET['dir']) && !isset($_GET['shared']))
        {
            echo '<span onclick="clickable(this)" data-tree='.$_SERVER['PHP_SELF'].'>My Files</span>';
        }
        elseif(!isset($_GET['dir']) && isset($_GET['shared']) && $_GET['shared'] == "/")
        {
           echo '<span onclick="clickable(this)" data-tree="'.$_SERVER['PHP_SELF'].'?shared=/">Shared with me</span>';
        }
        ?>
    </div>

    <!--        <!-- liste des répertoires-->
    <!--        et des sous-répertoires -->
    <?php
    echo '<div class="content-arbo">';

    arborescence($userId,"1","/");    // owner, isOwner, dir (à voir pour le dir pour mettre la base ) + owner = idOwner en fonction de l'user qui se connecte

    if(!isset($_GET['dir']) && !isset($_GET['shared']))
        contenu($userId,"1","/");
    elseif(!isset($_GET['dir']) && isset($_GET['shared']))
    {
        contenu($userId,"1",$_GET['shared'], true);
    }
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
