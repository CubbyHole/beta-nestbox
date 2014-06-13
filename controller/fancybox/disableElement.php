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
            });
    }
</script>

<div id="utils_fancybox">
    <div id="imageClose">
        <img src="./content/img/icon_close_box.png" onclick="closeBoxAndReload();"/>
    </div>
    <div id="infosElement">
        <span class="glyphicon glyphicon-info-sign" onclick="elementInformation();"></span>
    </div>
</div>

<?php
if( isset($_POST['var']) && !empty($_POST['var']) )
{
    $elementManager = new ElementPdoManager();
    $refElementManager = new RefElementPdoManager();
    $userManager = new UserPdoManager();


    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());


    echo '<div id="elementInformations">
            <p><label name="description">Element information:</label></p>
            <ul>
                <li>Element name : '.$element->getName().'</li>
                <li>Current directory : '.$element->getServerPath().'</li>
                <li>Type : '.$refElement->getDescription().'</li>
                <li>Size : '.$element->getSize().' KB</li>
                <li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>
            </ul>
          </div>';


    ?>
    <!-- formulaire pour renommer -->
    <form id="submitDisable" method="POST">
        <?php echo '<input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>'; ?>
        <p style="text-align: center;"><input type="button" onclick="disableElement();" class="btn-success btn" value="Disable" name="disableElem">
        <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel"></p>
    </form>
    <div id="results"></div>
<?php
}
?>
