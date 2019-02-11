(function(){ 
  var popupJqueryListener = setInterval(function(){  
    if(typeof jQuery !== 'undefined'){            
      clearInterval(popupJqueryListener)        
      jQuery(document).ready(function() {
        if(jQuery('.magebird-popup-index-edit').length==0 && jQuery('.magebird-popup-index-edit').length==0) return; 
        var clearcacheMsg = jQuery('.clearcache').attr('title');
        jQuery(".clearcache").after('<span class="clearcacheTip popupTooltip" title="'+clearcacheMsg+'">(?)</span>');
         
        jQuery('body').on('change', '#popup_main_page_id', function(){                      
          showIdsField();
        });
        jQuery('body').on('change', '#popup_position_horizontal_position', function(){                      
          showHorizontalPosField();
        });  
        
        jQuery('body').on('change', '#popup_position_vertical_position', function(){                      
          showVerticalFields();
        });                     
                             
        showIdsField();
        showHorizontalPosField();
        showVerticalFields();
        activateTooltip();
        jQuery('body').on('change', '#popup_main_width_unit', function(){                      
          widthUnitListener();
        });     
  
        widthUnitListener();
      });
    }
  },50)
  
  function widthUnitListener(){
      var widthUnit = jQuery('#popup_main_width_unit').val();
      if(widthUnit==1){
        jQuery("#popup_position_horizontal_position").prop("disabled", false);
      }else{
        jQuery("#popup_position_horizontal_position").prop("disabled", true);
        jQuery("#popup_position_horizontal_position").val(1);
      }    
  }
  
  function showIdsField(){
      var showAt = jQuery('#popup_main_page_id').val();
      if(jQuery.inArray('2', showAt)==-1){
        jQuery('#popup_main_product_ids').parent().parent().hide();
      }else{
        jQuery('#popup_main_product_ids').parent().parent().show();
      }
      
      if(jQuery.inArray('3', showAt)==-1){
        jQuery('#popup_main_category_ids').parent().parent().hide();  
      }else{
        jQuery('#popup_main_category_ids').parent().parent().show();
      }  
      
      if(jQuery.inArray('6', showAt)==-1){
        jQuery('#popup_main_specified_url').parent().parent().hide();  
      }else{
        jQuery('#popup_main_specified_url').parent().parent().show();
      }                                    
  }
  
  function showHorizontalPosField(){
      var verticalPos = jQuery('#popup_position_horizontal_position').val();
      if(verticalPos==1){
        jQuery('#popup_position_horizontal_position_px').parent().parent().hide();
      }else{
        jQuery('#popup_position_horizontal_position_px').parent().parent().show();
      }                                  
  }  
  
  function showVerticalFields(){
      var verticalPos = jQuery('#popup_position_vertical_position').val();
      if(verticalPos==4){
        jQuery('#popup_position_vertical_position_px').parent().parent().hide();
        //jQuery('#element_id_position').parent().parent().hide();
      }else{
        jQuery('#popup_position_vertical_position_px').parent().parent().show();
        //jQuery('#element_id_position').parent().parent().show();
      }                                 
  }   
  
  function activateTooltip(){
    jQuery('.popupTooltip').hover(function(e){ // Hover event
    var titleText = jQuery(this).attr('title');
    jQuery(this).data('tiptext', titleText).removeAttr('title');
    jQuery('<p class="tooltip"></p>')
      .html(titleText)
      .appendTo('body')
      .css('top', (e.pageY -50) + 'px')
      .css('left', (e.pageX - 340) + 'px')
      .fadeIn('fast');
    }, function(){ // Hover off event
      jQuery(this).attr('title', jQuery(this).data('tiptext'));
      jQuery('.tooltip').remove();
    });
  }  
})();

function setIssetCookie(cname) {
    var d = new Date();
    d.setTime(d.getTime() + (900*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=1; " + expires + "; path=/";        
}

function getIssetCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function disableInputs(selector){
    var errorMsg = '';
    var widgetButtonListen = setInterval(function(){
      if(jQuery('.error-msg.mb_widget').length>0){
        clearInterval(widgetButtonListen)
        errorMsg = jQuery('.error-msg.mb_widget').text();
        jQuery(".adminhtml-magebird-popup-edit #insert_button").attr("onclick",'return false;'); 
        jQuery(".adminhtml-magebird-popup-edit #insert_button").attr('id','insertPopupWidget');
        jQuery(selector + ' input,'+selector + ' textarea,' + selector + ' button,' + selector + ' select').attr("disabled",true); 
      }
    },10)   
        
    if ( jQuery.isFunction(jQuery.fn.on) ) {
      jQuery('body').on('click', '#insertPopupWidget', function(){                      
        alert(errorMsg)
        return false;
      });     
    }else{
      jQuery("#insertPopupWidget").click(function() {
        alert(errorMsg)
        return false;       
      });    
    }          
} 

function dismissNetworkError(el){
    var element = jQuery(el)
    jQuery(el).text('dismissing...');
    var url = jQuery(el).attr('data-url');
    jQuery.ajax({    
      type: "POST",
      url: url,
      data:'form_key='+window.FORM_KEY,
      success: function(response){      
        if(response.success=='false'){
          alert(response.error)
        }else{
          element.closest('.message-error').fadeOut();                 
        }          
      }
    });

};