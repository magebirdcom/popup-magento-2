<?php
namespace Magebird\Popup\Block\Adminhtml\Popup;
use Magebird\Popup\Model\Status;
class Template extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_collectionFactory;

    protected $_popup;

    protected $_storeManager;
    
    protected $_currency;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebird\Popup\Model\Popup $popup,
        \Magebird\Popup\Model\ResourceModel\Popup\CollectionFactory $collectionFactory,
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
        $this->setDefaultSort('popup_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(false);
    }


    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $collection->getSelect()->joinLeft(
            array('po' => $this->_resource->getTableName('magebird_popup_orders')),
            'po.popup_id=main_table.popup_id', 
            array('couponSalesCount'=>'COUNT(order_id)')
        );
  
        $collection->getSelect()->joinLeft(                                                     
            array('o' => $this->_resource->getTableName('sales_order')),
            'po.order_id=o.entity_id',
            array('popupRevenue'=>'ROUND(SUM(base_total_paid),2)')
        );                  
        $collection->getSelect()->joinLeft(                                           
            array('ps' => $this->_resource->getTableName('magebird_popup_stats')),
            'ps.popup_id=main_table.popup_id',
            array('popupSalesCount'=>'popup_purchases','popupVisitors'=>'popup_visitors','totalVisitors'=>'visitors','totalSalesCount'=>'purchases','totalCarts'=>'total_carts','popupCarts'=>'popup_carts')
        );   
        
        $collection->getSelect()->group('main_table.popup_id');  
        $currency = $this->_currency->getCurrencySymbol();                  
        $collection->getSelect()->columns(array('currency' => new \Zend_Db_Expr("'$currency'")));
                                 
        $storeViewId = $this->getStoreId();
        if ($storeViewId) {
            $collection->addStoreFilter($storeViewId);
        }          
              
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
      $this->addColumn('popup_id', array(
          'header'    => __('Id'),
          'align'     =>'left',
          'width'     => '20px',
          'index'     => 'popup_id',
      ));   
            
      $this->addColumn('title', array(
          'header'    => __('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));      
      
      $this->addColumn('status', array(
          'header'    => __('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',          
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
        
      $this->addColumn('action',
       array(
          'header'    =>  __('Action'),
          'width'     => '114',
          'type'      => 'text',
          'filter'    => false,
          'sortable'  => false,
          'is_system' => true,
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Actionlink',
      ));  
        
	    $this->addColumn('views', array(
          'header'    => __('Impressions'),
          'align'     =>'left',
          'index'     => 'views',
          'filter'    => false,
          'width'     => '80px',
      ));  
      
	    $this->addColumn('avg_time', array(
          'header'    => __('Time per <br />view')."<span class='popupTooltip' title='".__("Time per view until any action is taken such as close popup, click inside popup, register, subscribe, ...").")'>(?)</span>",
          'align'     =>'left',
          'width'     => '84px',
          'index'     => 'total_time',
          'filter'    => false,   
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Avgtime',                  
      ));       
      
	    $this->addColumn('popup_closed', array(
          'header'    => __('Popup closed<br />without interaction'),
          'align'     =>'left',
          'index'     => 'popup_closed',
          'filter'    => false,
          'width'     => '80px', 
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Percent',         
      )); 
      
	    $this->addColumn('window_closed', array(
          'header'    => __('Window <br />closed')."<span class='popupTooltip' title='".__("Client closed browser window without popup interaction (e.g. subscribed newsletter) while popup was still opened.")."'>(?)</span>",
          'align'     =>'left',
          'index'     => 'window_closed',
          'filter'    => false,
          'width'     => '80px',  
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Percent',        
      ));   
      
	    $this->addColumn('page_reloaded', array(
          'header'    => __('Page <br /> reloaded')."<span class='popupTooltip' title='".__("Client refreshed the browser window without popup interaction (e.g. subscribed newsletter) or pressed back browser button while popup was still opened.")."'>(?)</span>",
          'align'     =>'left',
          'index'     => 'page_reloaded',
          'filter'    => false,
          'width'     => '80px',  
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Percent',        
      ));        
      
	    $this->addColumn('click_inside', array(
          'header'    => __('Clicks inside <br /> popup'),
          'align'     =>'left',
          'index'     => 'click_inside',
          'filter'    => false,
          'width'     => '30px',  
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Percent',        
      ));
      
	    $this->addColumn('goal_complition', array(
          'header'    => __('Goal <br />completed')."<span class='popupTooltip' title='".__("User signed up, clicked button, subscribed newletter or liked your page through your popup widget. If you do not use any popup widgets, this will be always 0.")."'>(?)</span>",
          'align'     =>'left',
          'index'     => 'goal_complition',
          'filter'    => false,
          'width'     => '30px',  
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Percent',        
      ));      
      
	    $this->addColumn('sales_generated', array(
          'header'    => __('Conversion')."<span style='color:black;important;cursor:pointer;' class='popupTooltip' title='".__("<strong>Coupon Sales:</strong><br>How much revenue coupon code generated. Shows data only for popups with coupon code and orders with paid invoices.<br><strong>Coupon Orders:</strong><br>Number of placed orders assisted by coupon code from this popup.<br><strong>Conversion:</strong><br>How many users who have seen this popup placed order.<br><strong>Abonded cart:</strong><br>How many users who added product to cart AND have seen this popup left your site without completing the purchase.")."'>(Details ?)</span>",
          'align'     =>'left',
          'index'     => 'sales_generated',
          'filter'    => false,
          'sortable'  => false,
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Sales',        
      ));       
      
      $this->addColumn('action',
       array(
          'header'    =>  __('Action'),
          'width'     => '114',
          'type'      => 'text',
          'filter'    => false,
          'sortable'  => false,
          'is_system' => true,
          'renderer'  => '\Magebird\Popup\Block\Adminhtml\Renderer\Actionlink',
      ));                   

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('popup_id');
        $this->getMassactionBlock()->setFormFieldName('popup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => __('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => __('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('reset', array(
             'label'    => __('Reset statistics to 0'),
             'url'      => $this->getUrl('*/*/massReset'),
             'confirm'  => __('Are you sure? All statistics data for selected popups will be deleted and reset to 0.')
        ));    
            

        $statuses = Status::getOptionArray();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> __('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Status'),
                         'values' => $statuses
                     )
             )
        ));
        
        
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['popup_id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId)->getId();
    }     
}
