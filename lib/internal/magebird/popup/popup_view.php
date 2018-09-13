<?php

class popup_view{

	var $helper;
  
	public function toHtml($collection,$helper,$currentProduct,$cartProduct){  
		$this->helper = $helper;    
		$style = "";
		$script = "";
		$popups = array();            
		foreach($collection as $popup): 
		//template preview page
		if(!isset($popup['popup_id'])) $popup['popup_id'] = $popup['template_id'];
      
		//image popup  
		if($popup['popup_type']==1){  
			if($popup['image'] && $popup['width_unit']==2){
				$imgSrc = urldecode($this->helper->getParam('baseUrl')) . "pub/media/" . $popup['image'];          
			}elseif($popup['image']){
				$imgSrc = urldecode($this->helper->getParam('baseUrl')) . "pub/media/" . $popup['image'];          
			}     
			$style .= '.mbdialog.popupid'.$popup['popup_id'].' .dialogBody img{width:100%; height:auto;}';
        
			if(!$popup['image']){
				$content = '';
			}elseif($popup['url']){
				$content = "<a class='imgType' href='".$popup['url']."'><img src='".$imgSrc."' /></a>";    
			}else{
				$content = "<img src='".$imgSrc."' />";       
			}              
		}else{
			$content = $popup['parsed_content'];
			$content = $this->parseProdAttr($content,$currentProduct);        
			$content = $this->parseProdAttr($content,$cartProduct,true);  
		}           
    
		//editor bugfix for tables -> see http://stackoverflow.com/questions/22803723/border-spacing-css-property-overrides-table-cellspacing                      
		$cellspacing = $this->getHtmlAttributeVal("cellspacing",$content);
		$popupBackground = $popup['popup_background'];
		if(strpos($popupBackground,'#') === false) $popupBackground = "#".$popupBackground;
		$borderColor = $popup['border_color'];
		if(strpos($borderColor,'#') === false) $borderColor = "#".$borderColor;    
		$popup_js['popupId'] = $popup['popup_id'];
		if($popup['vertical_position']==4) $popup['background_color'] = 4; //if push content down
		$popup_js['overlay'] = $popup['background_color'];
		$popup_js['appearType'] = $popup['appear_effect'];
		//$popup_js['closeEffect'] = $popup['close_effect'];
		$popup_js['closeEffect'] = 1;
		$popup_js['startTime'] = '';
		$popup_js['showWhen'] = $popup['show_when'];
		$popup_js['scrollPx'] = $popup['scroll_px'];
		//if show on click
		if($popup['show_when']==4){
			$popup_js['selector'] = $popup['click_selector'];
			//if show on hover
		}elseif($popup['show_when']==5){
			$popup_js['selector'] = $popup['hover_selector'];
			$popup_js['closeOnOut'] = $popup['close_on_hoverout']; 
		}
		$popup_js['secondsDelay'] = $popup['seconds_delay'];
		$popup_js['totalSecondsDelay'] = isset($popup['total_seconds_delay']) ? $popup['total_seconds_delay'] : 0;
		$popup_js['cartSecondsDelay'] = isset($popup['cart_seconds_delay']) ? $popup['cart_seconds_delay'] : 0;
		$popup_js['closeTimeout'] = $popup['close_on_timeout'];
		$popup_js['closeOnOverlay'] = $popup['close_on_overlayclick'];
		$popup_js['title'] = $popup['title'];
		$popup_js['content'] = $content;
		$popup_js['horizontalPosition'] = $popup['horizontal_position'];
		$popup_js['horizontalPositionPx'] = $popup['horizontal_position_px'];
		$popup_js['verticalPosition'] = $popup['vertical_position'];
		$popup_js['verticalPositionPx'] = $popup['vertical_position_px'];
		if(isset($popup['element_id_position'])){
			$popup_js['elementIdPosition'] = $popup['element_id_position'];
		}else{
			$popup_js['elementIdPosition'] = '';
		}
        
		$popup_js['closeStyle'] = $popup['close_style'];
		if($helper->getParam('previewId') || $helper->getParam('templateId')){
			$popup_js['makeStats'] = false;
			$popup_js['cookieId'] = "c".rand(1,10000); //need this for countdown timer
		}else{
			$popup_js['makeStats'] = true;
			$popup_js['cookieTime'] = $popup['cookie_time'];
			$popup_js['cookieId'] = $popup['cookie_id'];
			$popup_js['showingFrequency'] = $popup['showing_frequency'];         
		}          
		$popups[$popup['popup_id']] = $this->correctHttp($popup_js);  
     
		if($cellspacing = $this->getHtmlAttributeVal("cellspacing",$content)){
			$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody table{border-spacing:{$cellspacing}px}";
		}
		if($cellpadding = $this->getHtmlAttributeVal("cellpadding",$content)){
			$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody > table > tbody > tr > td{padding:{$cellpadding}px !important}";
		}  
      
		$border = $popup['border_color'] ? "border:{$popup['border_size']}px solid {$borderColor};" : "";
		$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody{{$border};padding:{$popup['padding']}px;}";
		if($popup['width_unit']==2){
			$maxWidth = $popup['max_width'] ? " max-width:".$popup['max_width']."px;" : "";    
			$style .= ".mbdialog.popupid{$popup['popup_id']}{width:".$popup['width']."%;".$maxWidth."}";    
		}else{
			$style .= ".mbdialog.popupid{$popup['popup_id']}{width:".($popup['width']+($popup['border_size']*2)+($popup['padding']*2))."px;}";    
		}  
      
		if($popup['corner_style']==1){ //if sharp corners
			$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody{border-radius: {$popup['border_radius']}px; -webkit-border-radius:{$popup['border_radius']}px; -moz-border-radius: {$popup['border_radius']}px; -o-border-radius: {$popup['border_radius']}px;}";
		}elseif($popup['corner_style']==2){ //if circle popup
			$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody, .mbdialog.popupid{$popup['popup_id']} .dialogBody table{border-radius: 50%; -webkit-border-radius: 50%; -moz-border-radius: 50%; -o-border-radius: 50%;}";
			$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody{height:{$popup['width']}px;}";
		}                                                                                          
      
		if($popup['background_color']==2){ //popup overlay background 1=white,2=dark,3 and 4=no overlay
			$style .= ".dialogBg.popupid{$popup['popup_id']}{background: rgba(0, 0, 0, 0.65);}";           
		}
    
		if($popup['close_on_overlayclick']==0){
			$style .= ".dialogBg.popupid{$popup['popup_id']}{cursor:auto;}";
		}    
    
		$style .= ".mbdialog.popupid{$popup['popup_id']} .dialogBody{background-color: {$popupBackground};".($popup['popup_shadow']!=1 ? "-moz-box-shadow:none;-webkit-box-shadow:none;box-shadow:none;" : "")."}";
		$style .= ".mbdialog.popupid{$popup['popup_id']} table{border-spacing:none !important;}";
		$style .= ".popupid{$popup['popup_id']} .dialogClose.style4,
		.popupid{$popup['popup_id']} .dialogClose.style6,
		.popupid{$popup['popup_id']} .dialogClose.style7,
		.popupid{$popup['popup_id']} .dialogClose.style8,
		.popupid{$popup['popup_id']} .dialogClose.style9,
		.popupid{$popup['popup_id']} .dialogClose.style10,
		.popupid{$popup['popup_id']} .dialogClose.style11,
		.popupid{$popup['popup_id']} .dialogClose.style3{
		top:".($popup['border_size']+0)."px;right:".($popup['border_size']+0)."px;
		}";
		$style .= ".mbdialog.popupid{$popup['popup_id']}{";
		if($popup['background_color']==3){
			$style .= "position:fixed;";  
		}
		if($popup['vertical_position']==4){
			$style .= "position:relative;";  
		}      
		if($popup['horizontal_position']==1){ //1=center
			if($popup['width_unit']==2){
				$style .= "left: 0;";
				$style .= "right: 0;";
				$style .= "margin:0 auto;";
			}else{
				$style .= "left:50%;";
				$style .= "margin-left:-".(($popup['width']+($popup['border_size']*2)+($popup['padding']*2))/2)."px;";          
			}
		}elseif($popup['horizontal_position']==2){ //px from left of the screen
			$style .= "left:{$popup['horizontal_position_px']}px;";
			$style .= "margin:0;";
		}elseif($popup['horizontal_position']==3){ //px from right of the screen
			$style .= "right:{$popup['horizontal_position_px']}px;";
			$style .= "left:auto;";
			$style .= "margin:0;";
		}elseif($popup['horizontal_position']==4){ //Define px from center to left          
			$style .= "left:50%;";
			$style .= "margin-left:-".(($popup['width']+($popup['border_size']*2)+($popup['padding']*2))/2+$popup['horizontal_position_px'])."px;";
		}elseif($popup['horizontal_position']==5){ //Define px from center to right
			$style .= "right:50%;";
			$style .= "margin-left:0px !important;";
			$style .= "margin-right:-".(($popup['width']+($popup['border_size']*2)+($popup['padding']*2))/2+$popup['horizontal_position_px'])."px;";
		}           
		if($popup['vertical_position']==1){ //px from top
			$style .= "top:{$popup['vertical_position_px']}px;";
		}elseif($popup['vertical_position']!=4){ //px from bottom
			$style .= "bottom:{$popup['vertical_position_px']}px;";
		}
		$style .= "}";
      
		if($popup['custom_css']){      
			$style .= $this->getPrefixedCss($popup['custom_css'],'.mbdialog.popupid'.$popup['popup_id']);
		} 
		$style = $this->correctHttp($style,true);
		if($popup['custom_script']){
			$script .= $popup['custom_script']."\n";
		}          
		endforeach; 
    
		?>     
		<script data-cfasync="false" type="text/javascript">  
			//<![CDATA[
			if(typeof mb_popups === 'undefined'){
				var mb_popups = null;
			}    

			var popupScriptListener = setInterval(function(){
					//must wait for body and jquery to be loaded!
					//mb_popups is for extra check to avoid any infinitive loop 
					if(!mb_popups){  
					
						clearInterval(popupScriptListener);    
						var mb_popup_style = jQuery('<style type="text/css">'+<?php echo json_encode($style); ?>+'</style>');
						jQuery('html > head').append(mb_popup_style);  
						mb_popups = <?php echo json_encode($popups) ?>;                        
						mb_popup.serverLocalTime = <?php echo (time()+@date("Z"))?>;
						mb_popup.serverTime = <?php echo time()?>;
						mb_popup.clientTime = new Date().getTime();
						for (var key in mb_popups) {
							if (!mb_popups.hasOwnProperty(key)) continue;
							mb_popups[key].dialogActive = false;
							mb_popups[key].removed = false;
							mb_popup.prepareDialog(mb_popups[key]);              
							if(mb_popups[key].makeStats!=false){
								mb_popup.showStatsGlobal = true;
							} 
						}                                   
						if(mb_popup.showStatsGlobal){
							mb_popup.onbeforeunloadHandler();
						}      
             
						<?php echo $script?>
					}
				},10)
			//]]>
		</script>
		<?php    
	}	  

