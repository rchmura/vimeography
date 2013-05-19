<?php

require_once(VIMEOGRAPHY_PATH . 'vendor/vimeo.php-master/vimeo.php');

abstract class Vimeography_Core
{
  const ENDPOINT  = 'https://api.vimeo.com/';

  protected $_vimeo;
  protected $_auth;

  /**
   * The parameters to send in the Vimeo request.
   *
   * @var array
   */
  protected $_params = array();

  /**
   * The type of source that we're retrieving videos from.
   *
   * @var string
   */
  protected $_source_type;

  /**
   * The name of the source where the videos are located.
   * This can be a username, channel name, group name or album name
   *
   * @var mixed   A string or integer
   */
  protected $_source_value;

  /**
   * [$_endpoint description]
   * @var [type]
   */
  protected $_endpoint;

  /**
   * An optional string pointing to the video that
   * should be featured in the gallery.
   *
   * @var string
   */
  protected $_featured_value;

  /**
   * Limit a gallery to show only this amount of videos.
   *
   * @var int
   */
  protected $_limit;

  /**
   * [__construct description]
   * @param [type] $settings [description]
   */
  public function __construct($settings)
  {

    if ($url = $this->_validate_vimeo_source($settings['source']))
    {
      $source_data         = $this->_get_vimeo_source_data($url);
      $this->_source_type  = $source_data['type'];
      $this->_source_value = $source_data['value'];
    }

    if (isset($settings['featured']) AND !empty($settings['featured']) AND $url = $this->_validate_vimeo_source($settings['featured']))
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
    $this->_endpoint = $this->_get_vimeo_endpoint($this->_source_type, $this->_source_value);
    $video_set       = $this->_make_vimeo_request($this->_endpoint, $this->_params);

    if (! empty($this->_featured_value))
    {
      $this->_endpoint = $this->_get_vimeo_endpoint('video', $this->_featured_value);
      $featured_video  = $this->_make_vimeo_request($this->_endpoint);

      $result_set = $this->_arrange_featured_video($video_set, $featured_video);
    }
    else
    {
      $result_set = $video_set;
    }

    if (isset($this->_limit))
      $result_set = $this->_limit_video_set($result_set);

    // $combined_json = str_replace(']', ',', $videos) . str_replace('[', ' ', $response);

    return $result_set;
  }

  /**
   * Sets the Vimeo source type and value based on the provided URL.
   *
   * @param   array $url A Parsed Vimeo URL
   * @return  array
   */
  abstract protected function _get_vimeo_source_data($url);

  /**
   * Build the Vimeo API endpoint based on the source type and source value.
   *
   * @param  string $source_type  The source type [eg. channel, album]
   * @param  string $source_value The source value [eg. staffpicks, hd]
   * @return string               Vimeo Simple API URL
   */
  abstract protected static function _get_vimeo_endpoint($source_type, $source_value);

  /**
   * Checks if the provided Vimeo URL is valid and if so, returns an array
   * containing the URL parts
   *
   * @param  string $source_url Source collection of Vimeo videos.
   * @return array              Schema of URL
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
   * Send a cURL Wordpress request to retrieve the requested data from the Vimeo API.
   *
   * @param  string $endpoint Vimeo API endpoint
   * @return array  Result set
   */
  private function _make_vimeo_request($endpoint, $params)
  {
    $response = $this->_vimeo->request( $endpoint, $params );

    switch ($response['status'])
    {
      case 200:
        if (isset($response['body']->data)) :
          return $response['body']->data;
        else :
          return $response['body']; // featured video
        endif;
        break;
      case 304:
        throw new Vimeography_Exception('not modified from the Vimeo API! '. $response['body']->error);
        break;
      case 400:
        throw new Vimeography_Exception(__('a bad request made was made. ' . $response['body']->error));
      case 404:
        throw new Vimeography_Exception('the plugin could not retrieve data from the Vimeo API! '. $response['body']->error);
        break;
      default:
        throw new Vimeography_Exception('Unknown response status '. $response['body']->error);
        break;
    }
  }

  /**
   * Arrange the video set to contain the video to be featured at the beginning of the set.
   *
   * @param  array $video_set      Vimeo Videos
   * @param  array $featured_video a Vimeo Video
   * @return string $video_set      Arranged array of Vimeo Videos
   */
  private function _arrange_featured_video($video_set, $featured_video)
  {
    // Does the featured video exist in the set?
    // If so, remove it from the set and place at front.
    $found = FALSE;

    foreach ($video_set as $key => $video)
    {
      if ($video->uri === $featured_video->uri)
      {
        unset($video_set[$key]);
        $found = TRUE;
      }
    }

    // If it does not exist, we need to remove the last video in the
    // video set and place the featured video up front.
    if ($found == FALSE AND $this->_limit == count($video_set))
      array_pop($video_set);

    // Add the featured video to the front.
    array_unshift($video_set, $featured_video);

    return array_values($video_set);
  }

  /**
   * Remove videos from the video set if there is an imposing limit.
   *
   * @return array of Vimeo videos.
   */
  private function _limit_video_set($video_set)
  {
    if ($this->_limit < count($video_set))
    {
      for ($video_to_delete = (count($video_set) - 1); $video_to_delete >= $this->_limit; $video_to_delete--)
        unset($video_set[$video_to_delete]);
    }

    return $video_set;
  }

}