<?php
/*
Plugin Name: Vimeography Theme: Harvestone
Plugin URI: https://vimeography.com/themes
Theme Name: Harvestone
Theme URI: https://vimeography.com/themes/harvestone
Version: 2.0
Description: Harvestone is the base gallery theme that comes prepackaged with Vimeography.
Author: Dave Kiss
Author URI: https://vimeography.com
Copyright: Dave Kiss
*/

if ( ! class_exists('Vimeography_Themes_Harvestone') ) {

  class Vimeography_Themes_Harvestone {

    /**
     * The current version of this theme
     *
     * @var string
     */
    public $version = '2.0';


    /**
     * Include this theme in the Vimeography theme loader.
     */
    public function __construct() {
      add_action('plugins_loaded', array( $this, 'load_theme' ) );
    }


    /**
     * Has to be public so the wp actions can reach it.
     * @return [type] [description]
     */
    public function load_theme() {
      do_action('vimeography/load-addon-plugin', __FILE__);
    }

  }

  new Vimeography_Themes_Harvestone;
}