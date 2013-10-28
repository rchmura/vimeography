<?php
/*
Plugin Name: Vimeography
Plugin URI: http://vimeography.com
Description: Vimeography is the easiest way to set up a custom Vimeo gallery on your site.
Version: 1.1.4
Author: Dave Kiss
Author URI: http://davekiss.com
License: MIT
*/

if (!function_exists('json_decode'))
  wp_die('Vimeography requires the JSON PHP extension.');

global $wpdb;

// Define constants
define( 'VIMEOGRAPHY_URL',  plugin_dir_url(__FILE__) );
define( 'VIMEOGRAPHY_PATH', plugin_dir_path(__FILE__) );
define( 'VIMEOGRAPHY_THEME_URL',   WP_CONTENT_URL . '/vimeography/themes/' );
define( 'VIMEOGRAPHY_THEME_PATH',  WP_CONTENT_DIR . '/vimeography/themes/' );
define( 'VIMEOGRAPHY_ASSETS_URL',  WP_CONTENT_URL . '/vimeography/assets/' );
define( 'VIMEOGRAPHY_ASSETS_PATH', WP_CONTENT_DIR . '/vimeography/assets/' );
define( 'VIMEOGRAPHY_CACHE_URL',   WP_CONTENT_URL . '/vimeography/cache/' );
define( 'VIMEOGRAPHY_CACHE_PATH',  WP_CONTENT_DIR . '/vimeography/cache/' );
define( 'VIMEOGRAPHY_BASENAME', plugin_basename( __FILE__ ) );
define( 'VIMEOGRAPHY_VERSION', '1.1.4');
define( 'VIMEOGRAPHY_GALLERY_TABLE', $wpdb->prefix . "vimeography_gallery");
define( 'VIMEOGRAPHY_GALLERY_META_TABLE', $wpdb->prefix . "vimeography_gallery_meta");
define( 'VIMEOGRAPHY_CURRENT_PAGE', basename($_SERVER['PHP_SELF']));
define( 'VIMEOGRAPHY_CLIENT_ID', 'fc0927c077cb47345eadf7c513d70f4aa564f30d');

require_once(VIMEOGRAPHY_PATH . 'lib/exception.php');

// Require Mustache.php
if (! class_exists('Mustache_Engine'))
{
  require_once(VIMEOGRAPHY_PATH . '/vendor/mustache.php-master/src/Mustache/Autoloader.php');
  Mustache_Autoloader::register();
}

class Vimeography
{
  /**
   * [$themes description]
   * @var array
   */
  public $themes = array();

  /**
   * [$active_theme description]
   * @var [type]
   */
  public $active_theme = NULL;

  /**
   * [$instance description]
   * @var [type]
   */
  private static $instance = NULL;

  /**
   * Creates or returns an instance of this class.
   *
   * @return  Vimeography A single instance of this class.
   */
  public static function get_instance()
  {
    if ( NULL == self::$instance )
      self::$instance = new self;

    return self::$instance;
  }

  /**
   * [__construct description]
   */
  private function __construct()
  {
    add_action( 'init',           array($this, 'vimeography_init') );
    add_action( 'admin_init',     array($this, 'vimeography_requires_wordpress_version') );
    add_action( 'admin_init',     array($this, 'vimeography_check_if_db_exists') );
    add_action( 'admin_init',     array($this, 'vimeography_load_updater'));
    add_action( 'admin_init',     array($this, 'vimeography_check_if_just_updated'));
    add_action( 'admin_init',     array($this, 'vimeography_activate_bugsauce'));
    add_action( 'init',           array($this, 'vimeography_move_folders') );
    add_action( 'plugins_loaded', array($this, 'vimeography_update_database') );
    add_action( 'admin_menu',     array($this, 'vimeography_add_menu') );
    add_action( 'do_robots',      array($this, 'vimeography_block_robots') );

    // Check the URL for any Vimeography-related parameters
    add_filter( 'query_vars',             array($this, 'vimeography_add_query_vars') );
    add_filter( 'upgrader_pre_install',   array($this, 'vimeography_pre_upgrade'), 10, 2 );
    add_action( 'generate_rewrite_rules', array($this, 'vimeography_add_rewrite_rules' ) );
    add_action( 'parse_request',          array($this, 'vimeography_parse_request') );

    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_update_tables') );
    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_flush_rewrite_rules') );
    register_deactivation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_deactivate_bugsauce') );

    add_filter( 'plugin_action_links', array($this, 'vimeography_filter_plugin_actions'), 10, 2 );
    add_shortcode( 'vimeography',      array($this, 'vimeography_shortcode') );

    // Add shortcode support for widgets
    add_filter( 'widget_text', 'do_shortcode' );

    // Load the themes that are hooking in to this action.
    add_action('vimeography/load-theme', array($this, 'vimeography_load_theme'));
  }

