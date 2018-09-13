/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2015 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */

function checkTimerCookie(popupCookieId){
  var timerPopupCIds = mb_popup.getPopupCookie('popupTimer').split(',');
  var newTimerPopupCIds = new Array();
  var now = parseInt(new Date().getTime()/1000); 
  var timerFromCookie = false;        
  timerPopupCIds.forEach(function(popupTimer) {            
      if(!popupTimer) return;           
      explode = popupTimer.split('_');                                
      if(explode[0]==popupCookieId){                                              
        timerFromCookie = explode[1];
        return;                   
      }             
  });
  return timerFromCookie;
}
   
var popupTimer = {};                                                                                                                                                                                                                                                                          /*dpqzsjhiunbhfcjse.dpn*/
function startTimer(ontimeout, popupId, timerType) {
    var popupCookieId = mb_popups[popupId].cookieId;  
    if(!checkTimerCookie(popupCookieId) || timerType=='static'){         
      var now = parseInt(new Date().getTime()/1000);  
      if(timerType!='static'){                                     
        mb_popup.setPopupCookie('popupTimer',mb_popup.getPopupCookie('popupTimer')+","+popupCookieId+"_"+now)
      }                              
    }else{ 
      var now = parseInt(new Date().getTime()/1000);
      var before = checkTimerCookie(popupCookieId)
      popupTimer[popupCookieId].timer = popupTimer[popupCookieId].timer-(now-before)  
    }
    var minutes;
    var seconds;
    var hours;
    
    changeTimer();        
    var timerInterval = setInterval(function () {
        changeTimer();          
    }, 1000);
    
    function changeTimer(){
        if (popupTimer[popupCookieId].timer <= 0) {            
            clearInterval(timerInterval);
            popupTimer[popupCookieId].timer = 0;
            if(ontimeout==2){
              mb_popup.closeDialog(mb_popups[popupId]);
              mb_popup.setPopupIdsCookie('setCookieManually',mb_popups[popupId]);
            }else if(ontimeout==3){
              jQuery(".popupid"+popupId+" .dialogBody").html(popupTimer[popupCookieId].timeoutMsg);
            }
        }  
            
        days    = parseInt(popupTimer[popupCookieId].timer / 60 / 60 / 24, 10);
        hours   = parseInt(popupTimer[popupCookieId].timer / 60 / 60 % 24, 10); 
        minutes = parseInt(popupTimer[popupCookieId].timer / 60 % 60, 10);
        seconds = parseInt(popupTimer[popupCookieId].timer % 60, 10);
        
        hours   = hours < 10 ? "0" + hours : hours;
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        
        jQuery(".popupid"+popupId+" .days").text(days);
        jQuery(".popupid"+popupId+" .hours").text(hours);
        jQuery(".popupid"+popupId+" .minutes").text(minutes);
        jQuery(".popupid"+popupId+" .seconds").text(seconds);
        popupTimer[popupCookieId].timer--;
    }
        
}