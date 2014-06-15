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
    function disableElement() {
        var data = 'idElement='+$("#idElement").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/disableElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {console.log(msg);
                    $("#disableElem").css({
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
    <!-- formulaire pour renommer -->
    <form id="submitDisable" method="POST">
        <?php
              echo '<p><label name="chooseDestination">Are you sure you want to disable this element :</label></p>';
              echo '<input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>';
        ?>
        <p style="text-align: center;"><input type="button" onclick="disableElement();" class="btn-danger btn" value="Disable" name="disableElem" id="disableElem">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancel"></p>
    </form>
    <div id="results"></div>
<?php
}
?>
