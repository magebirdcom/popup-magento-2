If you have any problems with the installation please contact us and we will install the extension FOR FREE. Free installation offer is valid also for free trial version.

---------------------------------------
INSTALLATION:
---------------------------------------

You can install the extension in two ways. If your server supports Composer you can use composer. Or you can use directly ftp upload.

Step 1

Install by Composer :
Go to the Magento folder and run the command: composer require magebird/popup
 

Install by Ftp upload :
Download the latest version of the extension from https://www.magebird.com/download/Popup_package_m2.zip and upload everything inside folder "For upload" to your Magento root folder.

Step 2

In command line, using "cd", navigate to your Magento 2 root directory. Run commands:

php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

Popup extension will be added to admin menu under CONTENT->Magebird Popup. 
