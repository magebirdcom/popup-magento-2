<?php
class api
{     
    protected $adminUrl;
    protected $password;
    protected $username;

    public function __construct($url,$apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }
    
    function subscribe($email,$listIds,$firstName,$lastName,$optin,$templateId){ 
       $url = $this->url."subscriber/optIn.php";
        $subscriber = array(
          'trigger_only'=>'yes',
          'api_action'=>'add',
          'double_optin'=>$optin,
          'opt_out_type'=>2,
          'email'=>$email,
          'lists'=>$listIds,
          'customSubField1'=>$firstName,
          'customSubField2'=>$lastName  
        );

        if($this->apiKey){
          $subscriber['api_key'] = $this->apiKey;
        }
        if($templateId){
          $subscriber['trigger_newsletter'] = $templateId;
        }   
        
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_POST, 1);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $subscriber);
        curl_setopt($request, CURLOPT_TIMEOUT, 5);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($request);            
    }

}