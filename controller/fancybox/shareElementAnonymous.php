<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 15/06/14
 * Time: 16:00
 */


header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

?>

<script type="text/javascript">
    function shareElementAnonymous() {
        var data = 'idElement='+$("#idElement").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/shareElementAnonymous.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(token)/;
                if(reg.test(msg) == true)
                {
                    $("#submitShareAnonymous").css({
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
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $userManager = new UserPdoManager();

    $element = $elementManager->findById($_GET['id']);

    if($element->getDownloadLink() == '')
    {
?>

    <!-- formulaire pour déplacer -->
    <form id="submitShareAnonymous" style="margin-top: 50px;text-align: center;" method="POST">
        <?php
        echo '<p><label name="createDownloadLink">Do you want to create a download link:</label></p>';
        echo '<input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>';
        ?>
        <p style="text-align: center;"><input type="button" onclick="shareElementAnonymous();" class="btn-success btn" value="Generate" name="shareAnonymousElem" id="shareAnonymousElem">
            <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancel"></p>
    </form>

<?php
    }
    else
    {
        echo '<div id="downloadLink"  style="margin-top: 50px;text-align: center;">';
            echo 'This element already has a download link. Copy the following link to your friend to give them chance to download this element';
            echo '<br />';
            echo '<font color="green">http://localhost/Nestbox/view/grouse.php?token='.$element->getDownloadLink().'<font>';
        echo '</div>';
    }
?>
    <div id="results"></div>
<?php
}
?>