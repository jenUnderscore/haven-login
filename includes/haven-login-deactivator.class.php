<?php

/**
 * Haven_Deactivator
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Haven Login
 * @subpackage haven-login/includes
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Deactivator {

  /**
   * Do a thing when the plugin is deactivated
   * Note that this is contained by add_action('update_option_active_plugins',function(){})
   * 
   * @since    1.0.0
   */
  public static function deactivate($plugin) {
		if ( plugin_basename( HAVEN_LOGIN_PLUGIN_FILE ) != plugin_basename($plugin) ) {
			return;
		}
    
		flush_rewrite_rules();
  }

}