	public function parseProdAttr($content,$product,$cartProd=false){  
		if($cartProd){
			$attrs = explode('{{productCartAttribute="',$content);
		}else{
			$attrs = explode('{{productAttribute="',$content);
		}    
		unset($attrs[0]); 
		if(count($attrs>0)){     
			if(!$product) return $content;   
            
			foreach($attrs as $attr){
				$value = explode('"}}',$attr);
				$attrCode = $value[0];
				if($cartProd){
					$search = '{{productCartAttribute="'.$attrCode.'"}}';
				}else{
					$search = '{{productAttribute="'.$attrCode.'"}}';
				}                      
				if(isset($product[$attrCode]) && $product[$attrCode]){  
					$replace = $product[$attrCode];                             
				}else{
					$replace = '';
				}            
				if($attrCode=='price'){
					$replace = number_format($replace, 2);
				}                           
				$content = str_replace($search,$replace,$content);                
			}
		}  
		return $content;
	} 
  
	public function getHtmlAttributeVal($attrib, $tag){
		//get attribute from html tag
		$re = '/' . preg_quote($attrib) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
		if(preg_match($re, $tag, $match)){
			return urldecode($match[2]);
		}
		return false;
	} 
  
	public function getPrefixedCss($css,$prefix){
		$parts = explode('}', $css); 
		foreach($parts as &$part){                    
			$checkPart = trim($part);
			if(empty($checkPart)){
				continue;
			}    
          
			$prefix2 = substr(str_shuffle("dpqzsjhiunbhfcjseepudpn"), 0, 6);        
			$partDetails = explode('{',$part);
			if(substr_count($part,"{")==2){
				$mediaQuery = $partDetails[0]."{";
				$partDetails[0] = $partDetails[1];
				$mediaQueryStarted = true;
			}
			//old version had class .dialog, new has .mbdialog          
			$subParts = explode(',', $partDetails[0]);  
			foreach($subParts as &$subPart){    
				if(trim($subPart)=="@font-face"                                            
					|| strpos($subPart,".dialog ")!==false 
					|| strpos($subPart," .dialog")!==false
					|| strpos($subPart,".dialog#")!==false
					|| strpos($subPart,".dialog.")!==false
					|| strpos($subPart,"dialogBg")!==false
					|| (strpos($subPart,".dialog")!==false && strlen($subPart)==7)) continue;

              
				if(strpos($subPart,$prefix)!==false){
					$subPart = trim($subPart);                                
				}elseif(strpos($subPart,".mbdialog")!==false){
					$subPart = str_replace(".mbdialog", $prefix, $subPart);
				}else{
					$subPart = $prefix . ' ' . trim($subPart);
				}                     
              
			}  
               
     
			if(substr_count($part,"{")==2){
				$part = $mediaQuery."\n".implode(', ', $subParts)."{".$partDetails[2];
			}elseif(empty($part[0]) && $mediaQueryStarted){
				$mediaQueryStarted = false;
				$part = implode(', ', $subParts)."{".$partDetails[2]."}\n"; //finish media query
			}else{      
				$part = implode(', ', $subParts)."{".$partDetails[1];
			}  
		}
                      
		$prefixedCss = implode("}\n", $parts);
		return $prefixedCss;   
	} 
  
	public function correctHttp($content,$all=false){
		if($this->helper->isSecure()){
			if($all){
				$patterns = array(
					'http',
				);      
			}else{
				$patterns = array(
					'src="http',
					"src='http",    
					'stylesheet" href="http',
					"stylesheet' href='http",
					'link href="http',
					"link href='http"
				);      
			}
			foreach($patterns as $p){
				//to avoid dobule s, otherwise https would become httpss
				$content = str_replace($p."s", $p, $content);
				$content = str_replace($p, $p."s", $content);
			}    
		}
		return $content; 
	} 
    
}