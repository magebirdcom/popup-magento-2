<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;

class Categories extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $request;
    protected $popup;
    protected $urlBuilder;
    public function __construct(         
        \Magento\Framework\UrlInterface $urlBuilder,        
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
        $this->urlBuilder = $urlBuilder;
        parent::__construct($factoryElement,$factoryCollection,$escaper,$data);
    }
    public function getElementHtml(){
     
        $action = $this->request->getActionName();
        if($action=="copy"){
          $value = '';                  
        }elseif($action=="duplicate"){
          $popupId = $this->request->getParam('copyid');
          $value = $this->_popup->load($popupId)->getData('category_ids');        
        }else{
          $popupId = $this->request->getParam('id');        
          $value = $this->_popup->load($popupId)->getData('category_ids');
        }                       
        
        $url = $this->urlBuilder->getUrl('*/*/categorychooser');
        $html = '<input style="width:80%;" id="popup_main_category_ids" name="category_ids" data-ui-id="popup-edit-tab-cartconditions-fieldset-element-text-category-ids" value="'.$value.'" type="text" class=" input-text admin__control-text"><button id="categories">Select</button>';        
        $html .= "
        <div id='categoryBrowser'>
            <h1>Please wait.... </h1>
        </div>          
        <script type='text/javascript'>// <![CDATA[
            require(
                [
                    'jquery',
                    'Magento_Ui/js/modal/modal'
                ],
                function($,modal) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: 'Choose categories',
                        closeText: 'Close',
                        buttons: [{
                            text: $.mage.__('Submit'),
                            class: '',                    
                            click: function (data) {
                                categories = [];
                                jQuery('.categoryTree form input:checked').each(function(){
                                  categories.push(jQuery(this).val());
                                }) 
                                jQuery(\"input[name='category_ids']\").val(categories.join(','));  
                                this.closeModal();
                            }
                        }],
                        escapeKey: function () {
                            if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                                this.options.isOpen && this.modal[0] === document.activeElement) {
                                this.closeModal();
                            }
                        }
                    };
        
                    var popup = modal(options, $('#categoryBrowser'));
                    $('#categories').on('click',function(e){
                        e.preventDefault();
                        $('#categoryBrowser').modal('openModal');
                        jQuery.ajax({
                            url: '".$url."',
                            type: 'POST',
                            data : {form_key: window.FORM_KEY},
                            success: function(data){
                                jQuery('#categoryBrowser').html(data);
                            },
                            error: function(result){
                                console.log('error');
                            }
                        });                
                    });
        
                }
            );
        // ]]></script>";

        return $html;

    }
}