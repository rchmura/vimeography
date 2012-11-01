<?php

class Vimeography_Theme_Settings_Colorpicker extends Mustache 
{
	public $settings = array();
    
	public function __construct()
	{
		// Without the @, this generates warnings?
		// Notice: Undefined offset: 0 in /Users/davekiss/Sites/vimeography.com/wp-includes/plugin.php on line 762/780
		@add_action('wp_enqueue_scripts', $this->_load_scripts());			
	}
	
	public function _load_scripts()
	{		
    wp_register_script('vimeography_colorpicker', VIMEOGRAPHY_ASSETS_URL.'js/plugins/colorpicker.js', array('jquery'));
    wp_enqueue_script( 'vimeography_colorpicker' );
    
    wp_register_style('vimeography_colorpicker', VIMEOGRAPHY_ASSETS_URL.'css/colorpicker.css');
    wp_enqueue_style( 'vimeography_colorpicker' );
	}
			           
}