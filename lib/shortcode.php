<?php

class Vimeography_Shortcode extends Vimeography
{
  private $_atts;
  private $_content = NULL;

  public $token;
  private $_gallery_id;
  private $_gallery_settings;

  public function __construct($atts, $content)
  {
    $this->_atts = $atts;
    $this->_content = $content;

    try
    {
      $this->_gallery_id       = $this->_get_gallery_id();
      $this->_gallery_settings = $this->apply_shortcode_gallery_settings();
      $this->token             = $this->_get_gallery_token($this->_gallery_settings);

      $this->vimeography_register_customizations();
    }
    catch (Vimeography_Exception $e)
    {
      die("Error creating Vimeography gallery: ".$e->getMessage());
    }
  }

  /**
   * [_get_gallery_id description]
   * @return [type] [description]
   */
  private function _get_gallery_id()
  {
    if (isset($this->_atts['id']))
    {
      $this->_atts['id'] = intval($this->_atts['id']);
      if ($this->_atts['id'] != 0)
      {
        global $wpdb;
        $result = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_TABLE.' WHERE id = '.$this->_atts['id'].' LIMIT 1;');

        if ( empty($result) )
          throw new Vimeography_Exception('A gallery with the ID of '.$this->_atts['id'].' was not found.');

        return $result[0]->id;
      }
      else
      {
        throw new Vimeography_Exception('You entered an invalid gallery ID.');
      }
    }
    else
    {
      // ID not set, creating a gallery from shortcode
      if (! empty($content))
        throw new Vimeography_Exception('Inline galleries are not currently supported. Stay tuned!');

      return FALSE;
    }
  }

  /**
   * Let's get the data for this gallery from the db
   * @return [type] [description]
   */
  private static function _get_db_gallery_settings($id)
  {
    global $wpdb;
    $db_gallery_settings = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$id.' LIMIT 1;');

    if ( empty($db_gallery_settings) )
      throw new Vimeography_Exception('Your Vimeography gallery settings could not be found.');

    return $db_gallery_settings[0];
  }

  /**
   * [apply_shortcode_gallery_settings description]
   * @return [type] [description]
   */
  public function apply_shortcode_gallery_settings()
  {
    if (! empty($this->_gallery_id))
      $db_gallery_settings = $this->_get_db_gallery_settings($this->_gallery_id);

    // Get admin panel options
    $default_settings = get_option('vimeography_default_settings');

    $fallback_gallery_settings['theme']    = isset($db_gallery_settings->theme_name)     ? $db_gallery_settings->theme_name     : $default_settings['theme_name'];
    $fallback_gallery_settings['featured'] = isset($db_gallery_settings->featured_video) ? $db_gallery_settings->featured_video : $default_settings['featured_video'];
    $fallback_gallery_settings['endpoint'] = isset($db_gallery_settings->resource_uri)   ? $db_gallery_settings->resource_uri   : $default_settings['resource_uri'];
    $fallback_gallery_settings['cache']    = isset($db_gallery_settings->cache_timeout)  ? $db_gallery_settings->cache_timeout  : $default_settings['cache_timeout'];
    $fallback_gallery_settings['width']    = isset($db_gallery_settings->gallery_width)  ? $db_gallery_settings->gallery_width  : '';

    // Get shortcode attributes
    $shortcode_gallery_settings = shortcode_atts( array(
      'theme'    => $fallback_gallery_settings['theme'],
      'featured' => $fallback_gallery_settings['featured'],
      'source'   => $fallback_gallery_settings['endpoint'],
      'cache'    => $fallback_gallery_settings['cache'],
      'width'    => $fallback_gallery_settings['width'],
    ), $this->_atts );

    $shortcode_gallery_settings['width'] = $this->_validate_gallery_width($shortcode_gallery_settings['width']);

    if ($shortcode_gallery_settings['source'] != $fallback_gallery_settings['endpoint'])
      $shortcode_gallery_settings['source'] = Vimeography::validate_vimeo_source($shortcode_gallery_settings['source']);

    return $shortcode_gallery_settings;
  }

  /**
   * [_validate_gallery_width description]
   * @param  [type] $width [description]
   * @return [type]        [description]
   */
  private static function _validate_gallery_width($width)
  {
    if (!empty($width))
    {
      preg_match('/(\d*)(px|%?)/', $width, $matches);
      // If a number value is set...
      if (!empty($matches[1]))
      {
        // If a '%' or 'px' is set...
        if (!empty($matches[2]))
        {
          // Accept the valid matching string
          $width = $matches[0];
        }
        else
        {
          // Append a 'px' value to the matching number
          $width = $matches[1] . 'px';
        }
      }
      else
      {
        // Not a valid width
        $width = '';
      }
    }
    return $width;
  }

  /**
   * Create a token for the resulting shortcode gallery settings
   * The `option_name` column has a limit of 64 characters,
   * so we need to shorten the generated hash.
   * @return [type] [description]
   */
  private static function _get_gallery_token($shortcode)
  {
    return substr(md5(serialize($shortcode)), 0, -24);
  }

  /**
   * Creates a static custom stylesheet based on any customizations
   * the user has made to their gallery. This allows for selective namespacing
   * of the CSS selectors, giving much more control over what we target.
   *
   * This is super rad.
   *
   * @return [type] [description]
   */
  public function vimeography_register_customizations()
  {
    $customizations = get_transient('vimeography_theme_settings_' . $this->_gallery_id);
    if ( $customizations !== FALSE)
    {
      $css = '';
      $name = 'vimeography-gallery-' . $this->_gallery_id . '-custom';
      $filename = $name . '.css';
      $filepath = VIMEOGRAPHY_ASSETS_PATH . 'css/' . $filename;
      $file_url = VIMEOGRAPHY_ASSETS_URL . 'css/' . $filename;

      foreach ($customizations as $setting)
      {
        $namespace = $setting['namespace'] == TRUE ? '#vimeography-gallery-'.$this->token : '';

        for ($i = 0; $i < count($setting['targets']); $i++)
          $css .= $namespace . $setting['targets'][$i] . ' { ' . $setting['attributes'][$i] . ': ' . $setting['value'] . "; } \n";
      }

      if ( file_exists($filepath) )
      {
        $stylesheet = file_get_contents($filepath);
        if ($stylesheet !== $css)
        {
          file_put_contents($filepath, $css);
        }
      }
      else
      {
        file_put_contents($filepath, $css);
      }

      // Make sure the current theme's stylesheet handle is set as a dependency
      wp_register_style($name, $file_url, array($this->_gallery_settings['theme']));
      wp_enqueue_style($name);
    }
  }

  /**
   * [output description]
   * @return [type] [description]
   */
  public function output()
  {
    try
    {
      require_once(VIMEOGRAPHY_PATH . 'lib/core.php');
      require_once(VIMEOGRAPHY_PATH . 'lib/renderer.php');

      if ( class_exists( 'Vimeography_Pro' ) )
      {
        do_action('vimeography/load_pro');
        $vimeography = new Vimeography_Core_Pro($this->_gallery_settings);
        $renderer    = new Vimeography_Pro_Renderer($this->_gallery_settings, $this->token);
      }
      else
      {
        require_once(VIMEOGRAPHY_PATH . 'lib/core/basic.php');

        $vimeography = new Vimeography_Core_Basic($this->_gallery_settings);
        $renderer    = new Vimeography_Renderer($this->_gallery_settings, $this->token);
      }

      require_once(VIMEOGRAPHY_PATH . 'lib/cache.php');
      $cache = new Vimeography_Cache($this->_gallery_settings);

      $cache_file = VIMEOGRAPHY_CACHE_PATH . $this->token . '.cache';

      // If the cache exists,
      if ( $cache->exists($cache_file) )
      {
        // and the cache is expired,
        if ( ($last_modified = $cache->expired($cache_file) ) !== FALSE )
        {
          // make the request with a last modified header.
          $video_set = $vimeography->fetch($last_modified);

          // Here is where we need to check if $video_set exists, or if it
          // returned a 304, in which case, we can safely update the cache's last modified
          // and return it.
          if ($video_set == NULL)
          {
            $cache->renew($cache_file);
            $video_set = $cache->get($cache_file);
          }
        }
        else
        {
          // If it isn't expired, return it.
          $video_set = $cache->get($cache_file);
        }
      }
      else
      {
        // If a cache doesn't exist, go get the videos, dude.
        $video_set = $vimeography->fetch();
        $paging = $vimeography->get_paging();
      }

      // Cache the results.
      if ($this->_gallery_settings['cache'] != 0)
        $cache->set($cache_file, $video_set);

      $renderer->set_paging($paging);
      // Render that ish.
      return $renderer->render($video_set);
    }
    catch (Vimeography_Exception $e)
    {
      return "Vimeography error: ".$e->getMessage();
    }
  }

}