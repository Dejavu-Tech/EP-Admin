"use strict";
$(function() {
    $('#main-menu').smartmenus({
        subMenusSubOffsetX: 1,
        subMenusSubOffsetY: -8,
        hideOnClick : false,
        hideTimeout : 500
    });
});

//toggle menu
$(".vertical-mobile-sidebar").click(function(){
    $('.sm').css("left","0px");
});
$(".mobile-back").click(function(){
    $('.sm').css("left","-300px");
});
$(".mega-menu-header .vertical-mobile-sidebar").click(function(){
    $('.sm').css("left","inherit");
    $('.sm').css("right","0px");
});
$(".mega-menu-header .mobile-back").click(function(){
    $('.sm').css("right","-300px");
    $('.sm').css("left","inherit");
});
$('.drilldown').drilldown();