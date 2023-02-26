<?php 

class Haven_Login_User {
    private $id;
    private $details;
    private $user_id;

    public function __construct($id, $details) {
      $this->id = $id;
      $this->details = $details;
    }
    
    public function getId(){
      return $this->id;
    }

    public function getAccountId(){
      return $this->details->account->id;
    }
    
    public function getDetail($key=null){
      if($key) return $this->details->$key;
        else return $this->details;
    }
    public function getCompanyName(){
      return $this->getDetail('company_name');
    }

    public function getFirstname(){
      return $this->getDetail('firstname');
    }

    public function getLastname(){
      return $this->getDetail('lastname');
    }

    public function getEmail(){
      return $this->getDetail('email');
    }
    
    public function getDetails(){
      return $this->getDetail();
    }
  }