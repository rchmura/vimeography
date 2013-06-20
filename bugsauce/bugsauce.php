<?php
/*
Theme Name: Bugsauce
Theme URI: vimeography.com/themes/bugsauce
Version: 3.0
Description: is the base theme that comes prepackaged with Vimeography.
Author: Dave Kiss
Author URI: vimeography.com
*/

class Vimeography_Themes_Bugsauce
{
  public $version = '3.0';

  public $enable_byline = 0;
  public $enable_title = 0;
  public $enable_portrait = 0;

	public function __construct()
	{
		// Without the @, this generates warnings?
		// Notice: Undefined offset: 0 in /Users/davekiss/Sites/vimeography.com/wp-includes/plugin.php on line 762/780
		@add_action('wp_enqueue_scripts', $this->_load_scripts());
	}

  public function __set($name, $value)
  {
    $this->$name = $value;
  }

	public function _load_scripts()
	{
		// First things first. jQuery.
		wp_enqueue_script('jquery');

		// Register our common scripts
		wp_register_script('froogaloop', 'http://a.vimeocdn.com/js/froogaloop2.min.js');
		wp_register_script('flexslider', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.flexslider.js', array('jquery'));
		wp_register_script('fitvids', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.fitvids.js', array('jquery'));
		wp_register_script('spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/spin.min.js', array('jquery'));
		wp_register_script('jquery-spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.spin.js', array('jquery', 'spin'));

		// Register our custom scripts
		wp_register_style('bugsauce', VIMEOGRAPHY_THEME_URL.'bugsauce/media/bugsauce.css');

		wp_enqueue_script('froogaloop');
		wp_enqueue_script('flexslider');
		wp_enqueue_script('fitvids');
		wp_enqueue_script('spin');
		wp_enqueue_script('jquery-spin');

		wp_enqueue_style('bugsauce');
	}

  public function featured()
  {
    // optional helpers
    require_once(VIMEOGRAPHY_PATH .'lib/helpers.php');
    $helpers = new Vimeography_Helpers;

    $this->featured->oembed = $helpers->get_featured_embed($this->featured->link);

    return $this->featured;
  }

  public function videos()
  {
  	// optional helpers
  	require_once VIMEOGRAPHY_PATH .'lib/helpers.php';
  	$helpers = new Vimeography_Helpers;

  	// add featured video to the beginning of the array
  	if (is_array($this->featured))
  		array_unshift($this->data, $this->featured[0]);

  	return $helpers->apply_common_formatting($this->data);
  }

}