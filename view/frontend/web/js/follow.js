jQuery('#pinButton').click(function () {
    showCoupon('Pinterest follow');
});  
if(window.FB){
  FB.init({
    appId      : '266922980004856',
    xfbml      : true,
    version    : 'v2.1'
  });
  FB.Event.subscribe('edge.create', function(response) {
      showCoupon('Facebook like')
      jQuery(".popupid"+smPopupId+" .dialogBody").trigger('click');
  }); 
}else{
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '266922980004856',
      xfbml      : true,
      version    : 'v2.1'
    });
    FB.Event.subscribe('edge.create', function(response) {
        showCoupon('Facebook like')
        jQuery(".popupid"+smPopupId+" .dialogBody").trigger('click');
    });        
  }; 
}

(function(d, s, id){
   var js, fjs = d.getElementsByTagName(s)[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement(s); js.id = id;
   js.src = "//connect.facebook.net/en_US/sdk.js";
   fjs.parentNode.insertBefore(js, fjs);
 }(document, 'script', 'facebook-jssdk')); 

var twitterListener = setInterval(function(){  
  if(jQuery(".popupid"+smPopupId).is(":visible")){ 
    clearInterval(twitterListener)                           

    window.twttr = (function (d,s,id) {
        var t, js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
        js.src="https://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js, fjs);
        return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
    }(document, "script", "twitter-wjs-popup"));   
  
    twttr.ready(function (twttr) {
        twttr.events.bind('click', function (event) { 
          jQuery(".popupid"+smPopupId+" .dialogBody").trigger('click');
        });
        twttr.events.bind('follow', function(event) {
            showCoupon('Twitter follow'); 
        });
        twttr.events.bind('tweet', function(event) {
            showCoupon('Twitter tweet');
        });        
    }); 
  }
},10)        
   
function showCoupon(action){
  mb_popup.gaTracking(mb_popups[smPopupId],'Popup ' + action);
  mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[smPopupId]);
  mb_popups[smPopupId].completedAction = 1; //1=goal completed,2=popup closed,3=window closed or refreshed   
  var timeoutSeconds = 0;
  jQuery(".popupid"+smPopupId+" .dialogBody").append('<p style="font-size:16px; font-weight:bold; position:absolute;left:10px; top:5px;">'+smWorkingText+'</p>')
  if(action=="Gplus follow"){
    timeoutSeconds = 2000;    
  }
     
  setTimeout(function(){  
    var cpnExp = '';
    if (typeof popupTimer !== "undefined" && typeof popupTimer[mb_popups[popupId].cookieId] !== "undefined"){
      var cpnExp = popupTimer[mb_popups[smPopupId].cookieId].timer;   
    }       
    jQuery.ajax({
      type: "POST",
      url: mb_popup.correctHttps(smCouponAction),
      data: "popupId="+smPopupId+"&widgetId="+smWidgetId+"&coupon_code="+smCouponCode+"&cpnExpInherit="+cpnExp, 
      dataType:'json', 
      success: function(response){      
  			if(!response.exceptions) {  				                 
          smSuccessMsg = smSuccessMsg.replace("{{var coupon_code}}",response.coupon);
          mb_popup.setPopupCookie('lastCoupon',smPopupId+"-"+response.coupon)
          jQuery(".popupid"+smPopupId+" .dialogBody").html(smSuccessMsg);         
  			}            
      }             
    })
  },timeoutSeconds); 
} 

jQuery('.gplusRecommend').click(function () {
    showCoupon('Gplus follow');
});  

function gplusCallback(jsonParam) {
  if(jsonParam.state=="on"){
    showCoupon('Gplus follow')
    jQuery(".popupid"+smPopupId+" .dialogBody").trigger('click');
  }
}                                                                                                                                                 