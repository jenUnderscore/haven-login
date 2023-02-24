<?php

/**
 * Haven_Login_Activator
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Haven Login
 * @subpackage haven-login/includes
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Activator {
  
	public function __construct() {
  }

  private function is_bulk_activation(){
    return (
      ( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) &&
      ( isset( $_POST['checked'] ) && count( $_POST['checked'] ) > 1 )
    );
  }

  public function activate_haven_plugin() {        
    $haven_dependency = 'haven-wp-plugin/haven.php';

    $installed_plugins = get_plugins();
    $is_bulk_activation = self::is_bulk_activation(); 
    
    if((array_key_exists( $haven_dependency,$installed_plugins)) && !is_plugin_active($haven_dependency)){
      //including this here ensures the activation hook is registered
      require_once WP_PLUGIN_DIR . '/'.$haven_dependency;
      if(!is_plugin_active($haven_dependency)){
        $current = get_option( 'active_plugins' );
        $plugin = plugin_basename( $haven_dependency );
        if ( !in_array( $plugin, $current ) ) {
          $current[] = $plugin;
          sort($current);
          do_action( 'activate_plugin', trim( $plugin ) );
          update_option( 'active_plugins', $current );
          do_action( 'activate_' . trim( $plugin ) );
          do_action( 'activated_plugin', trim( $plugin ) );
        }
      }
    }
    elseif(!array_key_exists( $haven_dependency,$installed_plugins)){
      $settings = new Haven_Admin_Settings();
      $settings->save_addon_default_settings('login');
      self::redirect_to_settings_page();
    }

    return null;
  }

  private function redirect_to_settings_page(){
    exit( wp_redirect( admin_url( 'admin.php?page=haven_settings&tab=login' ) ) );
  }

  /**
   * Do a thing when the plugin activates
   *
   * @since    1.0.0
   */
  public static function activate($plugin) {
		if ( plugin_basename( HAVEN_LOGIN_PLUGIN_FILE ) != plugin_basename($plugin) ) {
			return;
		}

    //$current = get_option( 'active_plugins' );
    //$current[] = $plugin;
    //sort($current);
    //update_option( 'active_plugins', $current );
    //add_action('update_option_active_plugins', function() {
    //  self::activate_haven_plugin();
    //});
    //add_action('update_option_active_plugins', function() {
    //  //this activates the Haven Plugin if it is present
    //});
    add_action('update_option_active_plugins', function() {
      add_filter('filter_haven_permissions',function($permissions){
        $permissions[] = 'login';
        return $permissions;
      });

      self::activate_haven_plugin();
    });
  }
}
