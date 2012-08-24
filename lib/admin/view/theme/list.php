<?php

class Vimeography_Theme_List extends Mustache 
{
	public $messages = array();
    
	public function __construct()
	{
		if (isset($_POST))
			$this->_validate_form();
	}
		
	public function themes()
	{
		$themes = array();
		
		$theme_data = $this->_get_vimeography_themes();
		
		foreach ($theme_data as $theme_info)
		{
			$local_path = VIMEOGRAPHY_THEME_PATH . strtolower($theme_info['name']) . '/' . strtolower($theme_info['name']) .'.jpg';
						
			$theme_info['thumbnail'] = file_exists($local_path) ? VIMEOGRAPHY_THEME_URL . strtolower($theme_info['name']) . '/' . strtolower($theme_info['name']) .'.jpg' : 'http://placekitten.com/g/200/150';
			
			$themes[] = $theme_info;
		}
				
		return $themes;
	}
	
	public function nonce()
	{
	   return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
	}
		
	protected function _validate_form()
	{
		$url = wp_nonce_url('admin.php?page=vimeography-my-themes');
		
		if (false === ($creds = request_filesystem_credentials($url) ) )
		{
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in, 
			// so stop processing for now
			
			return true; // stop the normal page form from displaying
		}
			
		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem($creds) )
		{
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials($url);
			return true;
		}
		
		if (empty($_FILES)) return;
		
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( !empty($_FILES) && check_admin_referer('vimeography-install-theme','vimeography-theme-verification') )
		{			
			$name = substr(wp_filter_nohtml_kses($_FILES['vimeography-theme']['name']), 0, -4);
			
			if ($_FILES['vimeography-theme']['type'] != 'application/zip')
			{
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh Roh.', 'message' => 'Make sure you are uploading the actual .zip file, not a subfolder or file.');
			}
			else
			{
				global $wp_filesystem;
				
				if (! unzip_file($_FILES['vimeography-theme']['tmp_name'], VIMEOGRAPHY_THEME_PATH))
				{
					$this->messages[] = array('type' => 'error', 'heading' => 'Ruh Roh.', 'message' => 'The theme could not be installed.');
				}
				else
				{
					$this->messages[] = array('type' => 'success', 'heading' => 'Theme installed.', 'message' => 'You can now use the "'.$name.'" theme in your galleries.');
				}
			}

		}
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