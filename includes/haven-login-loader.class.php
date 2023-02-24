<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://havendestinations.ca
 * @since      1.0.0
 *
 * @package    Haven Login
 * @subpackage haven-login/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Haven Login
 * @subpackage haven-login/includes
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {

		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );

	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {

		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );

	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

  }
  
    
  /*function haven_wpautop($content) {
    global $post;
    if ($post->is_virtual)
      return $content;
    else
      return wpautop($content);
  }*/

  function haven_footer(){
    //echo ' <script defer src="'.HAVEN_LOGIN_PLUGIN_URL.'/vendor/fontawesome/js/fa-haven.min.js"></script>';
  }

  function haven_admin_head(){
    /*echo '
      <script>
        FontAwesomeConfig = { searchPseudoElements: true };
      </script>
    ';
    echo ' <script defer src="'.HAVEN_LOGIN_PLUGIN_URL.'/vendor/fontawesome/js/fa-haven.min.js"></script>';
*/
  }

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
    }
    
    add_action('wp_footer', array($this,'haven_footer') );
    add_action('admin_head', array($this,'haven_admin_head') );
	}  

	/**
	 * Static loader method
	 * @param string $name
	 */
	public static function autoload( $name ) {

    //transform the called class, replace underscores with hyphens
    $filename = strtolower(str_replace("_","-",$name));   
    
    //each array key is a file prefix that matches the class prefix after the $filename transformation above
    $require_folders = array(
      'haven-login-admin'   => array( 
          HAVEN_LOGIN_PLUGIN_DIR.'/admin/' 
      )
      , 'haven-login-public'  => array( 
          HAVEN_LOGIN_PLUGIN_DIR.'/public/' 
      )
      , 'haven-login-'        => array( 
          HAVEN_LOGIN_PLUGIN_DIR.'/includes/' 
      )
    );

    foreach ( $require_folders as $prefix => $paths ){      
      foreach($paths as $path){
        if ( $prefix == substr( basename( $filename ), 0, strlen ( $prefix ) ) && file_exists( $path . $filename . '.class.php') ){

          //var_dump('requiring: '.trim($path,HAVEN_LOGIN_PLUGIN_DIR) . $filename . '.class.php');

          require_once $path . $filename . '.class.php';     
          break;
        }
      }
    }

    require_once HAVEN_LOGIN_PLUGIN_DIR.'/vendor/autoload.php';
  }
  
}
