/**
 * Created by Harry on 16/04/14.
 */

$(document).ready(function(){
    $("#arbo a").click(function(e){
        e.preventDefault();
        page = $(this).attr("href");
        console.log(page);
        $.ajax({
            url: "../.."+page,
            cache:false,
            success: function(html){
                afficher(html);
            },
            error:function(XMLHttpRequest, textStatus, errorThrown){
                alert(textStatus);
            }
        })
        return false;
    })
});

function afficher(data){
     $("#contenu").empty();
     $("#contenu").append(data);
}


$(document).ready(function(){
    $("div[data-element-type='file']").click(function(){
        $("div[data-element-type='file']").removeClass('active');
        $("div[data-element-type='folder']").removeClass('active');
        $(this).addClass('active');

        var elementId = this.id;
        var elementName = this.name;
        var deleteAction = "<div id='elementToDelete' name="+ elementId +" ><img src='content/img/icon_delete.png'></div>";
       //$("#actions").empty();
        hideNewFolderForm();
        hideDeleteForm();
        $("#deleteElement").empty();
        $("#deleteElement").append(deleteAction);
    });

    $("div[data-element-type='folder']").click(function(){
            $("div[data-element-type='file']").removeClass('active');
            $("div[data-element-type='folder']").removeClass('active');
            $(this).addClass('active');

        var elementId = this.id;
        var elementName = this.name;
        var deleteAction = "<div id='elementToDelete' name="+ elementId +" ><img src='content/img/icon_delete.png'></div>";

        hideNewFolderForm();
        hideDeleteForm();
        $("#deleteElement").empty();
        $("#deleteElement").append(deleteAction);
    });

    $("#addFolder").click(function(){
       hideDeleteForm();
       showNewFolderForm();
    });

    $("#deleteElement").click(function()
    {
        var elementToDelete = document.getElementById("elementToDelete");
        var nameElementToDelete = elementToDelete.getAttribute("name");

        var nameElement = "<input type='text' name='elementToDelete' value="+ nameElementToDelete +" readonly='true'>";
        hideNewFolderForm();

        var input = document.getElementById("submitDelete");
        input.removeChild(input.lastChild);

        $("#submitDelete").append(nameElement);
        showDeleteForm();
    });


    function hideNewFolderForm(){
        $("#newFolder").css({
            'display':'none'
        });
    }
    function showNewFolderForm(){
        $("#newFolder").css({
            'display':'inline'
        });
    }
    function hideDeleteForm(){
        $("#submitDelete").css({
            'display':'none'
        });
    }
    function showDeleteForm(){
        $("#submitDelete").css({
            'display':'inline'
        });
    }
//    var submitButtonDelete = document.getElementById('delElem');
//
//    $(submitButtonDelete).click(function()
//    {
//        var elementToDelete = document.getElementById("elementToDelete");
//        var nameElementToDelete = elementToDelete.getAttribute("name");
//        alert (nameElementToDelete);
//            $.ajax({
//                type: "POST",
//                url: "../Nestbox/controller/functions.php",
//                data: {nameElement : nameElementToDelete}
//            });
//        alert('ajax ok');
//    });
});


/*function folderExist(data)
{
    $("#newFolder").append(data);
}*/
