<?php

class Dotmailer
{
    var $apiEmail;
    var $password;

    public function __construct($apiEmail,$password)
    {
        $this->apiEmail = $apiEmail;
        $this->password = $password;
    }

    public function subscribe($addressId,$data)
    {                          
      //encode the data as json string
      $requestBody = json_encode($data);  
      $url = 'https://apiconnector.com/v2/address-books/'.intval($addressId).'/contacts';
      //initialise curl session
      $ch = curl_init();
  
      //curl options
      curl_setopt($ch, CURLAUTH_BASIC, CURLAUTH_DIGEST);
      curl_setopt($ch, CURLOPT_USERPWD, $this->apiEmail . ':' . $this->password); // credentials
      curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Accept: ' . 'application/json' ,'Content-Type: application/json'));
  
      //curl execute and json decode the response
      $responseBody = json_decode(curl_exec($ch));
  
      //close curl session
      curl_close($ch);
      return $responseBody;
    }


}
