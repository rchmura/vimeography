<?php

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
   * @var string
   */
  private $_featured_value;

  private $_limit;

	public function __construct($settings)
	{
    if ($url = $this->_validate_vimeo_source($settings['source']))
    {
      $source_data         = $this->_get_vimeo_source_data($url);
      $this->_source_type  = $source_data['type'];
      $this->_source_value = $source_data['value'];
    }

    if (isset($settings['featured']) AND $url = $this->_validate_vimeo_source($settings['source']))
    {
      $source_data           = $this->_get_vimeo_source_data($url);
      $this->_featured_value = $source_data['value'];
    }

    if (isset($settings['limit']))
      $this->_limit = $settings['limit'];
	}

	/**
	 * Fetch the videos to be displayed in the Vimeography Gallery.
   *
	 * @return string  $result_set JSON Object of Vimeo Videos
	 */
	public function fetch()
	{
		$result = array();

    $request_url = $this->_get_vimeo_request_url($this->_source_type, $this->_source_value);
    $video_set   = $this->_make_simple_vimeo_request($request_url);

    if (! empty($this->_featured))
    {
			$request_url    = $this->_get_vimeo_request_url('video', $this->_featured_value);
			$featured_video = $this->_make_simple_vimeo_request($request_url);
    }
    else
    {
      $featured_video = '';
    }

    $result_set = $this->_arrange_featured_video($video_set, $featured_video);

    if (isset($this->_limit))
      $result_set = $this->_limit_video_set($result_set);

		// $combined_json = str_replace(']', ',', $videos) . str_replace('[', ' ', $response);

		return $result_set;
	}

  /**
   * Remove videos from the video set if there is an imposing limit.
   *
   * @return string JSON object of Vimeo videos.
   */
  private function _limit_video_set($video_set)
  {
    $video_set = json_decode($video_set, TRUE);

    if ($this->_limit < count($video_set))
    {
      for ($video_to_delete = (count($video_set) - 1); $video_to_delete >= $this->_limit; $video_to_delete--)
        unset($video_set[$video_to_delete]);
    }

    return json_encode($video_set);
  }

/**
 * Arrange the video set to contain the video to be featured at the beginning of the set.
 *
 * @param  string $video_set      JSON Object of Vimeo Videos
 * @param  string $featured_video JSON Object of a Vimeo Video
 * @return string $video_set      Arranged JSON Object of Vimeo Videos
 */
  private static function _arrange_featured_video($video_set, $featured_video)
  {
    if (! empty($featured_video))
    {
      // Check if the featured video already exists in the video set.
      $video_set      = json_decode($video_set, TRUE);
      $video_to_check = json_decode($featured_video, TRUE);
      $found          = FALSE;

      // Does the featured video exist in the set?
      // If so, remove it from the set and place at front.
      foreach ($video_set as $key => $video)
      {
        if ($video['id'] === $video_to_check['id'])
        {
          unset($video_set[$key]);
          $found = TRUE;
        }
      }

      // If it does not exist, we need to remove the last video in the
      // video set and place the featured video up front.
      if ($found == FALSE)
        array_pop($video_set);

      // Add the featured video to the front.
      array_unshift($video_set, $featured_video);

      $video_set = json_encode(array_values($video_set));
    }

    return $video_set;
  }

  /**
   * Checks if the provided Vimeo URL is valid and if so, returns an array
   * containing the URL parts
   *
   * @param  string $source_url [description]
   * @return array              [description]
   */
  private function _validate_vimeo_source($source_url)
  {
    $scheme = parse_url($source_url);

    if (empty($scheme['scheme']))
      $source_url = 'https://' . $source_url;

    if ((($url = parse_url($source_url)) !== FALSE) && (preg_match('~vimeo[.]com$~', $url['host']) > 0))
    {
      return $url;
    }
    else
    {
      throw new Vimeography_Exception('You must provide a valid Vimeo source from where to retrieve Vimeo videos.');
    }
  }

  /**
   * Sets the Vimeo source type and value based on the provided URL.
   *
   * @param   array $source_url A Parsed Vimeo URL
   * @return  bool
   */
  private function _get_vimeo_source_data($url)
  {
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

    $type  = rtrim(array_shift($url), 's');
    $value = array_shift($url);

    return array('type' => $type, 'value' => $value);
  }

/**
 * Build the Vimeo simple API url based on the source type and source value.
 *
 * @param  string $source_type  The source type [eg. channel, album]
 * @param  string $source_value The source value [eg. staffpicks, hd]
 * @return string               Vimeo Simple API URL
 */
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
   * @param  string $url Vimeo API source
   * @return string      JSON string
   */
	private static function _make_simple_vimeo_request($url)
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