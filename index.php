<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'required.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="content/js/navigation.js"></script>
    <link rel=stylesheet type="text/css" href="content/css/style.css">
</head>
<body>

<?php

$USER = 'Bellod'; //en fonction de l'user connecté

?>
<div id="contenu">
<div id="actions">
<?php
    echo '<span><div id="addFolder"><img src="content/img/icon_add.png"></div>';
    echo '<div id="deleteElement"></div></span>';
?>
</div>


<!--  formulaire pour la création de dossier -->
<form id="newFolder" method="POST" style="display: none;">
    <?php if(isset($_GET['dir']))
    {
        echo ' <input type="text" name="currentDirectory" value="'.$_GET['dir'].'" style="display: none;">';
    }
    else
    {
        echo ' <input type="text" name="currentDirectory" value="/" style="display: none;">';
    }
    ?>
    <input type="text" name="nameNewFolder" placeholder="Folder name">
    <input type="submit" value="Create new folder" name="createNewFolder">
</form>


<!--  formulaire pour la suppression d'élément -->
<form id="submitDelete" method="POST" style="display: none">
    <?php if(isset($_GET['dir']))
    {
        echo '<input type="text" name="currentDirectory" value="'.$_GET['dir'].'" style="display: none;">';
    }
    else
    {
        echo ' <input type="text" name="currentDirectory" value="/" style="display: none;">';
    }
    ?>
    <input type="submit" value="Delete Element" name="deleteElem">
</form>

<br />
<!--        <!-- liste des répertoires-->
<!--        et des sous-répertoires -->
<?php
   arborescence("536749adedb5025416000029","1","/");    // owner, isOwner, dir (à voir pour le dir pour mettre la base ) + owner = idOwner en fonction de l'user qui se connecte

   if(!isset($_GET['dir']) || $_GET['dir'] == "/")
       contenu("536749adedb5025416000029","1","/");
    else
    {
       contenu("536749adedb5025416000029","1",$_GET['dir']);
    }
  ?>

<!---->
<!--    <!-- liste des fichiers -->
<!--    -->
</div>
</body>
</html>
