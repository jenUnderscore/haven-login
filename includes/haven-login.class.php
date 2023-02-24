<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.       
 *
 * @since      1.0.0
 * @package    Haven Login Plugin
 * @subpackage haven-login/includes
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Plugin{

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
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $haven_login    The string used to uniquely identify this plugin.
	 */
	protected $haven_login;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Haven Login Public
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $plugin_public   
	 */
	protected $plugin_public;

	/**
	 * Haven Login Admin
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $plugin_admin   
	 */
	protected $plugin_admin;
  
	/**
	 * Haven Login FAQ
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $haven_login_faq 
	 */
	protected $haven_login_faq;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'HAVEN_LOGIN_VERSION' ) ) {
			$this->version = HAVEN_LOGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->haven_login = 'haven_login';

		$this->load_dependencies();
		$this->set_locale();
    
    if( !is_admin() ) $this->load_public();
      else $this->load_admin();

    $this->load_haven_login_faq();
	}
  
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Haven_Login_Loader. Orchestrates the hooks of the plugin.
	 * - Haven_Login_i18n. Defines internationalization functionality.
	 * - Haven_Login_Admin. Defines all hooks for the admin area.
	 * - Haven_Login_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/haven-login-loader.class.php';
        
		$this->loader = new Haven_Login_Loader();
    
		//this dynmically loads the plugin dependencies
    spl_autoload_register( array( $this->loader , 'autoload' ) );
    
    if( HAVEN_LOGIN_DEBUG ) $this->set_debug_mode();
  }
 
  private function set_debug_mode(){    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // set to the user defined error handler
    set_error_handler( array( new Haven_System_Errors(), 'error_handler' ) );
  }

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Haven_Login_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Haven_Login_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Instantiate the admin-facing part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_admin() {
		$this->plugin_admin = new Haven_Login_Admin( $this->get_loader(), $this->get_haven_login(), $this->get_version() );
	}
	/**
	 * Retrieve the admin-facing part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_admin() {
	  return $this->plugin_admin;
	}


	/**
	 * Instantiate the public-facing part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_public() {
		$this->plugin_public = new Haven_Login_Public( $this->get_loader(), $this->get_haven_login(), $this->get_version() );
	}

	/**
	 * Retrieve the public-facing part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_public() {
	  return $this->plugin_public;
	}

	/**
	 * Instantiate the faq plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_haven_login_faq() {
		$this->haven_login_faq = new Haven_Login_Faq( $this->get_loader(), $this->get_haven_login(), $this->get_version() );
	}

	/**
	 * Retrieve the public-facing part of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_haven_login_faq() {
	  return $this->haven_login_faq;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
    
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_haven_login() {
		return $this->haven_login;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Haven_Login_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
}