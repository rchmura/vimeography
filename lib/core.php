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
   * Pagination details from the Vimeo request.
   *
   * @var object
   */
  protected $_paging;

  /**
   * [__construct description]
   * @param [type] $settings [description]
   */
  public function __construct($settings)
  {
    $this->_endpoint = $settings['source'] . '/videos';

    if ( isset($settings['featured']) AND ! empty($settings['featured']) )
      $this->_featured = '/videos/' . preg_replace("/[^0-9]/", '', $settings['featured']);
  }

  /**
   * Fetch the videos to be displayed in the Vimeography Gallery.
   *
   * @return string  $result_set JSON Object of Vimeo Videos
   */
  public function fetch($last_modified = NULL)
  {
    if (! $this->_verify_vimeo_endpoint($this->_endpoint) )
        throw new Vimeography_Exception('Endpoint is not valid.');

    $response  = $this->_make_vimeo_request($this->_endpoint, $this->_params, $last_modified);
    $video_set = $this->_get_video_set($response);

    $this->_paging = $response->paging;

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

    // $combined_json = str_replace(']', ',', $videos) . str_replace('[', ' ', $response);

    return $result_set;
  }

  public function get_paging()
  {
    return $this->_paging;
  }

  abstract protected static function _verify_vimeo_endpoint($resource);

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
        throw new Vimeography_Exception('Unknown response status '. $response['body']->error);
        break;
    }
  }

  private function _get_video_set($body)
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


  /**
   * Gets the videos for gallery
   *
   * Code refactorized from Shortcode->output(). The code was used too in the
   * ajax method. This looks like the right place to place the pagination logic
   *
   * @todo Move to a better library or model class.
   */
  static public function getVideoSet (Vimeography_Core $vimeography, $gallerySettings, $token)
  {
      require_once (VIMEOGRAPHY_PATH . 'lib/cache.php');
      $cache = new Vimeography_Cache($gallerySettings);

      $cache_file = VIMEOGRAPHY_CACHE_PATH . $token . '.cache';

      // If the cache exists,
      if ($cache->exists($cache_file)) {
          // and the cache is expired,
          if (($last_modified = $cache->expired($cache_file)) !== FALSE) {
              // make the request with a last modified header.
              $video_set = $vimeography->fetch($last_modified);

              // Here is where we need to check if $video_set exists, or if it
              // returned a 304, in which case, we can safely update the
              // cache's last modified
              // and return it.
              if ($video_set == NULL) {
                  $cache->renew($cache_file);
                  $video_set = $cache->get($cache_file);
              }
          } else {
              // If it isn't expired, return it.
              $video_set = $cache->get($cache_file);
          }
      } else {
          // If a cache doesn't exist, go get the videos, dude.
          $video_set = $vimeography->fetch();
          $paging = $vimeography->get_paging();
      }

      // Cache the results.
      if ($gallerySettings['cache'] != 0) {
          $cache->set($cache_file, $video_set);
      }
      return $video_set;
  }


}