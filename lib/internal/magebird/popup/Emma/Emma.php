<?php
/*
http://api.myemma.com/php_signup_example.html
*/
class Emma
{
    var $publicApiKey;
    var $privateApiKey;
    var $accountId;

    public function __construct($accountId,$publicApiKey,$privateApiKey)
    {
        $this->accountId = $accountId;
        $this->publicApiKey = $publicApiKey;
        $this->privateApiKey = $privateApiKey;
    }

    public function subscribe($groupIds, $email, $fields,$sendOptin)
    {                          
      $member_data = array(
        "email" => $email,
        "fields" => $fields,
        "group_ids" => explode(",",$groupIds),
        "opt_in_confirmation"=>$sendOptin
      );
      $url = "https://api.e2ma.net/".$this->accountId."/members/signup";
      
      
      // setup and execute the cURL command
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_USERPWD, $this->publicApiKey . ":" . $this->privateApiKey);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($member_data));
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($member_data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $api = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      //execute post       
      if($http_code > 200) {
        $response['status'] = 2;
        $api = json_decode($api);
        if($api->error){
          $response['error'] = $api->error;
        }else{
          $response['error'] = "Please check your account id, private and public key";
        }        
      } else {
        $response['status'] = 1;
      }
      return $response;
    }


}
