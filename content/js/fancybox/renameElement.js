/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".renameElement").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '12%',
        height		: '20%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        showCloseButton : true,
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=renameElement",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});