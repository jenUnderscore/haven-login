<?php

class Haven_Login_Auth0API
{
	private $auth0;
	private $session;
  private $user;
  private $settings;

	public function __construct()
	{
    $this->settings = stripslashes_deep(get_option( 'haven-settings', array() ));
    
    if($this->checkEnv()){
      
      // Load our environment variables from the .env file:
      (Dotenv\Dotenv::createImmutable(HAVEN_PRIVATE_DIR))->load();

      $this->auth0 = new \Auth0\SDK\Auth0([
          'domain' => $_ENV['AUTH0_DOMAIN'],
          'clientId' => $_ENV['AUTH0_CLIENT_ID'],
          'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
          'cookieSecret' => $_ENV['AUTH0_COOKIE_SECRET']
      ]);
      
      // Define route constants:
      define('ROUTE_URL_INDEX', rtrim(get_bloginfo('wpurl'), '/'));
      define('ROUTE_URL_LOGIN', ROUTE_URL_INDEX . '/?login');
      define('ROUTE_URL_CALLBACK', ROUTE_URL_INDEX . '/?callback');
      define('ROUTE_URL_CHECKUSER', ROUTE_URL_INDEX . '/?checkuser');
      define('ROUTE_URL_LOGOUT', ROUTE_URL_INDEX . '/?logout');
      
      $this->setSession();
      $this->checkAuthRequest();
    }
	}

  public function checkEnv(){
    // Load our environment variables from the .env file:
    (Dotenv\Dotenv::createImmutable(HAVEN_PRIVATE_DIR))->load();

    return ($_ENV['AUTH0_DOMAIN'] && $_ENV['AUTH0_CLIENT_ID'] && $_ENV['AUTH0_CLIENT_SECRET'] && $_ENV['AUTH0_COOKIE_SECRET']);
  }
	
	private function checkAuthRequest(){		
		if(array_key_exists('logout',$_GET)) $this->doLogout();
			else if(array_key_exists('login',$_GET)) $this->doLogin();
        else if(array_key_exists('signup',$_GET)) $this->doSignup();
				  else if(array_key_exists('callback',$_GET)) $this->doCallback();
				    else if(array_key_exists('checkuser',$_GET)) $this->checkUser();

    $this->setUserDetails();
	}

  public function getSetting($key=null){
    if($key) return $this->settings[$key];
    
    return $this->settings;
  }

  private function setUserDetails(){
    if($this->session && $this->session->user){
      if(array_key_exists('email',$this->session->user)){
        $api = new Haven_API('user');
        $results = $api->process('email='.$this->session->user['email'], "GET");

        if($results){
          $result = current($results);
          $this->user = new Haven_Login_User($result->id,$result->details);
        }
      }
    }
  }

  public function getPublicUser(){
    if($this->user) return $this->user->getDetails();

    return null;
  }

  public function isLoggedIn(){
    return ($this->checkEnv() && $this->session !== null);
  }

  public function auth0EmailForm($title='',$minimal=false){
    if (!$this->isLoggedIn()) {
      $out = '';

      $fields = array(
            	array("type"=>"textbox","title"=>"E-mail","id"=>"email","name"=>"email","required"=>true,"description"=>"","jscript"=>"","style"=>"","class"=>"","validate"=>"valid_email","placeholder"=>"Type your email here","no_row"=>true)
      );


      $form_properties = array(
          'action'  => '/?checkuser'
        , 'name'    => 'haven_checkemail_form'
        , 'id'      => 'haven_checkemail_form' 
        , 'hide_rows' => true
        , 'class' => 'ui form'
      );

      $form = new Haven_Form();
      $form->set_properties( $form_properties );
      $form->set_data(array_merge($_POST,array("email_title"=>"<div>E-mail:</div>")));
      $form->add_fields($fields);
      $form->add_hidden("haven_nonce", wp_create_nonce( "haven_login" ));

      //button row: add_button($title,$type,$name='',$jscript='',$link='',$class='')
      $form->add_button('<i class="right arrow icon"></i> Next',"submit",'','','',' right labeled icon');

      if($title) $out .= '<h3>' . $title . '</h3>';
      $out .= $form->print_form("POST",false,"","320px");

      if(!$minimal){
        $out = '<div class="ui fluid card haven_emailcheck"><div class="content">' . $out . '</div></div>';
      }

      return $out;
    }

    return '';
  }
	
