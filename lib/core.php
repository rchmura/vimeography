<?php

// CREATE A NEW RENDERER FOR THE CORE CLASS.
// THISS WILL ACCEPT ALL PARAMATERS OF PRO PLUS POTENTIALLY LESS

class Vimeography_Core extends Vimeography
{
	const ENDPOINT = 'http://vimeo.com/api/v2/';
	const FORMAT = '.json';

  /**
   * The type of source that we're retrieving videos from.
   *
   * @var string
   */
  private $_source_type;

  /**
   * The name of the source where the videos are located.
   * This can be a username, channel name, group name or album name
   *
   * @var mixed   A string or integer
   */
  private $_source_value;

  /**
   * An optional string pointing to the video that
   * should be featured in the gallery.
   *
   * Note: This is the only remaining property that needs converted before it can be used.
   *
   * @var string
   */
  private $_featured;

	public function __construct($settings)
	{
		$this->_set_vimeo_source_data($settings['source']);

    if (isset($settings['featured']))
      $this->_featured = $settings['featured'];
	}

	/**
	 * Returns an array of results or a multi-dim array of results and featured result.
	 * @return [type] [description]
	 */
	public function fetch()
	{
		$result = array();

		$request_url = _get_vimeo_request_url($this->_source_type, $this->_source_value);
		$video_set = $this->_make_simple_vimeo_request($request_url);

    if (! empty($this->_featured))
    {
      $this->_set_vimeo_source_data($this->_featured);

			$request_url    = _get_vimeo_request_url($this->_source_type, $this->_source_value);
			$featured_video = $this->_make_simple_vimeo_request($request_url);

			$x = $this->_add_featured_video_to_video_set($video_set, $featured_video);
    }

		// $combined_json = str_replace(']', ',', $videos) . str_replace('[', ' ', $response);

		return $result;
	}

	/**
	 * Check if the featured video already exists in the main request object.
	 *
	 * @param string $featured_video json
	 */
	private function _add_featured_video_to_video_set($video_set, $featured_video)
	{
		$video_to_check = json_decode($featured_video, true);
		$video_object   = json_decode($result[0], true);
		$video_found    = FALSE;

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

  /**
   * Sets the Vimeo source type and value based on the provided URL.
   *
   * @param string $source_url A Vimeo URL
   * @return  bool
   */
  private function _set_vimeo_source_data($source_url)
  {
    $scheme = parse_url($source_url);
    if (empty($scheme['scheme'])) $source_url = 'https://' . $source_url;

    if ((($url = parse_url($source_url)) !== FALSE) && (preg_match('~vimeo[.]com$~', $url['host']) > 0))
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

      $this->_source_type  = rtrim(array_shift($url), 's');
      $this->_source_value = array_shift($url);

      return TRUE;
    }
    else
    {
      throw new Vimeography_Exception('Couldn\'t determine request method: You must provide a valid Vimeo source from where to retrieve Vimeo videos.');
    }
  }

	private static function _get_vimeo_request_url($source_type, $source_value)
	{
		switch ($source_type)
		{
			case 'album':
				$result = 'album/' . $source_value . '/videos';
				break;
			case 'channel':
				$result = 'channel/' . $source_value . '/videos';
				break;
			case 'group':
				$result = 'group/' . $source_value . '/videos';
				break;
			case 'user':
				$result = $source_value . '/videos';
				break;
			case 'video':
				$result = 'video/' . $source_value;
				break;
			default:
				throw new Vimeography_Exception($source_type.' is not a valid Vimeo source parameter.');
				break;
		}

		return self::ENDPOINT.$result.self::FORMAT;
	}

	/**
	 * Send a cURL Wordpress request to retrieve the requested data from the Vimeo simple API.
	 *
	 * @access private
	 * @param string $url
	 * @return string
	 */
	private function _make_simple_vimeo_request($url)
	{
		$response = wp_remote_get($url, array('timeout' => 10));

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
}