<?php
namespace Magebird\Popup\Block\Adminhtml\Template;
use Magebird\Popup\Model\Status;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_collectionFactory;

    protected $_popup;

    protected $_storeManager;
    
    protected $_currency;   
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebird\Popup\Model\Popup $popup,
        \Magebird\Popup\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_popup = $popup;
        $this->_resource = $resource;
        $this->_storeManager = $context->getStoreManager();
        $this->_currency = $currency;                         
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('popupGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('DESC');
        $this->setDefaultLimit('100');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }


    protected function _prepareCollection()
    {
      $this->parseAllTemplates();      
      $collection = $this->_collectionFactory->create();
      $collection->setOrder('template_id');
      $collection->getSelect()->order('position DESC');
      $collection->getSelect()->order('template_id ASC');      
      //$collection->setDefaultDir('ASC');             
      $this->setCollection($collection);      
      return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
      $this->addColumn('template', array(
          'header'    => __('Template preview image'),
          'align'     =>'left',
          'width'     => '420px',
          'index'     => 'comment_id',
          'filter'    => false,
          'sortable'  => false,          
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Image',
      ));                        	                  

	    $this->addColumn('title', array(
          'header'    => __('Title'),
          'align'     =>'left',
          'index'     => 'title',
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Title',
      ));  
      
      $this->addColumn('action',
       array(
          'header'    =>  __('Action'),
          'width'     => '200px',
          'type'      => 'text',
          'filter'    => false,
          'sortable'  => false,
          'is_system' => true,
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Templateaction',
      ));                

        return parent::_prepareColumns();
    }

    public function parseAllTemplates(){
        /*
        $stores = $this->_storeManager->getStores(false);
        $numStores = count($stores);        
        $sql = "SELECT COUNT(*) FROM ".$this->_resource->getTableName('magebird_popup_template');
        $numTemplates = $connection->fetchOne($sql);
        */
        $connection= $this->_resource->getConnection();
        $sql = "SELECT COUNT(*) FROM ".$this->_resource->getTableName('magebird_popup_content')."
                WHERE is_template=1";
        $numParsed = $connection->fetchOne($sql);
        if($numParsed==0){
          $this->_popup->parsePopupContent(null,true);
        }       
    }
          
}
