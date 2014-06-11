/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".downloadElement").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '14%',
        height		: '22%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=downloadElement",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});