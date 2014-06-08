/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".moveElement").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '80%',
        height		: '80%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=copyElement",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});