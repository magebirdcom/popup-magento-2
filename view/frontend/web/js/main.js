 /**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2017 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */                                  
  var mb_popup = {
      showStatsGlobal : false,
      serverTime : '',
      defaultDomain : '',
      clientTime : '',
      statsUrl : '',
      showPopupsUrl : '',
      cursorPositions : '', 
      lastScrollTop : '',
      showDialogWin: function (popup) {     
          if(popup.removed) return; //for case if manually removed -> jQuery('.dialogBg.popupid'+mb_popups[11].popupId).remove(); mb_popups[11].removed= true;    
          //if show only once set cookie
          if(popup.showingFrequency==2){
            mb_popup.setPopupIdsCookie(null,popup);
          }
                    
          //if with background overlay
          if(mb_popup.hasOverlay(popup)){
            if(mb_popup.checkIfMobile()) {  
              jQuery("body").css({'position' : 'relative', 'height' : 'auto'});                  
            }   
            //to avoid conflict with theme save old height value
            if(!jQuery("html").attr("data-height")){
              if(jQuery('html').height() == jQuery(window).height()){
                jQuery("html").attr({'data-height' : '100%'});
              }else{
                jQuery("html").attr({'data-height' : 'auto'});
              }
            }
            if(!jQuery("html").attr("data-overflow")){
              jQuery("html").attr({'data-overflow' : jQuery("html").css('overflow')});
            }                                                                    
          }        
          if(popup.makeStats){
            mb_popup.gaTracking(popup,'Popup showed');
          }   
          
          //we want to show on exit popup immediately before user close browser
          if(popup.showWhen==6){
              mb_popup.showeffectHandler(popup);
              popup.startTime = new Date().getTime();
              mb_popup.dialogCloseHandler(popup);                                        
              mb_popup.clickInsideDialogHandler(popup);              
              return; 
          } 
          /*for the rest of popups make sure all images and css files are loaded
          first to display popup smoothly*/                     
          var imgsLoaded = false;
          var imgElement = jQuery('.mbdialog.popupid'+popup.popupId).find("img");
          var numImagesLoaded = 0; 
          
          imgElement.each(function() {
              var image = new Image();
              image.src = this.src;
              image.onload = function () {
                numImagesLoaded++;
                if(numImagesLoaded==imgElement.length){
                  imgsLoaded = true;
                }         
              }       
          });
          var intervalX = 0;
          var cssImgListener = setInterval(function(){
              //wait 1000ms to load all images and css, if not display popup anyway
              intervalX++;
              if(intervalX === 50 
                || ((imgsLoaded || imgElement.length==0) 
                    && (popup.content.indexOf('cssLoadedChecker')==-1 
                        || (jQuery('.mbdialog.popupid'+popup.popupId + ' .cssLoadedChecker.moctod-dribegam').css("display")==="none" 
                            && jQuery('.mbdialog.popupid'+popup.popupId + ' .cssLoadedChecker2.moctod-dribegam').css("display")==="none"))
                   )
              ){                                      
                  mb_popup.showeffectHandler(popup);
                  popup.startTime = new Date().getTime();                                
                  mb_popup.dialogCloseHandler(popup);                                           
                  clearInterval(cssImgListener)
              }
          },20)          
          
          mb_popup.clickInsideDialogHandler(popup);                               
          //keyupHandler(popup);                                             
      },  
      closeDialog: function (popup,isAuto) { 
          setTimeout(function(){ //use timeout, otherwise saveposition won't work      
            isAuto = typeof isAuto !== 'undefined' ? isAuto : false;                                               
            //we will disable fadeout method soon and closeEffect will be only used. Anyway leafe that for now
            if(popup.closeEffect==1){
              //use hide instead of remove for case if show on hover or show on click
              jQuery(".mbdialog.popupid"+popup.popupId).hide();
              jQuery(".dialogBg.popupid"+popup.popupId).hide();
              mb_popup.closeDialogCallback(popup,isAuto);            
            }else{
              jQuery(".mbdialog.popupid"+popup.popupId).fadeOut(600);
              if(!mb_popup.hasOverlay(popup)){
                  mb_popup.closeDialogCallback(popup,isAuto);
              }else{
                //must be callback! Otherwise jQuery(".dialogBg").length will be still 1 and checkIfMobile wont work properly
                jQuery(".dialogBg.popupid"+popup.popupId).fadeOut(600, function(){   
                  jQuery(".mbdialog.popupid"+popup.popupId).hide();
                  jQuery(".dialogBg.popupid"+popup.popupId).hide();
                  mb_popup.closeDialogCallback(popup,isAuto);
                });            
              }             
            }
          },20)                              
      },
      
      closeDialogCallback: function(popup,isAuto){
               
          //only for onclick and onhover. For other events we don't want to enoying and showing popup again
          if((popup.showWhen==4 || popup.showWhen==5) && popup.showingFrequency!=2 && popup.showingFrequency!=1){
            popup.dialogActive = false;
          } 
          //stop youtube
          var video = jQuery(".popupid"+popup.popupId+" .dialogBody iframe").attr("src");
          if(video && video.indexOf("youtube")!=-1){
            jQuery(".popupid"+popup.popupId+" .dialogBody iframe").attr("src","");
            jQuery(".popupid"+popup.popupId+" .dialogBody iframe").attr("src",video);          
          } 

          if(mb_popup.checkIfMobile() && mb_popup.hasOverlay(popup)) { 
            jQuery(window).scrollTop(mb_popup.lastScrollTop)            
          }
                             
          //jQuery(".dialogBg").filter is used for case if multi popups
          if(mb_popup.hasOverlay(popup) && jQuery(".dialogBg:visible").length == 0){                       
            jQuery("html").css({'overflow' : jQuery("html").attr('data-overflow'), 'height' : jQuery("html").attr('data-height')});
            //jQuery("html").css({'overflow' : 'auto', 'height' : jQuery("html").attr('data-height')});
            if(mb_popup.checkIfMobile()){
              jQuery("body").css({'height' : jQuery("html").attr('data-height')});
            }
          }      
                  
          setTimeout(function(){      
            var isClosedByUser = '0';
            if(!isAuto){
              mb_popup.setPopupIdsCookie('closePopup',popup);    
              isClosedByUser = '1';
            }            
            if(popup.makeStats){
              mb_popup.gaTracking(popup,'Popup closed without action');              
              popup.makeStats = false;
              seconds = (new Date().getTime())-popup.startTime;
              jQuery.ajax({
                type: mbPopupParams.requestMethod,
                url: mb_popup.statsUrl,
                data: "time="+seconds+"&closed="+isClosedByUser+"&popupId="+popup.popupId         
              });                    
            } 
          },40)
                 
      },
            
      showHandler: function (popup) {          
          //if when leaving the page
          if(popup.showWhen==6){     
            var sensitive = 0; //for case if we want to add later support to show popup faster
            var edgeSensitive = 0;
            if(navigator.userAgent.indexOf('Windows NT 10')!=-1 || navigator.userAgent.indexOf('Edge')!=-1) edgeSensitive=50; //Microsoft Edge bugfix             
            var wasMouseBeforeDown = false; //otherwise in some browsers it shows when you swith tab
            var example = false;
            jQuery(document).mouseout(function(e){             
              var t = e.relatedTarget || e.toElement;              
              if(e.pageY>1){
                wasMouseBeforeDown = true;
              }   
              if ((!t || "HTML"==t.nodeName || (sensitive>(e.pageY - jQuery(window).scrollTop()))) && popup.dialogActive==false && (e.pageY-edgeSensitive-sensitive) < jQuery(window).scrollTop() && wasMouseBeforeDown) {                   
                  mb_popup.showDialogWin(popup);
                  return false;
              }             
            });
          //if show when using scroller
          }else if(popup.scrollPx>0 && popup.showWhen==3){       
            jQuery(document).scroll(function () {
                var y = jQuery(this).scrollTop();        
                if (y > popup.scrollPx && popup.dialogActive==false) { 
                    mb_popup.showDialogWin(popup);
                }
            });
          //if show on selector click
          }else if(popup.showWhen==4){    
            jQuery(popup.selector).css('cursor','pointer'); //without that on ipad on click works only for <a> element 
            if ( jQuery.isFunction(jQuery.fn.on) ) {      
              jQuery('body').on("click", popup.selector,function(e){
                if(popup.dialogActive==false){
                  e.preventDefault();
                  //if click selector is inside another popup
                  mb_popup.clickInsideAnotherHandler(this);                                                             
                  mb_popup.showDialogWin(popup);   
                  //return false for case if nasted popups to prevent double click inside stats
                  return false;         
                }  
                //if user click again after closing popup
                if(jQuery(".popupid"+popup.popupId).css("display")=="none"){
                  jQuery(".popupid"+popup.popupId).show();
                }                   
              });     
            }else{
              jQuery(popup.selector).live("click", function(e) {
                if(popup.dialogActive==false){
                  e.preventDefault();
                  mb_popup.showDialogWin(popup);    
                  return false;       
                }  
                //if user click again after closing popup
                if(jQuery(".popupid"+popup.popupId).css("display")=="none"){
                  jQuery(".popupid"+popup.popupId).show();
                }                          
              });
            }   
          //if show on selector hover       
          }else if(popup.showWhen==5){
              
              if ( jQuery.isFunction(jQuery.fn.on) ) {
                  jQuery('body').on({                    
                    mouseenter: function(e){
                      mb_popup.inhover(popup,e)
                    },
                    mouseleave: function(){
                      mb_popup.unhover(popup)
                    }
                  },popup.selector);
                  
                  jQuery('body').on("touchstart", popup.selector,function(e){
                    if(popup.dialogActive==false){
                      e.preventDefault();
                      mb_popup.showDialogWin(popup);           
                    }
                    //if user hover again after closing popup
                    if(jQuery(".popupid"+popup.popupId).css("display")=="none"){
                      jQuery(".popupid"+popup.popupId).show();
                    }                       
                  }); 
                                                 
              }else{
                  jQuery(popup.selector).hover(
                    function(e) {
                      mb_popup.inhover(popup,e)                 
                    },
                    function() {
                      mb_popup.unhover(popup);                
                    }                 
                  );
                  
                  jQuery(popup.selector).live("touchstart", function(e) {
                    if(popup.dialogActive==false){
                      e.preventDefault();
                      mb_popup.showDialogWin(popup);           
                    }  
                    //if user hover again after closing popup
                    if(jQuery(".popupid"+popup.popupId).css("display")=="none"){
                      jQuery(".popupid"+popup.popupId).show();
                    }                            
                  });                                                              
              }   
          }else if(popup.secondsDelay>0 && popup.showWhen==2){
            setTimeout(function(){mb_popup.showDialogWin(popup)},popup.secondsDelay*1000);
          }else if(popup.showWhen==7){
            var totaltimeIntervalCheck = false;
            var totaltimeInterval = setInterval(function(){
              if(parseInt(mb_popup.getPopupCookie('totalTime'))>popup.totalSecondsDelay && !totaltimeIntervalCheck){                          
                clearInterval(totaltimeInterval) 
                totaltimeIntervalCheck = true;         
                mb_popup.showDialogWin(popup)                 
              }
            },1000)  
          }else if(popup.showWhen==8){      
            if(!mb_popup.getPopupCookie('cartAddedTime')) return;      
            var carttimeIntervalCheck = false;
            var cartAddedAgo; 
            var carttimeInterval = setInterval(function(){
              cartAddedAgo = parseInt(new Date().getTime()/1000)-(mb_popup.getPopupCookie('cartAddedTime')-(mb_popup.serverLocalTime-parseInt(mb_popup.clientTime/1000)))
              if(cartAddedAgo>popup.cartSecondsDelay && !carttimeIntervalCheck){                          
                clearInterval(carttimeInterval) 
                carttimeIntervalCheck = true;         
                mb_popup.showDialogWin(popup)                 
              }
            },1000)                                                  
          }else{
            mb_popup.showDialogWin(popup);
          } 
          
      },
      
      inhover: function(popup,e){                
          if(popup.dialogActive==false){
            e.preventDefault();
            mb_popup.showDialogWin(popup);           
          }
          //if user hover again after closing popup
          setTimeout(function(){
            if(jQuery(".popupid"+popup.popupId).css("display")=="none"){
              jQuery(".popupid"+popup.popupId).show();
            }
          }, 100);      
      },
      
      unhover: function(popup){                
          if(popup.closeOnOut==1 && (!mb_popup.hasOverlay(popup))){
            setTimeout(function(){  
              if (!jQuery('.mbdialog:hover').length) {    
                jQuery(".popupid"+popup.popupId).hide();
              }else{
                jQuery('.mbdialog').mouseleave(function() {
                    setTimeout(function(){
                      if (!jQuery(popup.selector+':hover').length) {
                        jQuery(".popupid"+popup.popupId).hide();
                      }
                    }, 10);
                });  
              }
            }, 300); //give user enough time to hover popup                    
          }      
      },
            
      showeffectHandler: function (popup) {                
          popup.dialogActive = true;
          //we hide this before for case if images loading takes time
          //jQuery('.dialogBg.popupid'+popup.popupId).css('visibility','visible');
          if(mb_popup.checkIfMobile() && mb_popup.hasOverlay(popup)) {
            mb_popup.lastScrollTop = jQuery(window).scrollTop();
            setTimeout(function(){jQuery("html, body").scrollTop(0)},300)
          }
          //fix if body is not heigh enough
          if(mb_popup.checkIfMobile() && mb_popup.hasOverlay(popup) && jQuery(window).height()>jQuery("body").height()){
            jQuery('.dialogBg').height(jQuery(window).height()); 
          }          
          jQuery('.dialogBg.popupid'+popup.popupId).show(); 
                       
          switch(popup.appearType){
            case '7':
              mb_popup.rotateZoomShow(popup);
              break;
            case '6':
              mb_popup.elasticShow(popup); 
              break;
            case '4':
              mb_popup.slideupShow(popup); 
              break;
            case '3':
              mb_popup.slidedownShow(popup);
              break;
            case '2':
              setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).fadeIn(1000)},100)   
              break;
            default:                                                            
              //if bounce popup
              if(popup.showWhen==6){
                jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})
              }else{
                //not sure why we need 50ms. Don't remove until test it very well 
                setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},50)
              }
              break;                                              
          } 
          
          if(mb_popup.hasOverlay(popup)){
            if(mb_popup.checkIfMobile()){
              jQuery(".dialogBg").css({'overflow-y' : 'auto'});
            }else if((popup.appearType!=1 && popup.appearType!=2)){
              jQuery("html").css({'overflow' : 'hidden', 'height' : '100%'});
            }else{          
              jQuery("html").css({'overflow' : 'auto'});
              jQuery(".dialogBg").css({'overflow-y' : 'hidden'});            
              var scrollerInterval = 0;
              var popupBottomPx = 0;
              //need interval because large images are loaded after popup is showed
              var scrollerChanger = setInterval(function(){
                  scrollerInterval++;
                  if(scrollerInterval > 60){                                                                                  
                      clearInterval(scrollerChanger)
                  }else{
                      popupBottomPx = jQuery('.popupid'+popup.popupId+' .dialogBody').offset().top-jQuery(window).scrollTop()+jQuery('.popupid'+popup.popupId+' .dialogBody').outerHeight(true);
                      if(jQuery(window).height()<popupBottomPx){
                        jQuery("html").css({'overflow' : 'hidden', 'height' : '100%'});
                        jQuery(".dialogBg").css({'overflow-y' : 'auto'});               
                      }                 
                  }           
              },50)
            }                      
          }   
          mb_popup.cartStats(popup);                                                                
      },
            
      keyupHandler: function (popup) {
          jQuery(document).keyup(function(e) {    
            if (e.keyCode == 27) {   
              mb_popup.closeDialog(popup);
            }
          }); 
      },
        
      onbeforeunloadHandler: function () { 
        window.onunload = window.onbeforeunload = (function(){
          var didMyThingYet=false;
          return function(){                                  
            if (didMyThingYet) return;
            didMyThingYet=true;           
            var sendData = {};
            for (var key in mb_popups) {                
                if (!mb_popups.hasOwnProperty(key)) continue;
                if (!mb_popups[key].dialogActive) continue; //if popup was already closed or hasn't been showed yet                 
                if (jQuery('.mbdialog.popupid'+mb_popups[key].popupId).is(":visible")) {
                  //if stats for click inside popup have been set makeStats have been set to false
                  if(mb_popups[key].makeStats){                               
                    //for popup without overlay and with show when is on load (showWhen=1), we already set stats inside block 
                    if(mb_popup.hasOverlay(mb_popups[key]) || (!mb_popup.hasOverlay(mb_popups[key]) && mb_popups[key].showWhen!=1)){                    
                      seconds = (new Date().getTime())-mb_popups[key].startTime;
                      sendData[mb_popups[key].popupId] = seconds;
                      mb_popups[key].makeStats = false;                    
                      mb_popup.gaTracking(mb_popups[key],'Window closed/left while popup still opened');
                    }
                  }
                }
            }

            function isEmpty(obj) {
                for(var prop in obj) {
                    if(obj.hasOwnProperty(prop))
                        return false;
                }
            
                return true;
            }
            
            if(!isEmpty(sendData)){
              var lastPageviewId = Math.random().toString(36).substring(2,10);                            
              //20 because in rare cases pageloads lasts more than 10s and $expire<time() doesn't match
              mb_popup.setPopupCookie('lastPageviewId',lastPageviewId,20);
              if(mbPopupParams.ajaxAsync=='true'){                                
                jQuery.ajax({
                  type: mbPopupParams.requestMethod,
                  url: mb_popup.statsUrl,
                  data: "windowClosed=1&lastPageviewId="+lastPageviewId+"&popupIds="+JSON.stringify(sendData),
                  async:true       
                }); 
  
                var dummyUrl = decodeURIComponent(mbPopupParams.rootUrl)+'skin/frontend/base/default/css/magebird_popup/style_v148.css';            
                jQuery.ajax({
                  type: mbPopupParams.requestMethod,
                  url: dummyUrl, 
                  async:false      
                });              
              }else{     
                jQuery.ajax({
                  type: mbPopupParams.requestMethod,
                  url: mb_popup.statsUrl, 
                  data: "windowClosed=1&lastPageviewId="+lastPageviewId+"&popupIds="+JSON.stringify(sendData),
                  async:false       
                });               
              }          
            }
                       
          }        
        }());    
      },      
             
      dialogCloseHandler: function (popup) {    
        if(popup.closeTimeout>0){
          setTimeout(function(){mb_popup.closeDialog(popup,true)},popup.closeTimeout*1000)       
        }
        if ( jQuery.isFunction(jQuery.fn.on) ) {   
          jQuery("body").on("click", '.popupid'+popup.popupId+' .dialogCloseCustom',function(e){
              (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
              mb_popup.closeDialog(popup);        
          });   
          //click outside dialog window
          jQuery('body').on("click", '.dialogBg.popupid'+popup.popupId, function(event){       
            if (popup.closeOnOverlay!=0 && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId).length && jQuery('.mbdialog.popupid'+popup.popupId).is(":visible")) {    
              mb_popup.closeDialog(popup); 
            };
          });     
        }else{ 
          jQuery('.popupid'+popup.popupId+' .dialogCloseCustom').live("click", function(e) {
              (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
              mb_popup.closeDialog(popup);        
          });    
          //click outside dialog window
          jQuery('.dialogBg.popupid'+popup.popupId).live("click", function(event) { 
            if (popup.closeOnOverlay!=0 && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId).length && jQuery('.mbdialog.popupid'+popup.popupId).is(":visible")) {    
              mb_popup.closeDialog(popup); 
            };
          });     
        }
      },
             
      clickInsideDialogHandler: function (popup) {   
          if ( jQuery.isFunction(jQuery.fn.on) ) {                         
              jQuery('body').on("click", function(event){
                if (jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId).length && jQuery('.mbdialog.popupid'+popup.popupId).is(":visible")
                  && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId+' .dialogCloseCustom').length
                  && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId+' .dialogClose').length) {
                    mb_popup.clickInsideDialog(popup,event);
                };      
              });     
          }else{     
              jQuery('body').live("click", function(event) {
                if (jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId).length && jQuery('.mbdialog.popupid'+popup.popupId).is(":visible")
                  && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId+' .dialogCloseCustom').length
                  && !jQuery(event.target).closest('.mbdialog.popupid'+popup.popupId+' .dialogClose').length) {    
                    mb_popup.clickInsideDialog(popup,event);
                };      
              });      
          }
      },
      
      clickInsideAnotherHandler: function (_this) {
        if(jQuery(_this).parents('.mbdialog').hasClass('mbdialog')){
          jQuery(".mbdialog").hide();
          jQuery(".dialogBg").hide();
          var dialogClass = jQuery(_this).parents('.mbdialog').attr('class');
          var classList = dialogClass.split(/\s+/);
          for (var i = 0; i < classList.length; i++) {
             if (classList[i].indexOf("popupid") > -1) {
               var oldDialog = mb_popups[classList[i].replace(/[^0-9]/g,'')] 
             }
          }
         
          if(oldDialog.makeStats){
            mb_popup.setPopupIdsCookie('clickInside',oldDialog);          
            mb_popup.gaTracking(oldDialog,'Clicked inside popup');
            seconds = (new Date().getTime())-oldDialog.startTime;
            jQuery.ajax({
              type: mbPopupParams.requestMethod,
              url: mb_popup.statsUrl,
              data: "time="+seconds+"&clickInside=1&popupId="+oldDialog.popupId        
            });
            oldDialog.makeStats = false;       
          }                    
        }       
      },
              
      dialogLocked : false,
      
      clickInsideDialog: function (popup,event) {
        //mobile support for zoom         
        if(mb_popup.checkIfMobile()) {
          if(popup.overlay==3 && mb_popup.dialogLocked==false){   
              jQuery('.mbdialog.popupid'+popup.popupId).css('position','absolute');   
              jQuery('.mbdialog.popupid'+popup.popupId).css('top', (jQuery(".mbdialog").offset().top+jQuery(document).scrollTop()) + "px");
              jQuery('.mbdialog.popupid'+popup.popupId).css('left', (jQuery(".mbdialog").offset().left+jQuery(document).scrollLeft()) + "px");
              jQuery('.mbdialog.popupid'+popup.popupId).css('margin', "0px");
              mb_popup.dialogLocked = true;
          }
        }
        if(!popup.completedAction){
          popup.completedAction = 4; //1=goal completed,2=popup closed,3=window closed or refreshed,4=clicked inside
        }          
        if(window.location.href.indexOf("popup/index/preview/")==-1 && window.location.href.indexOf("popup/index/template/")==-1){
          mb_popup.setPopupIdsCookie('clickInside',popup);
        }          
        if(popup.makeStats){
          mb_popup.gaTracking(popup,'Clicked inside popup');          
          seconds = (new Date().getTime())-popup.startTime;
          jQuery.ajax({
            type: mbPopupParams.requestMethod,
            url: mb_popup.statsUrl,
            data: "time="+seconds+"&clickInside=1&popupId="+popup.popupId       
          });
          popup.makeStats = false;       
        }
      },
            
      prepareDialog: function (popup) {
         
          popupDialog = '<div class="mbdialog popupid'+popup.popupId+'" data-popupid="'+popup.popupId+'">';
          if(popup.closeStyle!=5){
          popupDialog += '<a href="javascript:void(0)" onclick="mb_popup.closeDialog(mb_popups['+popup.popupId+'])" class="dialogClose style'+popup.closeStyle+' overlay'+popup.overlay+'"></a>'
          }
          popupDialog += '<div class="dialogBody"></div></div>';      
          //if background overlay
          if(mb_popup.hasOverlay(popup)){
            popupDialog = '<div class="dialogBg popupid'+popup.popupId+'">'+popupDialog+'</div>'; 
          }
          if(popup.verticalPosition==4){
            jQuery("body").prepend(popupDialog); 
          }else{
            jQuery("body").append(popupDialog);
          }               
          jQuery(".popupid"+popup.popupId+" .dialogBody").append(popup.content);
          if(mb_popup.checkIfMobile() && mb_popup.hasOverlay(popup)) {   
            //because some mobile devices have problem with fixed position          
            jQuery('.dialogBg.popupid'+popup.popupId).css('position','absolute');
          } 
          
          if(popup.verticalPosition==3 && popup.elementIdPosition!='' && jQuery(popup.elementIdPosition).length){
            //if define final position px from top of defined element  
            popup.verticalPositionPx = parseInt(jQuery(popup.elementIdPosition).offset().top) + parseInt(popup.verticalPositionPx)                    
            jQuery('.mbdialog.popupid'+popup.popupId).css({top:popup.verticalPositionPx+"px"})            
          }
          if(popup.horizontalPosition==6 && popup.elementIdPosition!='' && jQuery(popup.elementIdPosition).length){
            //if define final position px from left of defined element  
            jQuery('.mbdialog.popupid'+popup.popupId).css({left:parseInt(jQuery(popup.elementIdPosition).offset().left) + parseInt(popup.horizontalPositionPx)+"px"})            
          }          
                             
          mb_popup.showHandler(popup);                                                                        
      },
            
      rotateZoomShow: function (popup) {
          setTimeout(function(){
            jQuery('.mbdialog.popupid'+popup.popupId).addClass('transform-rotate-zoom1');
            jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})
          },100);    
          setTimeout(function(){
            jQuery('.mbdialog.popupid'+popup.popupId).addClass('transform-rotate-zoom2');
          },500);    
      },
            
      elasticShow: function (popup) {
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},100);
          jQuery('.mbdialog.popupid'+popup.popupId).addClass('transform-elastic1');
          setTimeout(function(){
            jQuery('.mbdialog.popupid'+popup.popupId).addClass('transform-elastic2');
          },300);   
          setTimeout(function(){
            jQuery('.mbdialog.popupid'+popup.popupId).addClass('transform-elastic3');
          },640);  
      }, 
      
      slidedownShow: function (popup) {
        var timeout_ms = 0;
        if(mbPopupParams.isAjax!=1 && popup.showWhen==1) timeout_ms = 500;        
        //verticalPosition 4 means the block will push content down and popup won't appear above but top of it. This is why we use margin.
        if(popup.verticalPosition==4){      
          jQuery('.mbdialog.popupid'+popup.popupId).css("margin-top","-"+jQuery('.mbdialog.popupid'+popup.popupId).outerHeight()+"px");
        }else{
          jQuery('.mbdialog.popupid'+popup.popupId).css({top:"-"+jQuery('.mbdialog.popupid'+popup.popupId).outerHeight()+"px"});
        }                      
        if(!mb_popup.cssTransitions() && !mb_popup.checkIfMobile()) {
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},100+timeout_ms)
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).addClass('popuptransition')},110+timeout_ms)
          if(popup.verticalPosition==4){
            setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({"margin-top":"0px"})},120+timeout_ms)                   
          }else if(popup.verticalPosition==1 || popup.verticalPosition==1){ //if define final position px from top or from top of defined element              
            setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({top:popup.verticalPositionPx+"px"})},120+timeout_ms)                      
          }else{
            //if define final position px from bottom        
            setTimeout(function(){
              var topPosition = jQuery(window).height()-jQuery(".popupid"+popup.popupId+" .dialogBody").outerHeight()-popup.verticalPositionPx;
              jQuery('.mbdialog.popupid'+popup.popupId).animate({top:topPosition+"px"},700);
            },120+timeout_ms)
          }         
        }else{                                                                    
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},100+timeout_ms)
          if(popup.verticalPosition==4){
            setTimeout(function(){
              jQuery('.mbdialog.popupid'+popup.popupId).animate({"margin-top":"0px"},700)
            },1+timeout_ms)                      
          }else if(popup.verticalPosition==1 || popup.verticalPosition==3){  
            setTimeout(function(){
              jQuery('.mbdialog.popupid'+popup.popupId).animate({top:popup.verticalPositionPx+"px"},700)
            },1+timeout_ms)
          }else{        
            setTimeout(function(){
              var topPosition = jQuery(window).height()-jQuery(".popupid"+popup.popupId+" .dialogBody").outerHeight()-popup.verticalPositionPx;
              jQuery('.mbdialog.popupid'+popup.popupId).animate({top:topPosition+"px"},700);
            },120+timeout_ms)
          } 
        }
      },
            
      slideupShow: function (popup) { 
        if(popup.verticalPosition==4){ //verticalPosition 4 can not have slide up animation, use default display instead
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},50)
          return;
        }
        //1=px from top,2=px from bottom                  
        if(popup.verticalPosition==1 || popup.verticalPosition==3){ 
          jQuery('.mbdialog.popupid'+popup.popupId).css({top:jQuery(window).height()+"px"});           
          setTimeout(function(){jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"})},100)      
          jQuery('.mbdialog.popupid'+popup.popupId).animate({top:popup.verticalPositionPx+"px"},800);         
        }else{        
          var popupHeight = jQuery('.mbdialog.popupid'+popup.popupId).height();
          if(popupHeight==0) popupHeight = 600; //jquery 1.3 fix
          jQuery('.mbdialog.popupid'+popup.popupId).css({bottom:"-"+popupHeight+"px"});        
          setTimeout(function(){
            jQuery('.mbdialog.popupid'+popup.popupId).css({display:"block"});                        
            jQuery('.mbdialog.popupid'+popup.popupId).animate({bottom:popup.verticalPositionPx+"px"},450);                                                      
          },100)                                                           
        } 
      },
      
      randomString: function (length,current) {
        current = current ? current : '';
        return length ? rand( --length , "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkldpqzsjhiunbhfcjseepudpnuvwxyz0123456789".charAt( Math.floor( Math.random() * 60 ) ) + current ) : current;       
      },       
           
      checkIfMobile: function () {
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
          return true;
        }
        return false;
      }, 
           
      cssTransitions: function () {
        if( 'WebkitTransition' in document.body.style ||
            'MozTransition' in document.body.style ||
            'OTransition' in document.body.style ||
            'transition' in document.body.style
        ) {
          return true;
        }
        return false;
      },
      
      gaTracking: function(popup,action){
        if(!mbPopupParams.doGaTracking) return; 
        if(typeof ga != "undefined"){
          ga('send', 'event', 'Magebird Popup', action+' - Popup Id '+popup.popupId, popup.title);
        }else if(typeof _gaq != "undefined"){
          _gaq.push(['_trackEvent', 'Magebird Popup', action+' - Popup Id '+popup.popupId, popup.title]);
        }else if(typeof dataLayer != "undefined"){
          dataLayer.push({'event': 'gaEvent','gaEventCategory': 'Magebird Popup','gaEventAction': action+' - Popup Id '+popup.popupId,'gaEventLabel': popup.title})
        }
      },
      
      getCookie: function(cname) {
          var name = cname + "=";
          var ca = document.cookie.split(';');
          for(var i=0; i<ca.length; i++) {
              var c = ca[i];
              while (c.charAt(0)==' ') c = c.substring(1);
              if (c.indexOf(name) != -1) return decodeURIComponent(c.substring(name.length,c.length));
          }
          return "";
      },
      
      setCookie: function(cname, cvalue, exdays, urlencode) {
          var d = new Date();
          d.setTime(d.getTime() + (exdays*24*60*60*1000));
          var expires = "expires="+d.toUTCString();
          if(urlencode) cvalue = encodeURIComponent(cvalue)                                               
          document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
      },
      /*
      cookiename:cookievalue=expireTime|cookiename:cookievalue[key_value,key2_value]=expireTime
      */
      getPopupCookie: function(cookieName, alsoExpirePart){         
        var cookie = mb_popup.getCookie('popupData');
        cookieName = cookie.split(cookieName+":");
        if(!cookieName[1]){        
          if(cookieName == 'lastSession' && mb_popup.getCookie('lastPopupSession')){
            value = mb_popup.getCookie('lastPopupSession');
          }else if(cookieName == 'lastPageviewId' && mb_popup.getCookie('lastPageviewId')){
            value = mb_popup.getCookie('lastPageviewId');
          }else{
            return '';
          }
        }else{
          value = cookieName[1].split("|");
          value = value[0];
        }  
        
        if(!alsoExpirePart){
          value = value.split("=");                                  
          if(value[1]){
            expire = value[1];
            var curTime = parseInt(new Date().getTime()/1000)+(mb_popup.serverTime-parseInt(mb_popup.clientTime/1000));
            if(expire<curTime) return '';
          }                   
          value = value[0];
        } 
    
        return value;  
      }, 
        
      setPopupCookie : function(cookieName,value,expireTime){
        if(expireTime){
          var time = parseInt(new Date().getTime()/1000)+(mb_popup.serverTime-parseInt(mb_popup.clientTime/1000));              
          expireTime = parseInt(expireTime)+parseInt(time);   
          value += "="+expireTime;        
        }                                
        if(mb_popup.getCookie('popupData')){
          var cookie = mb_popup.getCookie('popupData');          
          var oldVal = mb_popup.getPopupCookie(cookieName, true);
          if(cookie.indexOf(cookieName)!=-1){          
            var oldCookiePart = cookieName+":"+oldVal;
            var newCookiePart = cookieName+":"+value;
            cookie = cookie.replace(oldCookiePart,newCookiePart)             
          }else{
            cookie += "|"+cookieName+":"+value;
          }
        }else{
          cookie = cookieName+":"+value;      
        }        
        mb_popup.setCookie('popupData', cookie, 365, true);    
      },      
      
      dontShowAgain: function(elem){
        var popupId = jQuery(elem).closest(".mbdialog").attr('data-popupid')
        if(jQuery(".popupid"+popupId+" .rememberMe").is(':checked')){
          mb_popup.setPopupIdsCookie('setCookieManually',mb_popups[popupId])
        }else{                      
          mb_popup.setPopupIdsCookie('setCookieManually',mb_popups[popupId],true)
        }
      },
      
      setPopupIdsCookie: function(event,popup,remove,cookieId,cookieTime) {           
          if(window.location.href.indexOf("magebird_popup/index/preview/")!=-1 || window.location.href.indexOf("popup/index/template/")!=-1){
            return;
          }
          //we need this conditions because setPopupIdsCookie is called in every popup, even if popup has set to be shown every time for example 
          if(event=='goalCompleted' && popup && popup.showingFrequency!=6){           
            return;          
          }else if(event=='closePopup' && popup){ 
            //if not show until user close it OR nif not show until user close it or click inside   
            if(popup.showingFrequency!=1 && popup.showingFrequency!=5) return;
          }else if(event=='clickInside' && popup){    
            if(popup.showingFrequency!=4 && popup.showingFrequency!=5) return;
            //if not show only once
          }else if(popup && popup.showingFrequency!=2 && event!='setCookieManually' && event!='goalCompleted'){
            return;
          } 
          //example for manually: mb_popup.setPopupIdsCookie('setCookieManually',0,0,'eere',10);              
          var cookieId = cookieId ? cookieId : popup.cookieId;          
          var cookieTime = cookieTime ? cookieTime : popup.cookieTime;          
          var popupCookieIds = mb_popup.getCookie('popup_ids').split('|');
          var newPopupCookieIds = new Array();
          var popupCookieIdExist = false;          
          popupCookieIds.forEach(function(popupId) {                    
              if(!popupId) return;           
              explode = popupId.split('=');
              expire = explode[1];
              popupCookieId = explode[0];                             
              if(popupCookieId==cookieId){              
                if(remove){
                  expire = mb_popup.serverTime-1000;
                }else{
                  expire = mb_popup.serverTime+cookieTime*60*60*24;
                }
                                                
                popupCookieIdExist = true;                   
              }
              newPopupCookieIds.push(popupCookieId+"="+expire)             
          });  
          if(popupCookieIdExist==false && !remove){                        
            expire = mb_popup.serverTime+cookieTime*60*60*24;     
            newPopupCookieIds.push(cookieId+"="+expire)
          }          
          //set 1 year because we store different cookies to the same cookie
          mb_popup.setCookie('popup_ids',newPopupCookieIds.join('|'),365)    
      }, 
      
      hasOverlay: function(popup){     
        if(popup.overlay==3 || popup.overlay==4){
          return false;
        }
        return true;              
      },
      
      correctHttps: function(url){
        if(window.location.protocol == 'https:'){            
          if(url.substring(0,6)!=='https:'){   
            url = "https:"+url.substring(5);  
          }
        }else{
          if(url.substring(0,5)!=='http:'){
            url = "http:"+url.substring(6);  
          }  
        }
        return url;      
      }, 
      
      callbackRequest: function(){          
          mb_popup.showPopupsUrl = decodeURIComponent(mbPopupParams.rootUrl)+'magebirdpopup.php?changePubDir=1&rand='+Math.floor((Math.random() * 100000000) + 1);
          mb_popup.statsUrl = decodeURIComponent(mbPopupParams.rootUrl)+'magebirdpopup.php?action=stats&rand='+Math.floor((Math.random() * 100000000) + 1); 
          jQuery.ajax({
            type: mbPopupParams.requestMethod,
            url: mb_popup.showPopupsUrl,
            data:"storeId="+mbPopupParams.storeId+"&previewId="+mbPopupParams.previewId+"&templateId="+mbPopupParams.templateId+"&nocache=1&popup_page_id="+mbPopupParams.popupPageId+"&filterId="+mbPopupParams.filterId+"&ref="+encodeURIComponent(document.referrer)+"&url="+encodeURIComponent(window.location.href)+"&baseUrl="+encodeURIComponent(mbPopupParams.rootUrl)+"&customParams="+encodeURIComponent(mbPopupParams.customParams)+"&cEnabled="+navigator.cookieEnabled,
            success: function(response){  
              //prevent recursive call for case if page not found but doesn't trigger error and magento page with this js file is displayed
              if(typeof window.popupAjaxMade === 'undefined'){            
                jQuery("body").append(response);
                window.popupAjaxMade = true;                        
              }        
            },
            error: function(){
              console.log('Unknown error for url '+mb_popup.showPopupsUrl);          
            }                       
          });       
      },    
      totalTime: function(){
        var totalTime;
        var cookieData;
        var curTime;        
                
        setInterval(function(){
            curTime = parseInt(new Date().getTime()/1000)+(mb_popup.serverTime-parseInt(mb_popup.clientTime/1000))
            //for case if user has opened more tabs of the same site to prevent double counting
            if((new Date().getTime()-mb_popup.getPopupCookie('lastTimer'))>900){
              mb_popup.setPopupCookie('lastTimer',parseInt(new Date().getTime()))
              //if totalTime cookie not expired
              if(mb_popup.getPopupCookie('totalTime')){
                //cookieData[0] is cookie value, cookieData[1] is expire time
                cookieData = mb_popup.getPopupCookie('totalTime',true);
                //because setPopupCookie method has only option to delete or override expire time but not to leave it 
                mb_popup.setPopupCookie('totalTime',parseInt(cookieData.split('=')[0])+1,cookieData.split('=')[1]-parseInt(parseInt(curTime)));             
              }else{                             
                mb_popup.setPopupCookie('totalTime',1,7200);              
              }         
            }                           
        },1000)                 
      },    
      
      cartStats: function(popup){
          //need for conversion statistics if user added product to cart after seen popup
          var lastPopups = mb_popup.getPopupCookie('lastPopups').split(",");
          var idExists = false; 
          for (i = 0; i < lastPopups.length; i++) {
              if(popup.popupId == lastPopups[i]) idExists = true;
          }            
          if(idExists) return;
          //need for conversion statistics if user receives popup after added product to cart
          if(mb_popup.getPopupCookie('cartAdded')){
            if(mb_popup.showPopupsUrl.indexOf('magebirdpopup.php')==-1){
              var url = decodeURIComponent(mbPopupParams.baseUrl)+'magebird_popup/index/popupCartsCount?rand='+Math.floor((Math.random() * 100000000) + 1);
            }else{
              var url = decodeURIComponent(mbPopupParams.rootUrl)+'magebirdpopup.php?action=popupCartsCount&rand='+Math.floor((Math.random() * 100000000) + 1);
            }    
            jQuery.ajax({
              type: mbPopupParams.requestMethod,
              url: url,
              data:"popupId="+popup.popupId          
            });                       
          }       
      }           
                         
  }  
   
  mb_popup.totalTime();  
    

  if(typeof popupIntervalChecker === 'undefined'){ //to prevent override false if for some buggy reason main.js is loaded twice
    var popupIntervalChecker = false;
  }      
  var popupJqueryListener = setInterval(function(){
    if(typeof jQuery !== 'undefined' && typeof mbPopupParams !== 'undefined' && jQuery('body').length && !popupIntervalChecker && mbPopupParams.page!=0){
                
      clearInterval(popupJqueryListener) 
      popupIntervalChecker = true;  

      mbPopupParams.requestMethod = mbPopupParams.requestType == 2 ? "POST" : "GET";  
      mb_popup.showPopupsUrl = decodeURIComponent(mbPopupParams.rootUrl)+mbPopupParams.pubDir+'magebirdpopup.php?rand='+Math.floor((Math.random() * 100000000) + 1);
      mb_popup.statsUrl = decodeURIComponent(mbPopupParams.rootUrl)+mbPopupParams.pubDir+'magebirdpopup.php?action=stats&rand='+Math.floor((Math.random() * 100000000) + 1);                   
      jQuery.ajax({
        type: mbPopupParams.requestMethod,
        url: mb_popup.showPopupsUrl,
        data:"storeId="+mbPopupParams.storeId+"&previewId="+mbPopupParams.previewId+"&templateId="+mbPopupParams.templateId+"&nocache=1&popup_page_id="+mbPopupParams.popupPageId+"&filterId="+mbPopupParams.filterId+"&ref="+encodeURIComponent(document.referrer)+"&url="+encodeURIComponent(window.location.href)+"&baseUrl="+encodeURIComponent(mbPopupParams.rootUrl)+"&customParams="+encodeURIComponent(mbPopupParams.customParams)+"&cEnabled="+navigator.cookieEnabled,
        success: function(response){          
          //prevent recursive call for case if page not found but doesn't trigger error and magento page with this js file is displayed                                
          if(typeof window.popupAjaxMade === 'undefined'){            
            jQuery("body").append(response);
            window.popupAjaxMade = true;                        
          }            
        },
        error: function(error){  
          if(error.readyState!=0){
            mb_popup.callbackRequest();
          }                          
        }           
      }); 
      
    }
  },10)       