	public function printAccountMenu(){    
    if($this->checkEnv()){
      if ($this->isLoggedIn()) {
        // The user is logged in.
        $email = $this->session->user['email'];
        $picture = $this->session->user['picture'] ?? '';

        $name = 'Go To Dashboard';
        if($this->user){
          if($this->user->getFirstname()) $name = $this->user->getFirstname();  
        }

        $dashboard_url = parse_url($this->getSetting('auth0_dashboard'), PHP_URL_SCHEME) . '://' . parse_url($this->getSetting('auth0_dashboard'), PHP_URL_HOST);

        return '<li id="haven-menu" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children" aria-haspopup="true">
          <a href="' . $dashboard_url. '" class="button--user"><img src="' . $picture .'" class="haven__user--icon" /><div class="haven__user--content">' . $name . '<span>' . $email . '</span></div><i class="dropdown icon"></i></a>
            <ul class="sub-menu">
              <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . $dashboard_url  . '/account">Account Settings</a></li>
              <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . $dashboard_url  . '/logout">Logout</span></a></li>
            </ul>
          </li>
        ';
      }
      // The user isn't logged in.
      return  '<li id="haven-menu" class="menu-item menu-item-type-custom menu-item-object-custom">' .  $this->printSignIn().'</li>';
    }
		
		return '';
	}

  public function printSignIn($create=false){
    if($this->checkEnv()){
      if ($this->isLoggedIn() && $this->user) {
        return '';
      }
      
      $button = '<button class="button--login" onClick="window.location.href=\'' . $this->getSetting('auth0_login_href') . '\'">' . $this->getSetting('auth0_login_btn') .  ' </button>';
      if($create){
        return $this->printCreateButton() . $button;
      }

      return $button;
    }    
  }

  public function printCreateButton(){    
    if($this->getSetting('maintenance_mode') != "Y"){
      return '<button class="button--signup" onClick="window.location.href=\'/?signup\'">' . $this->getSetting('auth0_signup_text') . '</button>';
    }
  }
	
	private function setSession(){
		$this->session = $this->auth0->getCredentials();
	}

	private function doSignup(){
		// It's a good idea to reset user sessions each time they go to login to avoid "invalid state" errors, should they hit network issues or other problems that interrupt a previous login process:
		$this->auth0->clear();

		// Finally, set up the local application session, and redirect the user to the Auth0 Universal Login Page to authenticate.
		header("Location: " . $this->auth0->signup(ROUTE_URL_CALLBACK));
    exit;
	}
	
	private function doLogin(){
		// It's a good idea to reset user sessions each time they go to login to avoid "invalid state" errors, should they hit network issues or other problems that interrupt a previous login process:
		$this->auth0->clear();

		// Finally, set up the local application session, and redirect the user to the Auth0 Universal Login Page to authenticate.
		header("Location: " . $this->auth0->login(ROUTE_URL_CALLBACK));
    exit;
	}
	
	private function doLogout(){
    // Clear the user's local session with our app, then redirect them to the Auth0 logout endpoint to clear their Auth0 session.
    $this->auth0->clear();

    //redirects to self
    header("Location: " . $this->auth0->logout(ROUTE_URL_INDEX));
    exit;
	}

	private function checkUser(){
    if(array_key_exists('email',$_POST)){
      $email = $_POST['email'];
      $emailSanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
      
      $this->auth0->clear();
      $authentication = $this->auth0->authentication();
      //$management = $this->auth0->management();
      
      // Or, a fluent example:
      //$response = $this->auth0->management()->usersByEmail()->get('admin@centralcounties.ca');
      
      $response = $this->auth0->management()->usersByEmail()->get($emailSanitized);
      if ($response->getStatusCode() === 200) { // Checks that the status code was 200
        $result = json_decode($response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
          $params = array('login_hint' => $emailSanitized);
        if($result){
          header("Location: " . $this->auth0->login(ROUTE_URL_CALLBACK,$params));
          exit;
        }
        else{
          header("Location: " . $this->auth0->signup(ROUTE_URL_CALLBACK,$params));
          exit;
        }
        return true;  
      }

      //something went wrong go back to referrer
      header("Location: " . $_SERVER['HTTP_REFERER']);
      exit;
    }
    exit;
	}
	
	private function doCallback(){
    // Have the SDK complete the authentication flow:
    $this->auth0->exchange(ROUTE_URL_CALLBACK);
    
    $this->setSession();

    $root_url = parse_url($this->getSetting('auth0_dashboard'), PHP_URL_SCHEME) . '://' . parse_url($this->getSetting('auth0_dashboard'), PHP_URL_HOST);

    header("Location: " . $root_url . "/authenticate");
    
    exit;
	}
}