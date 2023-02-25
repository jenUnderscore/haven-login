<?php
/*
Plugin Name: Haven Login
Description: Plugin for adding login button for dashboard
Author: Haven Destinations
Version: 1.0.1
Author URI: http://havendestinations.ca
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//include hardcoded settings
require_once(path_join(plugin_dir_path( __FILE__ ),'config.php'));

/**
 * Create our own constants from WordPress stuff
 */
if ( !defined( 'HAVEN_LOGIN_PLUGIN_URL' ) )
  define( 'HAVEN_LOGIN_PLUGIN_URL',  path_join(plugins_url(), basename( dirname( __FILE__ ))));
if ( !defined( 'HAVEN_LOGIN_PLUGIN_FILE' ) )
  define( 'HAVEN_LOGIN_PLUGIN_FILE', __FILE__ );
if ( !defined( 'HAVEN_LOGIN_PLUGIN_DIR' ) )
  define( 'HAVEN_LOGIN_PLUGIN_DIR',dirname( __FILE__ ) );
if ( !defined( 'HAVEN_LOGIN_PLUGIN_PUBLIC_DIR' ) )
  define( 'HAVEN_LOGIN_PLUGIN_PUBLIC_DIR', path_join(dirname( __FILE__ ), 'public' ) );
if ( !defined( 'HAVEN_LOGIN_PLUGIN_PUBLIC_URL' ) )
  define( 'HAVEN_LOGIN_PLUGIN_PUBLIC_URL',  path_join(HAVEN_LOGIN_PLUGIN_URL, 'public' ) );
if ( !defined( 'HAVEN_LOGIN_PLUGIN_ADMIN_URL' ) )
  define( 'HAVEN_LOGIN_PLUGIN_ADMIN_URL',  path_join(HAVEN_LOGIN_PLUGIN_URL, 'admin' ) ); 


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/haven-activator.class.php
 */
function activate_haven_login() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/haven-login-activator.class.php';
	Haven_Login_Activator::activate( __FILE__ );
}
register_activation_hook( __FILE__, 'activate_haven_login' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/haven-deactivator.class.php
 */
function deactivate_haven_login() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/haven-login-deactivator.class.php';
	Haven_Login_Deactivator::deactivate(__FILE__);
}
register_deactivation_hook( __FILE__, 'deactivate_haven_login' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/haven-login.class.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_haven_login() {

  //add permissions
  add_filter('filter_haven_permissions',function($permissions){
    $permissions[] = 'login';
    return $permissions;
  });

	$plugin = new Haven_Login_Plugin();  
  //all

  //Haven FAQs
  if($plugin->get_haven_login_faq()){
    add_action('admin_menu', array($plugin->get_haven_login_faq(),'settings'));
    add_action('admin_menu', array($plugin->get_haven_login_faq(),'info_hooks'));              // Haven FAQs Widget - ensures we hook the metabox
    add_action('save_post', array($plugin->get_haven_login_faq(),'save_info'));                // Haven FAQs Widget - Save
    add_action('init', array($plugin->get_haven_login_faq(),'register_category_taxonomy'));    // Add haven_faq category taxonomy
    add_action('init', array($plugin->get_haven_login_faq(),'register_tag_taxonomy'));         // Add haven_faq category taxonomy
    add_action('init', array($plugin->get_haven_login_faq(),'create_post_type'));              // Add our  Custom Post Type "haven_faqs"
  }

  //public
  if($plugin->get_public()){
    add_filter('body_class', array($plugin->get_public(),'add_body_class'));

    //login shortcodes
    add_shortcode('haven-email-login', array($plugin->get_public(),'auth0_email_check'));
    add_shortcode('haven-login-buttons', array($plugin->get_public(),'login_buttons'));
    add_shortcode('haven-login', array($plugin->get_public(),'login_block'));
    add_shortcode('haven-maintenance', array($plugin->get_public(),'maintenance_mode'));

    //ignores the maintenance_mode and auth0_active flags
    add_shortcode('haven-test-login', array($plugin->get_public(),'login_form'));

    //highjack any stray midbi things in case the shortcodes are still out there
    //also filters away the CCT Cloud login button
    $plugin->get_public()->midbi_highjack();

  }
  
  $plugin->run();

  return $plugin;
}

//run the addon plugins
add_action('plugins_loaded', function(){
  $plugin = run_haven_login(); 
  if($public = $plugin->get_public()){ 
    if($public->isActive()){
      $GLOBALS['haven_user'] = $public->getAuthUser();
    }
  }
},10);

require 'kernl-update-checker/kernl-update-checker.php';
$MyUpdateChecker = Puc_v4_FactoryKernl::buildUpdateChecker(
    'https://kernl.us/api/v1/updates/63f93d414736a5f572458954/',
    __FILE__,
    'haven-login'
);
