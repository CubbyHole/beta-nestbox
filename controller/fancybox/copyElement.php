<?php
/**
 * Created by PhpStorm.
 * User: Harry
 * Date: 05/06/14
 * Time: 12:36
 */

header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';


if(isset($_SESSION['userId']))
    $userId = $_SESSION['userId'];
?>


<script type="text/javascript">
    function copyElement() {
        var data = 'destination='+$("#destination").select().val()+'&idElement='+$("#idElement").select().val()+'&keepRights='+$('input[name=keepRights]:checkbox:checked').val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/copyElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {
                    $("#copyElem").css({
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

    $refElementEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4002',
        'state' => 1
    ));
    if($refElementEmptyDirectory instanceof RefElement)
        $idRefElementEmptyDirectory = $refElementEmptyDirectory->getId();
    else
        return $refElementEmptyDirectory;

    $refElementNotEmptyDirectory = $refElementManager->findOne(array(
        'code' => '4003',
        'state' => 1
    ));
    if($refElementNotEmptyDirectory instanceof RefElement)
        $idRefElementNotEmptyDirectory = $refElementNotEmptyDirectory->getId();
    else
        return $refElementNotEmptyDirectory;


    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());

function cmp($a,$b)
{
    return strcmp($a, $b);
}
?>

<!-- formulaire pour copier -->
<form id="submitCopy" method="POST">
    <?php
        echo '<p><label name="chooseDestination">Select a destination:</label></p>';
        echo '<input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>
              <select name="destination" id="destination">
              <option>/</option>';

                $elementList = $elementManager->find(array(
                    'serverPath'=> new MongoRegex("/^/"),
                    'state' => 1,
                    'idOwner' => $userId,
                    '$or' => array(
                        array('idRefElement' => $idRefElementEmptyDirectory),
                        array('idRefElement' => $idRefElementNotEmptyDirectory)
                    )
                ),
                array(
                  'serverPath' => TRUE,
                  'name' => TRUE,
                  '_id' => FALSE
                ));

                    $f = function($array){return $array['serverPath'].$array['name'];};

                    $elementList = array_map($f, $elementList);
                    $result = array_unique($elementList);

                    usort($result, "cmp");
                foreach($result as $elem)
                {
                    if($elem != '/'.$element->getName())
                        echo '<option>'.$elem.'/</option>';
                }
        echo '</select>';

    ?>
    <br /><br />
    <p><input type="checkbox" name="keepRights" value="keepRights" id="keepRights" checked><label> Check this box if you want to keep the rights applied on its sub-elements ?</label></p>
    <p style="text-align: center;"><input type="button" onclick="copyElement();" class="btn-success btn" value="Copy" name="copyElem" id="copyElem">
    <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancel"></p>
</form>
<div id="results"></div>
<?php
}?>