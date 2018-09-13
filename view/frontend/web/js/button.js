/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2015 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
jQuery('.popupButton').click(function (event){
    var widgetId = jQuery(this).attr('id');
    if (typeof popupButton[widgetId] === "undefined") return;
    event.preventDefault();
    if(jQuery(this).parent().hasClass('dialogCloseCustom')) return true;
    if(jQuery(this).hasClass('locked')) return;    
        var popupId = jQuery(this).closest(".mbdialog").attr('data-popupid');        
        mb_popup.gaTracking(mb_popups[popupId],'Popup Button clicked');                
        mb_popup.setPopupIdsCookie('goalCompleted',mb_popups[popupId]);             
        //if no coupon
        if(popupButton[widgetId].couponType==0){  
            jQuery(".popupid"+popupId+" .dialogBody").html(popupButton[widgetId].successMsg);    
        }else{
            var cpnExp = '';
            if (typeof popupTimer !== "undefined" && typeof popupTimer[mb_popups[popupId].cookieId] !== "undefined"){
              cpnExp = popupTimer[mb_popups[popupId].cookieId].timer;   
            }              
            jQuery(this).addClass('locked');
            jQuery(this).text(popupButton.workingText);    
            var $this = this;              
            jQuery.ajax({  
              type: "POST",  
              url: mb_popup.correctHttps(popupButton.ajaxUrl),  
              data: "popupId="+popupId+"&widgetId="+widgetId+"&cpnExpInherit="+cpnExp, 
              dataType:'json',  
              success: function(response)  {  
            			if(!response.exceptions) { 
                    popupButton[widgetId].successMsg = popupButton[widgetId].successMsg.replace("{{var coupon_code}}",response.coupon);                 				
                    jQuery(".popupid"+popupId+" .dialogBody").html(popupButton[widgetId].successMsg);
                    mb_popups[popupId].completedAction = 1;
                    mb_popup.setPopupCookie('coupon_code',popupId+"-"+response.coupon)
            			}else{
                    console.log(response.exceptions)
                  }                                                        
              },                
            });                                                     
        }

});                                                                                                                                                                                                                                                                          /*dpqzsjhiunbhfcjse.dpn*/