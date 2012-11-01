<?php

class Vimeography_Gallery_Edit extends Mustache
{
	public $messages = array();
	
	public $gallery;
	public $videos;
	public $theme_supports_settings = FALSE;
	
	// Only set if the theme supports settings
	protected $_settings_file;
		
	public function __construct()
	{		
		if (isset($_POST))
			$this->_validate_form();
			
		wp_register_script( 'bootstrap-tooltip', VIMEOGRAPHY_URL.'media/js/bootstrap-tooltip.js');
		wp_register_script( 'bootstrap-popover', VIMEOGRAPHY_URL.'media/js/bootstrap-popover.js');
		wp_register_script( 'bootstrap-collapse', VIMEOGRAPHY_URL.'media/js/bootstrap-collapse.js');
		wp_register_script( 'bootstrap-affix', VIMEOGRAPHY_URL.'media/js/bootstrap-affix.js');
		if (! wp_script_is('jquery-ui'))
		{
			wp_register_script('jquery-ui', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js", false, null);
			wp_enqueue_script('jquery-ui');
		}
		wp_register_script( 'jquery-mousewheel', VIMEOGRAPHY_URL.'media/js/jquery.mousewheel.min.js', 'jquery');
		wp_register_script( 'jquery-custom-scrollbar', VIMEOGRAPHY_URL.'media/js/jquery.mCustomScrollbar.js', 'jquery');
		
		wp_enqueue_script( 'bootstrap-collapse');
		wp_enqueue_script( 'bootstrap-tooltip');
		wp_enqueue_script( 'bootstrap-popover');
		wp_enqueue_script( 'bootstrap-affix');
		wp_enqueue_script( 'jquery-mousewheel');
		wp_enqueue_script( 'jquery-custom-scrollbar');
		
		global $wpdb;
		
		if (isset($_GET['id']))
		{
			$gallery_id = $wpdb->escape(intval($_GET['id']));
			
			if (isset($_GET['refresh']) AND $_GET['refresh'] == 1)
			{
				$this->delete_vimeography_cache($gallery_id);
				$this->messages[] = array('type' => 'success', 'heading' => 'So fresh.', 'message' => 'Your videos have been refreshed.');
			}
			
			if (isset($_GET['delete-theme-settings']) AND $_GET['delete-theme-settings'] == 1)
			{
				$transient = delete_transient('vimeography_theme_settings_'.$gallery_id);
				$this->messages[] = array('type' => 'success', 'heading' => __('Theme settings cleared.'), 'message' => __('Your gallery appearance has been reset.'));
			}
		}
				
		$this->gallery = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$gallery_id.' LIMIT 1;');
		if (! $this->gallery)
		{
			$this->messages[] = array('type' => 'error', 'heading' => 'Uh oh.', 'message' => 'That gallery no longer exists. It\'s gone. Kaput!');
		}
		else
		{
      // Check if the active theme has a settings file.
      $settings_file = VIMEOGRAPHY_THEME_PATH . $this->gallery[0]->theme_name . '/settings.php';
      
      if (file_exists($settings_file))
      {
        $this->theme_supports_settings = TRUE;
        $this->_settings_file = $settings_file;    
      }
		}
								
		if (isset($_GET['created']) && $_GET['created'] == 1)
		{
			$this->messages[] = array('type' => 'success', 'heading' => 'Gallery created.', 'message' => 'Welp, that was easy.');
		}		
	}
	
