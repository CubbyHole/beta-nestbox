/**
 * Created by Harry on 16/04/14.
 */


function clickable (div)
{
    page = div.getAttribute("data-tree");
    console.log(page);
    $.ajax({
        url: "../.."+page,
        cache:false,
        success: function(html)
        {
            afficher(html.split("<body>")[1].split("</body>")[0]);
        },
        error:function(XMLHttpRequest, textStatus, errorThrown)
        {
            alert(textStatus);
        }
    })
    return false;
}

function afficher(data){
    $("body").empty();
    $("body").append(data);
}


function selectFile(file){
    var elementId = file.getAttribute("id");
    var elementName = file.getAttribute("name");
    var elementRight = file.getAttribute("class");

    $("div[data-element-type='file']").css({
        'backgroundColor':'transparent'
    });
    $("div[data-element-type='folder']").css({
        'backgroundColor':'transparent'
    });
    $("#".concat(elementId)).css({
        'backgroundColor':'#EEE'
    });

    var renameAction = "<div id='elementToRename' name="+ elementName +" class="+ elementId +"><a class='renameElement fancybox.ajax' href='controller/fancybox/renameElement.php?id="+ elementId +"'><img class='imgButton' src='content/img/icon_modify.png' title='Rename'></a></div>";
    var deleteAction = "<div id='elementToDisable' name="+ elementName +" class="+ elementId +"><a class='disableElement fancybox.ajax' href='controller/fancybox/disableElement.php?id="+ elementId +"'><img class='imgButton' src='content/img/icon_delete.png' title='Delete'></a></div>";
    var copyAction = "<div id='elementToCopy' name="+ elementName +" class="+ elementId +"><a class='copyElement fancybox.ajax' href='controller/fancybox/copyElement.php?id="+elementId+"'><img class='imgButton' src='content/img/icon_copy.png' title='Copy'></a></div>";
    var moveAction = "<div id='elementToMove' name="+elementName+" class="+ elementId +"><a class='moveElement fancybox.ajax' href='controller/fancybox/moveElement.php?id="+elementId+"'><img class='imgButton' src='content/img/icon_cut.png' title='Cut'></a></div>";
    var downloadAction = "<div id='elementToDownload' name="+elementName+" class="+elementId+"><a class='downloadElement fancybox.ajax' href='controller/fancybox/downloadElement.php?id="+elementId+"'><img class='imgButton' src='content/img/icon_download.png' title='Download'></a></div>";

    $("#renameElement").empty();
    $("#disableElement").empty();
    $("#copyElement").empty();
    $("#moveElement").empty();
    $("#downloadElement").empty();
    $("#downloadElement").append(downloadAction);

    if(elementRight == 11 || elementRight == null)
    {
        $("#renameElement").append(renameAction);
        $("#disableElement").append(deleteAction);
        $("#copyElement").append(copyAction);
        $("#moveElement").append(moveAction);
    }

}

function hoverFile(file)
{
    var elementId = file.getAttribute("id");
    var elementName = file.getAttribute("name");
    var elementRight = file.getAttribute("class");

    $("div[data-element-type='file']").mouseenter(function() {
        $(this).css("background", "#EEE");
    }).mouseleave(function() {
        $(this).css("background", "white");
    });
}

function selectFolder(folder){

    var elementId = folder.getAttribute("id");
    var elementName = folder.getAttribute("name");
    var elementRight = folder.getAttribute("class");

    $("div[data-element-type='file']").css({
        'backgroundColor':'transparent'
    });
    $("div[data-element-type='folder']").css({
        'backgroundColor':'transparent'
    });

    $("#".concat(elementId)).css({
        'backgroundColor':'#EEE'
    });

    var renameAction = "<div id='elementToRename' name="+ elementName +" class="+ elementId +"><a class='renameElement fancybox.ajax' href='controller/fancybox/renameElement.php?id="+ elementId +"'><img class='imgButton' src='content/img/icon_modify.png' title='Rename'></a></div>";
    var deleteAction = "<div id='elementToDisable' name="+ elementName +" class="+ elementId +"><a class='disableElement fancybox.ajax' href='controller/fancybox/disableElement.php?id="+ elementId +"'><img class='imgButton' src='content/img/icon_delete.png' title='Delete'></a></div>";
    var copyAction = "<div id='elementToCopy' name="+ elementName +" class="+ elementId +"><a class='copyElement fancybox.ajax' href='controller/fancybox/copyElement.php?id="+elementId+"'><img class='imgButton' src='content/img/icon_copy.png' title='Copy'></a></div>";
    var moveAction = "<div id='elementToMove' name="+elementName+" class="+ elementId +"><a class='moveElement fancybox.ajax' href='controller/fancybox/moveElement.php?id="+elementId+"'><img class='imgButton' src='content/img/icon_cut.png' title='Cut'></a></div>";

    $("#renameElement").empty();
    $("#disableElement").empty();
    $("#copyElement").empty();
    $("#moveElement").empty();
	$("#downloadElement").empty();

    if(elementRight == 11 || elementRight == null)
    {
        $("#renameElement").append(renameAction);
        $("#disableElement").append(deleteAction);
        $("#copyElement").append(copyAction);
        $("#moveElement").append(moveAction);
    }
}



function hoverFolder(folder)
{
    var elementId = folder.getAttribute("id");
    var elementName = folder.getAttribute("name");

//    $("div[data-element-type='folder']").mouseenter(function() {
//        $(this).css("background", "#EEE");
//    }).mouseleave(function() {
//        $(this).css("background", "white");
//    });

//    $("div[data-element-type='folder']").mouseenter(function() {
//        $(this).css("background", "#EEE");
//    });
}

