<?php


namespace Magebird\Popup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface{

    public function __construct(
    \Magento\Config\Model\ResourceModel\Config $resourceConfig,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    
    ) {
        $this->resourceConfig = $resourceConfig;    
        $this->scopeConfig = $scopeConfig;
    }  
        
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();
      
    $mainTablecolumns = $this->getMaintableColumns();
    $templateTableColumns = $this->getTemplateColumns(); 

    $conn = $installer->getConnection();               
    $mainTableExists=$conn->query("SHOW TABLES LIKE '".$installer->getTable('magebird_popup')."'");
    $alterMainTable = false;
    if($mainTableExists->fetch()){
      $tablePopupExists = true;
    }else{
      $tablePopupExists = false;
    }
    
    if($tablePopupExists){
      $oldColumns = $conn->query("SHOW COLUMNS FROM `".$installer->getTable('magebird_popup')."`");
      $rows = $oldColumns->fetchAll();
      foreach($rows as $row => $values){
        $oldColumnNames[] = $values['Field'];
      }
      foreach($mainTablecolumns as $columnName => $val){
        if(!in_array($columnName,$oldColumnNames)){
          $alterMainTable = true;
          $addColumns[$columnName] = $val;
        } 
      }                 
    } 
    
    if($alterMainTable){
      foreach($addColumns as $columnName => $value){
        $alter[] = "ADD ".$value;
      }
      $conn->query("ALTER TABLE `".$installer->getTable('magebird_popup')."` ".implode(" , ",$alter));
    }
    
