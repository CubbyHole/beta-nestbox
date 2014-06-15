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
        var data = 'email='+$("#email").select().val()+'&idElement='+$("#idElement").select().val()+'&refRight='+$("#refRightCodeShare").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/shareElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {
                    $("#shareElem").css({
                        'display':'none'
                    });
                    $("#cancelShare").css({
                        'display':'none'
                    });
                    $("#modifyShare").css({
                        'display':'none'
                    });
                    $("#results").css({
                        'color':'green'
                    });
                }
            });
    }
    function modifyShareElement(email) {
        var emailUser = email.getAttribute("name");
        var idUser = email.getAttribute("id");

        var data = 'email='+emailUser+'&idElement='+$("#idElement").select().val()+'&refRight='+$("#refRightCode").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/modifyShareElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {
                    $("#submitModifyRight").css({
                        'display':'none'
                    })
                    $("#results").css({
                        'color':'green'
                    });
                }
            });
    }

    function disableShareElement(idUser)
    {
        var idUser = idUser.getAttribute("id");

        var data = 'idUser='+idUser+'&idElement='+$("#idElement").select().val()+'&idOwner='+$("#idOwner").select().val();
        jQuery.ajax({
            type: 'POST',
            url: './controller/actions/disableShareElement.php',
            data: data
        }).success(function(msg){
                $("#results").html(msg);
                var reg = /(successfully)/;
                if(reg.test(msg) == true)
                {
                    $("#submitDisableRight").css({
                        'display':'none'
                    })
                    $("#results").css({
                        'color':'green'
                    });
                }
            });
    }

    function modifyRight(email) {

       var emailUser = email.getAttribute("class");
        var idUser = email.getAttribute("name");

       $("#submitShare").css({
          'display':'none'
       });

       $("#confirmModification").css({
           'display':'inline'
       });

        $("#confirmDisable").css({
            'display':'none'
        });

        var submitModifyRight = "<div id='submitModifyRight'><label name='modification'>Do you want to change the right of "+emailUser+":</label></p>" +
            "<p style='text-align: center;'>" +
            "<input type='button'  class='btn-success btn' value='Modify' name="+emailUser+" id="+idUser+" onclick='modifyShareElement(this);'> " +
            "<input type='button' class='btn-danger btn' onclick='parent.jQuery.fancybox.close();' value='Cancel' id='cancelModifyShare'>" +
            "</div>"   ;
        $("#confirmModification").empty();
        $("#confirmModification").append(submitModifyRight);
    }

    function disableRight(id) {

        var emailUser = id.getAttribute("class");
        var idUser = id.getAttribute("id");

        $("#submitShare").css({
            'display':'none'
        });

        $("#confirmModification").css({
            'display':'none'
        });

        $("#confirmDisable").css({
            'display':'inline'
        });

        //var mod = modifyShareElement(emailUser);
        var submitModifyRight = "<div id='submitDisableRight'><label name='modification'>Do you really want to disable the right for "+emailUser+":</label>" +
            "<p style='text-align: center;'>" +
            "<input type='button'  class='btn-danger btn' value='Disable' id="+idUser+" onclick='disableShareElement(this);'> " +
            "<input type='button' class='btn-danger btn' onclick='parent.jQuery.fancybox.close();' value='Cancel' id='cancelDisableShare'></p>" +
            "</div>";
        $("#confirmDisable").empty();
        $("#confirmDisable").append(submitModifyRight);
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
    $refRightManager = new RefRightPdoManager();
    $rightManager = new RightPdoManager();

    $element = $elementManager->findById($_GET['id']);
    $refElement = $refElementManager->findById($element->getRefElement());
    $user = $userManager->findById($element->getOwner());
    $refRightList = $refRightManager->findAll();

    $rightCriteria = array('idElement' => $element->getId(), 'state' => 1);
    $usersSharedElementList = $rightManager->find($rightCriteria);

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

             echo   '<div id="shareInformation">
                     <input type="hidden" name="idOwner" id="idOwner" value="'.$userId.'">
                     <input type="hidden" name="emailUser" id="emailUser" value="'.$user->getEmail().'">
                     <label style="width:150px;">'.$user->getFirstName().' '.$user->getLastName().'</label>
                     <select name="'.$user->getId().'" id="refRightCode" class="'.$user->getEmail().'" onChange="modifyRight(this);">
                        <option value="'.$codeRight.'">'.$descriptionRefRight.'</option>';
                    foreach($refRightList as $refRight)
                    {
                        if($refRight->getCode() != 10 && $refRight->getCode() != $codeRight )
                            echo '<option value="'.$refRight->getCode().'">'.$refRight->getDescription().'</option>';
                    }
             echo   '</select> <img src="./content/img/icon_close_box.png" class="'.$user->getEmail().'" id="'.$user->getId().'" onclick="disableRight(this)" title="Disable right"/></div>';
             echo '<hr>';

            }
            ?>
            <br /><br />
            <div id="confirmModification"></div>
            <div id="confirmDisable"></div>
        </form>
    <?php
    }
    ?>

    <!-- formulaire pour renommer -->
    <form id="submitShare" method="POST">
        <?php
        echo '<p><label name="newEmail">Enter an user email and select a right to share this element with him:</label></p>';
        echo '<input type="email" name="email" id="email" placeholder="Enter a email">
              <input type="hidden" name="idElement" id="idElement" value="'.$_GET['id'].'" read-only>
              <select name="refRight" id="refRightCodeShare">';
              foreach($refRightList as $refRight)
              {
                  if($refRight->getCode() != 10)
                    echo '<option value="'.$refRight->getCode().'">'.$refRight->getDescription().'</option>';
              }
        echo '</select>';
        ?>
        <br /><br />
        <p style="text-align: center;"><input type="button" onclick="shareElement();" class="btn-success btn" value="Share" name="shareElem" id="shareElem">
             <input type="button" class="btn-danger btn" onclick="parent.jQuery.fancybox.close();" value="Cancel" id="cancelShare"></p>
    </form>
    <div id="results"></div>
<?php
}
?>
