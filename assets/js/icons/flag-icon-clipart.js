(function($) {
    "use strict";
    $('.icon-lists div').click( function() {
        $(".fa-fa-icon-show-div").show().removeClass('opecity-0');
        var font_class = ($(this).children().children('i').attr('class'));
        var fafaclass= '&lt;i class="'+ font_class + '"&gt';
        var fafaclass1= '<i class="'+ font_class + '"></i>';
        //alert(fafaclass);
        $("#fclass").html(fafaclass);
        $("#fclass1").html(font_class);
        $("#icon_main").removeClass();
        $("#icon_main").addClass(font_class);
        $("#icon_main").addClass("fa-2x text-white");
        $(".inp-val").val(fafaclass1);
    });
    $(".close-icon").click(function(){
        $(".icon-hover-bottom").addClass("opecity-0");
        $(".fa-fa-icon-show-div").hide();
        $(".icon-lists").removeClass('m-b-50');
    });
})(jQuery);
function myFunction() {
    var copyText = document.getElementById("input_copy");
    copyText.select();
    document.execCommand("Copy");
};
