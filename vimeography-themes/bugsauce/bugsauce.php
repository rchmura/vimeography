<?php
/*
Theme Name: Bugsauce
Theme URI: vimeography.com/themes/bugsauce
Version: .1
Description: is the base theme that comes prepackaged with Vimeography.
Author: Dave Kiss
Author URI: vimeography.com
*/

class Vimeography_Themes_Bugsauce extends Mustache
{
    public $data;
    public $featured;
    public $gallery_id;
    
	public function __construct()
	{
		// Without the @, this generates warnings?
		// Notice: Undefined offset: 0 in /Users/davekiss/Sites/vimeography.com/wp-includes/plugin.php on line 762/780
		@add_action('wp_enqueue_scripts', $this->_load_scripts());
	}
	
	public function _load_scripts()
	{
		// First things first. jQuery.
		wp_deregister_script('jquery');
		wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"), false, '1.7.1');
		wp_enqueue_script('jquery');  
  
		wp_register_script('froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
		wp_register_script('flexslider.js', VIMEOGRAPHY_THEME_URL.'bugsauce/media/js/plugins/jquery.flexslider.js', array('jquery'));
		wp_register_script('fitvids.js', VIMEOGRAPHY_THEME_URL.'bugsauce/media/js/plugins/jquery.fitvids.js', array('jquery'));
		wp_register_script('bugsauce.js', VIMEOGRAPHY_THEME_URL.'bugsauce/media/js/bugsauce.js', array('jquery'));
		wp_register_style('bugsauce.css', VIMEOGRAPHY_THEME_URL.'bugsauce/media/css/bugsauce.css');
		
		wp_enqueue_script('froogaloop');		
		wp_enqueue_script('flexslider.js');
		wp_enqueue_script('fitvids.js');
		wp_enqueue_script('bugsauce.js');
				
		wp_enqueue_style('bugsauce.css');
	}
	        
    public function info()
    {
    	// optional helpers
    	require_once(VIMEOGRAPHY_PATH .'lib/helpers.php');
    	$helpers = new Vimeography_Helpers;
    	
    	// add featured video to the beginning of the array
    	if (is_array($this->featured))
    		array_unshift($this->data, $this->featured[0]);
    	
    	$items = array();
    	    	
    	foreach($this->data as $item)
    	{
			if ($item->duration AND ! strpos($item->duration, ':'))
			{
				$item->duration = $helpers->seconds_to_minutes($item->duration);
			}
			$items[] = $item;
    	}
    	    	    	
    	return $items;
    }
}