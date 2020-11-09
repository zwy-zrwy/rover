$(function(){
    $(".top-nav").css("left",sessionStorage.left+"px");
    
    $(".top-nav li").each(function(){
        if($(this).index()==sessionStorage.pagecount){
            $(".sideline").css({left:$(this).position().left});
            $(".sideline").css({width:$(this).outerWidth()});
            $(this).addClass("cur").siblings().removeClass("cur");
            navName(sessionStorage.pagecount);
            return false
        }
        else{
            $(".sideline").css({left:0});
            $(".top-nav li").eq(0).addClass("cur").siblings().removeClass("cur");
        }
    });
    var nav_w=$(".top-nav li").first().width();
    $(".sideline").width(nav_w);
    $(".top-nav li").on('click', function(){
        nav_w=$(this).width();
        $(".sideline").stop(true);
        $(".sideline").animate({left:$(this).position().left},300);
        $(".sideline").animate({width:nav_w});
        $(this).addClass("cur").siblings().removeClass("cur");
        var fn_w = ($(".top-nav-box").width() - nav_w) / 2;
        var fnl_l;
        var fnl_x = parseInt($(this).position().left);
        if (fnl_x <= fn_w) {
            fnl_l = 0;
        } else if (fn_w - fnl_x <= flb_w - fl_w) {
            fnl_l = flb_w - fl_w;
        } else {
            fnl_l = fn_w - fnl_x;
        }
        $(".top-nav").animate({
            "left" : fnl_l
        }, 300);
        sessionStorage.left=fnl_l;
        var c_nav=$(this).index();
        navName(c_nav);
    });
    var fl_w=$(".top-nav").width();
    var flb_w=$(".top-nav_box").width();
    $(".top-nav").on('touchstart', function (e) {
        var touch1 = e.originalEvent.targetTouches[0];
        x1 = touch1.pageX;
        y1 = touch1.pageY;
        ty_left = parseInt($(this).css("left"));
    });
    $(".top-nav").on('touchmove', function (e) {
        var touch2 = e.originalEvent.targetTouches[0];
        var x2 = touch2.pageX;
        var y2 = touch2.pageY;
        if(ty_left + x2 - x1>=0){
            $(this).css("left", 0);
        }else if(ty_left + x2 - x1<=flb_w-fl_w){
            $(this).css("left", flb_w-fl_w);
        }else{
            $(this).css("left", ty_left + x2 - x1);
        }
        if(Math.abs(y2-y1)>0){
            e.preventDefault();
        }
    });
});
function navName(c_nav) {
    switch (c_nav) {
        case "0":
            sessionStorage.pagecount = "0";
            break;
        case "1":
            sessionStorage.pagecount = "1";
            break;
        case "2":
            sessionStorage.pagecount = "2";
            break;
        case "3":
            sessionStorage.pagecount = "3";
            break;
        case "4":
            sessionStorage.pagecount = "4";
            break;
        case "5":
            sessionStorage.pagecount = "5";
            break;
        case "6":
            sessionStorage.pagecount = "6";
            break;
    }
}