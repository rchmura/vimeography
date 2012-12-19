<?php

class Vimeography_Theme_List extends Vimeography_Base 
{
	/**
	 * Checks if there is an incoming form submission.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		if (isset($_FILES['vimeography-theme']))
			$this->_validate_form();
	}
	
	/**
	 * Returns several security form fields for the new gallery form.
	 * 
	 * @access public
	 * @return mixed
	 */
	public static function nonce()
	{
	   return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
	}
		
	private function _validate_form()
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
				
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( ($_FILES['vimeography-theme']['error'] == 0) && check_admin_referer('vimeography-install-theme','vimeography-theme-verification') )
		{			
			$name = substr(wp_filter_nohtml_kses($_FILES['vimeography-theme']['name']), 0, -4);
			$ext = substr($_FILES['vimeography-theme']['name'], -4);
			
			if ($ext == '.zip')
			{
				global $wp_filesystem;
				
				if (! unzip_file($_FILES['vimeography-theme']['tmp_name'], VIMEOGRAPHY_THEME_PATH))
				{
					$this->messages[] = array('type' => 'error', 'heading' => 'Ruh Roh.', 'message' => __('The theme could not be installed.'));
				}
				else
				{
					$this->messages[] = array('type' => 'success', 'heading' => __('Theme installed.'), 'message' => __('You can now use the "') . $name . __('" theme in your galleries.'));
				}
			}
			else
			{
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh Roh.', 'message' => __('Make sure you are uploading the actual .zip file, not a subfolder or file.'));
			}
			
		}
	}
           
}