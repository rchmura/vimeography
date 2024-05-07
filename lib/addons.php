<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Addons {

  /**
   * Meta headers of the registered Vimeography themes
   *
   * @access public
   * @var array
   */
  public $themes = array();

  /**
   * Meta headers of all installed Vimeography addons
   *
   * @access public
   * @var array
   */
  public $installed_addons = array();

  /**
   * Meta headers of the current Vimeography theme marked as active.
   *
   * @access public
   * @var array
   */
  public $active_theme = NULL;

  /**
   * Add the plugin registration hook in the constructor.
   */
  public function __construct() {
    // First hook is kept for legacy purposes, do not remove.
    add_action( 'vimeography/load-theme', array( $this, 'vimeography_load_addon_plugin') );

    // The actual hook to use moving forward
    add_action( 'vimeography/load-addon-plugin', array( $this, 'vimeography_load_addon_plugin') );
  }

  /**
   * Captures the metadata from the calling Vimeography addon plugin
   * that is installed. This is used to send to the updater class and
   * to register all of the installed themes in the theme array.
   *
   * @param  string $plugin_path the PHP __FILE__ constant from the calling plugin
   * @return object             [description]
   */
  public function vimeography_load_addon_plugin($plugin_path) {
    $plugin = self::_get_plugin_data( $plugin_path );

    $plugin['basename']       = plugin_basename( $plugin_path );
    $plugin['slug']           = substr($plugin['basename'], 0, strpos($plugin['basename'], "/"));
    $plugin['thumbnail']      = plugins_url(strtolower($plugin['name']) .'.jpg', $plugin_path);
    $plugin['file_path']      = $plugin_path;
    $plugin['plugin_path']    = plugin_dir_path($plugin_path);

    // Hacky way of figuring this out, but will do for now.
    $plugin['type']           = file_exists( plugin_dir_path( $plugin_path ) . 'settings.php' ) ?
                                'theme' :
                                'extension';

    if ( $plugin['type'] === 'theme' ) {
      $plugin['partials_path']          = plugin_dir_path( $plugin_path ) . 'partials';
      $plugin['plugin_override_path']   = get_stylesheet_directory() . '/vimeography/' . trailingslashit( strtolower( $plugin['name'] ) );
      $plugin['partials_override_path'] = get_stylesheet_directory() . '/vimeography/' . trailingslashit( strtolower( $plugin['name'] ) ) . 'partials';
      $plugin['settings_file']          = plugin_dir_path( $plugin_path ) . 'settings.php';

      $default_theme = apply_filters('vimeography.gallery.new.theme', 'harvestone' );
      $plugin['is_default'] = strtolower( $plugin['name'] ) === strtolower( $default_theme );

      // Provide path to Javascript bundle if theme supports it.
      if ( version_compare( $plugin['version'], '2.0', '>=' ) ) {

        if ( defined('VIMEOGRAPHY_DEV') && VIMEOGRAPHY_DEV ) {
          $slug = strtolower($plugin['name']);
          $port = $slug === 'harvestone' ? "8153" : "8346";
          $bundle = $slug === 'harvestone' ? "scripts.js" : "vimeography-$slug/dist/$slug.js";

          // Use the gitpod env vars if they are defined
          if (defined('VIMEOGRAPHY_THEME_BUNDLE_JS_URL') && $port === "8346") {
            $plugin['app_path'] = VIMEOGRAPHY_THEME_BUNDLE_JS_URL . "/";
            $plugin['app_js'] = VIMEOGRAPHY_THEME_BUNDLE_JS_URL . '/' . $bundle;
            $plugin['app_css'] = VIMEOGRAPHY_THEME_BUNDLE_JS_URL . "/styles.css";
          } elseif (defined('VIMEOGRAPHY_HARVESTONE_JS_URL') && $port === "8153") {
            $plugin['app_path'] = VIMEOGRAPHY_HARVESTONE_JS_URL . "/";
            $plugin['app_js'] = VIMEOGRAPHY_HARVESTONE_JS_URL . '/' . $bundle;
            $plugin['app_css'] = VIMEOGRAPHY_HARVESTONE_JS_URL . "/styles.css";
          } else {
            $plugin['app_path'] = "http://localhost:$port/";
            $plugin['app_js'] = "http://localhost:$port/$bundle";
            $plugin['app_css'] = "http://localhost:8080/styles.css";
          }
        } else {
          // note: can use $plugin['version'] for cachebusting.

          $manifest = $plugin['plugin_path'] . 'dist/manifest.json';
          $manifest = file_get_contents( $manifest );
          $manifest = (array) json_decode( $manifest );

          $plugin['app_path'] = plugins_url( 'dist/', $plugin_path );
          $plugin['app_js'] = plugins_url( sprintf( 'dist/%s', $manifest['main.js'] ), $plugin_path );

          if ( isset( $manifest['main.css'] ) ) {
            $plugin['app_css'] = plugins_url( sprintf( 'dist/%s', $manifest['main.css'] ), $plugin_path );
          }
        }

      }

      $this->themes[] = $plugin;
    }

    // Load all addons into the public addons array
    $this->installed_addons[] = $plugin;

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
  private static function _get_plugin_data($plugin_file) {

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
   * Sets the active theme if it is found to be installed
   * and activated.
   *
   * @param string $theme_name
   */
  public function set_active_theme($theme_name) {

    foreach ( $this->themes as $index => $theme) {
      if ( strtolower( $theme['name'] ) === strtolower( $theme_name ) ) {
        $this->active_theme = $theme;
      }
    }

    if ( ! $this->active_theme ) {
      throw new Vimeography_Exception(
        esc_html__('The Vimeography theme you are trying to use is not installed or activated.', 'vimeography')
      );
    }

    return $this;
  }
}
