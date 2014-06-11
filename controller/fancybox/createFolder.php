<?php
header('Content-Type: text/html; charset=utf-8');

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 05/06/14
 * Time: 12:36
 */

if( isset($_POST['var']) && !empty($_POST['var']) )
{

    echo '<div id="elementInformations">
             <p><label name="directory">Current Directory : '.$_GET['dir'].'</label><p>
         </div>';

    ?>
    <!--  formulaire pour la création de dossier -->
    <form id="newFolder" method="POST">
        <?php echo '<input type="hidden" name="currentDirectory" value="'.$_GET['dir'].'" readonly>'; ?>
        <p><label name="enterAName">Enter a name for the new folder: </label></p>
        <input type="text" name="nameNewFolder" placeholder="Folder name">
        <br /><br />
        <input type="submit" class="btn-success btn" value="Create new folder" name="createNewFolder">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel">
    </form>
<?php
}
else
    echo "coucou";
?>
