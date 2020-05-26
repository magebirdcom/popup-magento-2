<?php

namespace Magebird\Popup\Block;
use Magento\Framework\View\Element\Template;
use AWeberAPI;

class Popup extends Template
{
 	protected $_msgs;
 	protected $dir;
		
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Filesystem\DirectoryList $dir
	){
		$this->_msgs = $messageManager;
		$this->dir = $dir;
		
		parent::__construct($context);
	}
	
	public function getAweberToken(){
    return $this->getRequest()->getParam('token');
  }
  
	public function getAweberSecret(){
    return $this->getRequest()->getParam('secret');
  }  
  	

	
    protected function _prepareLayout()
    {
 		return parent::_prepareLayout();
    }

}