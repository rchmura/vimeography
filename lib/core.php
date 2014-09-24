<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Vimeography_Core {
  const ENDPOINT  = 'https://api.vimeo.com/';

  /**
   * [$_vimeo description]
   *
   * @var instance
   */
  protected $_vimeo;

  /**
   * [$_auth description]
   *
   * @var string
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
   *
   * @var string
   */
  protected $_endpoint;

  /**
   * Limit a gallery to show only this amount of videos.
   *
   * @var int
   */
  protected $_limit = 0;

  /**
   * An optional resource string pointing to the video that
   * should be featured in the gallery.
   *
   * @var string
   */
  protected $_featured;

  /**
   * Set the class properties from the provided
   * shortcode settings array.
   *
   * @param array $settings
   */
  public function __construct($settings) {
    $this->_endpoint = $settings['source'];

    if ( isset( $settings['limit'] ) )
      $this->_limit = $settings['limit'];

    if ( isset($settings['featured']) AND ! empty($settings['featured']) ) {
      $this->_featured = '/videos/' . preg_replace("/[^0-9]/", '', $settings['featured']);
    }
  }

  /**
   * [_verify_vimeo_endpoint description]
   * @param  [type] $resource [description]
   * @return [type]           [description]
   */
  abstract protected function _verify_vimeo_endpoint($resource);

  /**
   * Gets the videos for the gallery.
   *
   * @param  int $expiration Length that the cache is valid, in seconds
   * @param  int $gallery_id
   * @return [type]             [description]
   */
  public function get_videos($expiration, $gallery_id) {
    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $cache = new Vimeography_Cache($gallery_id, $expiration);

    // If the cache file exists,
    if ( $cache->exists() ) {

      // and the cache file is expired,
      if ( ($last_modified = $cache->expired() ) !== FALSE) {

        // make the request with a last modified header.
        $result = $this->fetch($last_modified);

        // Here is where we need to check if $video_set exists, or if it
        // returned a 304, in which case, we can safely update the
        // cache's last modified and return it.
        if ( $result == NULL ) {
          $result = $cache->renew()->get();
        } else {
          // Cache the updated results.
          if ( intval($expiration) !== 0) {
            $cache->set($result);
          }
        }
      } else {

        // If it isn't expired, return it.
        $result = $cache->get();
      }
    } else {
      // If a cache doesn't exist, go get the videos, dude.
      $result = $this->fetch();

      // Cache the results.
      if ( intval($expiration) !== 0 && ( ! empty( $result->video_set ) ) ) {
        $cache->set($result);
      }
    }

    return $result;
  }

  /**
   * Fetch the videos to be displayed in the Vimeography Gallery.
   *
   * @param $last_modified
   * @return string  $response  Modified response from Vimeo.
   */
  public function fetch($last_modified = NULL) {
    if (! $this->_verify_vimeo_endpoint($this->_endpoint) )
      throw new Vimeography_Exception( sprintf( __('Endpoint %s is not valid.', 'vimeography'), $this->_endpoint ) );

    $response  = $this->_make_vimeo_request($this->_endpoint, $this->_params, $last_modified);

    // If 304 not modified, return
    if ($response == NULL) {
      return $response;
    }

    $video_set = $this->_get_video_set($response);

    if (! empty($this->_featured)) {
      $featured_response = $this->_make_vimeo_request($this->_featured, array(), NULL);
      $featured_video    = $this->_get_video_set($featured_response);
      $result_set        = $this->_arrange_featured_video($video_set, $featured_video);
    } else {
      $result_set = $video_set;
    }

    if ( isset($this->_limit) )
      $result_set = $this->_limit_video_set($result_set);

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
  private function _make_vimeo_request($endpoint, $params, $last_modified) {
    try {
      $response = $this->_vimeo->request( $endpoint, $params, 'GET', $last_modified );

      switch ($response['status']) {
        case 200:
          return $response['body'];
        case 304:
          return NULL;
        case 400:
          throw new Vimeography_Exception(
            __('a bad request made was made. ', 'vimeography') . $response['body']->error
          );
        case 401:
          throw new Vimeography_Exception(
            __('an invalid token was used for the API request. Try removing your Vimeo token on the Vimeography Pro page and following the steps again to create a Vimeo app.', 'vimeography')
          );
        case 404:
          throw new Vimeography_Exception(
            __('the plugin could not retrieve data from the Vimeo API! ', 'vimeography') . $response['body']->error
          );
        case 500:
          throw new Vimeography_Exception(
            __('looks like Vimeo is having some API issues. Try reloading, or, check back in a few minutes.', 'vimeography')
          );
        default:
          throw new Vimeography_Exception(sprintf(__('Unknown response status %1$d, %2$s', 'vimeography'), $response['status'], $response['body']->error ) );
      }
    } catch (Exception $e) {
      throw new Vimeography_Exception(
        __('the request to Vimeo failed. ', 'vimeography') . $e->getMessage()
      );
    }
  }

  /**
   * [_get_video_set description]
   * @param  [type] $body [description]
   * @return [type]       [description]
   */
  private static function _get_video_set($body) {
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
  private function _arrange_featured_video($video_set, $featured_video) {
    // Does the featured video exist in the set?
    // If so, remove it from the set and place at front.
    $found = FALSE;

    // We have to do this because if the featured video
    // exists in the collection as a contextual video,
    // it would not be removed if we only compared resouce urls
    // since they would not match.
    $featured_id = str_replace('/', '', strrchr($featured_video->link, '/'));

    foreach ($video_set as $key => $video) {
      if (strpos($video->uri, $featured_id) !== FALSE) {
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
  private function _limit_video_set($video_set) {
    if ($this->_limit < count($video_set) AND $this->_limit != 0) {
      for ($video_to_delete = (count($video_set) - 1); $video_to_delete >= $this->_limit; $video_to_delete--)
        unset($video_set[$video_to_delete]);
    }

    return $video_set;
  }
}