  /**
   * Check the wordpress version is compatible, and disable plugin if not.
   *
   * @access public
   * @return void
   */
  public function vimeography_requires_wordpress_version()
  {
    global $wp_version;
    $plugin_data = get_plugin_data( __FILE__, false );

    if ( version_compare($wp_version, "3.3", "<" ) )
    {
      if( is_plugin_active( VIMEOGRAPHY_BASENAME ) )
      {
        deactivate_plugins( VIMEOGRAPHY_BASENAME );
        wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
      }
    }
  }

  /**
   * [vimeography_check_for_updates description]
   * @return [type] [description]
   */
  public function vimeography_load_updater()
  {
    require_once(VIMEOGRAPHY_PATH . 'lib/update.php');
    $updater = new Vimeography_Update;
    $updater->vimeography_check_installed_theme_activations($this->themes);
  }

  /**
   * Perform any actions just before Vimeography is updated.
   *
   * @param  bool   $true       TRUE
   * @param  array  $hook_extra Slug of the plugin being updated
   * @return void
   */
  public function vimeography_pre_upgrade($true, $hook_extra)
  {
    // Vimeography is updating, deactivate all Vimeography plugins until we are back.
    if ($hook_extra['plugin'] === 'vimeography/vimeography.php')
    {
      $plugins = get_option('active_plugins');
      $vimeography_plugins = array();

      foreach($plugins as $plugin)
      {
        if (strpos($plugin, 'vimeography-') !== FALSE)
        {
          $vimeography_plugins[] = $plugin;
        }
      }

      deactivate_plugins($vimeography_plugins);
      update_option('vimeography_reactivate_plugins', $vimeography_plugins);
    }
  }

  /**
   * If Vimeography was just updated, make sure all the Vimeography plugins are activated.
   *
   * @return void
   */
  public function vimeography_check_if_just_updated()
  {
    $plugins = get_option('vimeography_reactivate_plugins');

    if ( $plugins )
    {
      activate_plugins($plugins);
      delete_option('vimeography_reactivate_plugins');
    }
  }

  /**
   * Runs on every page load.
   *
   * @access public
   * @return void
   */
  public function vimeography_init()
  {
    require_once(VIMEOGRAPHY_PATH . 'lib/init.php');
    $init = new Vimeography_Init;

    $init->vimeography_register_jquery();
    $init->vimeography_add_gallery_helper();
    $init->vimeography_written_block_robots();

    require_once(VIMEOGRAPHY_PATH . 'lib/ajax.php');
    new Vimeography_Ajax;
  }

  /**
   * [vimeography_load_theme description]
   * @param  [type] $theme_path [description]
   * @return [type]             [description]
   */
  public function vimeography_load_theme($theme_path)
  {
    $theme = self::_get_theme_data($theme_path);

    $theme['basename']      = plugin_basename($theme_path);
    $theme['slug']          = substr($theme['basename'], 0, strpos($theme['basename'], "/"));
    $theme['thumbnail']     = plugins_url(strtolower($theme['name']) .'.jpg', $theme_path);
    $theme['file_path']     = $theme_path;
    $theme['plugin_path']   = plugin_dir_path($theme_path);
    $theme['partials_path'] = plugin_dir_path($theme_path) . 'partials';
    $theme['settings_file'] = plugin_dir_path($theme_path) . 'settings.php';

    $this->themes[] = $theme;
  }

  /**
   * [set_active_theme description]
   * @param [type] $theme_name [description]
   */
  public function set_active_theme($theme_name)
  {
    foreach($this->themes as $index => $theme)
    {
      if (strtolower($theme['name']) === strtolower($theme_name))
      {
        $this->active_theme = $theme;
      }
    }
    return $this;
  }

  /**
   * Retrieves the meta data from the headers of a given plugin file.
   *
   * @access private
   * @static
   * @param mixed $plugin_file
   * @return void
   */
  private static function _get_theme_data($plugin_file)
  {

    $default_headers = array(
      'name'        => 'Theme Name',
      'theme-uri'   => 'Theme URI',
      'version'     => 'Version',
      'description' => 'Description',
      'author'      => 'Author',
      'author-uri'  => 'Author URI',
    );

    return get_file_data( $plugin_file, $default_headers );
  }

