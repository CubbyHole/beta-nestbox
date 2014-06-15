/**
 * Created by Harry on 05/06/14.
 */

$(document).ready(function(){
    $(".infoElement").fancybox({
        maxWidth	: 800,
        maxHeight	: 600,
        fitToView	: false,
        width		: '25%',
        height		: '25%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        ajax: {
            type     : "POST",
            cache    : false,
            data	 : "var=elementInformation",
            success	 : function(data){ $.fancybox(data); }
        }
    });
});