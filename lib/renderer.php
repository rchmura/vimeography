<?php

namespace Vimeography;

/**
 * The old approach was loading up the theme class, setting variables,
 * filtering the data, loading dependencies, and rendering
 * theme HTML server-side
 *
 * In 2.0, let's just make the video data
 * available to the theme by setting it on a global javascript
 * variable on the window and then triggering a load event on the
 * active theme's built javascript bundle.
 *
 * The theme javascript can then take over from there, performing
 * all of the tasks that used to be left up to the theme's PHP files and
 * Mustache implementation.
 *
 * @return [type] [description]
 */
class Renderer {

  public $version = '2.0';

  public function __construct( $engine ) {
    $this->gallery_id = $engine->gallery_id;
    $this->gallery_settings = $engine->gallery_settings;
    $this->theme = $engine->theme;
  }

  /**
   * Build the initial state for the gallery.
   *
   * @param  array $result Vimeo API response
   * @return $this
   */
  public function prepare( $result ) {
    $theme_name = strtolower( $this->theme['name'] );

    // Set base data for every single gallery
    $data = array(
      'id'      => $this->gallery_id,
      'theme'   => $theme_name,
      'version' => $this->theme['version'],
      'source'  => $this->gallery_settings['source'],
      'limit'   => absint( $this->gallery_settings['limit'] ),
      'pages'   => array(
        'default' => array(),
        'filter'  => array(),
      ),
    );

    // Merge the API response from Vimeo
    $data = array_merge( $data, (array) $result );

    // We won't use Vimeo's paging object, so delete it.
    unset( $data['paging'] );

    /**
     * Strip the video ID from its uri and set it
     * as the index in the video_set array.
     *
     * Let's also save the existing sort order to the
     * `pages` property in the store. That way, we can
     * always revert to it if we end up filtering the
     * videos on the client side and want to go back "home"
     *
     * @since  2.0
     */
    foreach ( $data['video_set'] as $i => $video ) {
      $id = absint( str_replace('/', '', strrchr($video->uri, '/')) );
      $data['video_set'][$id] = $video;
      unset($data['video_set'][$i]);

      $data['pages']['default'][$data['page']][] = absint( $id );
    }

    // Set remaining JS variables
    $this->data = apply_filters('vimeography.pro.localize', $data, $this->gallery_settings);

    return $this;
  }


  /**
   * [render description]
   * @return [type] [description]
   */
  public function render() {
    return $this->hydrate( $this->data )->template( $this->data );
  }

  /**
   * [hydrate description]
   * @return [type] [description]
   */
  protected function hydrate( $data ) {

    $local_data = array(
      'l10n_print_after' => sprintf('vimeography2.galleries.%1$s["%2$s"] = %3$s',
        $data['theme'],
        $data['id'],
        json_encode( $data )
      )
    );

    $theme_name = strtolower( $this->theme['name'] );
    wp_register_script( "vimeography-{$theme_name}", $this->theme['app_js'], array(), false, true );

    if ( isset( $this->theme['app_css'] ) ) {
      wp_register_style( "vimeography-{$theme_name}", $this->theme['app_css'] );
      wp_enqueue_style("vimeography-{$theme_name}");
    }

    wp_localize_script("vimeography-{$theme_name}", 'vimeographyBuildPath', $this->theme['app_path']);

    $router_mode = apply_filters('vimeography.pro.router_mode', 'abstract');
    wp_localize_script("vimeography-{$theme_name}", 'vimeographyRouterMode', $router_mode);

    wp_localize_script("vimeography-{$theme_name}",
      "vimeography2 = window.vimeography2 || {};
      window.vimeography2.galleries = window.vimeography2.galleries || {};
      window.vimeography2.galleries.{$theme_name} = window.vimeography2.galleries.{$theme_name} || {};
      vimeography2.unused",
    $local_data);

    wp_enqueue_script("vimeography-{$theme_name}");

    return $this;
  }

  /**
   * [template description]
   * @return [type] [description]
   */
  public function template( $data ) {
    $wrapper_class = 'vimeography-theme-' . esc_attr( $data['theme'] );
    $wrapper_class = apply_filters('vimeography.gallery.wrapper_class', $wrapper_class, $data);

    ob_start();
    ?>
      <div id="vimeography-gallery-<?php esc_attr_e($data['id']); ?>" class="<?php echo $wrapper_class; ?>" data-version="<?php esc_attr_e( $data['version'] ); ?>" <?php if ( ! empty( $this->gallery_settings['width'] ) ) : ?> style="max-width: <?php esc_attr_e( $this->gallery_settings['width'] ); ?>; margin: 0 auto;" <?php endif; ?> itemscope itemtype="http://schema.org/VideoGallery">
        <div id="subbie">
          <gallery></gallery>
        </div>
      </div>
    <?php
    return ob_get_clean();
  }

  /**
   * Output all data to the current screen.
   *
   * @return string
   */
  public function debug() {
    echo '<pre>';
    print_r( $this->data );
    echo '</pre>';
    die;
  }
}
