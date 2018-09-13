<?php
class restApi
{     
    protected $adminUrl;
    protected $password;
    protected $username;

    public function __construct(array $config)
    {
        //error checking
        $this->adminUrl = @$config['adminUrl'];
        $this->username = @$config['username'];
        $this->password = @$config['password'];
        $this->curl     = curl_init();
    }
    
    function subscribe($email,$listId,$confirmed=1){ 
       $url = $this->adminUrl . "admin/?";
       $ch = curl_init();
       $data["login"] = $this->username;
       $data["password"] = $this->password;
       curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_URL, $url);    
       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/emptyfile.txt");  
       $result = curl_exec($ch);
       if(strpos($result, 'login-form')!==false){
          $error = "phpList error: Wrong login";
          $status = 2;
       }elseif(!$result){
          $error = "phpList error: Please check url";
          $status = 2;
       }else{
          $post_data["email"] = $email;
          $post_data["emailconfirm"] = $email;
          $post_data["htmlemail"] = "1";
          $post_data["list[$listId]"] = "signup";
          $post_data["subscribe"] = "Subscribe";
          $post_data["makeconfirmed"] = $confirmed;
          $url = $this->adminUrl . "?p=subscribe&isPopup=1";
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_URL, $url);    
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          $error = null;
          $status = 1;                   
       }           
 
       return array('status'=>$status,'error'=>$error);
    }

}