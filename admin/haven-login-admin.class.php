<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       http://havendestinations.ca
 * @since      1.0.0
 * 
 * @package    Haven Login
 * @subpackage haven-login/admin
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Admin {
  
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
   * The Name of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name  The name of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;


  /**
   * The plugin slug of Haven dependency
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $haven_dependency    Slug of plugin dependency (haven-wp-pluginn/haven.php)
   */
  private $haven_dependency;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $loader        Pass the loader to allow us to add/remove actions and filters from here
   * @param      string    $haven_login   The name of this plugin.
   * @param      string    $version       The version of this plugin.
   */
  public function __construct( $loader, $haven_login, $version ) {

    $this->haven_login = $haven_login;
    $this->version = $version;
    $this->loader = $loader;
    $this->haven_dependency = 'haven-wp-plugin/haven.php';
    $this->plugin_name = 'Haven Login';
    
    
    $this->define_hooks();
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
    //Enable admin notices, this is also where we check if the basic Haven Plugin is installed and activated
    $this->loader->add_action( 'admin_notices', $this, 'admin_notices' );
    $this->loader->add_filter( 'plugin_action_links_' . plugin_basename(HAVEN_LOGIN_PLUGIN_FILE) , $this, 'add_settings_page_link' ); //Add settings page to the plugins listing
  }

  public function add_settings_page_link($links){
    //haven settings page open at Login tab
    $links[] = '<a href="' .  self_admin_url('admin.php?page=haven_settings&tab=login') . '">Settings</a>';

    return $links;
  }

  private function is_haven_active(){
    return is_plugin_active($this->haven_dependency);
  }

  private function is_haven_installed(){
    $installed_plugins = get_plugins();
    
    return (array_key_exists( $this->haven_dependency,$installed_plugins ) || in_array( $this->haven_dependency, $installed_plugins, true ));
  }

  private function verify_dependency(){
    if(!$this->is_haven_active()){
      if(!$this->is_haven_installed()){
        list($plugin,$plugin_file) = explode('/',$this->haven_dependency);
        $base_url = self_admin_url( 'update.php?action=install-plugin&plugin='.$plugin);
        $link = wp_nonce_url( $base_url, 'install-plugin_'.$plugin);
        $link_title = 'Install ';
        $missing_status = 'installed and activated';
      }
      else{
        $base_url = self_admin_url( 'plugins.php?action=activate&plugin=' . $this->haven_dependency );
        $link = wp_nonce_url( $base_url, 'activate-plugin_' . $this->haven_dependency);
        $link_title = 'Activate ';
        $missing_status = 'activated';
      }
      
      $dependency_notice = sprintf('Haven %s (or higher) must be %s in order to use %s.',HAVEN_DEPENDENCY_VERSION, $missing_status, $this->plugin_name);
      $dependency_button = sprintf('<a class="button" href="%s">%s Haven now</a>',$link, $link_title);

      $notice = '
          <p>' . $dependency_notice . '</p>
          <p>' . $dependency_button . '</p>
          ';

      $this->print_notice($notice,'error');
    }
  }

  private function print_callback_message(){
    if ( ! empty( $_GET['haven-login-message'] ) ) {
      $type = ( false !== stripos( $_GET['haven-login-message'], 'error' ) ? 'error' : 'success' );
      $notice = esc_html( urldecode( $_GET['haven-login-message'] ) );
      
      $this->print_notice($notice,$type);
    }
  }

  public function admin_notices() {

    $this->verify_dependency();

    $this->print_callback_message();
  }

  /**
   * @since    1.0.0
   * @access   private
   * @var      string    $notice        The text displayed in the notice
   * @var      string    $type          Type of notice: info(default), error, warning, success
   * @var      boolean   $dismissable   Whether to include a dismiss close  
   */
  private function print_notice($notice,$type='info',$dismissable=false){
    if($notice){
      $cssClass = 'notice';
      if($type)         $cssClass .= ' notice-'.$type;
      if($dismissable)  $cssClass .= ' is-dismissible';

      echo '
        <div class="' . $cssClass . '">
        ' . $notice . '
        </div>';
    }
  }

}