    $conn->multiQuery("
    	CREATE TABLE IF NOT EXISTS {$installer->getTable('magebird_popup')} ( 
        ".implode(" , ",$mainTablecolumns).",                   
    	  PRIMARY KEY (`popup_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
   
    $conn->multiQuery("
    	DROP TABLE IF EXISTS `{$installer->getTable('magebird_popup_template')}`;
   ");
      
   $conn->multiQuery("       
    	CREATE TABLE `{$installer->getTable('magebird_popup_template')}` ( 
        ".implode(" , ",$templateTableColumns).",                   
    	  PRIMARY KEY (`template_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_store')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `store_id` smallint(6) unsigned NOT NULL,
        PRIMARY KEY (`popup_id`,`store_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
   $conn->multiQuery("     
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_page')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `page_id` smallint(6) unsigned NOT NULL,
        PRIMARY KEY (`popup_id`,`page_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
   ");
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_content')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `store_id` tinyint(4) unsigned NOT NULL,
        `content` text NOT NULL,
        `is_template` boolean DEFAULT 0,
        PRIMARY KEY (`popup_id`,`store_id`,`is_template`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
   ");
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_product')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `product_id` int(11) unsigned NOT NULL,
        PRIMARY KEY (`popup_id`,`product_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
   $conn->multiQuery("  
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_category')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `category_id` int(11) unsigned NOT NULL,
        PRIMARY KEY (`popup_id`,`category_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
   ");
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_country')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `country_id` varchar(10) NOT NULL,
        PRIMARY KEY (`popup_id`,`country_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
  ");
  $conn->multiQuery("    
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_notcountry')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `country_id` varchar(10) NOT NULL,
        PRIMARY KEY (`popup_id`,`country_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
  ");
  $conn->multiQuery("    
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_subscriber')}` (
    	  `subscriber_id` int(11) unsigned NOT NULL auto_increment,
    	  `coupon_code` varchar(50) DEFAULT NULL,
        `subscriber_email` varchar(150) DEFAULT NULL,
        `rule_id` int(10) unsigned DEFAULT NULL,
        `apply_coupon` smallint(4) unsigned DEFAULT NULL,
        `send_coupon` smallint(4) unsigned DEFAULT NULL,
        `date_created` int(11) unsigned DEFAULT NULL,
        `expiration_date` timestamp NULL DEFAULT NULL,
        `coupon_length` TINYINT NULL DEFAULT NULL,
        `coupon_prefix` VARCHAR( 20 ) NULL DEFAULT NULL,
        `user_ip` VARCHAR( 20 ) NULL DEFAULT NULL,
        `popup_cookie_id` VARCHAR( 20 ) NULL DEFAULT NULL,                                              
        PRIMARY KEY (`subscriber_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
    ");
    $conn->multiQuery("        
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_customer_group')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `customer_group_id` smallint(6) unsigned NOT NULL,
        PRIMARY KEY (`popup_id`,`customer_group_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;           
   ");
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_referral')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `referral` varchar(200) NOT NULL,
        PRIMARY KEY (`popup_id`,`referral`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;       
   "); 
   
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_day')}` (
				`popup_id` smallint(6) unsigned NOT NULL,
				`day` smallint(6) unsigned NOT NULL,
				PRIMARY KEY (`popup_id`,`day`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;       
   ");
   
   $conn->multiQuery("   
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_notreferral')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `not_referral` varchar(200) NOT NULL,
        PRIMARY KEY (`popup_id`,`not_referral`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;     
   ");
   $conn->multiQuery("   
      DROP TABLE IF EXISTS `{$installer->getTable('magebird_mousetracking_popup')}`;
   ");
   $conn->multiQuery("
      CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_mousetracking_popup')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `mousetracking_id` int(11) NOT NULL,
        `popup_id` int(11) NOT NULL,
        `popup_width` smallint(4) NOT NULL,
        `popup_left_position` smallint(4) NOT NULL,
        `popup_top_position` smallint(4) NOT NULL,
        `start_seconds` smallint(4) NOT NULL,
        `total_ms` int(11) NOT NULL,
        `behaviour` tinyint(4) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
    ");
    $conn->multiQuery("  
      DROP TABLE IF EXISTS `{$installer->getTable('magebird_mousetracking')}`;
    ");
    $conn->multiQuery("
      CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_mousetracking')}` (
        `mousetracking_id` int(11) NOT NULL AUTO_INCREMENT,
        `mousetracking` text NOT NULL,
        `date_created` datetime NOT NULL,
        `user_ip` varchar(20) NOT NULL,
        `device` smallint(4) NOT NULL,
        `window_width` smallint(4) NOT NULL,
        `window_height` smallint(4) NOT NULL,
        PRIMARY KEY (`mousetracking_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;   
    ");  
    $conn->multiQuery("
      CREATE TABLE IF NOT EXISTS {$installer->getTable('magebird_popup_content')} (
        `popup_id` smallint(6) unsigned NOT NULL DEFAULT '0',
        `store_id` smallint(6) unsigned NOT NULL DEFAULT '0',
        `content` text NOT NULL,
        `is_template` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`popup_id`,`store_id`,`is_template`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
    $conn->multiQuery("  
      DELETE FROM {$installer->getTable('magebird_popup_content')} WHERE is_template=1;  
    ");
    $conn->multiQuery("
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_orders')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `order_id` int(11) NOT NULL,
        PRIMARY KEY (`popup_id`,`order_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;      
    ");
    $conn->multiQuery("  
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_popup_stats')}` (
    	  `popup_id` smallint(6) unsigned NOT NULL,
    	  `total_carts` int(11) NOT NULL DEFAULT 0,
        `popup_carts` int(11) NOT NULL DEFAULT 0,
        `purchases` int(11) NOT NULL DEFAULT 0,
        `popup_purchases` int(11) NOT NULL DEFAULT 0,
        `visitors` int(11) NOT NULL DEFAULT 0,
        `popup_visitors` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`popup_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;     
    ");
    
    $conn->multiQuery("  
    	CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebird_notifications')}` (
    	  `id` int(11) unsigned NOT NULL auto_increment,
        `origin_id` int(11) unsigned NOT NULL DEFAULT 0,
    	  `is_critical` smallint(4) unsigned DEFAULT NULL,
        `notification` text NOT NULL,
        `dismissed` smallint(4) unsigned DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `origin_id` (`origin_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
    ");            
      
    $conn->multiQuery("  
      INSERT IGNORE INTO {$installer->getTable('magebird_popup_stats')} (popup_id) SELECT popup_id FROM {$installer->getTable('magebird_popup')};          
    ");
		$installer->endSetup();
    $this->updateSetup();
  
    $this->alterSalesruleTable($conn,$installer);
    
    $this->insertPopupTemplates($conn, $installer);     
    
	}  
  
  function getMaintableColumns(){
    return array(
      "popup_id" => "`popup_id` int(11) unsigned NOT NULL auto_increment",
      "title"=>"`title` varchar(255) NOT NULL default ''",
      "popup_type"=>"`popup_type` smallint(6) NOT NULL default '1'",
      "width"=>"`width` smallint(6) NOT NULL default '1'",
      "width_unit"=>"`width_unit` smallint(6) NOT NULL default '1'",
      "max_width"=>"`max_width` smallint(6) DEFAULT '0'",
      "from_date"=>"`from_date` datetime DEFAULT NULL",
      "to_date"=>"`to_date` datetime DEFAULT NULL",
      "from_hour"=>"`from_hour` smallint(6) DEFAULT NULL",
      "to_hour"=>"`to_hour` smallint(6) NOT NULL default 24",       
      "appear_effect"=>"`appear_effect` smallint(6) NOT NULL default 1",
      "close_effect"=>"`close_effect` smallint(6) NOT NULL default 1",
      "cookie_time"=>"`cookie_time` float(11) DEFAULT NULL",
      "max_count_time"=>"`max_count_time` int(11) DEFAULT NULL",
      "cookie_id"=>"`cookie_id` varchar(55) DEFAULT NULL",
      "showing_frequency"=>"`showing_frequency` smallint(6) NOT NULL default '1'",
      "popup_content"=>"`popup_content` text NOT NULL default ''",
      "popup_content_parse"=>"`popup_content_parse` text NOT NULL default ''",
      "image"=>"`image` text NOT NULL default ''",
      "url"=>"`url` text NOT NULL default ''",
      "show_when"=>"`show_when` smallint(6) NOT NULL default '1'",
      "close_on_hoverout"=>"`close_on_hoverout` smallint(6) NOT NULL default '1'",
      "scroll_px"=>"`scroll_px` int(11) NOT NULL default '50'",
      "hover_selector"=>"`hover_selector` varchar(100) NOT NULL default ''",
      "click_selector"=>"`click_selector` varchar(100) NOT NULL default ''",
      "seconds_delay"=>"`seconds_delay` int(11) NOT NULL default '2'",
      "total_seconds_delay"=>"`total_seconds_delay` int(11) NOT NULL default '2'",
      "cart_seconds_delay"=>"`cart_seconds_delay` int(11) NOT NULL default '0'",
      "specified_url"=>"`specified_url` varchar(255) NOT NULL default ''",
      "if_referral"=>"`if_referral` varchar(255) NOT NULL default ''",
      "if_returning"=>"`if_returning` int(11) NOT NULL default '1'",
      "num_visited_pages"=>"`num_visited_pages` int(11) NOT NULL default '0'",
      "specified_not_url"=>"`specified_not_url` varchar(255) NOT NULL default ''",
      "border_color"=>"`border_color` varchar(25) NOT NULL default ''",
      "border_radius"=>"`border_radius` smallint(6) NOT NULL default '6'",
      "border_size"=>"`border_size` smallint(6) NOT NULL default '0'",
      "padding"=>"`padding` smallint(6) NOT NULL default '10'",
      "corner_style"=>"`corner_style` smallint(6) NOT NULL default '1'",
      "background_color"=>"`background_color` smallint(6) NOT NULL default '1'",
      "popup_background"=>"`popup_background` varchar(55) NOT NULL default '#FFFFFF'",
      "status"=>"`status` smallint(6) NOT NULL default '0'",
      "popup_shadow"=>"`popup_shadow` smallint(6) NOT NULL default '1'",
      "views"=>"`views` int(11) NOT NULL default '0'",
      "popup_closed"=>"`popup_closed` int(11) NOT NULL default '0'",
      "click_inside"=>"`click_inside` int(11) NOT NULL default '0'",
      "goal_complition"=>"`goal_complition` int(11) NOT NULL default '0'",
      "window_closed"=>"`window_closed` int(11) NOT NULL default '0'",
      "last_rand_id"=>"`last_rand_id` varchar(15) NOT NULL default ''",
      "page_reloaded"=>"`page_reloaded` int(11) NOT NULL default '0'",
      "total_time"=>"`total_time` int(11) NOT NULL default '0'",
      "horizontal_position"=>"`horizontal_position` smallint(6) NOT NULL default '1'",
      "horizontal_position_px"=>"`horizontal_position_px` int(11) NOT NULL default '0'",
      "vertical_position"=>"`vertical_position` smallint(6) NOT NULL default '1'",
      "vertical_position_px"=>"`vertical_position_px` int(11) NOT NULL default '0'",
      "element_id_position"=>"`element_id_position` varchar(50) NOT NULL default ''",  
      "close_style"=>"`close_style` smallint(6) NOT NULL default '1'",
      "close_on_overlayclick"=>"`close_on_overlayclick` smallint(6) NOT NULL default '1'",
      "close_on_timeout"=>"`close_on_timeout` smallint(6) NOT NULL default '0'",
      "custom_css"=>"`custom_css` text NOT NULL default ''",
      "custom_script"=>"`custom_script` text NOT NULL default ''",
      "devices"=>"`devices` smallint(6) NOT NULL default '1'",
      "user_login"=>"`user_login` smallint(6) NOT NULL default '1'",
      "cookies_enabled"=>"`cookies_enabled` smallint(6) NOT NULL default '1'",
      "priority"=>"`priority` smallint(6) NOT NULL default '1'",
      "stop_further"=>"`stop_further` smallint(6) NOT NULL default '1'",  
      "if_pending_order"=>"`if_pending_order` smallint(6) NOT NULL default '0'",
      "product_attribute"=>"`product_attribute` varchar(200) NOT NULL default ''",
      "product_categories"=>"`product_categories` varchar(200) NOT NULL default ''",
      "product_cart_attr"=>"`product_cart_attr` varchar(200) NOT NULL default ''",
      "not_product_cart_attr"=>"`not_product_cart_attr` varchar(200) NOT NULL default ''",
      "cart_product_categories"=>"`cart_product_categories` varchar(200) NOT NULL default ''",  
      "product_in_cart"=>"`product_in_cart` smallint(6) NOT NULL default '0'",
      "cart_subtotal_min"=>"`cart_subtotal_min` int(11) NOT NULL default '0'",
      "cart_subtotal_max"=>"`cart_subtotal_max` int(11) NOT NULL default '0'",
      "user_ip"=>"`user_ip` varchar(100) NOT NULL default ''",
      "user_not_subscribed"=>"`user_not_subscribed` smallint(6) NOT NULL default '1'", 
      "cart_qty_min"=>"`cart_qty_min` int(11) NOT NULL default '0'",
      "cart_qty_max"=>"`cart_qty_max` int(11) NOT NULL default '0'"  
    );
  }
  
  function getTemplateColumns(){
    return array(
      "template_id" => "`template_id` int(11) unsigned NOT NULL auto_increment",
      "position" => "`position` int(11) unsigned NOT NULL default '0'",
      "template_type"=>"`template_type` smallint(6) NOT NULL default '1'",
      "title"=>"`title` varchar(255) NOT NULL default ''",
      "description"=>"`description` varchar(355) NOT NULL default ''",  
      "popup_type"=>"`popup_type` smallint(6) NOT NULL default '1'",
      "width"=>"`width` smallint(6) NOT NULL default '1'",
      "width_unit"=>"`width_unit` smallint(6) NOT NULL default '1'",
      "max_width"=>"`max_width` smallint(6) DEFAULT '0'",
      "appear_effect"=>"`appear_effect` smallint(6) NOT NULL default 1",
      "close_effect"=>"`close_effect` smallint(6) NOT NULL default 1",
      "popup_content"=>"`popup_content` text NOT NULL default ''",
      "popup_content_parse"=>"`popup_content_parse` text NOT NULL default ''",  
      "image"=>"`image` text NOT NULL default ''",                
      "preview_image"=>"`preview_image` text NOT NULL default ''",  
      "url"=>"`url` text NOT NULL default ''",
      "show_when"=>"`show_when` smallint(6) NOT NULL default '1'",
      "scroll_px"=>"`scroll_px` int(11) NOT NULL default '50'",
      "hover_selector"=>"`hover_selector` varchar(100) NOT NULL default ''",
      "click_selector"=>"`click_selector` varchar(100) NOT NULL default ''",
      "seconds_delay"=>"`seconds_delay` int(11) NOT NULL default '0'",
      "border_color"=>"`border_color` varchar(25) NOT NULL default ''",
      "border_radius"=>"`border_radius` smallint(6) NOT NULL default '6'",
      "border_size"=>"`border_size` smallint(6) NOT NULL default '0'",
      "padding"=>"`padding` smallint(6) NOT NULL default '10'",
      "corner_style"=>"`corner_style` smallint(6) NOT NULL default '1'",
      "background_color"=>"`background_color` smallint(6) NOT NULL default '1'",
      "popup_background"=>"`popup_background` varchar(55) NOT NULL default '#FFFFFF'",
      "popup_shadow"=>"`popup_shadow` smallint(6) NOT NULL default '1'",
      "horizontal_position"=>"`horizontal_position` smallint(6) NOT NULL default '1'",
      "horizontal_position_px"=>"`horizontal_position_px` int(11) NOT NULL default '0'",
      "vertical_position"=>"`vertical_position` smallint(6) NOT NULL default '1'",
      "vertical_position_px"=>"`vertical_position_px` int(11) NOT NULL default '0'",
      "close_style"=>"`close_style` smallint(6) NOT NULL default '1'",
      "close_on_overlayclick"=>"`close_on_overlayclick` smallint(6) NOT NULL default '1'",
      "close_on_timeout"=>"`close_on_timeout` smallint(6) NOT NULL default '0'",
      "custom_css"=>"`custom_css` text NOT NULL default ''",
      "custom_script"=>"`custom_script` text NOT NULL default ''",
    );
  } 
  
  function alterSalesruleTable($conn, $installer){
    $columns = $conn->query("SHOW COLUMNS FROM `".$installer->getTable('salesrule_coupon')."` LIKE 'is_popup'");
    if(!$columns->fetch()){
      $conn->query("ALTER TABLE `".$installer->getTable('salesrule_coupon')."` 
                     ADD `is_popup` smallint(4) NULL DEFAULT NULL
                     ");                 
    }
    
    $columns = $conn->query("SHOW COLUMNS FROM `".$installer->getTable('salesrule_coupon')."` LIKE 'user_ip'");
    if(!$columns->fetch()){
      $conn->query("ALTER TABLE `".$installer->getTable('salesrule_coupon')."` 
                     ADD `user_ip` VARCHAR( 20 ) NULL DEFAULT NULL,
                     ADD `popup_cookie_id` VARCHAR( 20 ) NULL DEFAULT NULL
                     ");                 
    } 
     
  }

  public function updateSetup(){
    $resp = null;
    if(isset($_SERVER['HTTP_HOST'])){
      $domain=$_SERVER['HTTP_HOST'];
    }else{
      $domain=$this->scopeConfig->getValue('web/unsecure/base_url');
    }
    $data=http_build_query(array("extension" =>"popup_m2","domain"=>$domain,"affId"=>0));
    if(function_exists('curl_version')){
      $ch = @curl_init();  
      @curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/licence/new.php?".$data); 
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
      $resp = @curl_exec($ch); 
      @curl_close($ch); 
    }
    if($resp==null){
      $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
      $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
      $context = stream_context_create($options);
      $resp=@file_get_contents("https://www.magebird.com/licence/new.php",false,$context,0,100);
    }
  }
  
  function insertPopupTemplates($conn, $installer){
    $query = "
    INSERT IGNORE INTO `{$installer->getTable('magebird_popup_template')}`                                                                                                                                                                                                                                                                                                                                                                                          
    (template_id,position,template_type,title,description,popup_type,width,width_unit,max_width,appear_effect,popup_content,image,preview_image,url,show_when,scroll_px,seconds_delay,border_color,border_radius,border_size,padding,corner_style,background_color,popup_background,popup_shadow,horizontal_position,horizontal_position_px,vertical_position,vertical_position_px,close_style,close_on_overlayclick,close_on_timeout,custom_css,custom_script)
    VALUES   
    ('1','0','1','Newsletter buttom right','','2','320','1','0','4',:description1,'','popup/preview/template1.jpg','','1','50','2','','4','0','20','1','3',' #f5f5f5','1','3','100','2','-20','3','1','0',:custom_css1,:custom_script1),
    ('2','0','1','Newsletter buttom right 2','','2','320','1','0','4',:description2,'','popup/preview/template2.jpg','','1','50','2','','4','0','20','1','3',' #f5f5f5','1','3','100','2','-20','2','1','0',:custom_css2,:custom_script2),
    ('3','0','1','Newsletter 3 (responsive)','','2','95','2','520','4',:description3,'','popup/preview/template3.jpg','','1','50','2','','4','0','20','1','1',' #f5f5f5','1','1','100','1','220','2','1','0',:custom_css3,:custom_script3),
    ('4','0','1','Simple newsletter example (responsive)','','2','95','2','390','1',:description4,'','popup/preview/template4.jpg','','1','50','2','','8','0','20','1','2','#a2cae6','1','1','0','1','100','2','1','0',:custom_css4,:custom_script4),
    ('5','0','1','Image popup exsample (responsive)','','1','96','2','420','2',:description5,'popup/magebird.jpg','popup/preview/template5.jpg','','1','50','2','#FFF','2','12','0','1','2','#FFFFFF','1','1','0','1','100','2','1','0',:custom_css5,:custom_script5),
    ('6','0','1','Sample with orange border (responsive)','','2','95','2','430','1',:description6,'','popup/preview/template6.jpg','','1','50','2','#ff9841','7','9','20','1','1','#FFFFFF','1','1','0','1','150','3','1','0',:custom_css6,:custom_script6),
    ('7','0','1','Circle newsletter','','2','300','1','0','3',:description7,'','popup/preview/template7.jpg','','1','50','2','#FFFFFF','6','8','60','2','1','#75c7d7','1','1','0','1','100','1','1','0',:custom_css7,:custom_script7),
    ('8','0','1','Dark circle newsletter','','2','300','1','0','3',:description8,'','popup/preview/template8.jpg','','1','50','2','','6','0','60','2','2','#586469','1','1','0','1','100','1','1','0',:custom_css8,:custom_script8),
    ('9','0','1','Brown background brush','','2','433','1','0','2',:description9,'','popup/preview/template9.jpg','','1','50','2','#FFFFFF','3','8','0','1','1','#FFFFFF','1','1','0','1','100','4','1','0',:custom_css9,:custom_script9),
    ('10','0','1','Big sticker','','2','328','1','0','1',:description10,'','popup/preview/template10.jpg','','1','50','2','','6','0','10','1','3','','2','4','250','1','160','5','1','0',:custom_css10,:custom_script10),
    ('11','0','1','Small sticker','','2','153','1','0','1',:description11,'','popup/preview/template11.jpg','','1','50','2','','6','0','10','1','3','','2','4','250','1','150','5','1','0',:custom_css11,:custom_script11),
    ('12','0','1','Newsletter with image','','2','538','1','0','3',:description12,'','popup/preview/template12.jpg','','1','50','2','#FFFFFF','6','6','10','0','2','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css12,:custom_script12),
    ('13','0','1','Newsletter with image 2','','2','95','2','568','3',:description13,'','popup/preview/template13.jpg','','1','50','2','#FFFFFF','4','6','25','1','2','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css13,:custom_script13),
    ('14','0','1','Newsletter with image 3','','2','538','1','568','3',:description14,'','popup/preview/template14.jpg','','1','50','2','#FFFFFF','6','0','10','0','2','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css14,:custom_script14),
    ('15','0','1','Notification with button widget','','2','400','1','0','4',:description15,'','popup/preview/template15.jpg','','1','50','2','#000000','6','1','10','1','3','#f9f5ce','1','3','70','2','-20','3','1','0',:custom_css15,:custom_script15),
    ('16','0','1','Promo bottom','','2','800','1','0','1',:description16,'','popup/preview/template16.jpg','','1','50','2','#FFFFFF','16','2','10','1','3','#26292a','1','1','70','2','-56','4','1','0',:custom_css16,:custom_script16),
    ('17','0','1','Bottom important promo notification','','2','4000','1','0','2',:description17,'','popup/preview/template17.jpg','','1','50','2','#FFFFFF','6','2','10','0','3','#B41016','1','1','0','2','-5','1','1','0',:custom_css17,:custom_script17),
    ('18','0','1','Simple marketing popup (responsive)','','2','95','2','670','1',:description18,'','popup/preview/template18.jpg','','1','50','2','','8','0','20','1','2','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css18,:custom_script18),
    ('19','0','1','Newsletter with sticker (responsive)','','2','95','2','380','1',:description19,'','popup/preview/template19.jpg','','1','50','2','#FFFFFF','10','8','30','1','2','#E2E1DE','1','1','0','1','100','2','1','0',:custom_css19,:custom_script19),
    ('20','0','1','Blue button inside (responsive)','','2','100','2','320','1',:description20,'','popup/preview/template20.jpg','','1','50','2','#FFFFFF','16','8','20','1','1','#F5F5F5','1','1','0','1','100','2','1','0',:custom_css20,:custom_script20),
    ('21','0','1','Newsletter with social links 2 (responsive)','','2','95','2','440','1',:description21,'','popup/preview/template21.jpg','','1','50','2','#ad234a','6','8','30','0','2','#e9e6dd','1','1','0','1','100','3','1','0',:custom_css21,:custom_script21),
    ('22','0','1','Newsletter with social links (responsive)','','2','95','2','440','1',:description22,'','popup/preview/template22.jpg','','1','50','2','#FFFFFF','8','11','30','1','1','#e9e6dd','1','1','0','1','100','2','1','0',:custom_css22,:custom_script22),
    ('23','0','1','Social links','','2','280','1','0','1',:description23,'','popup/preview/template23.jpg','','1','50','2','','2','0','10','1','3','#fffcb8','1','3','100','1','20','3','1','0',:custom_css23,:custom_script23),
    ('24','0','1','Social links bottom','','2','200','1','0','4',:description24,'','popup/preview/template24.jpg','','1','50','2','','2','0','16','1','3','#FFF','1','2','100','2','-25','3','1','0',:custom_css24,:custom_script24),
    ('25','0','1','Promo popup with frame','','2','360','1','0','1',:description25,'','popup/preview/template25.jpg','','1','50','2','','6','0','10','0','1','#F5F3F3','1','1','0','1','100','5','1','0',:custom_css25,:custom_script25),
    ('26','0','1','Newsletter popup with frame','','2','373','1','373','1',:description26,'','popup/preview/template26.jpg','','1','50','2','','6','0','10','0','1','#F5F3F3','1','1','0','1','100','5','1','0',:custom_css26,:custom_script26),
    ('27','0','1','Newsletter with sticker 2 (responsive)','','2','95','2','370','1',:description27,'','popup/preview/template27.jpg','','1','50','2','#cbcccd','6','8','30','0','3','#191515','2','1','0','1','100','4','1','0',:custom_css27,:custom_script27),
    ('28','0','1','Newsletter with arrow for better conversion','','2','95','2','440','1',:description28,'','popup/preview/template28.jpg','','1','50','2','#FFFFFF','6','4','10','0','2','#efefee','1','1','0','1','100','3','1','0',:custom_css28,:custom_script28),
    ('29','0','1','Slopy popup (responsive)','','2','95','2','420','1',:description29,'','popup/preview/template29.jpg','','1','50','2','','6','0','30','0','2','#e90089','1','1','0','1','100','2','1','0',:custom_css29,:custom_script29),
    ('30','0','1','Slopy popup 2 (responsive)','','2','95','2','420','1',:description30,'','popup/preview/template30.jpg','','1','50','2','','6','0','30','0','1','#4e9eb8','1','1','0','1','100','2','1','0',:custom_css30,:custom_script30),
    ('31','0','1','Tv screen (responsive)','','2','95','2','440','1',:description31,'','popup/preview/template31.jpg','','1','50','2','','6','5','30','0','1','#FFFFFF','1','1','0','1','100','2','1','0',:custom_css31,:custom_script31),
    ('32','0','1','Tv screen 2 (responsive)','','2','95','2','440','1',:description32,'','popup/preview/template32.jpg','','1','50','2','#FFFFFF','6','5','30','0','2','#e90089','1','1','0','1','100','4','1','0',:custom_css32,:custom_script32),
    ('33','0','1','Bubble left','','2','400','1','0','1',:description33,'','popup/preview/template33.jpg','','1','50','2','','2','0','30','1','4','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css33,:custom_script33),
    ('34','0','1','Bubble top','','2','350','1','400','1',:description34,'','popup/preview/template34.jpg','','1','50','2','','2','0','30','1','4','#FFFFFF','1','4','150','1','100','3','1','0',:custom_css34,:custom_script34),
    ('35','0','1','Popup with header (responsive)','','2','95','2','400','1',:description35,'','popup/preview/template35.jpg','','1','50','2','#C7C7C7','2','1','20','1','1','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css35,:custom_script35),
    ('36','0','1','Popup with red header (responsive)','','2','95','2','400','1',:description36,'','popup/preview/template36.jpg','','1','50','2','','4','0','20','1','2','#F5F0F0','1','1','0','1','100','5','1','0',:custom_css36,:custom_script36),
    ('37','0','1','Popup with silver header (responsive)','','2','95','2','350','3',:description37,'','popup/preview/template37.jpg','','1','50','2','','4','0','20','1','1','#FFFFFF','1','1','0','1','100','4','1','0',:custom_css37,:custom_script37),
    ('38','0','1','Custom h1 style (responsive)','','2','95','2','400','1',:description38,'','popup/preview/template38.jpg','','1','50','2','#03a2b1','6','1','20','0','1','#FFFFFF','1','1','0','1','100','5','1','0',:custom_css38,:custom_script38),
    ('39','0','1','Custom line style (responsive)','','2','95','2','400','1',:description39,'','popup/preview/template39.jpg','','1','50','2','#333','6','1','20','0','1','#FFFFFF','1','1','0','1','100','5','1','0',:custom_css39,:custom_script39),
    ('40','0','1','Popup with ribbon (responsive)','','2','95','2','358','1',:description40,'','popup/preview/template40.jpg','','1','50','2','#1F1E1E','11','4','10','1','2','#FFFFFF','1','1','0','1','100','5','1','0',:custom_css40,:custom_script40),
    ('41','0','1','Popup newsletter with ribbon 2 (responsive)','','2','95','2','300','1',:description41,'','popup/preview/template41.jpg','','1','50','2','#FFFFFF','6','0','10','1','1','#edf2f7','1','1','0','1','100','2','1','0',:custom_css41,:custom_script41),
    ('42','0','1','Popup without background','','2','400','1','420','1',:description42,'','popup/preview/template42.jpg','','1','50','2','','6','0','0','0','1','','2','1','0','1','100','2','1','0',:custom_css42,:custom_script42),
    ('43','0','1','Tucked corners','','2','380','1','0','7',:description43,'','popup/preview/template43.jpg','','1','50','2','','6','0','53','0','1','','2','1','0','1','100','3','1','0',:custom_css43,:custom_script43),
    ('44','0','1','Popup without background 2','','2','400','1','0','1',:description44,'','popup/preview/template44.jpg','','1','50','2','','6','0','20','0','2','','2','1','0','1','100','4','1','0',:custom_css44,:custom_script44),
    ('45','0','1','Popup newsletter with coupon','','2','380','1','0','7',:description45,'','popup/preview/template45.jpg','','1','50','2','','6','0','53','0','1','','2','1','0','1','100','3','1','0',:custom_css45,:custom_script45),
    ('46','0','1','Popup newsletter with coupon blue','','2','380','1','500','7',:description46,'','popup/preview/template46.jpg','','1','50','2','','6','0','53','0','1','','2','1','0','1','100','3','1','0',:custom_css46,:custom_script46),
    ('47','0','1','Popup newsletter with coupon 3 (responsive)','','2','95','2','314','2',:description47,'','popup/preview/template47.jpg','','1','50','2','','3','0','20','1','2','#FFFFFF','1','1','0','1','100','3','1','0',:custom_css47,:custom_script47),
    ('48','0','1','Popup newsletter with coupon 4 (responsive)','','2','95','2','314','2',:description48,'','popup/preview/template48.jpg','','1','50','2','','3','0','20','1','1','#efefe7','1','1','0','1','100','3','1','0',:custom_css48,:custom_script48),
    ('49','0','1','Popup newsletter with coupon left','','2','230','1','0','2',:description49,'','popup/preview/template49.jpg','','1','50','2','','3','0','20','1','3','#efefe7','1','2','-10','2','30','3','1','0',:custom_css49,:custom_script49),
    ('50','0','1','Popup newsletter with coupon dark','','2','230','1','0','2',:description50,'','popup/preview/template50.jpg','','1','50','2','','3','0','20','1','3','#393939','1','2','-10','2','30','4','1','0',:custom_css50,:custom_script50),
    ('51','0','1','Video','','2','450','1','0','1',:description51,'','popup/preview/template51.jpg','','1','50','2','#FFFFFF','6','5','30','0','1','#261f1e','1','1','0','1','100','2','1','0',:custom_css51,:custom_script51),
    ('52','0','1','Video 2','','2','500','1','0','1',:description52,'','popup/preview/template52.jpg','','1','50','2','#FFFFFF','1','5','0','1','1','#cecdd0','1','1','0','1','100','2','1','0',:custom_css52,:custom_script52),
    ('53','0','1','Video 3','','2','700','1','0','1',:description53,'','popup/preview/template53.jpg','','1','50','2','','6','5','10','0','2','','2','1','0','1','100','2','1','0',:custom_css53,:custom_script53),
    ('54','0','1','Mobile responsive popup','','2','95','2','275','1',:description54,'','popup/preview/template54.jpg','','1','50','2','','3','0','20','1','1','#efefe7','1','1','0','1','20','3','1','0',:custom_css54,:custom_script54),
    ('57','0','1','Popup slide up on click','','2','410','1','350','1',:description57,'','popup/preview/template57.jpg','','1','50','2','','6','0','20','0','3','#FFFFFF','1','3','-2','2','-165','4','1','0',:custom_css57,:custom_script57),
    ('60','2','1','Right side image (responsive)','','2','95','2','580','1',:description60,'','popup/preview/template60.jpg','','1','50','2','','6','0','0','0','2','#FFFFFF','2','1','100','1','100','5','1','0',:custom_css60,:custom_script60),
    ('61','2','1','Popup with background image (responsive)','Read instructions how to change image <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#designTips\">here</a>.','2','100','2','600','2',:description61,'','popup/preview/template61.jpg','','1','50','2','#FFFFFF','6','0','0','0','2','#FFFFFF','2','1','0','1','100','4','1','0',:custom_css61,:custom_script61),
    ('62','2','1','Simple general popup (responsive)','','2','100','2','360','3',:description62,'','popup/preview/template62.jpg','','1','50','2','','4','0','20','1','2',' #f5f5f5','1','1','100','1','130','3','1','0',:custom_css62,:custom_script62),
    ('63','2','1','Left side image (responsive)','','2','95','2','650','1',:description63,'','popup/preview/template63.jpg','','1','50','2','','6','0','0','0','2','#FFFFFF','2','1','100','1','100','3','1','0',:custom_css63,:custom_script63),
    ('64','3','1','Image behind (responsive)','Read instructions how to change image <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#designTips\">here</a>.','2','95','2','800','1',:description64,'','popup/preview/template64.jpg','','1','50','2','','6','0','0','0','2','#FFFFFF','1','1','100','1','50','1','1','0',:custom_css64,:custom_script64),
    ('66','2','1','Top block with newsletter (responsive)','','2','100','2','0','3',:description66,'','popup/preview/template66.jpg','','1','50','2','#FFFFFF','6','0','10','0','4','#00909e','1','1','0','4','100','4','1','0',:custom_css66,:custom_script66),
    ('67','3','1','Male fashion (responsive)','Read instructions how to change image <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#designTips\">here</a>.','2','95','2','650','1',:description67,'','popup/preview/template67.jpg','','1','50','','','6','0','0','0','1','#69747b','1','1','100','1','50','4','1','0',:custom_css67,:custom_script67),
    ('68','3','1','Fresh offers (responsive)','Read instructions how to change image <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#designTips\">here</a>.','2','100','2','600','2',:description68,'','popup/preview/template68.jpg','','1','50','','#FFFFFF','6','0','0','0','2','#FFFFFF','2','1','0','1','100','8','1','0',:custom_css68,:custom_script68),
    ('69','3','1','Green','','2','100','2','600','2',:description69,'','popup/preview/template69.jpg','','1','50','','#FFFFFF','6','0','0','0','2','#FFFFFF','2','1','0','1','100','8','1','0',:custom_css69,:custom_script69),
    ('70','3','1','Popup trigger','Read instructions <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#popupTrigger\">here</a>.','2','100','1','0','1',:description70,'','popup/preview/template70.jpg','','1','50','','','4','0','10','0','3','#e8184e','1','3','10','2','100','4','1','0',:custom_css70,:custom_script70),
    ('71','3','1','Slide to right with discount (responsive)','','2','400','1','550','1',:description71,'','popup/preview/template71.jpg','','1','50','','','6','0','0','0','3','','2','2','-1000','1','100','3','1','0',:custom_css71,:custom_script71),
    ('72','3','1','Slide to left with discount (responsive)','','2','400','1','0','1',:description72,'','popup/preview/template72.jpg','','1','50','','','6','0','0','0','3','','2','3','-1000','1','100','9','1','0',:custom_css72,:custom_script72),
    ('73','3','1','Board (responsive)','Read instructions how to change image <a target=\"_blank\" href=\"http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#designTips\">here</a>.','2','95','2','714','2',:description73,'','popup/preview/template73.jpg','','1','50','','#FFFFFF','6','0','0','0','3','','2','1','0','2','0','6','1','0',:custom_css73,:custom_script73),
    ('74','3','1','Left side image (responsive)','','2','95','2','710','1',:description74,'','popup/preview/template74.jpg','','1','50','','','6','0','0','0','2','#f6f6f6','2','1','100','1','100','3','1','0',:custom_css74,:custom_script74)            
    ";
    
    $binds = array(
    'description1' => "<p style=\"text-align: left;\"><strong><span style=\"color: #2f87ce; font-size: x-large;\">What's hot and new?<br /></span></strong></p>\r\n<p style=\"text-align: left;\">Enter your email address to receive <strong>exclusive offers</strong> and other news from our website</p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eft0\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/3.phtml\" width=\"300\" width_unit=\"1\" button_color=\"#3694D2\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>",
    'custom_css1' => "",
    'custom_script1' => "",
    'description2' => "<div id=\"newsletterWrapper\">\r\n<p style=\"text-align: left;\"><span style=\"color: #000000;\"><span style=\"font-size: x-large;\">What's hot and new?</span><strong><span style=\"font-size: x-large;\"><br /></span></strong></span></p>\r\n<p style=\"text-align: left;\">Enter your email address to receive <strong>exclusive offers</strong> and other news from our website</p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eft1\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/3.phtml\" width=\"300\" width_unit=\"1\" button_color=\"#3694D2\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"%3Cp%20id=%22newsletterWrapper%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>\r\n</div>",
    'custom_css2' => "#newsletterWrapper{\r\n  margin:0 -10px;\r\n  padding:17px;\r\n  border-top:1px solid #E4E3E3;\r\n  background-color:#FFFFFF;\r\n}\r\n\r\n.dialogBody{\r\nbackground-image:url('/media/popup/envelope_lines.png');\r\npadding:8px;\r\n}\r\n\r\n.dialogClose.style3{\r\ntop:-2px;\r\n}",
    'custom_script2' => "",
    'description3' => "<div id=\"newsletterWrapper\">\r\n<p style=\"text-align: left;\"><span style=\"color: #000000;\"><span style=\"font-size: x-large;\">What's hot and new?</span><strong><span style=\"font-size: x-large;\"><br /></span></strong></span></p>\r\n<p style=\"text-align: left;\">Enter your email address to receive <strong>exclusive offers</strong> and other news from our website</p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eft2\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/7.phtml\" width=\"90\" width_unit=\"2\" button_color=\"#3694D2\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"%3Cp%20id=%22newsletterWrapper%22%20style=%22margin:0%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>\r\n</div>",
    'custom_css3' => "#newsletterWrapper{\r\n  padding:17px;\r\n  background-color:#FFFFFF;\r\n}\r\n\r\n.dialogBody{\r\nbackground-image:url('/media/popup/envelope_lines.png');\r\npadding:8px;\r\n}",
    'custom_script3' => "",
    'description4' => "<h1 style=\"text-align: left;\"><span style=\"color: #ffffff;\"><strong><span style=\"font-size: x-large; text-shadow: 1px 1px #000;\">Hear about exclusive savings!<br /></span></strong></span></h1>\r\n<p style=\"text-align: left;\">Enter your email address to hear about exclusive savings and news!</p>\r\n<p style=\"text-align: left;\">{{widget widget_id=\"_eft3\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/4.phtml\" width=\"90\" width_unit=\"2\" button_color=\"#D83C3C\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p style=\"text-align: left;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>",
    'custom_css4' => "",
    'custom_script4' => "",
    'description5' => "",
    'custom_css5' => "",
    'custom_script5' => "",
    'description6' => "<p style=\"text-align: center;\"><span style=\"font-size: x-large; color: #ff6600;\"><strong>Important message!</strong></span></p>\r\n<p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas tempor nisi sed mauris tristique, sit amet placerat odio mollis. In ultrices egestas turpis.<br /><a href=\"#\"><img src=\"{{media url=\"wysiwyg/popup/magebird1.jpg\"}}\" alt=\"\" style=\"width:90%;max-width:300px\" height=\"auto\" /></a></p>",
    'custom_css6' => "",
    'custom_script6' => "",
    'description7' => "<h2 style=\"text-align: center;\"><span style=\"font-size: x-large;\"><strong><span style=\"color: #f5f5fe;\"><br /></span></strong></span></h2>\r\n<h2 style=\"text-align: center;\"><span style=\"font-size: x-large;\"><strong><span style=\"color: #f5f5fe; text-shadow: 1px 1px #000;\">Subscribe to our newsletter</span></strong></span></h2>\r\n<p style=\"text-align: center;\">Enter your email address to hear about exclusive savings and news!</p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eft4\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/4.phtml\" width=\"300\" button_text=\"Subscribe\" button_color=\"#95BF31\" thanks_msg=\"Thank you for your subscription!\"}}</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>",
    'custom_css7' => "",
    'custom_script7' => "",
    'description8' => "<h2 style=\"text-align: center;\"><span style=\"font-size: x-large;\"><strong><span style=\"color: #f5f5fe; text-shadow: 1px 1px #000;\">Subscribe to our newsletter</span></strong></span></h2>\r\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5;\">Enter your email address to hear about exclusive savings and news!</span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eft5\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/1.phtml\" width=\"300\" width_unit=\"1\" button_text=\"Subscribe\" button_color=\"#f16520\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"%3Cp%20style=%22text-align:center;margin-top:30px;color:#FFFFFF%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small; color: #f5f5f5;\">*We will never share your information</span></p>",
    'custom_css8' => "",
    'custom_script8' => "",
    'description9' => "<table style=\"border-spacing: 20px; background-image: url('{{media url=\"wysiwyg/popup/brush2.png\"}}'); width: 433px; height: 446px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p style=\"text-align: center;\"><strong><span style=\"font-size: xx-large; text-shadow: 1px 1px #FFFFFF;\">Heading title goes here<br /></span></strong></p>\r\n<p style=\"text-align: center;\">Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus facilisis, mauris convallis consequat pulvinar, turpis nisl porttitor enim, quis tempus mi orci id metus. Maecenas ut sem lectus.</p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p style=\"text-align: center;\"><img src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"185\" height=\"185\" /></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n<p style=\"text-align: center;\"><a href=\"#\">Vestibulum ante ipsum primis&gt;&gt;</a></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css9' => "", 
    'custom_script9' => "",
    'description10' => "<table style=\"border-spacing: 20px; background-image: url('{{media url=\"wysiwyg/popup/sticker1_big.png\"}}'); width: 328px; height: 325px;\" border=\"0\" >\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p style=\"text-align: center;\"><strong><span style=\"font-size: xx-large; text-shadow: 1px 1px #000;\"><span style=\"color: #ffffff;\"><br /></span></span></strong></p>\r\n<p style=\"text-align: center;\"><strong><span style=\"font-size: xx-large; text-shadow: 1px 1px #000;\"><span style=\"color: #ffffff;\">40% OFF ONLY TODAY</span></span></strong></p>\r\n<p style=\"text-align: center;\"><a href=\"#\"><span style=\"text-decoration: underline;\">Read more&gt;&gt;</span></a></p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css10' => "",
    'custom_script10' => "",
    'description11' => "<table style=\"border-spacing: 20px; background-image: url('{{media url=\"wysiwyg/popup/sticker1_small.png\"}}'); width: 152px; height: 152px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p style=\"text-align: center;\"><span style=\"font-size: medium;\"><strong><span style=\"text-shadow: 1px 1px #000000;\"><span style=\"color: #ffffff;\">40% OFF ONLY TODAY</span></span></strong></span></p>\r\n<p style=\"text-align: center;\"><a href=\"#\"><span style=\"text-decoration: underline;\">Read more&gt;&gt;</span></a></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css11' => "",
    'custom_script11' => "",
    'description12' => "<table style=\"width: 538px; height: 190px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h1><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">Subscribe to our Discount Club</span></strong></span></h1>\r\n<p>To receive exclusive offers and our most popular news, please enter your email address and press subscribe.</p>\r\n<p>Don't worry, <strong>we hate spam too</strong> and we won't share your data with anybody!</p>\r\n<p>&nbsp;</p>\r\n</td>\r\n<td>\r\n<p><img src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"213\" height=\"192\" /></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div id=\"newsletterRibon\">{{widget widget_id=\"_eft6\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/2.phtml\" width=\"400\" button_color=\"#03a2b1\"}}</div>",
    'custom_css12' => ".dialogBody{\r\n    overflow:visible;\r\n    background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n    background: -o-radial-gradient(white, white, #e1e1e1);\r\n    background: -moz-radial-gradient(white, white, #e1e1e1); \r\n    background: radial-gradient(white, white, #e1e1e1);\r\n}\r\n\r\n#newsletterRibon {\r\n	position:relative;\r\n	padding:20px 30px;\r\n	margin:0 -26px -16px;\r\n	border:1px solid #c9c8c8;\r\n	background:#f1f1f1;\r\n	color:#6a6340;\r\n	box-shadow:0 -4px 4px rgba(0,0,0,0.3);\r\n}\r\n#newsletterRibon:before,\r\n#newsletterRibon:after {\r\n	content:\" \";\r\n	border-bottom:10px solid #929191;	/* Colour of the triangle. To flip the effect, use border-bottom. */\r\n	position:absolute;\r\n	top:-11px; /* +1 to compensate for the border */\r\n} \r\n#newsletterRibon:before {\r\n	border-left:10px solid transparent;\r\n	left:-1px;	/* Only required if the element has a border */\r\n	}\r\n#newsletterRibon:after {\r\n	border-right:10px solid transparent;\r\n	right:-1px;\r\n}",
    'custom_script12' => "",
    'description13' => "<table border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h1><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">Subscribe to our Discount Club</span></strong></span></h1>\r\n<p>To receive exclusive offers and our most popular news, please enter your email address and press subscribe.</p>\r\n<p>Don't worry, <strong>we hate spam too</strong> and we won't share your data with anybody!</p>\r\n</td>\r\n<td>\r\n<p><img src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"213\" height=\"192\" /></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>{{widget widget_id=\"_eft7\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/2.phtml\" width=\"70\" width_unit=\"2\" button_color=\"#F9A300\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>",
    'custom_css13' => ".dialogBody{\r\nbackground-image:linear-gradient(white, #e1e1e1);\r\n}",
    'custom_script13' => "",
    'description14' => "<table style=\"width: 100%; height: 190px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h1><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">Subscribe to our Discount Club</span></strong></span></h1>\r\n<p>To receive exclusive offers and our most popular news, please enter your email address and press subscribe.</p>\r\n<p>Don't worry, <strong>we hate spam too</strong> and we won't share your data with anybody!</p>\r\n<p>&nbsp;</p>\r\n</td>\r\n<td>\r\n<p><img src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"213\" height=\"192\" /></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<div id=\"newsletterRibon\" style=\"background-color: #03a2b1;\"><img class=\"whiteArrow\" src=\"{{media url=\"wysiwyg/popup/whitearrow.png\"}}\" alt=\"\" />{{widget widget_id=\"_eft8\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/7.phtml\" width=\"400\" width_unit=\"1\" button_color=\"#fcfcfc\" button_text_color=\"#00000\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</div>",
    'custom_css14' => ".dialogBody{\r\n    overflow:visible;\r\n    background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n    background: -o-radial-gradient(white, white, #e1e1e1);\r\n    background: -moz-radial-gradient(white, white, #e1e1e1); \r\n    background: radial-gradient(white, white, #e1e1e1);\r\n}\r\n\r\n#newsletterRibon {\r\n	position:relative;\r\n        text-align:center;\r\n	margin:0px -10px -10px -10px;\r\n        padding:20px;\r\n	background:#f1f1f1;\r\n	color:#03a2b1;\r\n}\r\n\r\n#newsletterRibon form{\r\nmargin-left:-28px;\r\n}\r\n\r\n#newsletterRibon input{\r\nwidth:97%;\r\n}\r\n\r\n.whiteArrow{\r\nposition: absolute; \r\nleft: 14px; \r\ntop: -20px;\r\n}",
    'custom_script14' => "",
    'description15' => "<p><span style=\"font-size: large;\">Notification title goes here!</span></p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur eu augue id dui fermentum sollicitudin. Vestibulum vitae lectus elit. Aliquam vestibulum lacinia accumsan.</p>\r\n<p>{{widget widget_id=\"_eft9\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#ea7d00\" button_size=\"middle\" template=\"widget/buttons/7.phtml\" button_text=\"More info >\" buttontext_color=\"#FFFFFF\" button_href=\"#\"}}</p>\r\n<p>&nbsp;</p>",
    'custom_css15' => "",
    'custom_script15' => "",
    'description16' => "<table style=\"background-color: #26292a; width: 770px; height: 92px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p style=\"line-height: 34px;\"><span style=\"font-size: x-large;\"><strong><span style=\"color: #ffffff;\">&nbsp; Promo title goes here Lorem ipsum dolor!</span></strong></span></p>\r\n</td>\r\n<td style=\"width: 160px;\">\r\n<p>{{widget widget_id=\"_efr0\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#ffde00\" button_size=\"middle\" template=\"widget/buttons/7.phtml\" button_text=\"More info >\" buttontext_color=\"#000000\" button_href=\"#\"}}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css16' => "",
    'custom_script16' => "",
    'description17' => "<table style=\"background-color: #b41016; width: 610px; height: 32px; margin: 0px auto;\" border=\"0\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"text-align: center;\"><strong><span style=\"color: #ffffff; font-size: large;\">Your promo title or notification goes here</span></strong></td>\r\n<td style=\"text-align: center;\">{{widget widget_id=\"_efr1\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#dad8d8\" button_size=\"middle\" template=\"widget/buttons/13.phtml\" button_text=\"Click me text >\" buttontext_color=\"#373434\" button_href=\"#\"}}</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css17' => "",
    'custom_script17' => "",
    'description18' => "<p style=\"border-bottom: 1px solid #d9d9d9; padding-bottom: 10px;\"><span style=\"font-size: x-large; color: #cc0000;\"><strong>Your promo title goes here...</strong></span></p>\r\n<table border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p><span style=\"font-size: medium;\"><span style=\"font-size: medium;\">Sed quam dolor, faucibus id viverra a, fringilla et nisl. Maecenas turpis libero, convallis quis molestie feugiat, tincidunt vel ante. Suspendisse auctor non turpis id ornare. Fusce mattis pretium sollicitudin. Sed pretium eros vel dictum viverra.</span></span></p>\r\n<p><span style=\"font-size: medium;\"><strong>Integer rutrum nisl non risus malesuada...</strong> Suspendisse nibh purus, tincidunt ut tellus eget, auctor convallis quam. Vivamus tincidunt consectetur porttitor. Maecenas auctor dolor eu congue iaculis. Vestibulum risus sapien, tincidunt vitae consectetur ac, tincidunt eu purus. Vivamus mattis lorem vitae varius convallis.</span></p>\r\n<p><span style=\"font-size: medium;\"><span style=\"font-size: medium;\"><span style=\"text-decoration: underline;\"><span style=\"color: #0000ff; text-decoration: underline;\"><strong>Click here...</strong></span></span></span></span></p>\r\n</td>\r\n<td>\r\n<p>&nbsp;</p>\r\n<p><img src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"254\" height=\"228\" /></p>\r\n<p><span style=\"font-size: medium;\"><br /></span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css18' => "",
    'custom_script18' => "",
    'description19' => "<table style=\"width: 100%; height: 122px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p><span style=\"font-size: x-large; line-height: 23px; color: #ef0505;\"><strong><span style=\"color: #000000;\">Exclusive offers and deals!</span><br /></strong></span></p>\r\n<p>Enter your email to receive exclusive offers and deals.</p>\r\n</td>\r\n<td><img src=\"{{media url=\"wysiwyg/popup/greatoffers.png\"}}\" alt=\"\" width=\"99\" height=\"99\" /></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>{{widget widget_id=\"_efr2\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/1.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#333333\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>",
    'custom_css19' => "",
    'custom_script19' => "",
    'description20' => "<p style=\"text-align: center;\"><span style=\"color: #2f87ce;\"><strong><span style=\"font-size: x-large;\">Your title goes here...</span></strong></span></p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus vel aliquam metus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.<br /><br /></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_efr3\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#3694D2\" button_size=\"big\" template=\"widget/buttons/2.phtml\" button_text=\"Click me >\" buttontext_color=\"#FFFFFF\" button_href=\"#\"}}</p>",
    'custom_css20' => "",
    'custom_script20' => "",
    'description21' => "<p><span style=\"color: #000000; font-size: x-large;\"><strong>KEEP IN TOUCH WITH US!</strong></span></p>\r\n<p><span style=\"font-size: small;\">Enter your email address to hear about exclusive savings and news!</span><br /> {{widget widget_id=\"_efr4\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/7.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#ad0836\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p style=\"border-top: 1px solid #cdcdcd; padding-top: 10px; margin-top: 17px;\"><span style=\"color: #000000; font-size: x-large;\"><strong>Stay connected with us</strong></span></p>\r\n<p><span style=\"font-size: small;\">Join us on Facebook or Twitter to not miss exclusive offers.</span></p>\r\n<p><span style=\"font-size: small;\"><a href=\"http://www.facebook.com\"><img src=\"{{media url=\"wysiwyg/popup/facebook.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"http://www.twitter.com\"><img src=\"{{media url=\"wysiwyg/popup/twitter.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a><br /><span style=\"font-size: small;\">Facebook&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Twitter</span><br /></span></p>",
    'custom_css21' => "",
    'custom_script21' => "",
    'description22' => "<p><span style=\"color: #000000; font-size: x-large;\"><strong>KEEP IN TOUCH WITH US!</strong></span></p>\r\n<p><span style=\"font-size: small;\">Enter your email to receive news about exclusive savings and deals!</span><br /> {{widget widget_id=\"_efr5\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/7.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#004a88\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p style=\"border-top: 1px solid #cdcdcd; padding-top: 10px; margin-top: 17px;\"><span style=\"color: #000000; font-size: x-large;\"><strong>Stay connected with us</strong></span></p>\r\n<p><span style=\"font-size: small;\">Join us on Facebook or Twitter to not miss exclusive offers.</span></p>\r\n<p><span style=\"font-size: small;\"><a href=\"http://www.facebook.com\"><img src=\"{{media url=\"wysiwyg/popup/facebook.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"http://www.twitter.com\"><img src=\"{{media url=\"wysiwyg/popup/twitter.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a><br /><span style=\"font-size: small;\">Facebook&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Twitter</span><br /></span></p>",
    'custom_css22' => "",
    'custom_script22' => "",
    'description23' => "<p><strong><span style=\"color: #000000; font-size: medium;\">Stay connected with us<br /></span></strong></p>\r\n<p><span style=\"font-size: small;\">Join us on social profiles to not miss<br />exclusive offers and deals!</span></p>\r\n<p><span style=\"font-size: small;\"><a href=\"http://www.facebook.com\"><img src=\"{{media url=\"wysiwyg/popup/facebook.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"http://www.twitter.com\"><img src=\"{{media url=\"wysiwyg/popup/twitter.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a>&nbsp; &nbsp; &nbsp; &nbsp;&nbsp; <a href=\"http://plus.google.com/&lrm;\"><img src=\"{{media url=\"wysiwyg/popup/gplus.png\"}}\" alt=\"\" width=\"37\" height=\"38\" /></a><br /><span style=\"font-size: small;\">Facebook&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Twitter</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Gplus<br /></span></p>",
    'custom_css23' => "",
    'custom_script23' => "",
    'description24' => "<p><strong><span style=\"color: #000000; font-size: medium;\"><span style=\"color: #333333;\">Stay connected with us</span>!<br /></span></strong></p>\r\n<p><span style=\"font-size: small;\">Join us on social profiles to not <br />miss exclusive offers and deals!</span></p>\r\n<p><span style=\"font-size: small;\">&nbsp;<a href=\"http://www.facebook.com\"><img src=\"{{media url=\"wysiwyg/popup/facebook2.png\"}}\" alt=\"\" width=\"48\" height=\"48\" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"http://www.twitter.com\"><img src=\"{{media url=\"wysiwyg/popup/twitter2.png\"}}\" alt=\"\" width=\"48\" height=\"48\" /></a>&nbsp; &nbsp;&nbsp;&nbsp; <a href=\"http://plus.google.com/&lrm;\"><img src=\"{{media url=\"wysiwyg/popup/gplus2.png\"}}\" alt=\"\" width=\"48\" height=\"48\" /></a><br /><span style=\"font-size: small;\">Facebook&nbsp;&nbsp; &nbsp;&nbsp; Twitter</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Gplus<br /><br /></span></p>",
    'custom_css24' => "",
    'custom_script24' => "",
    'description25' => "<table style=\"border-color: #991f29; border-width: 1px; border-style: solid; border-collapse: collapse; width: 360px; height: 162px;\" border=\"1\" cellpadding=\"20\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<p style=\"text-align: center;\"><span style=\"font-size: large;\"><strong>Your promo title goes here</strong></span></p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: small;\">Sed quam dolor, faucibus id viverra a, fringilla et nisl. Phasellus sem tellus, condimentum ac volutpat vel, scelerisque sit amet nulla.&nbsp;<strong> </strong></span></p>\r\n<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"{{media url=\"wysiwyg/popup/magebird.png\"}}\" alt=\"\" width=\"172\" height=\"155\" /></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_efr6\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#981c26\" button_size=\"normall\" template=\"widget/buttons/14.phtml\" button_text=\"Your click me text >\" buttontext_color=\"#FFFFFF\" button_href=\"#\"}}</p>\r\n<p style=\"text-align: center;\">Morbi elementum sapien nisi. Curabitur quis volutpat massa.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css25' => "",
    'custom_script25' => "",
    'description26' => "<table style=\"border-color: #bc1107; border-width: 1px; border-style: solid; border-collapse: collapse; width: 341px; height: 209px;\" border=\"1\" cellpadding=\"20\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-large;\"><span style=\"color: #bc1107;\">EXCLUSIVE SAVINGS!</span><span style=\"color: #bc1107;\"><strong><br /></strong></span></span></p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: small;\">Enter your email address to receive news about exclusive savings and offers!<br /><strong> </strong></span></p>\r\n<p style=\"text-align: center;\"><br />{{widget widget_id=\"_efr7\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/6.phtml\" width=\"330\" button_text=\"SAVE NOW!\" button_color=\"#bc1107\"}}</p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css26' => "",
    'custom_script26' => "",
    'description27' => "<table style=\"position: relative; width: 100%; height: 122px; background-color: #191515;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p><span style=\"font-size: x-large; line-height: 23px; color: #ffffff;\"><strong>Exclusive offers and deals!</strong></span><span style=\"color: #ffffff;\"><br /><br />Enter your email to receive exclusive offers and deals.</span></p>\r\n</td>\r\n<td>&nbsp;<img src=\"{{media url=\"wysiwyg/popup/greatoffers.png\"}}\" alt=\"\" width=\"99\" height=\"99\" /></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>{{widget widget_id=\"_efr8\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/7.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#d90202\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"%3Cp%20style=%22text-align:center;color:#FFFFFF%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</p>",
    'custom_css27' => "",
    'custom_script27' => "",
    'description28' => "<table border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<p>&nbsp;</p>\r\n<p><img style=\"float: left;\" src=\"{{media url=\"wysiwyg/popup/arrow.png\"}}\" alt=\"\" width=\"58\" height=\"68\" />&nbsp;&nbsp;&nbsp;&nbsp;</p>\r\n</td>\r\n<td>\r\n<p style=\"text-align: left;\"><span style=\"font-size: x-large;\">Exclusive offers and deals!</span><br />Enter your email to receive exclusive offers and deals<br />from us.<br />{{widget widget_id=\"_efr9\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/2.phtml\" width=\"100\" width_unit=\"2\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p style=\"text-align: left; margin-top: -13px;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>",
    'custom_css28' => "",
    'custom_script28' => "",
    'description29' => "<div id=\"popupFlop\">\r\n<p><span style=\"font-size: xx-large;\"><strong>Popup title<br /></strong></span></p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin consequat in justo ac sagittis. Mauris purus nunc, malesuada id sem eu, euismod porttitor mi. In at leo mi. Praesent in ligula velit. Proin vulputate quam eu scelerisque dapibus. Maecenas vitae dolor at sem faucibus posuere. Maecenas iaculis nibh et ipsum sodales pellentesque. Ut sollicitudin adipiscing nibh, et tempus magna pharetra sed.</p>\r\n<p>&nbsp;</p>\r\n</div>",
    'custom_css29' => ".dialogBody{\r\n -webkit-transform: rotate(-3deg); \r\n-moz-transform: rotate(-3deg);\r\n-ms-transform: rotate(-3deg);\r\n-o-transform: rotate(-3deg);\r\n}\r\n\r\n.dialogBody #popupFlop{\r\n -webkit-transform: rotate(3deg); \r\n-moz-transform: rotate(3deg);\r\n-ms-transform: rotate(3deg);\r\n-o-transform: rotate(3deg);\r\n}\r\n\r\n.dialogClose.style2{\r\nright:-6px;\r\ntop:-21px;\r\n}",
    'custom_script29' => "",
    'description30' => "<div id=\"popupFlop\">\r\n<p><span style=\"font-size: xx-large; color: #fafafa;\"><strong>Popup title<br /></strong></span></p>\r\n<p><span style=\"color: #fafafa;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin consequat in justo ac sagittis. Mauris purus nunc, malesuada id sem eu, euismod porttitor mi. In at leo mi. Praesent in ligula velit. Proin vulputate quam eu scelerisque dapibus. Maecenas vitae dolor at sem faucibus posuere. Maecenas iaculis nibh et ipsum sodales pellentesque. Ut sollicitudin adipiscing nibh, et tempus magna pharetra sed. </span></p>\r\n<p>{{widget widget_id=\"_eer0\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#fc9003\" button_size=\"normall\" template=\"widget/buttons/14.phtml\" button_text=\"Click me\" buttontext_color=\"#FFFFFF\" button_href=\"#\" link_target=\"1\"}}</p>\r\n</div>",
    'custom_css30' => ".dialogBody{\r\n -webkit-transform: rotate(-3deg); \r\n-moz-transform: rotate(-3deg);\r\n-ms-transform: rotate(-3deg);\r\n-o-transform: rotate(-3deg);\r\n}\r\n\r\n.dialogBody #popupFlop{\r\n -webkit-transform: rotate(3deg); \r\n-moz-transform: rotate(3deg);\r\n-ms-transform: rotate(3deg);\r\n-o-transform: rotate(3deg);\r\n}\r\n\r\n.dialogClose.style2{\r\nright:-6px;\r\ntop:-21px;\r\n}",
    'custom_script30' => "",
    'description31' => "<div id=\"popupTV\">\r\n<p><span style=\"font-size: xx-large;\"><strong>Popup title<br /></strong></span></p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin consequat in justo ac sagittis. Mauris purus nunc, malesuada id sem eu, euismod porttitor mi. In at leo mi. Praesent in ligula velit. Proin vulputate quam eu scelerisque dapibus. Maecenas vitae dolor at sem faucibus posuere. Maecenas iaculis nibh et ipsum sodales pellentesque. Ut sollicitudin adipiscing nibh, et tempus magna pharetra sed.</p>\r\n<p>&nbsp;</p>\r\n</div>",
    'custom_css31' => ".dialogBody { \r\nmargin: 20px 0; \r\nborder-radius: 50% / 10%; \r\ntext-indent: .1em;\r\n } \r\n\r\n.dialogBody:before { \r\ncontent: ''; \r\ntop: 10%; \r\nbottom: 10%;\r\nright: -5%;\r\nleft: -5%; \r\nbackground: inherit; \r\nborder-radius: 9% / 50%; \r\n}\r\n\r\n.dialogClose{\r\nright:-23px;\r\n}",
    'custom_script31' => "",
    'description32' => "<div id=\"popupTV\">\r\n<p><span style=\"font-size: xx-large;\"><strong>Popup title<br /></strong></span></p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin consequat in justo ac sagittis. Mauris purus nunc, malesuada id sem eu, euismod porttitor mi. In at leo mi. Praesent in ligula velit. Proin vulputate quam eu scelerisque dapibus. Maecenas vitae dolor at sem faucibus posuere. Maecenas iaculis nibh et ipsum sodales pellentesque. Ut sollicitudin adipiscing nibh, et tempus magna pharetra sed.</p>\r\n<p>&nbsp;</p>\r\n</div>",
    'custom_css32' => ".dialogBody { \r\nmargin: 20px 0; \r\nborder-radius: 50% / 10%; \r\ntext-indent: .1em;\r\n } \r\n\r\n.dialogBody:before { \r\ncontent: ''; \r\ntop: 10%; \r\nbottom: 10%;\r\nright: -5%;\r\nleft: -5%; \r\nbackground: inherit; \r\nborder-radius: 9% / 50%; \r\n}\r\n\r\n.dialogClose.style4{\r\nright:-23px;\r\n}",
    'custom_script32' => "",
    'description33' => "<div id=\"popupBubble\">\r\n<p><span style=\"font-size: xx-large;\"><strong>Popup title<br /></strong></span></p>\r\n<p>Phasellus eu suscipit nibh, vitae sagittis dui. Fusce et feugiat dui. Aliquam quam ligula, elementum vel neque nec, tempor ullamcorper libero. Nulla auctor libero ligula, nec aliquet lorem condimentum ac. Nullam fringilla suscipit dui, sed fermentum odio aliquet at. Aliquam eleifend purus ac est accumsan, vitae venenatis ante accumsan. Quisque sagittis dolor eget tellus volutpat, eget interdum mauris egestas. Curabitur lacus nulla, molestie ac varius sed, malesuada non turpis.</p>\r\n<p>&nbsp;</p>\r\n</div>",
    'custom_css33' => ".dialogBody:before { \r\n content:\"\"; \r\nposition: absolute; \r\nright: 100%;\r\ntop: 26px; \r\nwidth: 0; \r\nheight: 0; \r\nborder-top: 13px solid transparent; \r\nborder-right: 26px solid; \r\nborder-bottom: 13px solid transparent;\r\ncolor:#FFFFFF;\r\n}",
    'custom_script33' => "",
    'description34' => "<div id=\"popupBubble\">\r\n<p><span style=\"font-size: xx-large;\"><strong>Popup title<br /></strong></span></p>\r\n<p>Phasellus eu suscipit nibh, vitae sagittis dui. Fusce et feugiat dui. Aliquam quam ligula, elementum vel neque nec, tempor ullamcorper libero. Nulla auctor libero ligula, nec aliquet lorem condimentum ac. Nullam fringilla suscipit dui, sed fermentum odio aliquet at. Aliquam eleifend purus ac est accumsan, vitae venenatis ante accumsan. Quisque sagittis dolor eget tellus volutpat, eget interdum mauris egestas. Curabitur lacus nulla, molestie ac varius sed, malesuada non turpis.</p>\r\n<p>&nbsp;</p>\r\n</div>",
    'custom_css34' => ".dialogBody:before { \r\ncontent:\"\"; \r\nposition: absolute; \r\nleft:30px;\r\ntop: -20px; \r\nwidth: 0; \r\nheight: 0; \r\nborder-left: 14px solid transparent; \r\nborder-right: 14px solid transparent; \r\nborder-bottom: 20px solid #eaeaea;\r\n}\r\n.dialogBody{\r\n    background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n    background: -o-radial-gradient(white, white, #e1e1e1);\r\n    background: -moz-radial-gradient(white, white, #e1e1e1); \r\n    background: radial-gradient(white, white, #e1e1e1);\r\n}",
    'custom_script34' => "",
    'description35' => "<h1 style=\"background-color: #f5f5f5; color: white;\"><span style=\"font-size: xx-large; font-weight: bold;\"><span style=\"color: #464646; font-size: x-large;\">POPUP TITLE</span><br /></span></h1>\r\n<p>Phasellus eu suscipit nibh, vitae sagittis dui. Fusce et feugiat dui. Aliquam quam ligula, elementum vel neque nec, tempor ullamcorper libero. Nulla auctor libero ligula, nec aliquet lorem condimentum ac. Nullam fringilla suscipit dui, sed fermentum odio aliquet at. Aliquam eleifend purus ac est accumsan, vitae venenatis ante accumsan. Quisque sagittis dolor eget tellus volutpat, eget interdum mauris egestas.</p>\r\n<p>&nbsp;</p>",
    'custom_css35' => ".dialogBody h1{\r\nmargin: -20px -20px 10px -20px;\r\npadding:4px 20px;\r\n}\r\n",
    'custom_script35' => "",
    'description36' => "<h1 style=\"background-color: #ba2d59; color: white;\"><strong><span style=\"color: #ffffff; font-size: x-large;\">Popup title</span></strong></h1>\r\n<p>Phasellus eu suscipit nibh, vitae sagittis dui. Fusce et feugiat dui. Aliquam quam ligula, elementum vel neque nec, tempor ullamcorper libero. Nulla auctor libero ligula, nec aliquet lorem condimentum ac. Nullam fringilla suscipit dui, sed fermentum odio aliquet at. Aliquam eleifend purus ac est accumsan, vitae venenatis ante accumsan. Quisque sagittis dolor eget tellus volutpat, eget interdum mauris egestas. Curabitur lacus nulla, molestie ac varius sed, malesuada non turpis.</p>\r\n<p style=\"text-align: center;\"><span class=\"dialogCloseCustom\" style=\"margin-right: 10px; margin-bottom: 10px; display: inline-block;\">{{widget widget_id=\"_eer1\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#ba2d59\" button_size=\"normall\" template=\"widget/buttons/4.phtml\" button_text=\"Enter\" buttontext_color=\"#FFFFFF\" button_href=\"eee\" link_target=\"1\"}}</span><span style=\"margin-right: 10px;\">{{widget widget_id=\"_eer2\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#ba2d59\" button_size=\"normall\" template=\"widget/buttons/4.phtml\" on_click=\"2\" button_text=\"Leave\" buttontext_color=\"#FFFFFF\" button_href=\"http://www.google.com\" link_target=\"1\"}}</span></p>\r\n<p>&nbsp;</p>",
    'custom_css36' => ".dialogBody h1{\r\nmargin: -20px -20px 10px -20px;\r\npadding:4px 20px;\r\n}",
    'custom_script36' => "",
    'description37' => "<h1 style=\"background-color: #4c4b4b; color: white; line-height: 40px;\"><span style=\"color: #ffffff; font-size: x-large;\">Popup title</span></h1>\r\n<p>Quisque sit amet suscipit est. Suspendisse vitae mollis libero. Sed mattis nulla urna, non auctor odio hendrerit eget. Donec porttitor rhoncus ullamcorper. Nulla facilisi. Vestibulum neque lorem, faucibus ut risus eget, pharetra porttitor urna. Nullam rhoncus laoreet sem, non egestas dui fermentum vel. Pellentesque et mi nec lorem sodales bibendum.</p>\r\n<p>&nbsp;</p>\r\n<hr style=\"border: 0; height: 0; border-top: 1px solid rgba(0, 0, 0, 0.1); border-bottom: 1px solid rgba(255, 255, 255, 0.3); margin-bottom: 10px;\" />\r\n<p style=\"margin-bottom: -10px; text-align: right;\"><span class=\"dialogCloseCustom\">{{widget widget_id=\"_eer3\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#649300\" button_size=\"small\" template=\"widget/buttons/4.phtml\" button_text=\"Got it\" buttontext_color=\"#FFFFFF \" button_href=\"#\" link_target=\"1\"}}</span></p>",
    'custom_css37' => ".dialogBody h1{\r\nmargin: -20px -20px 10px -20px;\r\npadding:4px 20px;\r\n}\r\n\r\n.dialogBody{\r\n    background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n    background: -o-radial-gradient(white, white, #e1e1e1);\r\n    background: -moz-radial-gradient(white, white, #e1e1e1); \r\n    background: radial-gradient(white, white, #e1e1e1);\r\n}",
    'custom_script37' => "",
    'description38' => "<h1 style=\"background-color: #03a2b1; color: white; width: 230px; text-align: center; margin: 0 auto;\"><span style=\"color: #ffffff;\"><span style=\"font-size: xx-large;\"><span style=\"font-size: x-large;\">POPUP TITLE</span></span><span style=\"font-size: xx-large; font-weight: bold;\"><br /></span></span></h1>\r\n<p><br />Aliquam mattis egestas imperdiet. Quisque a pulvinar neque, et pharetra urna. Vivamus feugiat rutrum convallis. Sed in lacinia nunc. Pellentesque pulvinar lobortis tortor, in rhoncus turpis ornare vitae. Sed ac cursus lacus. Quisque commodo vestibulum odio nec lobortis. Nullam eu justo quis mauris mattis convallis.</p>\r\n<p>Quisque sit amet suscipit est. Suspendisse vitae mollis libero. Sed mattis nulla urna, non auctor odio hendrerit eget. Donec porttitor rhoncus ullamcorper. Nulla facilisi. Vestibulum neque lorem, faucibus ut risus eget, pharetra porttitor urna. Nullam rhoncus laoreet sem, non egestas dui fermentum vel. Praesent vehicula, nulla at congue accumsan, magna arcu dapibus metus, eget ullamcorper felis augue gravida dui. Pellentesque et mi nec lorem sodales bibendum.</p>\r\n<p>&nbsp;</p>",
    'custom_css38' => "h1{\r\nmargin-top: -24px !important;\r\npadding:4px 20px;\r\n}\r\n\r\n.dialogBody{\r\nborder-top:5px solid #03a2b1;\r\n}",
    'custom_script38' => "",
    'description39' => "<p><span style=\"font-size: x-large;\">Popup title</span></p>\r\n<hr />\r\n<p><br />Aliquam mattis egestas imperdiet. Quisque a pulvinar neque, et pharetra urna. Vivamus feugiat rutrum convallis. Sed in lacinia nunc. Pellentesque pulvinar lobortis tortor, in rhoncus turpis ornare vitae. Sed ac cursus lacus. Quisque commodo vestibulum odio nec lobortis. Nullam eu justo quis mauris mattis convallis.</p>\r\n<p>Quisque sit amet suscipit est. Suspendisse vitae mollis libero. Sed mattis nulla urna, non auctor odio hendrerit eget. Donec porttitor rhoncus ullamcorper. Nulla facilisi. Vestibulum neque lorem, faucibus ut risus eget, pharetra porttitor urna. Nullam rhoncus laoreet sem, non egestas dui fermentum vel. Praesent vehicula, nulla at congue accumsan, magna arcu dapibus metus, eget ullamcorper felis augue gravida dui. Pellentesque et mi nec lorem sodales bibendum.</p>\r\n<p>&nbsp;</p>",
    'custom_css39' => "hr { \r\npadding: 0; \r\nborder: none; \r\nborder-top: medium double #333; \r\ncolor: #333; \r\ntext-align: center; \r\n}\r\n\r\nhr:after { \r\ncontent: \"§\"; \r\ndisplay: inline-block; \r\nposition: relative;\r\ntop: -17px; \r\nfont-size: 18px; \r\npadding: 0 2px;\r\nbackground: white;\r\n}",
    'custom_script39' => "",
    'description40' => "<p class=\"ribon\"><strong><span style=\"font-size: large;\">Your title<br /></span></strong></p>\r\n<p>&nbsp;</p>\r\n<p>Aliquam mattis egestas imperdiet. Quisque a pulvinar neque, et pharetra urna. Vivamus feugiat rutrum convallis. Sed in lacinia nunc. Pellentesque pulvinar lobortis tortor, in rhoncus turpis ornare vitae. Sed ac cursus lacus. Quisque commodo vestibulum odio nec lobortis. Nullam eu justo quis mauris mattis convallis.</p>\r\n<p>Quisque sit amet suscipit est. Suspendisse vitae mollis libero. Sed mattis nulla urna, non auctor odio hendrerit eget. Donec porttitor rhoncus ullamcorper. Nulla facilisi.</p>\r\n<p>&nbsp;</p>\r\n<p class=\"dialogCloseCustom\" style=\"color: #ffffff; font-size: 17px; text-shadow: 1px 1px black;\">No thank you</p>",
    'custom_css40' => ".dialogBody{\r\noverflow:visible;\r\n}\r\n\r\n.ribon{\r\n	position:relative;\r\n	padding:10px 30px;\r\n	margin:0 -20px -10px -20px;\r\n	border:1px solid #c9c8c8;\r\n	background:#f1f1f1;\r\n	color:#6a6340;\r\n	box-shadow:0 4px 4px rgba(0,0,0,0.3);\r\n}\r\n.ribon:before,\r\n.ribon:after {\r\n	content:\" \";\r\n	border-top:10px solid #929191;	/* Colour of the triangle. To flip the effect, use border-bottom. */\r\n	position:absolute;\r\n	bottom:-11px; /* +1 to compensate for the border */\r\n} \r\n.ribon:before {\r\n	border-left:10px solid transparent;\r\n	left:-1px;	/* Only required if the element has a border */\r\n	}\r\n.ribon:after {\r\n	border-right:10px solid transparent;\r\n	right:-1px;\r\n}\r\n\r\n.dialogCloseCustom{\r\nposition:absolute;\r\nbottom:-40px;\r\n}",
    'custom_script40' => "",
    'description41' => "<p class=\"ribon\"><span style=\"color: #000000; font-size: x-large;\">Discount club</span></p>\r\n<p>&nbsp;</p>\r\n<p>To receive <strong>exclusive offers</strong> and our most popular news, please enter your email address and press Subscribe.</p>\r\n<p>{{widget widget_id=\"_eer4\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/6.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#fc6706\" button_text_color=\"#FFFFFF\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%0A\"}}</p>\r\n<p><span style=\"font-size: x-small;\">We hate spam too and we won't share your data with anybody!</span></p>\r\n<p class=\"dialogCloseCustom\" style=\"color: #ffffff; font-size: 17px; text-shadow: 1px 1px black;\">No thank you</p>",
    'custom_css41' => ".dialogBody{\r\n        overflow:visible;\r\n        background: -webkit-radial-gradient(white, white, #e7f0f5); \r\n        background: -o-radial-gradient(white, white, #e7f0f5);\r\n        background: -moz-radial-gradient(white, white, #e7f0f5); \r\n        background: radial-gradient(white, white, #e7f0f5);\r\n}\r\n\r\n.ribon{\r\n	position:relative;\r\n	padding:10px 30px;\r\n	margin:0 -20px -10px -20px;\r\n	border:1px solid #c9c8c8;\r\n        background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n        background: -o-radial-gradient(white, white, #e1e1e1);\r\n        background: -moz-radial-gradient(white, white, #e1e1e1); \r\n        background: radial-gradient(white, white, #e1e1e1);\r\n	box-shadow:0 4px 4px rgba(0,0,0,0.3);\r\n}\r\n.ribon:before,\r\n.ribon:after {\r\n	content:\" \";\r\n	border-top:10px solid #929191;	/* Colour of the triangle. To flip the effect, use border-bottom. */\r\n	position:absolute;\r\n	bottom:-11px; /* +1 to compensate for the border */\r\n} \r\n.ribon:before {\r\n	border-left:10px solid transparent;\r\n	left:-1px;	/* Only required if the element has a border */\r\n	}\r\n.ribon:after {\r\n	border-right:10px solid transparent;\r\n	right:-1px;\r\n}\r\n\r\n.dialogCloseCustom{\r\nposition:absolute;\r\nbottom:-40px;\r\n}\r\n\r\n.dialogClose.style2{\r\n        right:-44px;\r\n        top:-34px;\r\n}",
    'custom_script41' => "",
    'description42' => "<p style=\"text-align: left;\"><strong><span style=\"color: #e1481d; font-size: 70px;\">Dont miss out that!</span></strong></p>\r\n<p style=\"text-align: left;\">Enter your email address to receive <strong>15% discount</strong> and other news from our website.</p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_eer5\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/2.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#e1481d\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\"YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}<br />&nbsp;</p>",
    'custom_css42' => "",
    'custom_script42' => "",
    'description43' => "<p class=\"dialogCloseCustom\">Back to the site&raquo;</p>\r\n<div class=\"tucked-corners top-corners\">\r\n<div class=\"tucked-corners bottom-corners\">\r\n<table style=\"width: 100%; height: 209px; border-color: #bc1107; border-style: solid; border-width: 0px; background-color: #f5f3f3;\" border=\"0\" cellpadding=\"20\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-large;\"><span style=\"color: #bc1107;\">EXCLUSIVE SAVINGS!</span><span style=\"color: #bc1107;\"><strong><br /></strong></span></span></p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: small;\">Enter your email address to receive news about exclusive savings and offers!</span></p>\r\n<p style=\"text-align: center;\"><br />{{widget widget_id=\"_eer6\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/6.phtml\" width=\"330\" button_text=\"SAVE NOW!\" button_color=\"#bc1107\" button_text_color=\"#FFFFFF\" thanks_msg=\"Thank you for your subscription! <p class='dialogCloseCustom'>Back to the site&raquo;</p>\"}}</p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>\r\n</div>",
    'custom_css43' => ".top-corners {\r\n   background-color:#f5f3f3;\r\n   position: relative;\r\n   padding:20px;\r\n  -moz-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n   box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n}\r\n\r\n.bottom-corners {\r\n    display: block;\r\n    position: relative;\r\n}\r\n\r\n.top-corners:after,\r\n.top-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    top: -25px;\r\n    width: 90px;\r\n    z-index: 2;\r\n   -moz-box-shadow:0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n   -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n   box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33); \r\n}\r\n.top-corners:after {\r\n    left: -50px;\r\n    -webkit-transform: rotate(-45deg);\r\n    -moz-transform: rotate(-45deg);\r\n    -ms-transform: rotate(-45deg);\r\n    -o-transform: rotate(-45deg);\r\n     transform: rotate(-45deg);\r\n}\r\n.top-corners:before {\r\n    right: -50px;\r\n    -webkit-transform: rotate(45deg);\r\n    -moz-transform: rotate(45deg);\r\n    -ms-transform: rotate(45deg);\r\n    -o-transform: rotate(45deg);\r\n    transform: rotate(45deg);\r\n}\r\n\r\n.bottom-corners:after,\r\n.bottom-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    bottom: -45px;\r\n    width: 90px;\r\n   -moz-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n   -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n   box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n}\r\n.bottom-corners:after {\r\n    left: -65px;\r\n    -webkit-transform: rotate(-135deg);\r\n    -moz-transform: rotate(-135deg);\r\n    -ms-transform: rotate(-135deg);\r\n    -o-transform: rotate(-135deg);\r\n     transform: rotate(-135deg);\r\n}\r\n.bottom-corners:before {\r\n    right: -65px;\r\n    -webkit-transform: rotate(135deg);\r\n    -moz-transform: rotate(135deg);\r\n    -ms-transform: rotate(135deg);\r\n    -o-transform: rotate(135deg);\r\n     transform: rotate(135deg);\r\n}\r\n\r\n.dialogBg{\r\nbackground-color:#e5e4e4 !important;\r\nbackground:#e5e4e4 !important;\r\n}\r\n\r\n.dialogCloseCustom{\r\nposition:absolute;\r\nbottom:-10px;\r\nleft:50px;\r\nz-index:9999;\r\nfont-size:20px;\r\ntext-decoration:underline;\r\ncursor:pointer;\r\n}\r\n\r\n",
    'custom_script43' => "",
    'description44' => "<p>&nbsp;</p>\r\n<p><span style=\"color: #ffffff; font-size: xx-large; text-shadow: 2px 3px black;\">Suspendisse facilisis ligula sem, sed gravida ligula elementum a. </span></p>\r\n<p>&nbsp;</p>\r\n<p class=\"dialogCloseCustom\">{{widget widget_id=\"_eer7\" type=\"Magebird\\Popup\\Block\\Widget\\Buttons\" button_color=\"#76BF69\" button_size=\"big\" template=\"widget/buttons/13.phtml\" button_text=\"Continue\" buttontext_color=\"#FFFFFF\" button_href=\"/\" link_target=\"1\"}}</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>",
    'custom_css44' => ".dialogBody h1{\r\nmargin: -20px -20px 10px -20px;\r\npadding:4px 20px;\r\n}\r\n",
    'custom_script44' => "",
    'description45' => "<p class=\"dialogCloseCustom\">Back to the site&raquo;</p>\r\n<div class=\"tucked-corners top-corners\">\r\n<div class=\"tucked-corners bottom-corners\">\r\n<table style=\"width: 100%; height: 209px; border-color: #bc1107; border-style: solid; border-width: 0px; background-color: #f5f3f3;\" border=\"0\" cellpadding=\"20\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<p style=\"text-align: center;\"><span style=\"color: #709e0c; font-size: xx-large;\">Subscribe now</span><span style=\"font-size: x-large;\"><span style=\"color: #bc1107;\"><strong><br /></strong></span></span></p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: xx-large;\">and get 5% off!</span><br /><br />{{widget widget_id=\"_eer8\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/6.phtml\" width=\"330\" width_unit=\"1\" button_text=\"SAVE NOW!\" button_color=\"#709e0c\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\"YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}</p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>\r\n</div>",
    'custom_css45' => ".top-corners {\r\n   background-color:#f5f3f3;\r\n   position: relative;\r\n   padding:20px;\r\n  -moz-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n   box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n}\r\n\r\n.bottom-corners {\r\n    display: block;\r\n    position: relative;\r\n}\r\n\r\n.top-corners:after,\r\n.top-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    top: -25px;\r\n    width: 90px;\r\n    z-index: 2;\r\n  -moz-box-shadow:0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33); \r\n}\r\n.top-corners:after {\r\n    left: -50px;\r\n    -webkit-transform: rotate(-45deg);\r\n       -moz-transform: rotate(-45deg);\r\n        -ms-transform: rotate(-45deg);\r\n         -o-transform: rotate(-45deg);\r\n            transform: rotate(-45deg);\r\n}\r\n.top-corners:before {\r\n    right: -50px;\r\n    -webkit-transform: rotate(45deg);\r\n       -moz-transform: rotate(45deg);\r\n        -ms-transform: rotate(45deg);\r\n         -o-transform: rotate(45deg);\r\n            transform: rotate(45deg);\r\n}\r\n\r\n.bottom-corners:after,\r\n.bottom-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    bottom: -45px;\r\n    width: 90px;\r\n  -moz-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n}\r\n.bottom-corners:after {\r\n    left: -65px;\r\n    -webkit-transform: rotate(-135deg);\r\n       -moz-transform: rotate(-135deg);\r\n        -ms-transform: rotate(-135deg);\r\n         -o-transform: rotate(-135deg);\r\n            transform: rotate(-135deg);\r\n}\r\n.bottom-corners:before {\r\n    right: -65px;\r\n    -webkit-transform: rotate(135deg);\r\n       -moz-transform: rotate(135deg);\r\n        -ms-transform: rotate(135deg);\r\n         -o-transform: rotate(135deg);\r\n            transform: rotate(135deg);\r\n}\r\n\r\n.dialogBg{\r\nbackground-color:#e5e4e4 !important;\r\nbackground:#e5e4e4 !important;\r\n}\r\n\r\n.dialogCloseCustom{\r\nposition:absolute;\r\nbottom:-10px;\r\nleft:50px;\r\nz-index:9999;\r\nfont-size:20px;\r\ntext-decoration:underline;\r\ncursor:pointer;\r\n}",
    'custom_script45' => "",
    'description46' => "<p class=\"dialogCloseCustom\">Back to the site&raquo;</p>\r\n<div class=\"tucked-corners top-corners\">\r\n<div class=\"tucked-corners bottom-corners\">\r\n<table style=\"width: 100%; height: 209px; border-color: #bc1107; border-style: solid; border-width: 0px; background-color: #f5f3f3;\" border=\"0\" cellpadding=\"20\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<p style=\"text-align: center;\"><span style=\"color: #61aac9; font-size: xx-large;\">Subscribe now</span><span style=\"font-size: x-large;\"><span style=\"color: #bc1107;\"><strong><br /></strong></span></span></p>\r\n<p style=\"text-align: center;\"><span style=\"font-size: xx-large;\">and get 5% off!</span><br /><br />{{widget widget_id=\"_eer9\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/6.phtml\" width=\"330\" width_unit=\"1\" button_text=\"SAVE NOW!\" button_color=\"#61aac9\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\"YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}</p>\r\n<p style=\"text-align: center;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>\r\n</div>",
    'custom_css46' => ".top-corners {\r\n   background-color:#f5f3f3;\r\n   position: relative;\r\n   padding:20px;\r\n  -moz-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n   box-shadow: 0 0 9px 0 rgba(0, 0, 0, 0.33);\r\n}\r\n\r\n.bottom-corners {\r\n    display: block;\r\n    position: relative;\r\n}\r\n\r\n.top-corners:after,\r\n.top-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    top: -25px;\r\n    width: 90px;\r\n    z-index: 2;\r\n  -moz-box-shadow:0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33); \r\n}\r\n.top-corners:after {\r\n    left: -50px;\r\n    -webkit-transform: rotate(-45deg);\r\n       -moz-transform: rotate(-45deg);\r\n        -ms-transform: rotate(-45deg);\r\n         -o-transform: rotate(-45deg);\r\n            transform: rotate(-45deg);\r\n}\r\n.top-corners:before {\r\n    right: -50px;\r\n    -webkit-transform: rotate(45deg);\r\n       -moz-transform: rotate(45deg);\r\n        -ms-transform: rotate(45deg);\r\n         -o-transform: rotate(45deg);\r\n            transform: rotate(45deg);\r\n}\r\n\r\n.bottom-corners:after,\r\n.bottom-corners:before {\r\n    background: #e5e4e4;\r\n    content: '';\r\n    height: 45px;\r\n    position: absolute;\r\n    bottom: -45px;\r\n    width: 90px;\r\n  -moz-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  -webkit-box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n  box-shadow: 0 7px 5px -7px rgba(0, 0, 0, 0.33);\r\n}\r\n.bottom-corners:after {\r\n    left: -65px;\r\n    -webkit-transform: rotate(-135deg);\r\n       -moz-transform: rotate(-135deg);\r\n        -ms-transform: rotate(-135deg);\r\n         -o-transform: rotate(-135deg);\r\n            transform: rotate(-135deg);\r\n}\r\n.bottom-corners:before {\r\n    right: -65px;\r\n    -webkit-transform: rotate(135deg);\r\n       -moz-transform: rotate(135deg);\r\n        -ms-transform: rotate(135deg);\r\n         -o-transform: rotate(135deg);\r\n            transform: rotate(135deg);\r\n}\r\n\r\n.dialogBg{\r\nbackground-color:#e5e4e4 !important;\r\nbackground:#e5e4e4 !important;\r\n}\r\n\r\n.dialogCloseCustom{\r\nposition:absolute;\r\nbottom:-10px;\r\nleft:50px;\r\nz-index:9999;\r\nfont-size:20px;\r\ntext-decoration:underline;\r\ncursor:pointer;\r\n}",
    'custom_script46' => "",
    'description47' => "<p style=\"margin-bottom: 0px; text-align: center;\"><span style=\"color: #709e0c; font-size: xx-large;\">Subscribe now</span></p>\r\n<p style=\"text-align: center;\"><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">and get 5% off!</span></strong></span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_rer0\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/4.phtml\" width=\"280\" width_unit=\"1\" button_text=\"SAVE NOW!\" button_color=\"#709e0c\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\" YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}</p>",
    'custom_css47' => "",
    'custom_script47' => "",
    'description48' => "<p style=\"margin-bottom: 0px; text-align: center;\"><span style=\"color: #f15b47; font-size: xx-large;\">Subscribe now</span></p>\r\n<p style=\"text-align: center;\"><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">and get 5% off!</span></strong></span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_rer1\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/1.phtml\" width=\"280\" width_unit=\"1\" button_text=\"Get 5% off\" button_color=\"#f15b47\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\" YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}</p>",
    'custom_css48' => "",
    'custom_script48' => "",
    'description49' => "<p style=\"margin-bottom: 0px; text-align: center;\"><strong><span style=\"color: #f15b47; font-size: large;\">Subscribe now</span></strong></p>\r\n<p style=\"text-align: center;\"><span style=\"color: #333333; font-size: large;\"><strong>and get 5% off!</strong></span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_rer2\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/5.phtml\" width=\"240\" width_unit=\"1\" button_text=\"Get 5% off\" button_color=\"#f15b47\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\"YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.\"}}</p>",
    'custom_css49' => "#popupNewsletter{\r\nfont-size:13px;\r\n}",
    'custom_script49' => "",
    'description50' => "<p style=\"margin-bottom: 0px; text-align: center;\"><span style=\"color: #ebebeb;\"><strong><span style=\"font-size: large;\">Subscribe now</span></strong></span></p>\r\n<p style=\"text-align: center;\"><span style=\"color: #fafafa; font-size: large;\"><strong>and get 5% off!</strong></span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_rer3\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/5.phtml\" width=\"240\" width_unit=\"1\" button_text=\"Get 5% off\" button_color=\"#393939\" button_text_color=\"#FFFFFF\" coupon_type=\"1\" coupon_code=\"YOUR COUPON GOES HERE\" on_success=\"1\" success_msg=\"%3Cp%20style=%22color:#FFFFFF%22%3EThank%20you%20for%20your%20subscription!%20Your%20coupon%20is:%20%7B%7Bvar%20coupon_code%7D%7D.%20To%20get%20discount%20copy%20&%20paste%20it%20to%20offer%20code%20box%20in%20your%20shopping%20cart.%3C/p%3E\"}}</p>",
    'custom_css50' => "#popupNewsletter{\r\nfont-size:13px;\r\n}",
    'custom_script50' => "",
    'description51' => "<p><iframe src=\"//www.youtube.com/embed/BBvsB5PcitQ?list=UUZeJjcZ0_rnvL-pp7p4Aq3A\" frameborder=\"0\" width=\"450\" height=\"253\"></iframe></p>",
    'custom_css51' => ".dialogBody { \r\nmargin: 20px 0; \r\nborder-radius: 50% / 10%; \r\ntext-indent: .1em;\r\n } \r\n\r\n.dialogBody:before { \r\ncontent: ''; \r\ntop: 10%; \r\nbottom: 10%;\r\nright: -5%;\r\nleft: -5%; \r\nbackground: inherit; \r\nborder-radius: 9% / 50%; \r\n}\r\n\r\n.dialogClose{\r\nright:-23px;\r\n}",
    'custom_script51' => "",
    'description52' => "<p><iframe src=\"//player.vimeo.com/video/66658527?byline=0&amp;portrait=0\" frameborder=\"0\" width=\"500\" height=\"281\"></iframe></p>",
    'custom_css52' => "",
    'custom_script52' => "",
    'description53' => "<p><iframe src=\"//player.vimeo.com/video/66658527?byline=0&amp;portrait=0\" frameborder=\"0\" width=\"700\" height=\"393\"></iframe></p>",
    'custom_css53' => "",
    'custom_script53' => "",
    'description54' => "<p style=\"margin-bottom: 0px; text-align: center;\"><span style=\"color: #f15b47; font-size: xx-large;\">Subscribe now</span></p>\r\n<p style=\"text-align: center;\"><span style=\"color: #333333;\"><strong><span style=\"font-size: xx-large;\">and get 5% off!</span></strong></span></p>\r\n<p style=\"text-align: center;\">{{widget widget_id=\"_rer4\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/1.phtml\" width=\"100\" width_unit=\"2\" button_text=\"Get 5% off\" button_color=\"#f15b47\" button_text_color=\"#FFFFFF\" thanks_msg=\"<p style='font-size:18px'>To get discount copy & paste <strong>'Your coupon code goes here'</strong> to offer code box in your shopping cart.</p> <a href='#' class='dialogCloseCustom'>Back to the site &raquo;</a>\"}}</p>",
    'custom_css54' => "",
    'custom_script54' => "",
    'description57' => "<h1 style=\"background-color: #4c4b4b; color: white; line-height: 42px;\"><span style=\"color: #ffffff; font-size: 30px;\">10% OFF. Join discount club!<br /></span></h1>\r\n<p>Join our discount club and get <strong>10% OFF</strong> now. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin commodo, nisi mollis mattis laoreet</p>\r\n<p>{{widget widget_id=\"_rer7\" type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" template=\"widget/newsletter/2.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#4c4b4b\" button_text_color=\"#FFFFFF\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</p>\r\n<p><span style=\"font-size: x-small;\">*We will never share your information</span></p>",
    'custom_css57' => ".dialogBody h1{\r\nmargin: -20px -20px 10px -20px;\r\npadding:4px 20px;\r\ncursor:pointer;\r\n}\r\n\r\n\r\n.dialogBody{\r\n    background: -webkit-radial-gradient(white, white, #e1e1e1); \r\n    background: -o-radial-gradient(white, white, #e1e1e1);\r\n    background: -moz-radial-gradient(white, white, #e1e1e1); \r\n    background: radial-gradient(white, white, #e1e1e1);\r\n}",
    'custom_script57' => "var showAll = false;\r\nvar bottom =0;\r\njQuery('.dialogBody h1').unbind().click(function(){\r\n   if(!showAll){\r\n     jQuery('.mbdialog').css({top:\"auto\"});\r\n     jQuery('.mbdialog').animate({bottom:\"0px\"},800);\r\n     showAll = true;\r\n   }else{\r\n     jQuery('.mbdialog').css({top:\"auto\"});\r\n     var bottom = jQuery(\".dialogBody\").innerHeight()-jQuery(\".dialogBody h1\").innerHeight();\r\n     jQuery('.mbdialog').animate({bottom:\"-\"+bottom+\"px\"},800);\r\n     showAll = false;   \r\n   }\r\n});\r\nvar slideUpPopup = setInterval(function(){\r\n  if(jQuery('.dialogBody').is(\":visible\")){                                           \r\n    clearInterval(slideUpPopup)    \r\n    setTimeout(function(){\r\n      var bottom = jQuery(\".dialogBody\").innerHeight()-jQuery(\".dialogBody h1\").innerHeight();\r\n      jQuery('.mbdialog').css({bottom:\"-\"+bottom+\"px\"});\r\n    }, 1);\r\n  }\r\n},10)",
    'description60' => "<table style=\"height: 376px; width: 100%; background-color: #eaeaea;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"width: 250px; text-align: center; padding: 15px;\">\r\n<p style=\"font-size: 40px;\"><span style=\"border-bottom: 1px solid black; padding-bottom: 20px;\">VIP CLUB</span></p>\r\n<p style=\"line-height: 1; font-size: 17px; margin: 35px 0 20px;\">Join our vip club and receive this winter <span style=\"font-weight: bold;\">exclusive offers</span> and <span style=\"font-weight: bold;\">discounts</span>.</p>\r\n<p style=\"clear: both; margin: 0 auto;\"><strong><span style=\"color: #9e6dc6;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_oj7l\" template=\"widget/newsletter/6.phtml\" width=\"250\" width_unit=\"1\" button_text=\"Join me\" button_color=\"#c2a079\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"2\" close_delay=\"2\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</span></strong></p>\r\n<p class=\"dialogCloseCustom\" style=\"text-decoration: underline; font-size: 17px; margin-top: 12px;\">No thanks</p>\r\n</td>\r\n<td class=\"imageField\" style=\"width: 304px;\"><img src=\"{{media url=\"wysiwyg/popup/winter_tall.jpg\"}}\" alt=\"\" width=\"324\" height=\"376\" style=\"display: block;\" /></td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css60' => "  @media (min-width: 100px) and (max-width: 400px) {\r\n.imageField{display:none;}\r\n.mbdialog{\r\nwidth:340px !important;\r\n}\r\n  }",
    'custom_script60' => "",
    'description61' => "<table style=\"border-spacing: 25px;background-image: url('{{media url=\"wysiwyg/popup/winter700.jpg\"}}'); width: 600px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"background-color: rgba(0, 0, 0, 0.35); border: 1px solid #ffffff; padding: 20px; text-shadow: 1px 1px #000000; color: #ffffff;\">\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 26px; font-style: italic; margin: 0;\">Hear about this winter</p>\r\n<p class=\"popupBig\" style=\"text-align: left; font-size: 70px; line-height: 1; margin: 0;\">EXCLUSIVE</p>\r\n<p class=\"popupBig\" style=\"text-align: left; font-size: 70px; line-height: 1; font-weight: bold; margin: 0;\">SAVINGS!</p>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 24px; line-height: 1; margin: 20px 0; font-style: italic;\">Enter your e-mail and hear about exclusive savings and news!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_9lz9\" template=\"widget/newsletter/2.phtml\" width=\"95\" width_unit=\"2\" button_text=\"Sign me up!\" button_color=\"#6aa167\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"2\" close_delay=\"2\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 16px; font-style: italic; margin: 0;\">* We will never share your information</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css61' => "@media (max-width: 600px) {\r\n.newsletterPopup td{display:block;}\r\n.newsletterPopup td input{margin:10px 0;}\r\n.newsletterPopup td button{margin:0;}\r\ntable{width:100% !important;}\r\n.popupBig{font-size:26px !important}\r\n.popupSmall{font-size:14px !important; margin:0 !important}\r\n}\r\n\r\n",
    'custom_script61' => "",
    'description62' => "<p style=\"text-align: center;\"><strong><span style=\"color: #2f87ce; font-size: x-large;\">What's hot and new?<br /></span></strong></p>\r\n<p style=\"font-size: 15px; line-height: 18px; text-align: center;\">Enter your email address to receive <strong>exclusive offers</strong> and other news from our website</p>\r\n<div style=\"text-align: center;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_xb1m\" template=\"widget/newsletter/1.phtml\" width=\"100\" width_unit=\"2\" button_color=\"#3694D2\" button_text_color=\"#FFFFFF\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"Thank%20you%20for%20your%20subscription!\"}}</div>\r\n<p style=\"text-align: center;\"><span style=\"font-size: x-small;\">*We will never share your information</span></p>",
    'custom_css62' => ".dialogBody *{\r\n   font-family: 'Open Sans', sans-serif;\r\n}",
    'custom_script62' => " WebFontConfig = {\r\n    google: { families: [ 'Open+Sans:400,600:latin' ] }\r\n  };\r\n  (function() {\r\n    var wf = document.createElement('script');\r\n    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +\r\n      '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';\r\n    wf.type = 'text/javascript';\r\n    wf.async = 'true';\r\n    var s = document.getElementsByTagName('script')[0];\r\n    s.parentNode.insertBefore(wf, s);\r\n  })();",
    'description63' => "<table style=\"height: 450px; width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td class=\"imageField\" style=\"width: 304px;\"><img src=\"{{media url=\"wysiwyg/popup/winter300.jpg\"}}\" style=\"display: block;\" alt=\"\" width=\"300\" height=\"450\" /></td>\r\n<td style=\"width: 300px; padding: 25px;\">\r\n<p class=\"fontBig\" style=\"font-size: 40px; line-height: 1; text-align: left;\"><br />Be the first to know...</p>\r\n<p style=\"line-height: 1; font-size: 17px; margin: 35px 0px 20px; text-align: left;\">Subscribe our newsletter and get exlusive access to&nbsp;access to exclusive offers, promotions, new arrivals and more!.</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_45vn\" template=\"widget/newsletter/12.phtml\" width=\"90\" width_unit=\"2\" button_text=\"Join me\" button_color=\"#6aa167\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"dialogCloseCustom\" style=\"text-decoration: underline; font-size: 17px; margin-top: 12px; text-align: left;\">No thanks</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css63' => "  @media (max-width: 600px) {\r\n.imageField{display:none;}\r\n.mbdialog{width:340px !important;}\r\n.dialogBody{background-color:#eaeaea !important;}\r\np{font-size:14px !important;}\r\n.fontBig{font-size:25px !important;}\r\n  }",
    'custom_script63' => "",
    'description64' => "<table style=\"background-image: url('{{media url=\"wysiwyg/popup/face_500.jpg\"}}'); background-position: right; background-size:auto 100%; background-repeat: no-repeat; width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding: 30px;\">\r\n<p class=\"fontBig\" style=\"text-align: left; font-size: 26px; font-style: italic; margin: 0;\">Hear about</p>\r\n<p class=\"fontBig\" style=\"text-align: left; font-size: 70px; line-height: 1; margin: 0;\">EXCLUSIVE</p>\r\n<p class=\"fontBig\" style=\"text-align: left; font-size: 70px; line-height: 1; font-weight: bold; margin: 0; color: #ff1176;\">SAVINGS!</p>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 24px; line-height: 1; margin: 20px 0; font-style: italic;\">Enter your e-mail and hear<br />about exclusive savings and news!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_sp7r\" template=\"widget/newsletter/10.phtml\" width=\"55\" width_unit=\"2\" button_text=\"Sign me up!\" button_color=\"#ff1176\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"dialogCloseCustom popupSmall\" style=\"text-decoration: underline; text-align: left; font-size: 18px; font-style: italic; margin-top: 10px;\">No thanks, I will pay full price</p>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 16px; font-style: italic; margin: 0;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css64' => "@media (max-width: 600px) {\r\n.fontBig{font-size:24px !important}\r\n.fontSmall{font-size:14px !important; margin:0 !important}\r\np{font-size:16px !important}\r\n/*table{background-size:200px;}*/\r\n.newsletterPopup form{width:100% !important}\r\ntd{background-color:rgba(0, 0, 0, 0.48);color:#ffffff}\r\n.dialogClose{top:-28px;right:-10px;}\r\n}",
    'custom_script64' => "",
    'description66' => "<table class=\"popupTable\" style=\"background-color: #00909e; height: 32px; margin: 0px auto;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"text-align: center; padding-right: 10px;\"><strong><span style=\"color: #ffffff; font-size: large;\">Your promo title or notification goes here</span></strong></td>\r\n<td style=\"text-align: center;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_ef52\" template=\"widget/newsletter/8.phtml\" width=\"250\" width_unit=\"1\" button_text=\"Subscribe\" button_color=\"#ffffff\" button_text_color=\"#000000\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cdiv%20style=%22text-align:center;padding:5px;%20color:#ffffff%22%3EThank%20you%20for%20your%20subscription!%3C/div%3E\"}}</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css66' => "@media (max-width: 600px) {\r\n.popupTable > tbody > tr > td{display:block !important;}\r\n*{font-size:15px !important;}\r\n  }\r\nbutton,input{\r\n-webkit-border-radius: 2px;\r\n-moz-border-radius: 2px;\r\nborder-radius: 2px;\r\n}",
    'custom_script66' => "",
    'description67' => "<table style=\"background-image: url('{{media url=\"wysiwyg/popup/male_fashion.jpg\"}}'); background-position: right; background-size: auto 100%; background-repeat: no-repeat; width: 100%; background-color: #69747b;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding: 30px;\">\r\n<p class=\"fontBig\" style=\"text-align: left; margin: 0px; line-height: 46px; color: white; font-size: 44px;\">Exclusive deals delivered<br />straight to your inbox!</p>\r\n<p class=\"popupSmall\" style=\"text-align: left; line-height: 1; font-style: italic; color: white; font-weight: normal; font-size: 21px; margin: 34px 0px;\">Sign up to know when is our next<br /> big sale, get exclusive promotions <br />&amp; more!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_tp5k\" template=\"widget/newsletter/14.phtml\" width=\"55\" width_unit=\"2\" button_text=\"SIGN UP NOW!\" button_color=\"#6f6f6f\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center;color:white%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 16px; font-style: italic; margin: 0;\">&nbsp;</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css67' => ".dialogBody *{font-family:sans-serif}\r\n@media (max-width: 600px) {\r\n.fontBig{font-size:24px !important;line-height:1 !important}\r\n.fontSmall{font-size:14px !important; margin:0 !important}\r\np{font-size:16px !important}\r\n/*table{background-size:200px;}*/\r\n.newsletterPopup form{width:100% !important}\r\ntd{background-color:rgba(0, 0, 0, 0.48);color:#ffffff}\r\n}\r\n\r\n",
    'custom_script67' => "",
    'description68' => "<table style=\"border-spacing: 55px; background-image: url('{{media url=\"wysiwyg/popup/fruits.jpg\"}}'); width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"background-color: #ffffff; padding: 20px; box-shadow: 0px 0px 8px 0px rgba(0,0,0,0.75);\">\r\n<p style=\"text-align: center; font-size: 33px; margin: 0px;\">Fresh discounts to your inbox</p>\r\n<div style=\"text-align: center;\">\r\n<p style=\"font-size: 18px; margin-top: 20px;\">Sign up to know when is our next <strong><span style=\"text-decoration: underline;\">big sale</span></strong>, get exclusive promotions &amp; more!</p>\r\n</div>\r\n<div style=\"text-align: center;\">&nbsp;</div>\r\n<div style=\"text-align: center;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_6y2y\" template=\"widget/newsletter/2.phtml\" width=\"100\" width_unit=\"2\" button_text=\"GET ME IN!\" button_color=\"#000000\" button_text_color=\"#FFFFFF\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22text-align:center;padding:20px;background-color:white;font-size:18px;margin:0;%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}<br />&nbsp;</div>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css68' => ".dialogBody *{\r\n   font-family: Oswald, sans-serif;\r\n}\r\n\r\n@media (max-width: 400px) {\r\ntable{border-spacing:12px !important}\r\ntable{width:100% !important;}\r\n.fontBig{font-size:26px !important; margin-top:20px !important; margin-bottom:10px !important;}\r\n.countdown{font-size:25px;}\r\np{font-size:15px !important;}\r\n.newsletterPopup td{display:block;}\r\n.newsletterPopup td input{margin:10px 0;}\r\n.newsletterPopup td button{margin:0;}\r\n.dialogClose{right:-6px;top:-25px;}\r\n}\r\n\r\n",
    'custom_script68' => "",
    'description69' => "<table style=\"background-image: url('{{media url=\"wysiwyg/popup/green.jpg\"}}'); background-size: 100%; width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding: 20px; text-shadow: 1px 2px 1px #000000;\">\r\n<p class=\"fontBig\" style=\"text-align: center; margin: 0px; font-size: 43px; color: white;\">Fresh discounts to your inbox</p>\r\n<div style=\"text-align: center;\">\r\n<p style=\"margin-top: 20px; font-size: 25px; color: white;\">Sign up to know when is our next big sale, get exclusive promotions &amp; more!</p>\r\n</div>\r\n<div style=\"text-align: center;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_413s\" template=\"widget/newsletter/2.phtml\" width=\"100\" width_unit=\"2\" button_text=\"GET ME IN!\" button_color=\"#000000\" button_text_color=\"#FFFFFF\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22text-align:center;padding:20px;background-color:#e1e1e1;font-size:18px;margin:0;%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}<br />&nbsp;</div>\r\n<div style=\"text-align: center;\">&nbsp;</div>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css69' => "@media (max-width: 400px) {\r\ntable{border-spacing:12px !important}\r\ntable{width:100% !important;}\r\n.fontBig{font-size:26px !important; margin-top:20px !important; margin-bottom:10px !important;}\r\n.countdown{font-size:25px;}\r\np,span{font-size:16px !important;}\r\n.newsletterPopup td{display:block;}\r\n.newsletterPopup td input{margin:10px 0;}\r\n.newsletterPopup td button{margin:0;}\r\n}\r\n\r\n",
    'custom_script69' => "",
    'description70' => "<p id=\"popupTrigger\" style=\"margin: 0; background-color: #e8184e; color: #ffffff; text-align: center; line-height: 20px;\">CLICK HERE<br />TO GET<br /><span style=\"font-size: 20px; font-weight: bold;\">10% OFF</span></p>",
    'custom_css70' => "",
    'custom_script70' => "",
    'description71' => "<table style=\"width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"height: 186px; background-color: #e2e1e1;\">\r\n<div style=\"padding: 20px 0 20px 50px;\">\r\n<p class=\"fontBig\" style=\"font-size: 30px; font-weight: bold; color: #960302; line-height: 1; text-align: left;\">5% OFF TODAY</p>\r\n<p style=\"line-height: 1; font-size: 17px; margin: 15px 0px 20px; text-align: left;\">When you subscribe to<br />our mailing list!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_v0aq\" template=\"widget/newsletter/8.phtml\" width=\"90\" width_unit=\"2\" button_text=\"Join me\" button_color=\"#000000\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center;background-color:#e2e1e1%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n</div>\r\n</td>\r\n<td id=\"slideLeft\" style=\"cursor: pointer; width: 36px;\"><img src=\"{{media url=\"wysiwyg/popup/rightslider.png\"}}\" alt=\"\" width=\"36\" height=\"186\" /></td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css71' => "  @media (max-width: 600px) {\r\n.mbdialog{width:95% !important}\r\n\r\n  }\r\n.dialogClose{\r\nleft:13px;\r\ntop:3px;\r\n}",
    'custom_script71' => "var isHidden = true;\r\njQuery('#slideLeft').click(function(){\r\n     popupWidth = jQuery(\".mbdialog\").outerWidth()-slideIconWidth;\r\n     if(isHidden){\r\n         isHidden = false;\r\n         jQuery(\".mbdialog\").animate({\"left\":\"-10px\"}, \"slow\");\r\n     }else{\r\n         isHidden = true;\r\n         jQuery(\".mbdialog\").animate({\"left\":\"-\"+popupWidth+\"px\"}, \"slow\");\r\n     }\r\n});\r\n\r\nvar slideIconWidth = jQuery(\"#slideLeft\").outerWidth();\r\nvar popupWidth = jQuery(\".mbdialog\").outerWidth()-slideIconWidth;\r\nvar slideUpPopup = setInterval(function(){\r\n  if(jQuery('.dialogBody').is(\":visible\")){                                         \r\n    clearInterval(slideUpPopup)    \r\n    setTimeout(function(){     \r\n     if(isHidden){\r\n         jQuery(\".mbdialog\").animate({\"left\":\"-\"+popupWidth+\"px\"}, \"slow\");\r\n     }else{\r\n         jQuery(\".mbdialog\").animate({\"left\":\"-10px\"}, \"slow\");\r\n     }\r\n    }, 1);\r\n  }\r\n},10)",
    'description72' => "<table style=\"width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td id=\"slideLeft\" style=\"cursor: pointer; width: 36px;\"><img src=\"{{media url=\"wysiwyg/popup/rightslider.png\"}}\" alt=\"\" width=\"36\" height=\"186\" /></td>\r\n<td style=\"height: 186px; background: url('/media/wysiwyg/popup/water2.jpg');\">\r\n<div style=\"padding: 20px;\">\r\n<p class=\"fontBig\" style=\"font-size: 30px; font-weight: bold; color: #000000; line-height: 1; text-align: left;\">5% OFF TODAY</p>\r\n<p style=\"line-height: 1; font-size: 17px; margin: 15px 0px 20px; text-align: left;\"><span style=\"color: #000000;\"><strong>When you subscribe to</strong></span><br /><span style=\"color: #000000;\"><strong>our mailing list!</strong></span></p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_9npl\" template=\"widget/newsletter/8.phtml\" width=\"90\" width_unit=\"2\" button_text=\"Join me\" button_color=\"#000000\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center;background-color:#f6f6f6;border:1px%20solid%20silver%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n</div>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css72' => "  @media (max-width: 600px) {\r\n.mbdialog{width:95% !important}\r\n\r\n  }\r\n.dialogClose{\r\nright:8px;\r\n}",
    'custom_script72' => "var isHidden = true;\r\njQuery('#slideLeft').click(function(){\r\n     popupWidth = jQuery(\".mbdialog\").outerWidth()-slideIconWidth;\r\n     if(isHidden){\r\n         isHidden = false;\r\n         jQuery(\".mbdialog\").css('right','-10px');\r\n     }else{\r\n         isHidden = true;\r\n         jQuery(\".mbdialog\").css('right','-'+popupWidth+'px');\r\n     }\r\n});\r\n\r\nvar slideIconWidth = jQuery(\"#slideLeft\").outerWidth();\r\nvar popupWidth = jQuery(\".mbdialog\").outerWidth()-slideIconWidth;\r\nvar slideUpPopup = setInterval(function(){\r\n  if(jQuery('.dialogBody').is(\":visible\")){                                         \r\n    clearInterval(slideUpPopup)    \r\n    setTimeout(function(){     \r\n     if(isHidden){\r\n         jQuery(\".mbdialog\").css('right','-'+popupWidth+'px');\r\n     }else{\r\n         jQuery(\".mbdialog\").css('right','-10px');\r\n     }\r\n    }, 1);\r\n  }\r\n},10)",
    'description73' => "<table style=\"background-image: url('{{media url=\"wysiwyg/popup/board.png\"}}'); width: 100%; height: 510px;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding: 30px 115px 0 115px; vertical-align: top;\">\r\n<div style=\"margin: 0 auto;\">\r\n<p class=\"fontBig\" style=\"text-align: left; font-size: 70px; line-height: 1; margin: 0;\">EXCLUSIVE</p>\r\n<p class=\"fontBig\" style=\"text-align: left; font-size: 70px; line-height: 1; font-weight: bold; margin: 0; color: #ff1176;\">SAVINGS!</p>\r\n<p class=\"popupSmall\" style=\"text-align: left; font-size: 24px; line-height: 1; margin: 20px 0; font-style: italic;\">Enter your e-mail and hear<br />about exclusive savings and news!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_vd49\" template=\"widget/newsletter/10.phtml\" width=\"100\" width_unit=\"2\" button_text=\"Sign me up!\" button_color=\"#ff1176\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center;%20background-color:white;%20border:1px%20solid%20silver%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"dialogCloseCustom popupSmall\" style=\"text-decoration: underline; text-align: left; font-size: 18px; font-style: italic; margin-top: 10px;\">No thanks, I will pay full price</p>\r\n</div>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css73' => "@media (max-width: 400px) {\r\ntable{border-spacing:12px !important;width:100% !important; height:auto !important; background:#ffffff !important}\r\ntable td{padding:10px !important}\r\n.fontBig{font-size:26px !important; margin-top:20px !important; margin-bottom:10px !important;}\r\np,span{font-size:16px !important;}\r\n}\r\n\r\n",
    'custom_script73' => "",
    'description74' => "<table style=\"height: 450px; width: 100%; padding: 20px;\">\r\n<tbody>\r\n<tr>\r\n<td class=\"imageField\" style=\"width: 265px;\"><img src=\"{{media url=\"wysiwyg/popup/jeans.jpg\"}}\" alt=\"\" width=\"300\" height=\"450\" /></td>\r\n<td style=\"padding: 0 26px;\">\r\n<p class=\"fontBig\" style=\"text-align: left; line-height: 1.5; font-size: 39px; color: black;\"><span>HEY :)</span><br /><span style=\"font-weight: bold; color: black;\">SAVE 5%</span> TODAY!</p>\r\n<p style=\"line-height: 1; font-size: 17px; margin: 25px 0px 20px; text-align: left;\">Love saving money?</p>\r\n<p style=\"font-size: 17px;\">Join our Discount Club and get a code for <span style=\"font-weight: bold;\">5% off</span>! It's that Simple!</p>\r\n<div style=\"text-align: left;\">{{widget type=\"Magebird\\Popup\\Block\\Widget\\Newsletter\" widget_id=\"_ivfy\" template=\"widget/newsletter/6.phtml\" width=\"90\" width_unit=\"2\" button_text=\"GET DISCOUNT CODE\" button_color=\"#000000\" button_text_color=\"#ffffff\" apply_coupon=\"1\" on_success=\"1\" success_msg=\"%3Cp%20style=%22padding:30px;%20font-size:20px;%20text-align:center%22%3EThank%20you%20for%20your%20subscription!%3C/p%3E\"}}</div>\r\n<p class=\"dialogCloseCustom\" style=\"text-decoration: underline; font-size: 14px; margin-top: 12px; text-align: left;\">NO THANKS, I DON'T NEED COUPON</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>",
    'custom_css74' => "#popup-newsletter-form button{font-size:15px;}\r\n#popupNewsletter{font-weight:normal;}\r\n  @media (max-width: 600px) {\r\n.imageField{display:none;}\r\n.mbdialog{width:340px !important;}\r\n.dialogBody{background-color:#eaeaea !important;}\r\np{font-size:14px !important;}\r\n.fontBig{font-size:25px !important;}\r\ntable{height:auto !important}\r\n  }",
    'custom_script74' => ""    
    );  
    $conn->query($query, $binds);
  }   
}