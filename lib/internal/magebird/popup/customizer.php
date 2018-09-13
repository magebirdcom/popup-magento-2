<?php
require_once('product_model.php');   
class customizer{

  public function getPopupsCustomizer($popups){
    foreach($popups as $popup){ 
      //for other type we set view on stats ajax call   
      if(($popup['background_color']==3 || $popup['background_color']==4) && $popup['show_when']==1){
        if(!$this->helper->getIsCrawler()){       
            $this->setPopupData($popup['popup_id'],'views',$popup['views']+1);
            $this->uniqueViewStats($popup['popup_id']); 
        }
      } 
    }
    return $popups;
  }
  
}  