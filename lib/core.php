<?php

// Require Mustache.php
if (! class_exists('Mustache'))
	require_once(VIMEOGRAPHY_PATH . '/vendor/mustache/Mustache.php');

class Vimeography_Core extends Vimeography
{
	const ENDPOINT = 'http://vimeo.com/api/v2/';
	const FORMAT = '.json';
		
	protected $_source;
	protected $_type;
	
	protected $_theme;
	protected $_limit;
	protected $_featured;
	protected $_gallery_id;
	
	protected $_debug = FALSE;
		
	public static function factory($class, $settings)
	{
		require_once(VIMEOGRAPHY_PATH .'lib/'. $class . '.php');
		$class_name = 'Vimeography_'. ucfirst($class);
		
		if (class_exists($class_name))
		{
			return new $class_name($settings);
		}
		else
		{
            throw new Vimeography_Exception('Class not found: '.$class_name);
		}
	}
			
	public function __construct($settings)
	{
		require_once(VIMEOGRAPHY_PATH .'lib/exception.php');
		
		$this->_theme      = $settings['theme'];
		$this->_featured   = $settings['featured'];
		$this->_source     = $settings['source'];
		$this->_limit      = $settings['limit'];
		$this->_gallery_id = $settings['id'];
	}
	
	/**
	 * Overload the constructed debugger to print the data instead of render it.
	 * 
	 * @access public
	 * @param mixed $debug (default: TRUE)
	 * @return void
	 */
	public function debug($debug = TRUE)
	{
		$this->_debug = $debug;
		return $this;
	}
	
	/**
	 * Retrieves the requested data from Vimeo API.
	 * This is called by the class loaded in the factory method.
	 * 
	 * TODO: This could potentially return a 404 page, and we don't want that, nor do we want to show it in the exception.
	 * @access protected
	 * @param mixed $data
	 * @return void
	 */
	protected function _retrieve()
	{
		$urls = array();
		$urls[] = $this->_build_url($this->_source);
		
		if (!empty($this->_featured))
			$urls[] = self::ENDPOINT.'video/'.$this->_featured.self::FORMAT;
		
		$result = array();
		$videos = '';
		$main_request = TRUE;
						
		foreach ($urls as $url)
		{
			if ($main_request == TRUE)
			{
				$number_of_requests = ceil($this->_limit / 20);
				
				for ($video_page = 1; $video_page <= $number_of_requests; $video_page++)
				{
					$response = $this->_make_vimeo_request($url, $video_page);
					
					if ($video_page == 1)
					{
						// If the limit is less than the total videos, we need to remove some videos.
						if ($this->_limit < count(json_decode($response)))
						{
							$video_object = json_decode($response);
							
							for ($video_to_delete = (count($video_object) - 1); $video_to_delete >= $this->_limit; $video_to_delete--)
							{
								unset($video_object[$video_to_delete]);
							}
							$response = json_encode($video_object);
						}
						
						$videos .= $response;
					}
					else
					{
						$videos = str_replace(']', ',', $videos);
						
						if ($this->_limit < ($video_page * 20))
						{
							$video_object = json_decode($response);
														
							for ($video_to_delete = count($video_object) - 1; $video_to_delete >= $this->_limit - (($video_page * 20) / 2); $video_to_delete--)
							{
								unset($video_object[$video_to_delete]);
							}
							$response = json_encode($video_object);
						}
						
						$response = str_replace('[', ' ', $response);
						$videos = $videos.$response;
					}
					
					// This stops the next request from occuring if the total
					// video count on the Vimeo source is less than what the
					// user specified that they would like to see
					if (count(json_decode($videos)) < 20) break;
					
				}
				$main_request = FALSE;
				$result[] = $videos;				
			}
			else
			{
				// A featured video was set, let's get it.
				$featured_video = $this->_make_vimeo_request($url, 1);
				
				// Now, we need to check if the featured video already exists in
				// the main video request object.
				$video_to_check = json_decode($featured_video, true);
				$video_object = json_decode($result[0], true);
				$video_found = FALSE;
												
				// If it does exist, we should remove it.
				foreach ($video_object as $video_key => $video_data)
				{
					if ($video_data['id'] === $video_to_check[0]['id'])
					{
						unset($video_object[$video_key]);
						$video_found = TRUE;
					}
				}
				
				// If it does not exist, we need to remove the last video in the
				// array to make room for the featured video.
				if ($video_found == FALSE)
					unset($video_object[count($video_object) - 1]);
					
				$result[0] = json_encode(array_values($video_object));
				$result[] = $featured_video;
			}
		
		}
		return $result;
	}
				
