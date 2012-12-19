<?php

/**
 * Vimeography_Base class.
 * 
 * @extends Mustache
 */
class Vimeography_Base extends Mustache
{
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
	public function vimeography()
	{
		if (function_exists('do_shortcode'))
			return do_shortcode( "[vimeography id='".$this->_gallery[0]->id."']" );
	}

	/**
	 * Returns the base admin url for the plugin.
	 * 
	 * @access public
	 * @static
	 * @return string
	 */
	public static function admin_url()
	{
		return get_admin_url().'admin.php?page=vimeography-';
	}
	
	/**
	 * Get the JSON data stored in the Vimeography cache for the provided gallery id.
	 * 
	 * @access public
	 * @static
	 * @param mixed $id
	 * @return void
	 */
	public static function get_vimeography_cache($id)
	{
    return FALSE === ( $vimeography_cache_results = get_transient( 'vimeography_cache_'.$id ) ) ? FALSE : $vimeography_cache_results;
  }
  
	/**
	 * Delete the transient cache entry for the given gallery id. This is a common function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $id
	 * @return void
	 */
	public static function delete_vimeography_cache($id)
  {
    return delete_transient('vimeography_cache_'.$id);
  }
  
	/**
	 * Gets the default settings created when Vimeography is installed.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_vimeography_defaults()
	{
		return get_option('vimeography_default_settings');
	}
	
	/**
	 * Creates the theme list to show in the appearance tab.
	 * 
	 * @access public
	 * @return array
	 */
	public function themes()
	{
		$themes = array();
		
		$theme_data = $this->_get_vimeography_themes();
		
		foreach ($theme_data as $theme_info)
		{
			$local_path = VIMEOGRAPHY_THEME_PATH . strtolower($theme_info['name']) . '/' . strtolower($theme_info['name']) .'.jpg';
			
			$theme_info['thumbnail'] = file_exists($local_path) ? VIMEOGRAPHY_THEME_URL . strtolower($theme_info['name']) . '/' . strtolower($theme_info['name']) .'.jpg' : 'http://placekitten.com/g/200/150';
			
			if (isset($this->_gallery))
			 $theme_info['active'] = strtolower($theme_info['name']) == $this->_gallery[0]->theme_name ? TRUE : FALSE;
									
			$themes[] = $theme_info;
		}
						
		return $themes;
	}
	
	/**
	 * Finds list of installed Vimeography themes by finding the directories in the theme folder
	 * and sending the mustache file to wordpress function get_file_data().
	 * 
	 * @access private
	 * @return array of themes
	 */
	private function _get_vimeography_themes() {
		$themes = array();
		
		$directories = glob(VIMEOGRAPHY_THEME_PATH.'*' , GLOB_ONLYDIR);
		
		foreach ($directories as $dir)
		{
			$theme_name = substr($dir, strrpos($dir, '/')+1);
			$themes[] = $this->_get_theme_data($dir.'/'.$theme_name.'.php');
		}
		
		return $themes;
	}
	
	/**
	 * Retrieves the meta data from the headers of a given plugin file.
	 * 
	 * @access private
	 * @static
	 * @param mixed $plugin_file
	 * @return void
	 */
	private static function _get_theme_data($plugin_file)
	{
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

  
}