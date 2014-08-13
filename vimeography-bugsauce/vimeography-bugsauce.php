<?php
/*
Plugin Name: Vimeography Theme: Bugsauce
Plugin URI: http://www.vimeography.com/themes
Theme Name: Bugsauce
Theme URI: vimeography.com/themes/bugsauce
Version: 1.1
Description: is the base theme that comes prepackaged with Vimeography.
Author: Dave Kiss
Author URI: http://www.vimeography.com/
Copyright: Dave Kiss
*/

if ( ! class_exists('Vimeography_Themes_Bugsauce') ) {
  class Vimeography_Themes_Bugsauce {
    /**
     * The current version of this theme
     *
     * @var string
     */
    public $version = '1.1';

    /**
     * Include this theme in the Vimeography theme loader.
     */
    public function __construct() {
      add_action('plugins_loaded', array( $this, 'load_theme' ) );
    }

    /**
     * Set any values sent to the theme.
     * @param [type] $name  [description]
     * @param [type] $value [description]
     */
    public function __set($name, $value) {
      $this->$name = $value;
    }

    /**
     * Has to be public so the wp actions can reach it.
     * @return [type] [description]
     */
    public function load_theme() {
      do_action('vimeography/load-theme', __FILE__);
    }

    public static function load_scripts() {
      wp_dequeue_script('fitvids');
      wp_dequeue_script('flexslider');

      wp_deregister_script('fitvids');
      wp_deregister_script('flexslider');

      // Register our common scripts
      wp_register_script('froogaloop', VIMEOGRAPHY_ASSETS_URL.'js/plugins/froogaloop2.min.js');
      wp_register_script('flexslider', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.flexslider.js', array('jquery'));
      wp_register_script('fitvids', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.fitvids.js', array('jquery'));
      wp_register_script('spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/spin.min.js', array('jquery'));
      wp_register_script('jquery-spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.spin.js', array('jquery', 'spin'));
      wp_register_script('vimeography-utilities', VIMEOGRAPHY_ASSETS_URL.'js/utilities.js', array('jquery'));
      wp_register_script('vimeography-pagination', VIMEOGRAPHY_ASSETS_URL.'js/pagination.js', array('jquery'));

      // Register our custom scripts
      wp_register_style('bugsauce', plugins_url('assets/css/bugsauce.css', __FILE__));

      wp_enqueue_script('froogaloop');
      wp_enqueue_script('flexslider');
      wp_enqueue_script('fitvids');
      wp_enqueue_script('spin');
      wp_enqueue_script('jquery-spin');
      wp_enqueue_script('vimeography-utilities');
      wp_enqueue_script('vimeography-pagination');

      wp_enqueue_style('bugsauce');
    }

    public function featured() {
      // optional helpers
      require_once(VIMEOGRAPHY_PATH .'lib/helpers.php');
      $helpers = new Vimeography_Helpers;

      $this->featured->oembed = $helpers->get_featured_embed($this->featured->link);

      return $this->featured;
    }

    public function videos() {
      // optional helpers
      require_once VIMEOGRAPHY_PATH .'lib/helpers.php';
      $helpers = new Vimeography_Helpers;

      // add featured video to the beginning of the array
      if ( is_array( $this->featured ) ) {
        array_unshift( $this->data, $this->featured[0] );
      }

      return $helpers->apply_common_formatting($this->data);
    }

  }
  new Vimeography_Themes_Bugsauce;
}
