/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".uploadElement").fancybox({
        maxWidth	: 1500,
        maxHeight	: 1000,
        fitToView	: false,
        width		: '35%',
        height		: '85%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=uploadElement",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});