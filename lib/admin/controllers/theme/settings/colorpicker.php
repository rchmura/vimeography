<?php

class Vimeography_Theme_Settings_Colorpicker
{
	public $settings = array();

  public function __construct($setting) {
    // Without the @, this generates warnings?
    // Notice: Undefined offset: 0 in /Users/davekiss/Sites/vimeography.com/wp-includes/plugin.php on line 762/780
    @add_action('wp_enqueue_scripts', $this->_load_scripts());

    foreach ($setting as $member => $value)
      $this->{$member} = $value;
  }

	public function _load_scripts() {
    wp_register_script('kendo-web', VIMEOGRAPHY_ASSETS_URL.'js/plugins/kendo.web.min.js', array('jquery'));
    wp_enqueue_script( 'kendo-web' );

    wp_register_style('kendo-common', VIMEOGRAPHY_ASSETS_URL.'css/kendo.common.min.css');
    wp_enqueue_style( 'kendo-common' );

    wp_register_style('kendo-bootstrap', VIMEOGRAPHY_ASSETS_URL.'css/kendo.bootstrap.min.css');
    wp_enqueue_style( 'kendo-bootstrap' );
	}

}
