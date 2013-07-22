<?php

require_once(VIMEOGRAPHY_PATH . 'vendor/vimeo.php-master/vimeo.php');

abstract class Vimeography_Core
{
  const ENDPOINT  = 'https://api.vimeo.com/';

  /**
   * [$_vimeo description]
   * @var [type]
   */
  protected $_vimeo;

  /**
   * [$_auth description]
   * @var [type]
   */
  protected $_auth;

  /**
   * The parameters to send in the Vimeo request.
   *
   * @var array
   */
  protected $_params = array();

  /**
   * [$_endpoint description]
   * @var [type]
   */
  protected $_endpoint;

  /**
   * An optional resource string pointing to the video that
   * should be featured in the gallery.
   *
   * @var string
   */
  protected $_featured;

  /**
   * [__construct description]
   * @param [type] $settings [description]
   */
  public function __construct($settings)
  {
    $this->_endpoint = $settings['source'];

    if ( isset($settings['featured']) AND ! empty($settings['featured']) )
      $this->_featured = '/videos/' . preg_replace("/[^0-9]/", '', $settings['featured']);
  }

  /**
   * [_verify_vimeo_endpoint description]
   * @param  [type] $resource [description]
   * @return [type]           [description]
   */
  abstract protected static function _verify_vimeo_endpoint($resource);

  /**
   * Gets the videos for the gallery.
   *
   * @param  [type] $expiration [description]
   * @param  [type] $gallery_id [description]
   * @return [type]             [description]
   */
  public function get_videos($expiration, $gallery_id)
  {
    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $cache = new Vimeography_Cache($gallery_id, $expiration);

    // If the cache file exists,
    if ( $cache->exists() )
    {
      // and the cache file is expired,
      if (($last_modified = $cache->expired()) !== FALSE)
      {
        // make the request with a last modified header.
        $result = $this->fetch($last_modified);

        // Here is where we need to check if $video_set exists, or if it
        // returned a 304, in which case, we can safely update the
        // cache's last modified
        // and return it.
        if ($result == NULL)
        {
          $result = $cache->renew()->get();
        }
      }
      else
      {
        // If it isn't expired, return it.
        $result = $cache->get();
      }
    }
    else
    {
      // If a cache doesn't exist, go get the videos, dude.
      $result = $this->fetch();
    }

    // Cache the results.
    if ($expiration !== 0)
      $cache->set($result);

    return $result;
  }

  /**
   * Fetch the videos to be displayed in the Vimeography Gallery.
   *
   * @param $last_modified
   * @return string  $response  Modified response from Vimeo.
   */
  public function fetch($last_modified = NULL)
  {
    if (! $this->_verify_vimeo_endpoint($this->_endpoint) )
        throw new Vimeography_Exception("Endpoint {$this->_endpoint} is not valid.");

    $response  = $this->_make_vimeo_request($this->_endpoint, $this->_params, $last_modified);
    $video_set = $this->_get_video_set($response);

    if (! empty($this->_featured))
    {
      $response       = $this->_make_vimeo_request($this->_featured);
      $featured_video = $this->_get_video_set($response);
      $result_set     = $this->_arrange_featured_video($video_set, $featured_video);
    }
    else
    {
      $result_set = $video_set;
    }

    unset($response->data);
    $response->video_set = $result_set;

    // $combined_json = str_replace(']', ',', $videos) . str_replace('[', ' ', $response);

    return $response;
  }

  /**
   * Send a cURL Wordpress request to retrieve the requested data from the Vimeo API.
   *
   * @param  string $endpoint Vimeo API endpoint
   * @return array  Response Body
   */
  private function _make_vimeo_request($endpoint, $params, $last_modified)
  {
    $response = $this->_vimeo->request( $endpoint, $params, $last_modified );

    switch ($response['status'])
    {
      case 200:
        return $response['body'];
        break;
      case 304:
        return NULL;
        break;
      case 400:
        throw new Vimeography_Exception(__('a bad request made was made. ' . $response['body']->error));
      case 404:
        throw new Vimeography_Exception('the plugin could not retrieve data from the Vimeo API! '. $response['body']->error);
        break;
      default:
        throw new Vimeography_Exception("Unknown response status #{$response['status']}, {$response['body']->error}");
        break;
    }
  }

  /**
   * [_get_video_set description]
   * @param  [type] $body [description]
   * @return [type]       [description]
   */
  private static function _get_video_set($body)
  {
    if (isset($body->data)) :
      return $body->data;
    else :
      return $body; // featured video
    endif;
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
    if ($found == FALSE)
      array_pop($video_set);

    // Add the featured video to the front.
    array_unshift($video_set, $featured_video);

    return array_values($video_set);
  }

}