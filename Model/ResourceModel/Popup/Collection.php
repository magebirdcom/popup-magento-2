<?php

/**
 * Popup Resource Collection
 */
namespace Magebird\Popup\Model\ResourceModel\Popup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebird\Popup\Model\Popup', 'Magebird\Popup\Model\ResourceModel\Popup');
    }
    
	  public function addStoreFilter($storeId) {  
    		$stores = array();
        //$extensionKey = Mage::getStoreConfig('magebird_popup/general/extension_key');
        //$configModel = Mage::getModel('core/config_data'); //use model to prevent cache
        //$trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');
        $extensionKey = 1;
        if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
          $stores[] = 10;
        }else{
          $stores[] = $storeId;
      		$stores[] = 0;           
        }                           
          
    		$storeTable = $this->getTable('magebird_popup_store');
        $this->getSelect()->join(
                        array('stores' => $storeTable),
                        'main_table.popup_id = stores.popup_id',
                        array()
                )
                ->where('stores.store_id in (?)', $stores)
                ->group('main_table.popup_id');
        
        return $this;
    }    
}