	/**
	 * Build the endpoint url based on the provided Vimeo URL.
	 * 
	 * @access private
	 * @param mixed $data
	 * @return void
	 */
	private function _build_url($source)
	{

		$scheme = parse_url($source);
		if (empty($scheme['scheme'])) $source = 'https://' . $source;

		if ((($url = parse_url($source)) !== FALSE) && (preg_match('~vimeo[.]com$~', $url['host']) > 0))
	    {
	    	// The URL is a valid Vimeo URL. Break it into an array containing the URL parts
	        $url = array_filter(explode('/', $url['path']), 'strlen');
	        
	        // If the array doesn't contain one of the following strings, it
	        // must be either a user or a video
	        if (in_array($url[1], array('album', 'channels', 'groups')) !== TRUE)
	        {
	        	if (is_numeric($url[1]))
	        	{
	            	array_unshift($url, 'video');
	        	}
	        	else
	        	{
	            	array_unshift($url, 'users');
	        	}
	        }
	        
	        // Put the URL parts into a conventional array
	        $parts = array('type' => rtrim(array_shift($url), 's'), 'name' => array_shift($url));	        
	    }
	    else
	    {
			throw new Vimeography_Exception('You must provide a valid Vimeo source from where to retrieve Vimeo videos.');
	    }
	    		
		switch ($parts['type'])
		{
			case 'album':
				$result = 'album/'.$parts['name'].'/'.$this->_type;
				break;
			case 'channel':
				$result = 'channel/'.$parts['name'].'/'.$this->_type;
				break;
			case 'group':
				$result = 'group/'.$parts['name'].'/'.$this->_type;
				break;
			case 'user':
				$result = $parts['name'].'/'.$this->_type;
				break;
			case 'video':
				$result = 'video/'.$parts['name'];
				break;
			default:
				throw new Vimeography_Exception($parts['type'].' is not a valid Vimeo source parameter.');
				break;
		}
		
		return self::ENDPOINT.$result.self::FORMAT;
	}
	
	/**
	 * Send a cURL Wordpress request to retrieve the requested data from the Vimeo simple API.
	 * 
	 * @access private
	 * @param mixed $url
	 * @param mixed $page
	 * @return void
	 */
	private function _make_vimeo_request($url, $page)
	{
		$response = wp_remote_get($url.'?page='.$page);
		
		if (isset($response->errors))
		{
			foreach ($response->errors as $error)
			{
				throw new Vimeography_Exception('the plugin did not retrieve data from the Vimeo API! '. $error[0]);
			}
		}
			
		if (strpos($response['body'], 'not found'))
			throw new Vimeography_Exception('the plugin could not retrieve data from the Vimeo API! '. $response['body']);
																	
		return $response['body'];
	}
		
	public function render($data)
	{
		if (! $this->_debug)
		{
			if (! isset($this->_theme))
				throw new Vimeography_Exception('You must specify a theme in either the admin panel or the shortcode.');
				
			if (!@require_once(VIMEOGRAPHY_THEME_PATH . $this->_theme . '/'.$this->_theme.'.php'))
				throw new Vimeography_Exception('The "'.$this->_theme.'" theme does not exist or is improperly structured.');	
							
			$class = 'Vimeography_Themes_'.ucfirst($this->_theme);
			
			if (!class_exists($class))
				throw new Vimeography_Exception('The "'.$this->_theme.'" theme class does not exist or is improperly structured.');	
						
			$mustache = new $class;
			$theme = $this->_load_theme($this->_theme);
												
			$mustache->data = json_decode($data[0]);
						
			if (isset($data[1]))
			{
				// featured video option is set
				$featured = json_decode($data[1]);				
			}
			else
			{
				$data = json_decode($data[0]);
				$featured = $data[0];
			}
									
			$mustache->featured = $featured;
			$mustache->gallery_id = $this->_gallery_id;
							
			return $mustache->render($theme);
		}
		else
		{
			echo '<h1>Vimeography Debug</h1>';
			echo '<pre>';
			print_r(json_decode($data));
			echo '</pre>';
			die;
		}
	}
	
	/**
	 * Retrieves the contents of a Vimeography theme's mustache template.
	 * 
	 * @access private
	 * @static
	 * @param mixed $name
	 * @return void
	 */
	private static function _load_theme($name)
	{
		$path = VIMEOGRAPHY_THEME_PATH . $name . '/videos.mustache';
		if (! $result = @file_get_contents($path))
			throw new Vimeography_Exception('The gallery template for the "'.$name.'" theme cannot be found.');
		return $result;
	}
}