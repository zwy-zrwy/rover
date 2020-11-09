(function($){
$.fn.aotime=function(){
	var data="";
	var _DOM=null;
	var TIMER;
	createdom =function(dom){
		_DOM=dom;
		data=$(dom).attr("data");
		data = data.replace(/-/g,"/");
		data = Math.round((new Date(data)).getTime()/1000);
		$(_DOM).html("<em class='J_day'></em><em class='J_hour'></em><em><b class='J_minute'></b>分</em><em><b class='J_second'></b>秒</em>")
		reflash();
	};
	reflash=function(){
		
		
		var	range  	= data-Math.round((new Date()).getTime()/1000),

		    secday = 86400,
				sechour = 3600,
				days   = parseInt(range/secday),
				hours  = parseInt((range%secday)/sechour), 
				min		= parseInt((range%sechour)/60),
				sec		= (range%sechour)%60;
	  if(range>0){
		  if(days>0){
			  $(_DOM).find(".J_day").html('<b>'+days+'</b>天');
			  $(_DOM).find(".J_hour").html('<b>'+nol(hours)+'</b>时');
		  }else{
			  if(hours>0){
				  $(_DOM).find(".J_hour").html("<b>"+nol(hours)+'</b>时');
			  }
		  }
		  $(_DOM).find(".J_minute").html(nol(min));
		  $(_DOM).find(".J_second").html(nol(sec));
	  }else{
	  	changeAuto();
		  window.location.reload();
	  }
	};
	TIMER = setInterval( reflash,1000 );
	nol = function(h){
		return h>9?h:'0'+h;
	}
	return this.each(function(){
		var $box = $(this);
		createdom($box);
	});
}
})(jQuery);
$(function(){
	$(".aotime").each(function(){
		$(this).aotime();
	});
});