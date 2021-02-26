"use strict";
$(window).scroll(function() {
    if ($(this).scrollTop()>100){
        $('.page-main-header').fadeOut();
        $( ".page-body-wrapper" ).addClass( "scorlled" );
    }
    else
    {
        $('.page-main-header').fadeIn();
        $( ".page-body-wrapper" ).removeClass( "scorlled" );
    }
});