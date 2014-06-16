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
?>
<script type="text/javascript">
    function createFolder() {
        var data = 'nameNewFolder='+$("#nameNewFolder").select().val()+'&directory='+$("#currentDirectory").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/createFolder.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {
                    $("#createNewFolder").css({
                        'display':'none'
                    });
                    $("#cancel").css({
                        'display':'none'
                    });
                    $("#results").css({
                        'color':'green'
                    });
                }
            });
    }
</script>


<div id="utils_fancybox">
    <div id="imageClose">
        <img src="./content/img/icon_close_box.png" onclick="closeBoxAndReload();"/>
    </div>
</div>
<?php

if( isset($_POST['var']) && !empty($_POST['var']) )
{
?>
    <!--  formulaire pour la création de dossier -->
    <form id="newFolder" method="POST">
        <?php echo '<input type="hidden" name="currentDirectory" id="currentDirectory" value="'.$_GET['dir'].'" readonly>'; ?>
        <p><label name="enterAName">Enter a name for the new folder: </label></p>
        <input type="text" name="nameNewFolder" id="nameNewFolder" placeholder="Folder name">
        <br /><br />
        <input type="button" onclick="createFolder();" class="btn-success btn" value="Create new folder" name="createNewFolder" id="createNewFolder">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" name="cancel" id="cancel">
    </form>
    <div id="results"></div>
<?php
}
?>
