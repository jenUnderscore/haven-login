<?php 
/** *
 * @package   Haven Login Plugin
 * @subpackage haven-login/includes
 * @author     JenUnderscore_ <jhood@underscoresolutions.com>
 */
class Haven_Login_Faq {
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

  public function __construct( $loader, $haven_login, $version ) {

    $this->haven_login = $haven_login;
    $this->version = $version;
    $this->loader = $loader; 
    $this->set_icon('<svg id="haven_icon" data-name="haven_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 75.5 97.6"><defs><style>.st0{fill:#e6e7e8;}</style></defs><g><path class="st0" d="M35,29.5c-5,2-7.5,7.7-5.5,12.7s7.7,7.5,12.7,5.5c5-2,7.5-7.7,5.5-12.7c0,0,0,0,0,0C45.7,30,40,27.6,35,29.5z" /><path class="st0" d="M66.5,0H17.1l-0.7,1.7c-1.9-0.8-3.8-1.3-5.8-1.7H9C4,0,0,4,0,9v57.5c0,5,4,9,9,9h20.3 
		c8.1,16.7,9.3,22.1,9.3,22.1s1.1-5.4,9.3-22.1h18.6c5,0,9-4,9-9V9C75.5,4,71.5,0,66.5,0z M29.5,9.4c1.6,2,2.9,4.2,3.9,6.6l-1.7,0.7 
		c-0.9-2.2-2.2-4.3-3.6-6.2L29.5,9.4z M20.8,1.8C23,3.1,25,4.6,26.8,6.4l-1.3,1.3c-1.7-1.7-3.6-3.2-5.7-4.3L20.8,1.8z M45.7,68.9 
		c-6.2,12.6-7,16.6-7,16.6s-0.8-4-7-16.6s-15-17.8-15-30c0-12.2,9.8-22,22-22s22,9.8,22,22C60.6,51,51.8,56.3,45.7,68.9z"/></g></svg>');
  }

  private function set_icon($svg){
    $this->icon = 'data:image/svg+xml;base64,' . base64_encode($svg);
  }
  private function get_icon(){
    return $this->icon;
  }

  /*------------------------------------*\
    Haven FAQs Custom Post Type
  \*------------------------------------*/
/*      'public' => true,
      'exclude_from_search' => false, 
      //'show_in_rest' => true,
      'publicly_queryable' => true,
      'hierarchical' => false, // Allows your posts to behave like Hierarchy Pages
      'has_archive' => true,
      'supports' => array(
        'title',
        'thumbnail',
        'excerpt',
        'custom-fields'
      ), // Go to Dashboard Custom  post for supports
      'can_export' => true, // Allows export in Tools > Export
      'capability_type'     => 'post',
      'rewrite' => array( 'slug' => 'enews' ),
      'taxonomies' => array(
        'post_tag'
      ), */
  public function create_post_type(){
    register_post_type('haven_faqs', // Register Custom Post Type
      array(
      'labels' => array(
        'name' => __('Haven FAQs', ' itheme'), // Rename these to suit
        'singular_name' => __('Haven FAQ Item', ' itheme'),
        'add_new' => __('Add New', ' itheme'),
        'add_new_item' => __('Add New Haven FAQ', ' itheme'),
        'edit' => __('Edit', ' itheme'),
        'edit_item' => __('Edit Haven FAQ', ' itheme'),
        'new_item' => __('New Haven FAQ', ' itheme'),
        'view' => __('View Haven FAQ', ' itheme'),
        'view_item' => __('View Haven FAQ', ' itheme'),
        'search_items' => __('Search Haven FAQ', ' itheme'),
        'not_found' => __('No Haven FAQs found', ' itheme'),
        'not_found_in_trash' => __('No Haven FAQs found in Trash', ' itheme')
      ),
      'public' => true,
      'exclude_from_search' => true,
      'show_in_rest' => true,
      'publicly_queryable' => true,
      'hierarchical' => false, // Allows your posts to behave like Hierarchy Pages
      'has_archive' => false,
      'menu_icon' => 'dashicons-editor-help', //$this->get_icon(),
      'menu_position' => 3,
      'supports' => array(
        'title',
        'editor',
        'custom-fields'
      ), // Go to Dashboard Custom  post for supports
      'can_export' => true, // Allows export in Tools > Export
      'capability_type' => 'post'
    ));
  }

  // register Haven FAQ Category taxonomies to go with the post type
  public function register_category_taxonomy() {
    // set up labels
    $labels = array(
      'name'              => 'Dashboard FAQ Categories',
      'singular_name'     => 'Category',
      'search_items'      => 'Search ategories',
      'all_items'         => 'All Categories',
      'edit_item'         => 'Edit Category',
      'update_item'       => 'Update Category',
      'add_new_item'      => 'Add New Category',
      'new_item_name'     => 'New Category',
      'menu_name'         => 'Categories'
    );
    // register taxonomy
    register_taxonomy( 'haven_faq_categories', array('haven_faqs'), array(
      'hierarchical' => true,
      'labels' => $labels,
      'query_var' => true,
      'show_admin_column' => true,
      'show_in_rest'      => true,
      'show_in_quick_edit' => true
    ) );
  }
  // register Haven FAQ Category taxonomies to go with the post type
  public function register_tag_taxonomy() {
    // set up labels
    $labels = array(
      'name'              => 'Dashboard FAQ Tags',
      'singular_name'     => 'Tag',
      'search_items'      => 'Search Tags',
      'all_items'         => 'All Tags',
      'edit_item'         => 'Edit Tag',
      'update_item'       => 'Update Tag',
      'add_new_item'      => 'Add New Tag',
      'new_item_name'     => 'New Tag',
      'menu_name'         => 'Tags'
    );
    // register taxonomy
    register_taxonomy( 'haven_faq_tags', array('haven_faqs'), array(
      'hierarchical' => false,
      'labels' => $labels,
      'query_var' => true,
      'show_admin_column' => true,
      'show_in_rest'      => true,
      'show_in_quick_edit' => true
    ) );
  }

  public function settings() {
    add_submenu_page('edit.php?post_type=haven_faqs', 'Haven FAQ Settings',   'Settings', 'edit_posts', 'haven_faq_options', array($this,'settings_form'));
    
    add_action( 'admin_init', array($this,'register_settings'));
  }

  public function settings_form(){
    ?>
    <div class="wrap">
    <h1>Haven FAQ Settings</h1>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'haven_faqs-settings' ); ?>
        <?php do_settings_sections( 'haven_faqs-settings' ); ?>
        <table class="form-table">
            <!--<tr valign="top">
            <th scope="row">Haven FAQ Content</th>
            <td>
              <?php
                //$settings = array();
                //$editor_id = 'global_haven_faq_content';
                //wp_editor( get_option('global_haven_faq_content') , $editor_id, $settings );
              ?>
            </td>
            </tr>-->
        </table>      
        <?php submit_button(); ?>  
    </form>
    </div>
  <?php
  }

