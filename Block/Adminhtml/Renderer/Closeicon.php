<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
 
/**
* CustomFormField Customformfield field renderer
*/
class Closeicon extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $request;
    protected $popup;
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\App\Request\Http $request,
        \Magebird\Popup\Model\Popup $popup,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {

        $this->_assetRepo = $assetRepo;
        $this->request = $request;
        $this->_popup = $popup;
        parent::__construct($factoryElement,$factoryCollection,$escaper,$data);
    }
    public function getElementHtml(){            
        $action = $this->request->getActionName();
        if($action=="copy"){
          $id = $this->request->getParam('copyid');
          $closeStyle = $this->_popup->load($id)->getData('close_style');        
        }elseif($action=="duplicate"){
          $popupId = $this->request->getParam('copyid');
          $closeStyle = $this->_popup->load($popupId)->getData('close_style');        
        }else{
          $popupId = $this->request->getParam('id');
          $closeStyle = $this->_popup->load($popupId)->getData('close_style');        
        }
        
        //$folder = $this->getViewFileUrl('Magebird_Popup::images/widget.png'); 
        $html = "<div class='closeIcons'>
                 <input name='close_style' type='radio' value='5' /><span style='margin-right:10px;'>Don't show close icon</span>
                 <input name='close_style' type='radio' value='2' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_big_preview.png')."' />
                 <input name='close_style' type='radio' value='3' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_simple_dark_preview.png')."' />
                 <input name='close_style' type='radio' value='4' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_simple_white_preview.png')."' />
                 <input name='close_style' type='radio' value='8' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_big_x.png')."' /><br>
                 <input name='close_style' type='radio' value='6' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_big_x_d.png')."' />
                 <input name='close_style' type='radio' value='9' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_big_x_bold.png')."' />
                 <input name='close_style' type='radio' value='10' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_big_x_bold_d.png')."' />
                 <input name='close_style' type='radio' value='11' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/white_circle.png')."' />
                 <input name='close_style' type='radio' value='1' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_dark.png')."' />
                 <input name='close_style' type='radio' value='7' /><img src='".$this->_assetRepo->getUrl('Magebird_Popup::images/close_transparent.png')."' />
                 </div>
                  ";
        
        if($closeStyle){
          $html = str_replace("value='$closeStyle'", "value='$closeStyle' checked='checked'", $html);
        } 
          
        return $html;

    }
}