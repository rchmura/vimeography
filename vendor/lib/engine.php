<?php

namespace Vimeography;

/**
 * \Vimeography\Engine class
 *
 * @since  2.0
 */
class Engine {

  /**
   * The gallery id for the current gallery, if any.
   * Used to pull the associated cache file for a gallery
   * request, to look up gallery settings in the database,
   * and to namespace the gallery DOM element with an informational classname.
   *
   * @var integer
   */
  public $gallery_id;


  /**
   * Used to determine various aspects about the display preferences
   * for the current gallery request, including gallery width,
   * featured video, resource, cache expiration etc.
   *
   * @var array
   */
  public $gallery_settings;


  /**
   * Header metadata for the active theme
   *
   * Note: always be sure to set at least the $theme['version']
   * in the requests so that we know which version of the renderer to load.
   *
   * @var array
   */
  public $theme;


  /**
   * The core Vimeography request processor
   * This varies based on whether or not Vimeography Pro is installed.
   *
   * @var class object
   */
  public $core;


  /**
   * The Vimeography renderer
   *
   * Depending on which version of Vimeography we have installed, this will
   * either build the initial Vuex state tree and provide a mounting point
   * for the gallery to be injected (v2.x), or, will use Mustache.php to get
   * the contents of a mustache template and render it serverside (1.x)
   *
   * @var class
   */
  public $renderer;


  /**
   * Sets the gallery ID associated with the current request.
   *
   * @param integer $id gallery ID
   */
  public function set_gallery_id( $id ) {
    $this->gallery_id = $id;
    return $this;
  }

  /**
   * Sets the gallery settings.
   *
   * @param [type] $settings [description]
   */
  public function set_gallery_settings( $settings ) {
    $this->gallery_settings = $settings;
    return $this;
  }


  /**
   * Sets the header metadata for the active theme
   *
   * @param array $theme
   */
  public function set_theme( $theme ) {
    $this->theme = $theme;
    return $this;
  }


  /**
   * Load up the correct core and renderer based on the
   * user's installed plugins.
   *
   * This needs to be called after the set_gallery_settings()
   * has been called.
   */
  public function load() {
    /**
     * We will decide which of these to use below based on
     * the versions of Vimeography Pro (if any) and the gallery theme
     * we have installed.
     */
    require_once VIMEOGRAPHY_PATH . 'lib/core.php';
    require_once VIMEOGRAPHY_PATH . 'lib/core/basic.php';
    require_once VIMEOGRAPHY_PATH . 'lib/renderer.php';

    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/core.php';
    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/core/basic.php';
    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/renderer.php';

    if ( class_exists( '\Vimeography_Pro' ) ) {

      do_action('vimeography/load_pro');

      if (
        version_compare( VIMEOGRAPHY_PRO_VERSION, '2.0', '>=' ) &&
        version_compare( $this->theme['version'], '2.0', '>=' )
      ) {
        $this->core = new Pro\Core( $this );
        $this->renderer = new Pro\Renderer( $this ); // load 2.0 pro renderer
      } else {

        if ( version_compare( $this->theme['version'], '2.0', '>=' ) ) {
          $keys = get_site_option('vimeography_activation_keys');

          if ( ! empty( $keys ) ) {
            foreach ( $keys as $key ) {
              if ( $key->plugin_name === 'vimeography-pro' ) {
                $link = sprintf( "https://vimeography.com/checkout/?edd_license_key=%s&download_id=36", $key->activation_key );
              }
            }
          }

          $message = sprintf( __('The <strong>%s</strong> gallery theme is only compatible with <strong>Vimeography Pro 2.</strong><br /> You\'re currently using <strong>Vimeography Pro version %s.</strong> Please try updating to the latest version of Vimeography Pro to use this theme. ', 'vimeography'), $this->theme['name'], VIMEOGRAPHY_PRO_VERSION );

          if ( isset( $link ) ) {
            $message .= '<br /><br />';
            $message .= sprintf( __('If your Vimeography Pro license key is expired, you can gain access to <strong>another year of updates and support</strong> by <a href="%s">renewing your license key on this page.</a>', 'vimeography'), $link );
          }

          throw new \Vimeography_Exception( $message );
        }

        $this->core = new \Vimeography_Core_Pro( $this->gallery_settings );
        $this->renderer = new \Vimeography_Pro_Renderer( $this->gallery_settings, $this->gallery_id ); // load deprecated 1.x pro renderer
      }

    } else {
      if ( version_compare( $this->theme['version'], '2.0', '>=' ) ) {
        $this->core = new Basic\Core( $this );
        $this->renderer = new Renderer( $this ); // load 2.0 renderer
      } else {
        $this->core = new \Vimeography_Core_Basic( $this->gallery_settings );
        $this->renderer = new \Vimeography_Renderer( $this->gallery_settings, $this->gallery_id ); // load deprecated 1.x renderer
      }

    }

    return $this;
  }


