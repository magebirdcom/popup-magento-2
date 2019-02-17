/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */

  if ( jQuery.isFunction(jQuery.fn.on) ) {      
    jQuery('body').on("click", ".login",function(e){
      e.preventDefault();
      jQuery("#login-form").show();
      jQuery("#signup-form").hide();             
    }); 
    jQuery('body').on("click", ".register",function(e){
      e.preventDefault();
      jQuery("#login-form").hide();
      jQuery("#signup-form").show();                 
    });        
  }else{  
    jQuery(".login").live("click", function(e) {
      e.preventDefault();
      jQuery("#login-form").show();
      jQuery("#signup-form").hide();                          
    });
    jQuery(".register").live("click", function(e) {
      e.preventDefault();
      jQuery("#login-form").hide();
      jQuery("#signup-form").show();                          
    });    
  }  
   
	jQuery('.registerPopup form').unbind().submit(function() {
    var action = jQuery(this).attr('data-action');
    var popupId = jQuery(this).closest(".mbdialog").attr('data-popupid');
    var widgetId = jQuery(this).attr('data-widgetId');            
    var $this = this;    
    var submitText = jQuery(".registerPopup button:visible").text();
    jQuery(".registerPopup button:visible").text(workingText);    
    jQuery(".registerPopup button:visible").attr('disabled','disabled');  
    var cpnExp = '';
    if (typeof popupTimer !== "undefined" && typeof popupTimer[mb_popups[popupId].cookieId] !== "undefined"){
      var cpnExp = popupTimer[mb_popups[popupId].cookieId].timer;   
    }    
    var loginRegister = jQuery(this).attr('id');   
    jQuery.ajax({
      type: "POST",
      url: mb_popup.correctHttps(jQuery(this).attr('action')),
      data: jQuery(this).serialize()+"&widgetId="+widgetId+"&popupId="+popupId+"&cpnExpInherit="+cpnExp, 
      dataType:'json', 
      success: function(response){              
  			if(!response.exceptions) {
        console.log(response)  				
        console.log(loginRegister)
        
          mb_popup.gaTracking(mb_popups[popupId],'User popup registration completed');
          mb_popups[popupId].completedAction = 1;
          mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);
          rgSuccessMsg[widgetId] = rgSuccessMsg[widgetId].replace("{{var coupon_code}}",response.coupon);
          if(loginRegister=='login-form'){
            location.reload();
            return; 
          }          
          jQuery($this).closest(".dialogBody").html(rgSuccessMsg[widgetId]);
          if(parseInt(rgSuccessAction[widgetId])==2){
            setTimeout(function(){
              mb_popup.closeDialog(mb_popups[popupId])
            }, rgActionDelay[widgetId]);                  
          }else if(parseInt(rgSuccessAction[widgetId])==3){
            setTimeout(function(){
              window.location.href = rgSuccessUrl[widgetId];
            }, rgActionDelay[widgetId]);                  
          }          
  			} else {
          jQuery($this).find('button').text(submitText);
          jQuery(".registerPopup button").removeAttr('disabled');         
          var errorHtml = '';
  				for(var i = 0; i < response.exceptions.length; i++) {
  					errorHtml += '<p>'+response.exceptions[i]+'</p>';
  				}          
          jQuery($this).closest(".mbdialog").find(".error").html('');
          jQuery($this).closest(".mbdialog").find(".error").append(errorHtml);
          jQuery($this).closest(".mbdialog").find(".error").fadeIn();
          setTimeout(function(){
            jQuery($this).closest(".mbdialog").find(".error").fadeOut();
          }, 3500);                     
  			}             
      }             
    });   
  }); 