  public function register_settings() {
    //register our settings
    register_setting( 'haven_faqs-settings', 'global_haven_faq_content' );
    //register_setting( 'haven_faqs-settings', 'some_other_option' );
    //register_setting( 'haven_faqs-settings', 'option_etc' );
  }

  public function info_hooks() {
    add_meta_box('haven_faq_info', 'Haven FAQ Info', array($this,'haven_faq_info'), array('haven_faqs'), 'normal', 'high');
  }

  public function haven_faq_info() {
    global $post;
  ?>
    <input type="hidden" name="haven_faq_info_noncename" id="haven_faq_info_noncename" value="<?php echo wp_create_nonce('haven_faq-info') ?>" />
    <table class="form-table">
      <tr valign="top">
      <td style="width:100px">Note</td>
      <td><textarea name="haven_faq_note" id="haven_faq_note"><?php echo get_post_meta($post->ID,'_haven_faq_note',true); ?></textarea></td>
      </tr>
      <tr valign="top">
      <td style="width:100px">Field ID</td>
      <td><input type="textbox" name="haven_faq_field_id" id="haven_faq_field_id" style="width:200px;" value="<?php echo get_post_meta($post->ID,'_haven_faq_field_id',true); ?>" /> <em>(admin only)</em></td>
      </tr>
    </table>
  <?php
  }

  public function save_info($post_id) {
    if(array_key_exists('haven_faq_info_noncename',$_POST)){
      if (!wp_verify_nonce($_POST['haven_faq_info_noncename'], 'haven_faq-info')) return $post_id;
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

      update_post_meta($post_id, '_haven_faq_note', sanitize_text_field($_POST['haven_faq_note']));
      update_post_meta($post_id, '_haven_faq_field_id', sanitize_text_field($_POST['haven_faq_field_id']));
    }
  }
}