  /**
  * Returns the theme settings form to the admin panel.
  * 
  * @access public
  * @return void
  */
  public function vimeography_theme_settings()
  {    
    if ($this->theme_supports_settings == TRUE)
    {
      // If so, include it here and loop through each setting.
      include_once($this->_settings_file);
      $results = array();
      
      foreach ($settings as $setting)
      {
        // If the setting type isn't set, throw an error.
        if (! isset($setting['type']))
          throw new Vimeography_Exception(__('One of your active theme settings does not specify the type of setting it is.'));
        
        // If the setting type class isn't found, throw an error.	
        if (!@require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/settings/'.$setting['type'].'.php'))
          throw new Vimeography_Exception(__('"'.esc_attr($setting['type']).'" is not a valid Vimeography theme setting type.'));	
        
        // Otherwise, include the setting if there are no errors with the class.          		
        $class = 'Vimeography_Theme_Settings_'.ucfirst($setting['type']);
        
        if (!class_exists($class))
          throw new Vimeography_Exception('The "'.$setting['type'].'" setting type does not exist or is improperly structured.');	
        
        // Load the template file for the current theme setting.			
        $mustache = new $class;
        $template = $this->_load_template('theme/settings/'.$setting['type']);
        
        // Populate the setting type class and render the results from the template.
        $mustache->settings = $setting;
        $results[]['setting'] = $mustache->render($template);    
      }
      
      return $results;
    }
    else
    {
      return FALSE;
    }
  }
		
	/**
	 * Returns the base admin url for the plugin. This is a common function.
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
	 * You don't know what this function does? Shame on you... This is a common function.
	 * 
	 * @access public
	 * @return html
	 */
	public function vimeography()
	{
		if (function_exists('do_shortcode'))
			return do_shortcode( "[vimeography id='".$this->gallery[0]->id."']" );
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
    
	public static function basic_nonce()
	{
	   return wp_nonce_field('vimeography-basic-action','vimeography-basic-verification');
	}
		
	public static function theme_nonce()
	{
	   return wp_nonce_field('vimeography-theme-action','vimeography-theme-verification');
	}
	
	public static function theme_settings_nonce()
	{
	   return wp_nonce_field('vimeography-theme-settings-action','vimeography-theme-settings-verification');
	}
		
	public function selected()
	{
		return array(
			$this->gallery[0]->cache_timeout => TRUE,
		);
	}
	
	public function gallery()
	{
		$this->gallery[0]->featured_video = $this->gallery[0]->featured_video === 0 ? '' : $this->gallery[0]->featured_video;
		return $this->gallery;
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
			$theme_info['active'] = strtolower($theme_info['name']) == $this->gallery[0]->theme_name ? TRUE : FALSE;
									
			$themes[] = $theme_info;
		}
						
		return $themes;
	}
	
	/**
	 * Finds list of installed Vimeography themes by finding the directories in
	 * the theme folder and sending the mustache file to wordpress function
	 * get_file_data().
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
	 * Retrieves the meta data from the headers of a given theme file.
	 * 
	 * @access private
	 * @static
	 * @param mixed $plugin_file
	 * @return void
	 */
	private static function _get_theme_data($theme_file)
	{
		$default_headers  = array(
			'name'          => 'Theme Name',
			'theme-uri'     => 'Theme URI',
			'version'       => 'Version',
			'description'   => 'Description',
			'author'        => 'Author',
			'author-uri'    => 'Author URI',
		);
		
		return get_file_data( $theme_file, $default_headers );
	}
				
	/**
	 * Controls the POST data and sends it to the proper validation function.
	 * 
	 * @access private
	 * @return void
	 */
	private function _validate_form()
	{	
		global $wpdb;
		$id = $wpdb->escape(intval($_GET['id']));
		
		if (!empty($_POST['vimeography_appearance_settings']))
		{
			$messages = $this->_vimeography_validate_appearance_settings($id, $_POST);
		}
		elseif (!empty($_POST['vimeography_basic_settings']))
		{
			$messages = $this->_vimeography_validate_basic_settings($id, $_POST);
		}
		elseif (!empty($_POST['vimeography_theme_settings']))
		{
			$messages = $this->_vimeography_validate_theme_settings($id, $_POST['vimeography_theme_settings']);
		}
		else
		{
			return FALSE;
		}
	}
			
	private function _vimeography_validate_appearance_settings($id, $input)
	{
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if (check_admin_referer('vimeography-theme-action','vimeography-theme-verification') )
		{
			try
			{
        global $wpdb;
        $settings['theme_name'] = strtolower($wpdb->escape(wp_filter_nohtml_kses($input['vimeography_appearance_settings']['theme_name'])));
        
        $result = $wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('theme_name' => $settings['theme_name']), array( 'gallery_id' => $id ) );
        if ($result === FALSE)
          throw new Exception('Your theme could not be updated.');
        
        $transient = delete_transient('vimeography_theme_settings_'.$id);
        
        $this->messages[] = array('type' => 'success', 'heading' => __('Theme updated.'), 'message' => __('You are now using the "') . $settings['theme_name'] . __('" theme.'));
			}
			catch (Exception $e)
			{
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
			}
		}
	}
	
