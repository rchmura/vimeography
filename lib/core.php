<?php

namespace Vimeography;

abstract class Core {

  public $version = '2.0';

  protected $_endpoint = 'https://api.vimeo.com/';

  /**
   * Vimeo library instance
   *
   * @var instance
   */
  protected $_vimeo;

  /**
   * Access token to send along with the Vimeo request.
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
   * The resource to request in the Vimeo request
   *
   * example: `/channels/staffpicks/videos`
   *
   * @var string
   */
  protected $_resource;


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
   * [$_cache description]
   * @var [type]
   */
  protected $_cache;


  /**
   * Whether or not to bypass checking and setting
   * the cache with relevant video data.
   *
   * Can be set with $engine->skip_cache()->fetch();
   *
   * @var bool
   */
  public $skip_cache = false;


  /**
   * Length of time that a cache file is good for,
   * in seconds.
   *
   * @var integer
   */
  private $_expiration = 3600;


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
    'user.account'
  );

  /**
   * Set the class properties from the provided
   * shortcode settings array.
   */
  public function __construct( $engine ) {
    $this->gallery_id = $engine->gallery_id;
    $this->gallery_settings = $engine->gallery_settings;

    $this->_resource = $this->gallery_settings['source'];

    if ( isset( $this->gallery_settings['limit'] ) ) {
      $this->_limit = $this->gallery_settings['limit'];
    }

    if ( isset( $this->gallery_settings['featured'] ) && ! empty( $this->gallery_settings['featured'] ) ) {
      $this->_featured = '/videos/' . preg_replace( "/[^0-9]/", '', $this->gallery_settings['featured'] );
    }

    if ( isset( $engine->gallery_settings['cache'] ) ) {
      $this->_expiration = intval( $engine->gallery_settings['cache'] );
    }

    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $this->_cache = new Cache( $engine );
  }


  /**
   * [_verify_vimeo_resource description]
   * @param  [type] $resource [description]
   * @return [type]           [description]
   */
  abstract protected function _verify_vimeo_resource( $resource );


  /**
   * Retrieves the videos for the gallery.
   *
   * @return [type]             [description]
   */
  public function get_videos() {
    if ( isset( $_GET['vimeography_nocache'] ) || $this->skip_cache === true ) {
      return $this->fetch();
    }

    // If the cache file exists,
    if ( $this->_cache->exists() ) {

      // and the cache file is expired,
      if ( $this->_cache->expired() === true ) {

        // make the request with a last modified header.
        $result = $this->fetch( $this->_cache->last_modified );

        // Here is where we need to check if $video_set exists, or if it
        // returned a 304, in which case, we can safely update the
        // cache's last modified and return it.
        if ( $result === null ) {
          $result = $this->_cache->renew()->get();
        } else {
          // Cache the updated results if we can.
          $this->_set_cache( $result );
        }
      } else {
        // If it isn't expired, return it.
        $result = $this->_cache->get();

        $result = apply_filters('vimeography.pro.paginate', $result);
      }
    } else {

      // If a cache doesn't exist, go get the videos, dude.
      $result = $this->fetch();

      // Cache the results if we can.
      $this->_set_cache( $result );
    }

    return $result;
  }


  /**
   * Fetch the videos to be displayed in the Vimeography Gallery.
   *
   * @param $last_modified
   * @return string  $response  Modified response from Vimeo.
   */
  public function fetch( $last_modified = null ) {

    if ( ! $this->_verify_vimeo_resource( $this->_resource ) ) {
      throw new \Vimeography_Exception( sprintf( __('The "%s" resource is not valid.', 'vimeography'), $this->_resource ) );
    }

    $response = $this->_make_vimeo_request( $this->_resource, $this->_params, $last_modified );

    // If 304 not modified, return
    if ( $response === null ) {
      return $response;
    }

    $video_set = $response->data;

    if ( ! empty( $this->_featured ) ) {
      $featured_video = $this->_make_vimeo_request( $this->_featured );
      $result_set     = $this->_arrange_featured_video( $video_set, $featured_video );
    } else {
      $result_set = $video_set;
    }

    unset( $response->data );
    $response->video_set = $result_set;

    return $response;
  }


  /**
   * Send a cURL Wordpress request to retrieve the requested data from the Vimeo API.
   *
   * @param  string $endpoint Vimeo API endpoint
   * @return array  Response Body
   */
  private function _make_vimeo_request( $endpoint, $params = array(), $last_modified = null ) {
    try {

      /**
       * Limit the request to return only the fields that
       * Vimeography themes actually use.
       *
       * @var [type]
       */
      $fields = apply_filters( 'vimeography.request.fields', $this->fields );

      /**
       * Add parameters which are common to all requests
       */
      $params = array_merge( array(
        'fields' => implode( $fields, ',' ),
      ), $params );

      /**
       * Allows Vimeography to disclude videos in the request response
       * that cannot be embedded due to their privacy settings or domain restrictions.
       */
      $filter = apply_filters('vimeography.request.privacy.filter', 'embeddable', $this->gallery_id, $this->gallery_settings);

      if ( $filter === 'embeddable' ) {
        $params['filter'] = 'embeddable';
        $params['filter_embeddable'] = 'true';
      }

      /**
       * Set the headers to send along with the Vimeo request.
       */
      $headers = array(
        'User-Agent' => sprintf( 'Vimeography loves you (%s)', home_url() ),
      );

      if ( $last_modified !== null ) {
        $headers['If-Modified-Since'] = $last_modified;
      }

      $headers = apply_filters( 'vimeography.request.headers', $headers, $this->gallery_id, $this->gallery_settings );

      /**
       * Perform the request.
       */
      $response = $this->_vimeo->request( $endpoint, $params, 'GET', true, $headers );

      // if ( isset( $response['headers']['X-RateLimit-Limit'] ) ) {
      //   $reset_date = new \DateTime();
      //   $reset_date->setTimestamp( $response['headers']['X-RateLimit-Reset'] );

      //   $this->rate_limit = array(
      //     'limit' => $response['headers']['X-RateLimit-Limit'],
      //     'remaining' => $response['headers']['X-RateLimit-Remaining'],
      //     'reset' => $reset_date,
      //   );
      // }

      switch ( $response['status'] ) {
        case 200:
          return $response['body'];
        case 304:
          return null;
        case 400:
          throw new \Vimeography_Exception(
            __('a bad request made was made. ', 'vimeography') . $response['body']->error
          );
        case 401:
          throw new \Vimeography_Exception(
            __('an invalid token was used for the API request. Try removing your Vimeo token on the Vimeography Pro page and following the steps again to create a Vimeo app.', 'vimeography')
          );
        case 403:
          if ( $response['body']->error_code ) {
            $error_code = $response['body']->error_code;
            $developer_message = $response['body']->developer_message;

            $error = sprintf( __('Your server\'s IP address (%1$s) is currently banned from using the Vimeo API. Please contact Vimeo support at https://vimeo.com/help/contact for more information. Make sure you include your server IP address in your support request. (%1$s)', 'vimeography'), $response['headers']['X-Banned-IP'] );
            $error .= sprintf( __('<br /><br />Error #%1$d: %2$s', 'vimeography'), $error_code, $developer_message );
          } else {
            $error = __('This site does not have the proper permissions to access that Vimeo collection.', 'vimeography');
          }

          throw new \Vimeography_Exception( $error );
        case 404:
          throw new \Vimeography_Exception(
            __('the plugin could not retrieve data from the Vimeo API! ', 'vimeography') . $response['body']->error
          );
        case 429:
          throw new \Vimeography_Exception(
            __('too many requests to Vimeo, please wait a moment and try again.', 'vimeography')
          );
        case 500: case 503:
          throw new \Vimeography_Exception(
            __('looks like Vimeo is having some API issues. Try reloading, or, check back in a few minutes. You can also check http://vimeostatus.com for live updates.', 'vimeography')
          );
        default:
          throw new \Vimeography_Exception( sprintf( __( 'Unknown response status %1$d, %2$s', 'vimeography' ), $response['status'], $response['body']->error ) );
      }
    } catch (Exception $e) {
      throw new \Vimeography_Exception(
        __( 'the request to Vimeo failed. ', 'vimeography' ) . $e->getMessage()
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
  private function _arrange_featured_video( $video_set, $featured_video ) {
    // Does the featured video exist in the set?
    // If so, remove it from the set and place at front.
    $found = false;

    // We have to do this because if the featured video
    // exists in the collection as a contextual video,
    // it would not be removed if we only compared resouce urls
    // since they would not match.
    $featured_id = str_replace( '/', '', strrchr( $featured_video->link, '/' ) );

    foreach ( $video_set as $key => $video ) {
      if ( strpos( $video->uri, $featured_id ) !== false ) {
        unset( $video_set[$key] );
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


  /**
   * Set the cache file contents if our
   * gallery settings and response data meet all
   * of the required criteria.
   *
   * @param array $data
   */
  private function _set_cache( $data ) {
    if (
      ! empty( $data->video_set ) &&
      $data->page === 1 &&
      $this->_expiration !== 0 &&
      isset( $this->gallery_id )
    ) {
      $data = apply_filters( 'vimeography/cache-videos', $data, $this->gallery_id );
      $this->_cache->set( $data );
    }
  }
}
