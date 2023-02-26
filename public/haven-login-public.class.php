<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://havendestinations.ca
 * @since      1.0.0
 *
 * @package    Haven Login
 * @subpackage haven-login/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Haven Login
 * @subpackage haven-login/public
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Public extends Haven_Login{
  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Haven_Login_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $haven_login    The ID of this plugin.
	 */
	private $haven_login;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Auth0 Object
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Haven_Login_Auth0API    $auth0  manages auth0 things
	 */
  private $auth0;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $haven_login       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
   * 
	 */
	public function __construct( $loader, $haven_login, $version ) {

    parent::__construct();
		$this->haven_login = $haven_login;
		$this->version = $version;
    $this->loader = $loader; 
    
    $this->auth0 = new Haven_Login_Auth0API();

    $this->define_hooks();    
  }

  public function getSetting($key=null){
    if($key) return $this->settings->$key;
    
    return $this->settings;
  }


  /**
   * Register all of the hooks related to the admin-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access  private
   * 
   */
  private function define_hooks() {
    $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles');
    $this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts');

    $this->loader->add_action( 'haven_virtual_pages', $this, 'create_virtual_page');
    
    //$this->loader->add_action( 'wp_head', $this, 'header_script' );

  }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
   * 
	 */
	public function enqueue_styles() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Haven_Login_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Haven_Login_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
    
    wp_register_style( $this->haven_login.'-style', HAVEN_LOGIN_PLUGIN_PUBLIC_URL . '/assets/css/haven-login.css', array(), $this->version, 'all' );
    wp_enqueue_style( $this->haven_login.'-style' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Haven_Login_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Haven_Login_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
     * 
     * wp_enqueue_script( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, bool $in_footer = false )
     * 
     * 3rd party vendor scripts are concatenated into one haven_vendor.min.js file
		 */
      wp_register_script( 'haven-login', HAVEN_LOGIN_PLUGIN_PUBLIC_URL . '/assets/js/haven-login.min.js', array(), $this->version, true);
      wp_enqueue_script( 'haven-login' );

			add_filter( 'wp_nav_menu_items', array(&$this,'haven_account_menu_links'), 10, 3 );
  }

  public function haven_dashboard_browse_menu(){
    if($this->getAuthUser()){

      $dashboard_url = parse_url($this->getSetting('auth0_dashboard'), PHP_URL_SCHEME) . '://' . parse_url($this->getSetting('auth0_dashboard'), PHP_URL_HOST);

      $out = '<li id="browsemenu" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children" aria-haspopup="true">
      <a href="#"><i class="bars icon"></i></a>
        <ul class="sub-menu">
        <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . $dashboard_url  . '">Dashboard</a></li>
          <li class="menu-item menu-item-type-custom menu-item-object-custom"><a class="item" href="/category/about/">About Us</a></li>
          <li class="menu-item menu-item-type-custom menu-item-object-custom"><a class="item" href="/category/tourism-marketing/">Tourism Marketing</a></li>
          <li class="menu-item menu-item-type-custom menu-item-object-custom"><a class="item" href="/category/product-development/">Product Development</a></li>
          <li class="menu-item menu-item-type-custom menu-item-object-custom"><a class="item" href="/category/research/">Research</a></li>
          <li class="menu-item menu-item-type-custom menu-item-object-custom"><a class="item" href="/category/support/">Partner Support</a></li>
        </ul>
      </li>';
      return $out;
    }

    return '';
  }

  public function haven_account_menu_links($items, $args){
    if($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){
      if($args->menu->slug == 'utility-nav'){
        $menu = "";

        $menu = trim($this->auth0->printAccountMenu()).trim($this->haven_dashboard_browse_menu());
        
        $items .= apply_filters( 'haven_filter_account_menu', $menu, $args);
      }
    }

    return $items;
  }

  public function add_body_class($classes){
    if($this->auth0->isLoggedIn()){
      $classes[] = 'haven-logged-in';   
    }    

    return $classes;
  }

  public function isActive(){
    return ($this->getSetting('auth0_activate') == "Y");
  }

  public function getAuthUser(){
    return $this->auth0->getPublicUser();
  }

  public function auth0_email_check($params=array()){
    extract(shortcode_atts(array(
      'hide_maintenance' => false,
      'minimal' => false,
      'title' => ''
    ), $params)); 

    if($this->getSetting('maintenance_mode') == "Y"){
      if(!$hide_maintenance) return $this->maintenance_mode($params);
    }  
    elseif($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){  
      return $this->auth0->auth0EmailForm($title,$minimal);
    }    
    return '';
  }

  public function login_block($params=array()){
    extract(shortcode_atts(array(
      'hide_maintenance' => false,
      'minimal' => false,
      'title' => ''
    ), $params)); 

    if($this->getSetting('maintenance_mode') == "Y"){
      if(!$hide_maintenance) return $this->maintenance_mode($params);
    }
    elseif($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){ 
      return $this->login_form($params);
    }
  }

  public function login_form($params=array()){
    extract(shortcode_atts(array(
      'hide_maintenance' => false,
      'minimal' => false,
      'title' => ''
    ), $params)); 

    $tpl = '
    <div class="ui fluid card">
      <div class="content">
        %s
        %s 
      </div>
      <div class="extra content">
        <div class="clear">
          %s
        </div> 
        <div>
          %s
          %s
        </div>
      </div>
    </div>';

    $block_title = ($this->getSetting('auth0_login_title')) ? '<h2>' . $this->getSetting('auth0_login_title') . '</h2>' : '';
    $message = ($this->getSetting('auth0_login_message')) ? '<div class="meta">' . wpautop($this->getSetting('auth0_login_message')) . '</div>' : '';
    $alert = ($this->getSetting('auth0_login_alert')) ? '<div class="login-alert">' . wpautop($this->getSetting('auth0_login_alert')) . '</div>' : '';
    $form = $this->auth0->auth0EmailForm($title,$minimal);
    $footer = ($this->getSetting('auth0_login_footer')) ? wpautop($this->getSetting('auth0_login_footer'))  : '';

    return sprintf($tpl,$block_title,$message,$form,$alert,$footer);
  }

  public function maintenance_mode($params=array()){
    if($this->getSetting('maintenance_mode') == "Y"){
      extract(shortcode_atts(array(
        'minimal' => false
      ), $params)); 

      $maintenance_date = '';
      if($this->getSetting('maintenance_date')){
        $maintenance_date = '<p class="small alignright"><em>' .$this->getSetting('maintenance_date') . '</em></p>';
      }
      $maintenance_title = '';
      if($this->getSetting('maintenance_title')){
        $maintenance_title = '<h3>' .$this->getSetting('maintenance_title') . '</h3>';
      }

      $out = '      
        <div class="content">
          '.$maintenance_title.'
          <div class="login-alert">
           ' . 
            wpautop($this->getSetting('maintenance_message'))
            . '
          </div>
          ' .  $maintenance_date . '
        </div>';

      if(!$minimal){
        $out = '<div class="ui fluid card">' . $out . '</div>';
      }

      return $out;
    }
    
    return '';
  }

  public function login_buttons($params){
    if($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){
      extract(shortcode_atts(array(
        'create' => null
      ), $params));
      
      return $this->auth0->printSignIn($create);
    }

    return '';
  }

  public function output(){
    $tpl = '<h2>%s</h2><p>%s</p><p>%s</p>';
    if(array_key_exists("id",$_GET)){
      switch($_GET["id"]){
        case 'reset':
          $title = 'Password Reset Required';
          $msg = 'An email with instructions has been sent to you. Click the link in the email to reset your password and proceed to log in.';
          $action = $this->auth0->printSignIn(false);
          
         break;
      }
    }

    $out = sprintf($tpl,$title,$msg,$action);

    return $out;
  }

  /*------------------------------------*\
    Cloud (midbi) Login Stuff
  \*------------------------------------*/
  function midbi_menu_modification($midbi_menu){
    if($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){
      $midbi_menu = '';
      return '';
    }
    
    return $midbi_menu;
  }
  public function midbi_highjack(){
    //always do this
    add_filter('midbi_filter_midbi_menu', array($this,'midbi_menu_modification'), 99, 1);   //remove existing CCTCloud login
      
    //only do this if the CCT Cloud is not present
    if ( !class_exists( 'MIDBIAutoloader' ) ) {
      add_shortcode('midbi_register', array($this,'midbi_highjack_redirect'));
      add_shortcode('midbi_account', array($this,'midbi_highjack_redirect'));
      add_shortcode('midbi_login', array($this,'midbi_highjack_redirect'));
      add_shortcode('midbi_searchmember', array($this,'midbi_highjack_shortcodes'));
      add_shortcode('midbi_submit_event', array($this,'midbi_highjack_shortcodes'));
    }
  }

  public function midbi_highjack_redirect(){
    if($this->getSetting('maintenance_mode') == "Y"){
      if(!$hide_maintenance) return $this->maintenance_mode($params);
    }  
    elseif($this->auth0->checkEnv() && $this->getSetting('auth0_activate') == "Y"){ 
      $title = 'Login';
      return $this->auth0->auth0EmailForm($title);
    }
  }

  public function midbi_highjack_shortcodes($params=array()){
    return '';
  }

}