	private function _vimeography_validate_basic_settings($id, $input)
	{
		if (check_admin_referer('vimeography-basic-action','vimeography-basic-verification') )
		{
			try
			{
				global $wpdb;
				$settings['cache_timeout']  = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_basic_settings']['cache_timeout']));
				$settings['featured_video'] = $wpdb->escape(wp_filter_nohtml_kses($input['vimeography_basic_settings']['featured_video']));
				$settings['video_limit'] = intval($input['vimeography_basic_settings']['video_limit']) <= 60 ? $input['vimeography_basic_settings']['video_limit'] : 60;
				
				if (!empty($input['vimeography_basic_settings']['gallery_width']))
				{			
					preg_match('/(\d*)(px|%?)/', $input['vimeography_basic_settings']['gallery_width'], $matches);
					// If a number value is set...
					if (!empty($matches[1]))
					{
						// If a '%' or 'px' is set...
						if (!empty($matches[2]))
						{
							// Accept the valid matching string
							$settings['gallery_width'] = $matches[0];
						}
						else
						{
							// Append a 'px' value to the matching number
							$settings['gallery_width'] = $matches[1] . 'px';
						}
					}
					else
					{
						// Not a valid width
						$settings['gallery_width'] = '';
					}
				}
				else
				{
					// blank setting
					$settings['gallery_width'] = '';
				}
																					
				$result = $wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('cache_timeout' => $settings['cache_timeout'], 'featured_video' => $settings['featured_video'], 'gallery_width' => $settings['gallery_width'], 'video_limit' => $settings['video_limit']), array( 'gallery_id' => $id ) );
				
				if ($result === FALSE)
					throw new Exception('Your settings could not be updated.');
					//$wpdb->print_error();
					
				$this->delete_vimeography_cache($id);
				$this->messages[] = array('type' => 'success', 'heading' => __('Settings updated.'), 'message' => __('Nice work. You are pretty good at this.'));
			}
			catch (Exception $e)
			{
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
			}
		}
	}
	
	private function _vimeography_validate_theme_settings($id, $input)
	{
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-theme-settings-action','vimeography-theme-settings-verification') )
    {
      try
      {
        $settings = array();
        foreach ($input as $setting)
        {
          // Convert the jQuery attribute selector to an actual css property
          $number_of_matches = preg_match_all('/[A-Z]/', esc_attr($setting['attribute']), $capitals, PREG_OFFSET_CAPTURE);
          if ($number_of_matches === FALSE) break;
          
          $i = 0; // offset in case of multiple capitals
          foreach ($capitals[0] as $capital)
          {
            $setting['attribute'] = strtolower(substr_replace($setting['attribute'], '-', $capital[1]+$i, 0));
            $i++;
          }
                    
          $setting['target'] = esc_attr($setting['target']);
          $setting['value'] = esc_attr($setting['value']);
          $settings[] = $setting;          
        }
        
        if (set_transient( 'vimeography_theme_settings_'.$id, $settings) === FALSE)
          throw new Exception(__('Your theme settings could not be saved. Try again!'));
        
        $this->messages[] = array('type' => 'success', 'heading' => __('Theme updated.'), 'message' => __('I didn\'t know that you were such a great designer!'));
      }
      catch (Exception $e)
      {
        $this->messages[] = array('type' => 'error', 'heading' => __('Oh no!'), 'message' => $e->getMessage());
      }
    }
	}
	
	/**
	 * Returns the file contents for the provided mustache template. Common Function.
	 * 
	 * @access protected
	 * @param mixed $name
	 * @return void
	 */
	protected function _load_template($name)
	{
		$path = VIMEOGRAPHY_PATH . 'lib/admin/templates/' . $name .'.mustache';
		if (! $result = @file_get_contents($path))
			wp_die('The admin template "'.$name.'" cannot be found.');
		return $result;
	}
	
}