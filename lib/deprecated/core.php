<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Vimeography_Core {

  const ENDPOINT  = 'https://api.vimeo.com/';

  /**
   * Vimeo library instance
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
   * The gallery id associated with the current request, if any.
   * Used to look up the corresponding cache file.
   *
   * @since 2.0
   * @var [type]
   */
  protected $_gallery_id;

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
   * Request the following fields to be returned from the Vimeo API.
   *
   * @var array
   */
  public $fields = array(
    'name',
    'uri',
    'link',
    'description',
    'duration',
    'width',
    'height',
    'embed',
    'tags.name',
    'tags.canonical',
    'created_time',
    'stats',
    'pictures',
    'status',
  );

  /**
   * Set the class properties from the provided
   * shortcode settings array.
   *
   * @param array $settings
   */
  public function __construct($settings) {
    $this->_endpoint = $settings['source'];

    if ( isset( $settings['limit'] ) ) {
      $this->_limit = $settings['limit'];
    }

    if ( isset( $settings['featured'] ) && ! empty( $settings['featured'] ) ) {
      $this->_featured = '/videos/' . preg_replace( "/[^0-9]/", '', $settings['featured'] );
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

    if ( isset( $_GET['vimeography_nocache'] ) && $_GET['vimeography_nocache'] == 1 ) {
      return $this->fetch();
    }

    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/cache.php';
    $cache = new Vimeography_Cache($gallery_id, $expiration);

    // If the cache file exists,
    if ( $cache->exists() ) {

      // and the cache file is expired,
      if ( ( $last_modified = $cache->expired() ) !== false) {

        // make the request with a last modified header.
        $result = $this->fetch( $last_modified );

        // Here is where we need to check if $video_set exists, or if it
        // returned a 304, in which case, we can safely update the
        // cache's last modified and return it.
        if ( $result === null ) {
          $result = $cache->renew()->get();
        } else {
          // Cache the updated results.
          if ( intval( $expiration ) !== 0) {
            $cache->set( $result );
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
      if ( intval( $expiration ) !== 0 && ( ! empty( $result->video_set ) ) ) {
        $result = apply_filters( 'vimeography/cache-videos', $result, $gallery_id );
        $cache->set( $result );
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
  public function fetch( $last_modified = NULL ) {

    if ( ! $this->_verify_vimeo_endpoint( $this->_endpoint ) ) {
      throw new Vimeography_Exception( sprintf( __('Endpoint %s is not valid.', 'vimeography'), $this->_endpoint ) );
    }

    $response = $this->_make_vimeo_request($this->_endpoint, $this->_params, $last_modified);

    // If 304 not modified, return
    if ( $response == NULL ) {
      return $response;
    }

    $video_set = $response->data;

    if ( ! empty( $this->_featured ) ) {
      $featured_video = $this->_make_vimeo_request( $this->_featured );
      $result_set     = $this->_arrange_featured_video( $video_set, $featured_video );
    } else {
      $result_set = $video_set;
    }

    unset($response->data);
    $response->video_set = $result_set;

    return $response;
  }

  /**
   * Send a cURL Wordpress request to retrieve the requested data from the Vimeo API.
   *
   * @param  string $endpoint Vimeo API endpoint
   * @return array  Response Body
   */
  private function _make_vimeo_request($endpoint, $params = array(), $last_modified = null) {
    try {

      /**
       * Only add request parameters if they don't already exist within
       * a query string in the $endpoint
       */
      $query = parse_url($endpoint, PHP_URL_QUERY);

      if ( empty( $query ) ) {

        // Limit the request to return only the fields that
        // Vimeography themes actually use.
        $fields = apply_filters( 'vimeography.request.fields', $this->fields );

        // Add parameters which are common to all requests
        $params = array_merge( array(
          'fields' => implode( $fields, ',' ),
          'filter' => 'embeddable',
          'filter_embeddable' => 'true',
        ), $params );

      }

      $headers = array(
        'User-Agent' => sprintf( 'Vimeography loves you (%s)', home_url() ),
      );

      if ( $last_modified !== null ) {
        $headers['If-Modified-Since'] = $last_modified;
      }

      $response = $this->_vimeo->request( $endpoint, $params, 'GET', true, $headers );

      if ( isset( $response['headers']['X-RateLimit-Limit'] ) ) {
        $this->rate_limit = array(
          'limit' => $response['headers']['X-RateLimit-Limit'],
          'remaining' => $response['headers']['X-RateLimit-Remaining'],
          'reset' => new \DateTime( $response['headers']['X-RateLimit-Reset'] )
        );
      }

      switch ( $response['status'] ) {
        case 200:
          return $response['body'];
        case 304:
          return null;
        case 400:
          throw new Vimeography_Exception(
            __('a bad request made was made. ', 'vimeography') . $response['body']->error
          );
        case 401:
          throw new Vimeography_Exception(
            __('an invalid token was used for the API request. Try removing your Vimeo token on the Vimeography Pro page and following the steps again to create a Vimeo app.', 'vimeography')
          );
        case 403:
          $error_code = $response['body']->error_code;
          $developer_message = $response['body']->developer_message;

          $error = sprintf( __('Your server\'s IP address (%1$s) is currently banned from using the Vimeo API. Please contact Vimeo support at https://vimeo.com/help/contact for more information. Make sure you include your server IP address in your support request. (%1$s)', 'vimeography'), $response['headers']['X-Banned-IP'] );
          $error .= sprintf( __('<br /><br />Error #%1$d: %2$s', 'vimeography'), $error_code, $developer_message );

          throw new Vimeography_Exception( $error );
        case 404:
          throw new Vimeography_Exception(
            __('the plugin could not retrieve data from the Vimeo API! ', 'vimeography') . $response['body']->error
          );
        case 500: case 503:
          throw new Vimeography_Exception(
            __('looks like Vimeo is having some API issues. Try reloading, or, check back in a few minutes. You can also check http://vimeostatus.com for live updates.', 'vimeography')
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
   * Arrange the video set to contain the video to be featured at the beginning of the set.
   *
   * @param  array $video_set      Vimeo Videos
   * @param  array $featured_video a Vimeo Video
   * @return string $video_set      Arranged array of Vimeo Videos
   */
  private function _arrange_featured_video($video_set, $featured_video) {
    // Does the featured video exist in the set?
    // If so, remove it from the set and place at front.
    $found = false;

    // We have to do this because if the featured video
    // exists in the collection as a contextual video,
    // it would not be removed if we only compared resouce urls
    // since they would not match.
    $featured_id = str_replace('/', '', strrchr($featured_video->link, '/'));

    foreach ($video_set as $key => $video) {
      if (strpos($video->uri, $featured_id) !== false) {
        unset($video_set[$key]);
        $found = true;
      }
    }

    // If it does not exist, we need to remove the last video in the
    // video set and place the featured video up front.
    if ( $found == false && $this->_limit == count($video_set) ) {
      array_pop($video_set);
    }

    // Add the featured video to the front.
    array_unshift( $video_set, $featured_video );

    return array_values( $video_set );
  }
}