  /**
   * Instruct the core class to bypass checking and setting the video cache.
   * Note: must call load() before using this method!
   *
   * @return $this
   */
  public function skip_cache() {
    $this->core->skip_cache = true;
    return $this;
  }


  /**
   * Retrieve the videos from either the remote API or the cached
   * video file based on our cache expiration and request settings.
   *
   * Note: cache can be entirely bypassed by adding ?vimeography_nocache=1 to
   * the $_GET parameters of the request.
   *
   * @return $this
   */
  public function fetch() {
    // choose a method based on version determined during load
    if ( isset( $this->core->version ) && version_compare( $this->core->version, '2.0', '>=' ) ) {
      $result = $this->core->get_videos();
    } else {
      $result = $this->core->get_videos( $this->gallery_settings['cache'], $this->gallery_id );
    }

    if ( empty( $result->video_set ) ) {
      throw new \Vimeography_Exception( __('the Vimeo source for this gallery does not have any videos.', 'vimeography') );
    }

    $this->data = $result;
    return $this;
  }


  /**
   * Formats the remote API response data
   * to better fit our templating needs.
   *
   * @return $this
   */
  public function post_process() {

    $videos = $this->data->video_set;

    require_once VIMEOGRAPHY_PATH . 'lib/helpers.php';
    $helpers = new \Vimeography_Helpers;

    /**
     * If the user prefers to limit the videos displayed to a subset
     * of the total videos returned during the request, truncate
     * them here.
     */
    if ( isset( $this->gallery_settings['limit'] ) ) {
      $videos = $helpers->limit_video_set( $videos, $this->gallery_settings['limit'] );
    }

    $videos = apply_filters('vimeography.pro.post_process', $videos);

    /**
     * Vimeography themes used to include this functionality
     * in their constructor file. Since 2.0, we'll just include
     * it here and remove it from all of the theme constructors.
     *
     * @todo
     */
    $videos = $helpers->apply_common_formatting( $videos );

    $this->data->video_set = $videos;
    return $this;
  }


  /**
   * [to_json description]
   * @return [type] [description]
   */
  public function to_json() {
    return json_encode( $this->data );
  }


  /**
   * Output the expected data based on the version combination
   * of Vimeography Pro and the gallery theme.
   *
   * We'll typically only call render() on the initial page load via shortcode.
   * Subsequent search and pagination requests will skip this and return
   * the JSON-encoded data directly via a separate method.
   *
   * - 2.0: Builds the Vuex store state and and mounts the Vue app
   * - 1.x: Fetch the Mustache template and populate it with video data
   *
   * @return string|html
   */
  public function render() {
    // choose a method based on version determined during load
    if ( isset( $this->renderer->version ) && version_compare( $this->renderer->version, '2.0', '>=' ) ) {
      return $this->renderer->prepare( $this->data )->render();
    } else {
      $this->renderer->load_theme();
      $renderer = apply_filters('vimeography/deprecated/reload-pro-renderer', $this->renderer, $this->gallery_settings, $this->gallery_id );
      return $renderer->render( $this->data );
    }
  }
}
