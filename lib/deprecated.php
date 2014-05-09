<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists ( 'class_alias' ) ) {
  function class_alias($original, $alias) {
    $newclass = create_function('','class '.$alias.' extends '.$original.' {}');
    $newclass();
  }
}

/**
 * Handle deprecated functions and backwards compatibility
 */
class Vimeography_Deprecated {

  /**
   * [$_vimeography_pro_version description]
   * @var [type]
   */
  protected $_vimeography_pro_version;

  public function __construct() {
    $this->_vimeography_pro_version = get_site_option('vimeography_pro_db_version');

    add_action('init', array($this, 'add_vimeo_library_class_alias') );
    add_action('vimeography/deprecated/reload-pro-renderer', array($this, 'reload_pro_renderer_variables'), 10, 3 );
    add_action('vimeography/deprecated/reload-pro-gallery-settings', array($this, 'reload_pro_gallery_settings'), 1, 2);
  }

  /**
   * Adds a class alias for the un-namespaced Vimeo library class name
   */
  public function add_vimeo_library_class_alias() {
    if ( $this->_vimeography_pro_version && version_compare($this->_vimeography_pro_version, '0.7.1', '<' ) ) {
      @class_alias('Vimeography_Vimeo', 'Vimeo');
    }
  }

  /**
   * Reload the Vimeography Pro constructor to ensure
   * that all of the necessary view variables exist.
   *
   * Called from lib/shortcode.php
   *
   * @since  1.2
   * @supports Vimeography Pro < 0.7
   * @return object $renderer
   */
  public function reload_pro_renderer_variables($renderer, $gallery_settings, $gallery_id) {

    if ( $this->_vimeography_pro_version && version_compare($this->_vimeography_pro_version, '0.7', '<' ) ) {
      $renderer->__construct($gallery_settings, $gallery_id);
    }

    return $renderer;
  }

  /**
   * Reload the Pro gallery settings on the edit screen just before
   * they are sent to the gallery editor.
   *
   * @since  1.2
   * @supports Vimeography Pro < 0.7
   * @return array $gallery
   */
  public function reload_pro_gallery_settings($klass, $gallery) {

    if ( $this->_vimeography_pro_version && version_compare($this->_vimeography_pro_version, '0.7', '<' ) ) {
      $klass->__construct();
    }

    return $gallery;
  }

}
