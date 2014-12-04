<?php
/*
Plugin Name: Vimeography
Plugin URI: http://vimeography.com
Description: Vimeography is the easiest way to set up a custom Vimeo gallery on your site.
Version: 1.2.8
Author: Dave Kiss
Author URI: http://davekiss.com
License: GPL3
Text Domain: vimeography
*/

if ( ! function_exists('json_decode') )
  wp_die( __('Vimeography requires the JSON PHP extension.', 'vimeography') );

if ( ! class_exists( 'Vimeography' ) ) {

  class Vimeography {
    /**
     * The Vimeography instance
     *
     * @var object
     */
    private static $instance = NULL;

    /**
     * Vimeography_Addons object
     *
     * @var object
     * @since 1.2
     */
    public $addons;

    /**
     * Vimeography_Update object
     *
     * @var object
     * @since 1.2
     */
    public $updater;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Vimeography A single instance of this class.
     */
    public static function get_instance() {
      if ( ! isset( self::$instance ) AND ! ( self::$instance instanceof Vimeography ) ) {
        self::$instance = new self;
        self::$instance->_define_constants();
        self::$instance->_include_files();
        Mustache_Autoloader::register();

        if ( is_admin() ) {
          new Vimeography_Admin_Actions;
          new Vimeography_Admin_Menu;
          new Vimeography_Admin_Plugins;
          self::$instance->updater = new Vimeography_Update;
        }

        // Can save these in public vars if need to access
        new Vimeography_Database;
        new Vimeography_Upgrade;
        new Vimeography_Deprecated;
        new Vimeography_Init;
        new Vimeography_Ajax;
        self::$instance->addons = new Vimeography_Addons;
        new Vimeography_Robots;
        new Vimeography_Shortcode;
      }

      return self::$instance;
    }

    /**
     * Empty constructor… boring.
     */
    public function __construct() { }

    /**
     * Define all of the constants used throughout the plugin.
     *
     * @return void
     */
    private function _define_constants() {
      global $wpdb;
      define( 'VIMEOGRAPHY_URL',  plugin_dir_url(__FILE__) );
      define( 'VIMEOGRAPHY_PATH', plugin_dir_path(__FILE__) );
      define( 'VIMEOGRAPHY_ASSETS_URL',  VIMEOGRAPHY_URL . 'lib/shared/assets/' );
      define( 'VIMEOGRAPHY_ASSETS_PATH', VIMEOGRAPHY_PATH. 'lib/shared/assets/' );
      define( 'VIMEOGRAPHY_CACHE_PATH',  WP_CONTENT_DIR . '/vimeography/cache/' );
      define( 'VIMEOGRAPHY_CUSTOMIZATIONS_PATH',  WP_CONTENT_DIR . '/vimeography/assets/css/' );
      define( 'VIMEOGRAPHY_CUSTOMIZATIONS_URL',   content_url() . '/vimeography/assets/css/' );
      define( 'VIMEOGRAPHY_BASENAME', plugin_basename( __FILE__ ) );
      define( 'VIMEOGRAPHY_VERSION', '1.2.8');
      define( 'VIMEOGRAPHY_GALLERY_TABLE', $wpdb->prefix . "vimeography_gallery");
      define( 'VIMEOGRAPHY_GALLERY_META_TABLE', $wpdb->prefix . "vimeography_gallery_meta");
      define( 'VIMEOGRAPHY_CURRENT_PAGE', basename($_SERVER['PHP_SELF']));
      define( 'VIMEOGRAPHY_CLIENT_ID', 'fc0927c077cb47345eadf7c513d70f4aa564f30d');
    }

    /**
     * Include the files required by Vimeography.
     * @return [type]
     */
    private function _include_files() {
      require_once VIMEOGRAPHY_PATH . 'lib/exception.php';

      // Require Mustache.php
      if ( ! class_exists('Mustache_Engine') ) {
        require_once VIMEOGRAPHY_PATH . '/vendor/mustache/mustache/src/Mustache/Autoloader.php';
      }

      if ( ! class_exists('Vimeography_Vimeo') ) {
        require_once VIMEOGRAPHY_PATH . 'vendor/davekiss/vimeo-php/vimeo.php';
      }

      require_once VIMEOGRAPHY_PATH . 'lib/database.php';
      require_once VIMEOGRAPHY_PATH . 'lib/upgrade.php';
      require_once VIMEOGRAPHY_PATH . 'lib/deprecated.php';
      require_once VIMEOGRAPHY_PATH . 'lib/addons.php';
      require_once VIMEOGRAPHY_PATH . 'lib/ajax.php';
      require_once VIMEOGRAPHY_PATH . 'lib/rewrite.php';
      require_once VIMEOGRAPHY_PATH . 'lib/filesystem.php';
      require_once VIMEOGRAPHY_PATH . 'lib/init.php';
      require_once VIMEOGRAPHY_PATH . 'lib/robots.php';
      require_once VIMEOGRAPHY_PATH . 'lib/shortcode.php';
      require_once VIMEOGRAPHY_PATH . 'vimeography-bugsauce/vimeography-bugsauce.php';

      if ( is_admin() ) {
        require_once VIMEOGRAPHY_PATH . 'lib/admin/actions.php';
        require_once VIMEOGRAPHY_PATH . 'lib/admin/menu.php';
        require_once VIMEOGRAPHY_PATH . 'lib/admin/plugins.php';
        require_once VIMEOGRAPHY_PATH . 'lib/update.php';
      }
    }

    /**
     * Checks if the provided Vimeo URL is valid and if so, returns a
     * string to be used as the collection endpoint.
     *
     * @param  string $source_url Source collection of Vimeo videos.
     * @return string             Vimeo Resource
     */
    public static function validate_vimeo_source($source_url) {
      // Add scheme if it wasn't provided in source url
      $scheme = parse_url( $source_url );

      if ( empty( $scheme['scheme'] ) ) {
        $source_url = 'https://' . $source_url;
      }

      // Only continue if the parse_url function didn't fail
      // and the host is one of vimeo.com or vimeopro.com
      if ( ( ($url = parse_url($source_url) ) !== FALSE ) && (preg_match('~vimeo(?:pro)?\.com$~', $url['host']) > 0)) {
        $host = $url['host'];

        // Create an array with the resource parts
        $url = array_values(array_filter(explode('/', $url['path']), 'strlen'));

        // If the array doesn't contain one of the following strings, it
        // must be either a user or a video
        if (in_array($url[0], array('album', 'channels', 'groups', 'categories')) !== TRUE) {
          if (is_numeric($url[0])) {
            array_unshift($url, 'videos');
          } else {
            array_unshift($url, 'users');
            if ( isset($url[2]) ) {
              if ($host != 'vimeo.com') {
                // Convert /users/username/portfolio_name to /users/username/portfolios/portfolio_name
                array_splice($url, 2, 0, array('portfolios'));
              } elseif ($url[2] === 'videos') {
                // Remove 'videos' from '/users/username/videos'
                unset($url[2]);
              }
            }
          }
        }

        // Make sure the resource is plural
        $url[0] = rtrim($url[0], 's') . 's';
        $resource = '/' . implode('/', $url);

        return $resource;
      } else {
        throw new Vimeography_Exception(
          __('That site doesn\'t look like a valid link to a Vimeo collection.', 'vimeography')
        );
      }
    }
  }
}

Vimeography::get_instance();