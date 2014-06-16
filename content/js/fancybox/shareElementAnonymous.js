/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".shareElementAnonymous").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '20%',
        height		: '20%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=shareElementAnonymous",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});