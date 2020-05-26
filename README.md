If you have any problems with the installation please contact us and we will install the extension FOR FREE. Free installation offer is valid also for free trial version.

---------------------------------------
INSTALLATION:
---------------------------------------

You can install the extension in three ways. If your server supports Composer you can use composer. You can also install extension through admin. Or you can use directly ftp upload.

Install through admin:
Follow Magento official guide https://docs.magento.com/marketplace/user_guide/quick-tour/install-extension.html.

Install by Composer or ftp upload:

Step 1

Install by Composer :
In command line, using "cd", navigate to your Magento 2 root directory and run the command: composer require magebird/popup


Install by Ftp upload :
Download the latest version of the extension from https://www.magebird.com/download/Popup_package_m2.zip and upload everything inside folder "For upload" to your Magento root folder.

Step 2

In command line, using "cd", navigate to your Magento 2 root directory. Run commands:


php bin/magento setup:upgrade

php bin/magento setup:di:compile

php bin/magento setup:static-content:deploy

Popup extension will be added to admin menu under CONTENT->Magebird Popup. 

Step 3

Check if script magebirdpopup.php is web accessible. To check if script is web accessible open www.yourdomain.com/magebirdpopup.php or www.yourdomain.com/pub/magebirdpopup.php and press ctrl+u. First line of source code should look like this:
<script data-cfasync="false" type="text/javascript">
If you don't see this line but you see Page not found or any other message please ask your server admin to enable it.

Step 4

By default Mailchimp integration is already included and no further step is required. We support also Aweber, MailerLite, Constant Contact, Campaign Monitor, ActiveCampaign, Sendy, phpList, dotmailer, GetResponse, Klaviyo, Nuevomailer, Emma, Mailjet but you need to contact us to arrange it. 
