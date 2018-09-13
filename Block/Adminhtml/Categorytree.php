<?php
/**
 * Adminhtml popup list block
 *
 */
namespace Magebird\Popup\Block\Adminhtml;

class Categorytree extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'Magebird_Popup';
        $this->_headerText = __('Popup Templates');
        parent::_construct();

    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getCategoryTree() {
   
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()                              
            ->addAttributeToSelect('*');
        foreach($categories as $key => $category){
          $cats[$key]['id'] = $category->getData('entity_id');
          $cats[$key]['parent_id'] = $category->getData('parent_id');
          $cats[$key]['name'] = $category->getData('name');
        }

        $tree = array ();
        foreach ($cats as $cat) {
        	$tree[$cat['parent_id']][] = $cat;
        } 
        return $tree; 
    }     
}
