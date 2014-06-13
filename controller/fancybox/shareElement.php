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
    function shareElement() {
        var data = 'email='+$("#email").select().val()+'&idElement='+$("#idElement").select().val()+'&refRight='+$("#refRightCode").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/shareElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
//                alert(data); 
            });
    }
    function modifyShareElement(email) {
        var email = email.getAttribute("name");

        var data = 'email='+email+'&idElement='+$("#idElement").select().val()+'&refRight='+$("#refRightCode").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/modifyShareElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
//                alert(data); 
            });
    }

    function modify(email) {

       var emailUser = email.getAttribute("class");

       $("#submitShare").css({
          'display':'none'
       });

        //var mod = modifyShareElement(emailUser);
        var submitModifyShare = "<p><label name='modification'>Please submit your modification for the user "+emailUser+":</label></p>" +
                                "<p style='text-align: center;'>" +
                                    "<input type='button'  class='btn-success btn' value='Modify' name="+emailUser+" onclick='modifyShareElement(this);'> " +
                                    "<input type='button' class='btn-danger btn' onclick='parent.jQuery.fancybox.close();' value='Cancel'>" +
                                "</p>"   ;
        $("#confirmModification").empty();
        $("#confirmModification").append(submitModifyShare);
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
    $refRightManager = new RefRightPdoManager();
    $rightManager = new RightPdoManager();

    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());
    $refRightList = $refRightManager->findAll();

    echo '<div id="elementInformations" style="">';
    echo '<p><label name="description">Element information:</label></p>
                <ul>
                    <li>Element name : '.$element->getName().'</li>
                    <li>Current directory : '.$element->getServerPath().'</li>
                    <li>Type : '.$refElement->getDescription().'</li>
                    <li>Size : '.$element->getSize().' KB</li>
                    <li>Owner : '.$user->getFirstName().' '.$user->getLastName().'</li>
                </ul>
          </div>';


    $usersSharedElementList = $rightManager->find(array('idElement' => $element->getId()));
    if(is_array($usersSharedElementList) && !array_key_exists('error', $usersSharedElementList))
    {
    ?>
    <form id="modifyShare" method="POST">
        <?php
        echo '<p><label name="nameRename">List of users who you shared this element:</label></p>';
        echo '<input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>';
        foreach($usersSharedElementList as $userSharedElement)
        {
         $user = $userManager->findById($userSharedElement->getUser());
         $refRight =  $refRightManager->findById($userSharedElement->getRefRight());
         $codeRight = $refRight->getCode();
         $descriptionRefRight = $refRight->getDescription();

         echo   '<p><input type="hidden" name="emailUser" id="emailUser" value="'.$user->getEmail().'">'.$user->getFirstName().' '.$user->getLastName().'
                 <select name="refRight" id="refRightCode" class="'.$user->getEmail().'" onChange="modify(this);">
                    <option value="'.$codeRight.'">'.$descriptionRefRight.'</option>';
                foreach($refRightList as $refRight)
                {
                    if($refRight->getCode() != 10 && $refRight->getCode() != $codeRight )
                        echo '<option value="'.$refRight->getCode().'">'.$refRight->getDescription().'</option>';
                }
                echo '</select></p>';
        }
        ?>
        <br /><br />
        <div id="confirmModification"></div>
    </form>

    <?php
    }
    ?>

    <!-- formulaire pour renommer -->
    <form id="submitShare" method="POST">
        <?php
        echo '<p><label name="newEmail">Enter a new email:</label></p>';
        echo '<input type="email" name="email" id="email" placeholder="Enter a email">
              <input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>
              <select name="refRight" id="refRightCode">';
              foreach($refRightList as $refRight)
              {
                  if($refRight->getCode() != 10)
                    echo '<option value="'.$refRight->getCode().'">'.$refRight->getDescription().'</option>';
              }
        echo '</select>';
        ?>
        <br /><br />
        <p style="text-align: center;"><input type="button" onclick="shareElement();" class="btn-success btn" value="Share" name="shareElem">
             <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel"></p>
    </form>
    <div id="results"></div>
<?php
}
?>
