<?php

// Require Mustache.php
require_once(VIMEOGRAPHY_PATH . '/vendor/mustache/Mustache.php');

class Vimeography_Core extends Vimeography
{
	const ENDPOINT = 'http://vimeo.com/api/v2/';
	const FORMAT = '.json';
		
	protected $_source;
	protected $_named;
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
		$this->_source     = $settings['from'];
		$this->_named      = $settings['named'];
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
	 * Build the endpoint url based on the provided information.
	 * 
	 * @access protected
	 * @param mixed $data
	 * @return void
	 */
	protected function _build_url($source)
	{
		switch ($source)
		{
			case 'album':
				$result = 'album/'.$this->_named.'/'.$this->_type;
				break;
			case 'channel':
				$result = 'channel/'.$this->_named.'/'.$this->_type;
				break;
			case 'group':
				$result = $this->_named.'/'.$this->_type;
				break;
			case 'user':
				$result = $this->_named.'/'.$this->_type;
				break;
			case 'video':
				$result = 'video/'.$this->_named;
				break;
			default:
				if (empty($source))
				{
					throw new Vimeography_Exception('You must provide a source from where to retrieve Vimeo videos.');
				}
				else
				{
					throw new Vimeography_Exception($source.' is not a valid Vimeo source parameter.');
				}
				break;
		}
		
		return self::ENDPOINT.$result.self::FORMAT;
	}
		
	/**
	 * Retrieves the requested data from Vimeo API.
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
					
					// This stops the next request from occuring if the total video count on the Vimeo source is less than what the user specified that they would like to see
					if (count(json_decode($videos)) < 20) break;
					
				}
				$main_request = FALSE;
				$result[] = $videos;
			}
			else
			{
				// Remove the last video in the array to make room for the featured video . NOTE: Don't want to do this when the featured video exists in the video_object
				/*$video_object = json_decode($result[0]);
				unset($video_object[count($video_object) - 1]);
				$result[0] = json_encode($video_object);*/
				
				// Make featured vid request here.
				$featured_video = $this->_make_vimeo_request($url, 1);
				$result[] = $featured_video;
			}
		
		}
		return $result;
	}
	
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
				
				// check if featured video is in the source array, and if so, remove it to avoid duplicates.
				$i = 0;
				foreach ($mustache->data as $video)
				{
					if ($video->id === $featured[0]->id)
						unset($mustache->data[$i]);
					$i++;
				}
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
	
	protected function _load_theme($name)
	{
		$path = VIMEOGRAPHY_THEME_PATH . $name . '/videos.mustache';
		if (! $result = @file_get_contents($path))
			throw new Vimeography_Exception('The gallery template for the "'.$name.'" theme cannot be found.');
		return $result;
	}
}