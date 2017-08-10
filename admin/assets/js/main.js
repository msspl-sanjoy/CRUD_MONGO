(function($){


$('.dropdown-toggle').dropdown();

var detect_cssVhVwSupport = function(hw){
	
var dv = document.createElement("div");
var div = document.body.appendChild(dv);
var dm = (hw =="vw"?"width":"height");
var wm = (hw=="vw"?"innerWidth":"innerHeight");
div.style[dm] = "100"+hw;
var elem_dm = parseInt(getComputedStyle(div, null)[dm],10);
var win_dm = window[wm];
dv.parentNode.removeChild( dv );
  if((!!elem_dm ==false ) && elem_dm!= win_dm){
   return false;
  }else{
   return true;
  };
};

if(detect_cssVhVwSupport("vh")===false){

 $(".mm-page").height($(window).innerHeight());
 $(".login_main_bg").height($(window).innerHeight());
};

// left menue height resize
/*if (Modernizr.mq('(min-width: 768px)') && Modernizr.mq('(max-width: 1023px)')) {
   console.log(1222122);
} */

})(jQuery);