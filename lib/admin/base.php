<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Vimeography_Base class
 */
class Vimeography_Base {
  public function __construct() { }

  /**
   * Holds any messages to be shown in the template.
   *
   * (default value: array())
   *
   * @var array
   * @access public
   */
  public $messages = array();

  /**
   * You don't know what this function does? Shame on you...
   *
   * @access public
   * @return html
   */
  public function vimeography() {
    if ( function_exists('do_shortcode') ) {
      return do_shortcode( "[vimeography id='" . $this->_gallery[0]->id . "']" );
    }
  }

  /**
   * Returns the base admin url for the plugin.
   *
   * @access public
   * @static
   * @return string
   */
  public static function admin_url() {
    return get_admin_url().'admin.php?page=vimeography-';
  }

  /**
   * Checks if the Vimeography Pro plugin is installed and activated
   * 
   * @return boolean
   */
  public static function has_pro() {
    return is_plugin_active('vimeography-pro/vimeography-pro.php');
  }

  /**
   * Gets the default settings created when Vimeography is installed.
   *
   * @access public
   * @return array
   */
  public function get_vimeography_defaults() {
    return get_option('vimeography_default_settings');
  }

  /**
   * Creates the theme list to show in the appearance tab.
   *
   * @access public
   * @return array
   */
  public function themes() {
    $themes = Vimeography::get_instance()->addons->themes;
    $activated_themes = get_option('vimeography_activation_keys');

    $items = array();
    foreach ($themes as $theme) {
      if (isset($this->_gallery))
       $theme['active'] = strtolower($theme['name']) == strtolower($this->_gallery[0]->theme_name) ? TRUE : FALSE;

      if (is_array($activated_themes)) {
        foreach ($activated_themes as $activation) {
          if (strtolower($activation->plugin_name) == strtolower($theme['slug']))
            $theme['activation_key'] = TRUE;
        }
      }

      $items[] = $theme;
    }

    return $items;
  }

}
