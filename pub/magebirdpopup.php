<?php        
session_name('MAGEBIRD_POPUP');                    
session_start(); 
require_once realpath(__DIR__) . '/../lib/internal/magebird/popup/magebird_popup.php';
require_once realpath(__DIR__) . '/../lib/internal/magebird/popup/customizer.php';
require_once realpath(__DIR__) . '/../lib/internal/magebird/popup/popup_model.php';
require_once realpath(__DIR__) . '/../lib/internal/magebird/popup/popup_helper.php';
require_once realpath(__DIR__) . '/../lib/internal/magebird/popup/popup_view.php';
header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 
$class  = new magebird_popup();