  /**
   * [vimeography_update_database description]
   * @return [type] [description]
   */
  public function vimeography_update_database()
  {
    require_once(VIMEOGRAPHY_PATH . 'lib/database.php');
    $db = new Vimeography_Database;

    $db->vimeography_update_db_to_0_6();
    $db->vimeography_update_db_to_0_7();
    $db->vimeography_update_db_to_0_8();
    $db->vimeography_update_db_to_1_0();
    $db->vimeography_update_db_to_1_0_7();
    $db->vimeography_update_db_to_1_1_4();
    $this->vimeography_update_db_version();
  }

  /**
   * [vimeography_check_if_db_exists description]
   * @return [type] [description]
   */
  public function vimeography_check_if_db_exists()
  {
    if (get_option('vimeography_db_version') == FALSE)
      $this->vimeography_update_db_version();
  }

   /**
   * Updates the Vimeography version stored in the database.
   *
   * @access public
   * @return void
   */
  public function vimeography_update_db_version()
  {
    update_option('vimeography_db_version', VIMEOGRAPHY_VERSION);
  }

  /**
   * Move the defined folders to the defined target path in wp-content/uploads.
   *
   * @access public
   * @return void
   */
  public function vimeography_move_folders()
  {
    $this->_move_folder(array('source' => VIMEOGRAPHY_PATH . 'vimeography-bugsauce/',     'destination' => str_replace('vimeography/', '', VIMEOGRAPHY_PATH) . 'vimeography-bugsauce/', 'clear_destination' => true, 'clear_working' => true));
    $this->_move_folder(array('source' => VIMEOGRAPHY_PATH . 'components/', 'destination' => VIMEOGRAPHY_ASSETS_PATH, 'clear_destination' => true, 'clear_working' => true));

    if (! file_exists(VIMEOGRAPHY_CACHE_PATH) )
    {
      // creates the dir and it's parent dirs
      if (! mkdir(VIMEOGRAPHY_CACHE_PATH, 0777, true) )
      {
        if( is_plugin_active( VIMEOGRAPHY_BASENAME ) )
        {
          deactivate_plugins( VIMEOGRAPHY_BASENAME );
          wp_die( "Vimeography could not create the cache directory. Please contact your host and request permissions to write to your Wordpress installation's wp-content directory.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
        }
      }
    }
  }

  /**
   * Add Settings link to "installed plugins" admin page.
   *
   * @access public
   * @param mixed $links
   * @param mixed $file
   * @return void
   */
  public function vimeography_filter_plugin_actions($links, $file)
  {
    if ( $file == VIMEOGRAPHY_BASENAME )
    {
      $settings_link = '<a href="admin.php?page=vimeography-edit-galleries">' . __('Settings') . '</a>';
      if (!in_array($settings_link, $links))
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
  }

  /**
   * Adds a new top level menu to the admin menu.
   *
   * @access public
   * @return void
   */
  public function vimeography_add_menu()
  {
    global $submenu;

    add_menu_page( 'Vimeography Page Title', 'Vimeography', 'manage_options', 'vimeography-edit-galleries', '', VIMEOGRAPHY_URL.'media/img/vimeography-icon.png' );
    add_submenu_page( 'vimeography-edit-galleries', 'Edit Galleries', 'Edit Galleries', 'manage_options', 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
    add_submenu_page( 'vimeography-edit-galleries', 'New Gallery', 'New Gallery', 'manage_options', 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
    add_submenu_page( 'vimeography-edit-galleries', 'Manage Activations', 'Manage Activations', 'manage_options', 'vimeography-manage-activations', array(&$this, 'vimeography_render_template' ));
    $submenu['vimeography-edit-galleries'][500] = array( 'Vimeography Themes', 'manage_options' , 'http://vimeography.com/themes' );
    add_submenu_page( 'vimeography-edit-galleries', 'Vimeography Pro', 'Vimeography Pro', 'manage_options', 'vimeography-pro', array(&$this, 'vimeography_render_template' ));
    add_submenu_page( 'vimeography-edit-galleries', 'Help', 'Help', 'manage_options', 'vimeography-help', array(&$this, 'vimeography_render_template' ));
  }

  /**
   * Renders the admin template for the current page.
   *
   * @access public
   * @return void
   */
  public function vimeography_render_template()
  {
    if ( !current_user_can( 'manage_options' ) )
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

    wp_register_style( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'media/css/bootstrap.min.css');
    wp_register_style( 'vimeography-admin', VIMEOGRAPHY_URL.'media/css/admin.css');
    wp_register_style( 'vimeography-type', 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700');

    wp_register_script( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'media/js/bootstrap.min.js');
    wp_register_script( 'vimeography-admin', VIMEOGRAPHY_URL.'media/js/admin.js', 'jquery');

    wp_enqueue_style( 'vimeography-bootstrap');
    wp_enqueue_style( 'vimeography-admin');
    wp_enqueue_style( 'vimeography-type');

    wp_enqueue_script( 'vimeography-bootstrap');
    wp_enqueue_script( 'vimeography-admin');

    $mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates'),));
    require_once(VIMEOGRAPHY_PATH . 'lib/admin/base.php');

    switch(current_filter())
    {
      case 'vimeography_page_vimeography-new-gallery':
        require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/new.php');

        if (is_plugin_active('vimeography-pro/vimeography-pro.php'))
        {
          do_action('vimeography-pro/load-new');
          $view = new Vimeography_Pro_Gallery_New;
        }
        else
        {
          $view = new Vimeography_Gallery_New;
        }

        $template = $mustache->loadTemplate('gallery/new');
        break;
      case 'toplevel_page_vimeography-edit-galleries':
        if (isset($_GET['id']))
        {
          require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php');

          if (is_plugin_active('vimeography-pro/vimeography-pro.php'))
          {
            do_action('vimeography-pro/load-editor');
            $view = new Vimeography_Pro_Gallery_Edit;
          }
          else
          {
            $view = new Vimeography_Gallery_Edit;
          }

          $mustache->setPartialsLoader( new Mustache_Loader_CascadingLoader( array(
              new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'),
            ) )
          );

          apply_filters('vimeography-pro/load-edit-partials', $mustache->getPartialsLoader());

          //$mustache->setPartialsLoader(new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'));

          $template = $mustache->loadTemplate('gallery/edit/layout');

        }
        else
        {
          require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/list.php');
          if (is_plugin_active('vimeography-pro/vimeography-pro.php'))
          {
            do_action('vimeography-pro/load-list');
            $view = new Vimeography_Pro_Gallery_List;
          }
          else
          {
            $view = new Vimeography_Gallery_List;
          }
          $template = $mustache->loadTemplate('gallery/list');
        }
        break;
      case 'vimeography_page_vimeography-manage-activations':
        require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/list.php');
        $view = new Vimeography_Theme_List;
        $template = $mustache->loadTemplate('theme/list');
        break;
      case 'vimeography_page_vimeography-pro':
        require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/pro.php');
        $view = new Vimeography_Pro_About;
        $template = $mustache->loadTemplate('vimeography/pro');
        break;
      case 'vimeography_page_vimeography-help':
        require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/help.php');
        $view = new Vimeography_Help;
        $template = $mustache->loadTemplate('vimeography/help');
        break;
      default:
        wp_die( __('The admin template for "'.current_filter().'" cannot be found.') );
      break;
    }

    echo $template->render($view);
  }

  /**
   * Create tables and define defaults when plugin is activated.
   *
   * @access public
   * @return void
   */
  public function vimeography_update_tables() {
    global $wpdb;

    delete_option('vimeography_default_settings');
    delete_option('vimeography_advanced_settings');

    add_option('vimeography_default_settings', array(
      'source_url'     => 'https://vimeo.com/channels/staffpicks/',
      'resource_uri'   => '/channels/staffpicks',
      'featured_video' => '',
      'video_limit'    => 25,
      'cache_timeout'  => 3600,
      'theme_name'     => 'bugsauce',
    ));

    $sql = 'CREATE TABLE '.VIMEOGRAPHY_GALLERY_TABLE.' (
    id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    title varchar(150) NOT NULL,
    date_created datetime NOT NULL,
    is_active tinyint(1) NOT NULL,
    PRIMARY KEY  (id)
    );
    CREATE TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' (
    id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    gallery_id mediumint(8) unsigned NOT NULL,
    source_url varchar(100) NOT NULL,
    resource_uri varchar(50) NOT NULL,
    featured_video varchar(100) DEFAULT NULL,
    video_limit mediumint(7) NOT NULL,
    gallery_width varchar(10) DEFAULT NULL,
    cache_timeout mediumint(7) NOT NULL,
    theme_name varchar(50) NOT NULL,
    PRIMARY KEY  (id)
    );
    ';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * Checks if the provided Vimeo URL is valid and if so, returns a
   * string to be used as the collection endpoint.
   *
   * @param  string $source_url Source collection of Vimeo videos.
   * @return string             Vimeo Resource
   */
  public static function validate_vimeo_source($source_url)
  {
    $scheme = parse_url($source_url);

    if (empty($scheme['scheme']))
      $source_url = 'https://' . $source_url;

    if ((($url = parse_url($source_url)) !== FALSE) && (preg_match('~vimeo(?:pro)?\.com$~', $url['host']) > 0))
    {
      $host = $url['host'];
      $url = array_values(array_filter(explode('/', $url['path']), 'strlen'));

      // If the array doesn't contain one of the following strings, it
      // must be either a user or a video
      if (in_array($url[0], array('album', 'channels', 'groups', 'categories')) !== TRUE)
      {
        if (is_numeric($url[0]))
        {
          array_unshift($url, 'videos');
        }
        else
        {
          array_unshift($url, 'users');

          if (isset($url[2]) AND $host != 'vimeo.com')
            array_splice($url, 2, 0, array('portfolios'));
        }
      }

      // Make sure the resource is plural
      $url[0] = rtrim($url[0], 's') . 's';
      $resource = '/' . implode('/', $url);

      return $resource;
    }
    else
    {
      throw new Vimeography_Exception( __('That site doesn\'t look like a valid link to a Vimeo collection.') );
    }
  }

  /**
   * Read the shortcode and return the output.
   * example:
   * [vimeography id="1" theme='apple']
   *
   * @access public
   * @param mixed $atts
   * @return void
   */
  public function vimeography_shortcode($atts, $content = NULL)
  {
    require_once(VIMEOGRAPHY_PATH . 'lib/shortcode.php');
    $shortcode = new Vimeography_Shortcode($atts, $content);
    return $shortcode->output();
  }

  /**
   * Adds the VIMEOGRAPHY_ASSETS_URL to the virtual robots.txt restricted list.
   *
   * @access public
   * @static
   * @return void
   */
  public static function vimeography_block_robots()
  {
    $blocked_asset_path = str_ireplace(site_url(), '', VIMEOGRAPHY_ASSETS_URL);
    echo 'Disallow: '.$blocked_asset_path."\n";
  }

  /**
   * [vimeography_add_query_vars description]
   * @param  [type] $vars [description]
   * @return [type]       [description]
   */
  public function vimeography_add_query_vars($vars)
  {
    $vars[] = 'vimeography_action';
    $vars[] = 'vimeography_gallery_id';
    return $vars;
  }

  /**
   * Adds custom rewrite rules.
   * @param  [type] $wp_rewrite [description]
   * @return [type]             [description]
   */
  function vimeography_add_rewrite_rules($wp_rewrite)
  {
    $wp_rewrite->rules = array(
        'vimeography/([0-9]{1,4})+/refresh\/?' => $wp_rewrite->index . '?vimeography_action=refresh&vimeography_gallery_id=' . $wp_rewrite->preg_index( 1 ),
        //'vimeography/notify\/?' => $wp_rewrite->index . '?vimeography_action=' . $wp_rewrite->preg_index( 1 ),
    ) + $wp_rewrite->rules;
  }

  /**
   * [vimeography_parse_request description]
   * @param  [type] $wp [description]
   * @return [type]     [description]
   */
  public function vimeography_parse_request($wp)
  {
    if (array_key_exists('vimeography_action', $wp->query_vars) AND $wp->query_vars['vimeography_action'] == 'refresh')
    {
      require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
      $cache = new Vimeography_Cache($wp->query_vars['vimeography_gallery_id']);
      if ($cache->exists())
        $cache->delete();
      die('Thanks, Vimeo. Cache busted.');
    }
  }

  /**
   * [vimeography_flush_rewrite_rules description]
   * @return [type] [description]
   */
  public function vimeography_flush_rewrite_rules()
  {
    return flush_rewrite_rules();
  }

  /**
   * [vimeography_activate_plugin description]
   * @param  [type] $basename [description]
   * @return [type]           [description]
   */
  public function vimeography_activate_bugsauce()
  {
    $bugsauce = str_replace('vimeography/', 'vimeography-bugsauce/', VIMEOGRAPHY_PATH);
    if ( is_plugin_inactive('vimeography-bugsauce/vimeography-bugsauce.php') AND file_exists($bugsauce) )
      activate_plugin('vimeography-bugsauce/vimeography-bugsauce.php');
  }

  /**
   * [vimeography_activate_plugin description]
   * @param  [type] $basename [description]
   * @return [type]           [description]
   */
  public function vimeography_deactivate_bugsauce()
  {
    if (is_plugin_active('vimeography-bugsauce/vimeography-bugsauce.php'))
      deactivate_plugins('vimeography-bugsauce/vimeography-bugsauce.php');
  }

  /**
   * Moves the given folder to the given destination.
   *
   * @access private
   * @param array $args (default: array())
   * @return void
   */
  private function _move_folder($args = array())
  {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Replaces simple `WP_Filesystem();` call to prevent any extraction issues
    // @link http://wpquestions.com/question/show/id/2685
    if (! function_exists('__return_direct'))
    {
      function __return_direct() { return 'direct'; }
    }

    add_filter( 'filesystem_method', '__return_direct' );
    WP_Filesystem();
    remove_filter( 'filesystem_method', '__return_direct' );

    global $wp_filesystem;

    // Always pass these
    $defaults = array(
      'source'            => '',
      'destination'       => '',
      'clear_destination' => false,
      'clear_working'     => false,
      'hook_extra'        => array(),
    );

    $args = wp_parse_args($args, $defaults);
    extract($args);

    @set_time_limit( 300 );

    if ( empty($source) || empty($destination) )
      return new WP_Error('bad_request', 'bad request.');

    //Retain the Original source and destinations
    $remote_source     = $source;
    $local_destination = $destination;

    if (! $wp_filesystem->dirlist($remote_source))
      return FALSE;

    $source_files       = array_keys( $wp_filesystem->dirlist($remote_source) );
    $remote_destination = $wp_filesystem->find_folder($local_destination);

    //Locate which directory to copy to the new folder, This is based on the actual folder holding the files.
    if ( 1 == count($source_files) && $wp_filesystem->is_dir( trailingslashit($source) . $source_files[0] . '/') ) //Only one folder? Then we want its contents.
      $source = trailingslashit($source) . trailingslashit($source_files[0]);
    elseif ( count($source_files) == 0 )
      return new WP_Error( 'incompatible_archive', 'incompatible archive string', __( 'The plugin contains no files.' ) ); //There are no files?
    else //Its only a single file, The upgrader will use the foldername of this file as the destination folder. foldername is based on zip filename.
      $source = trailingslashit($source);

    //Has the source location changed? If so, we need a new source_files list.
    if ( $source !== $remote_source )
      $source_files = array_keys( $wp_filesystem->dirlist($source) );

    if ( $clear_destination ) {
      //We're going to clear the destination if there's something there
      //$this->skin->feedback('remove_old');
      $removed = true;
      if ( $wp_filesystem->exists($remote_destination) )
        $removed = $wp_filesystem->delete($remote_destination, true);
      if ( is_wp_error($removed) )
        return $removed;
      else if ( ! $removed )
        return new WP_Error('remove_old_failed', 'couldnt remove old');
    } elseif ( $wp_filesystem->exists($remote_destination) ) {
      //If we're not clearing the destination folder and something exists there already, Bail.
      //But first check to see if there are actually any files in the folder.
      $_files = $wp_filesystem->dirlist($remote_destination);
      if ( ! empty($_files) ) {
        $wp_filesystem->delete($remote_source, true); //Clear out the source files.
        return new WP_Error('folder_exists', 'folder exists string', $remote_destination );
      }
    }

    //Create destination if needed
    if ( !$wp_filesystem->exists($remote_destination) )
      if ( !$wp_filesystem->mkdir($remote_destination, FS_CHMOD_DIR) )
        return new WP_Error('mkdir_failed', 'mkdir failer string', $remote_destination);

    // Copy new version of item into place.
    $result = copy_dir($source, $remote_destination);

    if ( is_wp_error($result) ) {
      if ( $clear_working )
        $wp_filesystem->delete($remote_source, true);
      return $result;
    }

    //Clear the Working folder?
    if ( $clear_working )
      $wp_filesystem->delete($remote_source, true);

    $destination_name = basename( str_replace($local_destination, '', $destination) );
    if ( '.' == $destination_name )
      $destination_name = '';

    $result = compact('local_source', 'source', 'source_name', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination', 'delete_source_dir');

    //Bombard the calling function will all the info which we've just used.
    return $result;

  }

}

Vimeography::get_instance();
