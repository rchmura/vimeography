<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Shortcode extends Vimeography {
  /**
   * The shortcode tag attributes applied by the user on a page or post.
   *
   * @var array
   */
  private $_atts;

  /**
   * The content located inside of the shortcode tag applied by the
   * user on a page or post.
   *
   * @var string
   */
  private $_content = NULL;

  /**
   * The id number associated with a gallery entry in the database.
   *
   * @var int or tokenized string
   */
  protected $_gallery_id;

  /**
   * The settings being used to render the current gallery.
   * This includes the theme name, resource URI, featured video, cache settings,
   * and gallery width parameter.
   *
   * @var array
   */
  private $_gallery_settings;

  /**
   * Hook into the Vimeography Shortcode and
   * add shortcode support for widgets.
   */
  public function __construct() {
    add_filter( 'widget_text', 'do_shortcode' );
    add_shortcode( 'vimeography', array($this, 'vimeography_shortcode') );
  }

  /**
   * Loads the gallery settings, generates any custom CSS, and creates the gallery token.
   *
   * @param array  $atts    The shortcode tag attributes applied by the user on a page or post.
   * @param string $content The content located inside of the shortcode tag.
   */
  public function vimeography_shortcode($atts, $content = NULL) {
    $this->_atts       = $atts;
    $this->_content    = $content;

    try {
      $this->_gallery_settings = self::_apply_shortcode_gallery_settings( $this->_atts );
      $this->_gallery_id       = isset( $atts['id'] ) ? intval( $atts['id'] ) : self::_get_inline_gallery_id( $this->_gallery_settings );

      $this->_vimeography_enqueue_custom_stylesheets();
      return $this->output();
    }
    catch (Vimeography_Exception $e) {
      return __("Vimeography Error: ", 'vimeography') . $e->getMessage();
    }
  }

  /**
   * Determines which gallery settings to use based on the provided
   * shortcode settings, the existing gallery db settings, and the
   * fallback gallery settings.
   *
   * @return array  The gallery settings to be used to render the current gallery.
   */
  private static function _apply_shortcode_gallery_settings($atts) {
    if (! empty( $atts['id'] ) ) {
      $db_gallery_settings = self::_get_db_gallery_settings( intval( $atts['id'] ) );
    }

    // Get admin panel options
    $default_settings = get_option('vimeography_default_settings');

    $fallback_gallery_settings             = array();
    $fallback_gallery_settings['theme']    = isset($db_gallery_settings->theme_name)     ? $db_gallery_settings->theme_name     : $default_settings['theme_name'];
    $fallback_gallery_settings['featured'] = isset($db_gallery_settings->featured_video) ? $db_gallery_settings->featured_video : $default_settings['featured_video'];
    $fallback_gallery_settings['endpoint'] = isset($db_gallery_settings->resource_uri)   ? $db_gallery_settings->resource_uri   : $default_settings['resource_uri'];
    $fallback_gallery_settings['limit']    = isset($db_gallery_settings->video_limit)    ? $db_gallery_settings->video_limit    : $default_settings['video_limit'];
    $fallback_gallery_settings['cache']    = isset($db_gallery_settings->cache_timeout)  ? $db_gallery_settings->cache_timeout  : $default_settings['cache_timeout'];
    $fallback_gallery_settings['width']    = isset($db_gallery_settings->gallery_width)  ? $db_gallery_settings->gallery_width  : '';

    // Get shortcode attributes
    $shortcode_gallery_settings = shortcode_atts( array(
      'theme'    => $fallback_gallery_settings['theme'],
      'featured' => $fallback_gallery_settings['featured'],
      'source'   => $fallback_gallery_settings['endpoint'],
      'limit'    => $fallback_gallery_settings['limit'],
      'cache'    => $fallback_gallery_settings['cache'],
      'width'    => $fallback_gallery_settings['width'],
    ), $atts, 'vimeography' );

    // Remove this line once 3.6 is the minimum supported version.
    $shortcode_gallery_settings = apply_filters('vimeography-pro/do-shortcode', $shortcode_gallery_settings, '', $atts);

    $shortcode_gallery_settings['width'] = self::_validate_gallery_width( $shortcode_gallery_settings['width'] );

    if ( $shortcode_gallery_settings['source'] != $fallback_gallery_settings['endpoint'] ) {
      $shortcode_gallery_settings['source'] = Vimeography::validate_vimeo_source( $shortcode_gallery_settings['source'] );
    }

    $shortcode_gallery_settings['source'] = $shortcode_gallery_settings['source'] . '/videos';

    return $shortcode_gallery_settings;
  }

  /**
   * Retrieves the gallery data for the provided gallery ID.
   *
   * @return object  The settings associated with the gallery in the database.
   */
  private static function _get_db_gallery_settings($id) {
    global $wpdb;

    $db_gallery_settings = $wpdb->get_results('
      SELECT *
      FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta
      JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery
      ON meta.gallery_id = gallery.id
      WHERE meta.gallery_id = '.$id.'
      LIMIT 1;
    ');

    if ( empty($db_gallery_settings) ) {
      throw new Vimeography_Exception( sprintf( __('a Vimeography gallery with an ID of "%1$s" was not found.', 'vimeography'), intval($id) ) );
    }

    return $db_gallery_settings[0];
  }

  /**
   * Verifies that the provided width setting is a valid CSS parameter
   *
   * @param  string $width
   * @return string        A percentage, pixel-based, or empty width value.
   */
  private static function _validate_gallery_width($width) {
    if ( ! empty($width) ) {
      preg_match('/(\d*)(px|%?)/', $width, $matches);
      // If a number value is set...
      if (!empty($matches[1]))
      {
        // If a '%' or 'px' is set...
        if (!empty($matches[2]))
        {
          // Accept the valid matching string
          $width = $matches[0];
        }
        else
        {
          // Append a 'px' value to the matching number
          $width = $matches[1] . 'px';
        }
      } else {
        // Not a valid width
        $width = '';
      }
    }
    return $width;
  }

  /**
   * Create a gallery_id token for any inline gallery that doesn't have an id.
   *
   * @return string  A unique token representing the current gallery
   */
  private static function _get_inline_gallery_id($shortcode) {
    return substr( md5( serialize($shortcode) ), 0, -24 );
  }

  /**
   * Load any custom CSS files that have been generated by the
   * Vimegraphy theme customization tools.
   *
   * @return void
   */
  private function _vimeography_enqueue_custom_stylesheets() {
    $name = 'vimeography-gallery-' . $this->_gallery_id . '-custom';
    $filename = $name . '.css';
    $filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $filename;
    $file_url = VIMEOGRAPHY_CUSTOMIZATIONS_URL  . $filename;

    if ( file_exists($filepath) ) {
      // Make sure the current theme's stylesheet handle is set as a dependency
      $dependency = strtolower( $this->_gallery_settings['theme'] );
      wp_register_style($name, $file_url, array($dependency), strval( filemtime($filepath) ) );
      wp_enqueue_style($name);
    }
  }

  /**
   * Loads the Vimeography engine and renderer and returns the rendered HTML for output.
   *
   * @return html | string
   */
  public function output() {
    try {
      require_once VIMEOGRAPHY_PATH . 'lib/core.php';
      require_once VIMEOGRAPHY_PATH . 'lib/renderer.php';

      if ( class_exists( 'Vimeography_Pro' ) ) {
        do_action('vimeography/load_pro');
        $vimeography = new Vimeography_Core_Pro( $this->_gallery_settings );
        $renderer    = new Vimeography_Pro_Renderer( $this->_gallery_settings, $this->_gallery_id );
      } else {
        require_once VIMEOGRAPHY_PATH . 'lib/core/basic.php';

        $vimeography = new Vimeography_Core_Basic( $this->_gallery_settings );
        $renderer    = new Vimeography_Renderer( $this->_gallery_settings, $this->_gallery_id );
      }

      $result = $vimeography->get_videos( $this->_gallery_settings['cache'], $this->_gallery_id );

      if ( empty( $result->video_set ) ) {
        throw new Vimeography_Exception( __('the Vimeo source for this gallery does not have any videos.', 'vimeography') );
      }

      // Render that ish.
      $renderer->load_theme();

      $renderer = apply_filters('vimeography/deprecated/reload-pro-renderer', $renderer, $this->_gallery_settings, $this->_gallery_id );
      return $renderer->render( $result );
    }
    catch (Vimeography_Exception $e) {
      return __("Vimeography error: ", 'vimeography') . $e->getMessage();
    }
  }
}
