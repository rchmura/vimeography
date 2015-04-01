<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Gallery_Edit extends Vimeography_Base {

  /**
   * Current loaded gallery
   *
   * @var array
   */
  protected $_gallery = array();

  /**
   * Current gallery ID set by GET param
   *
   * @var int
   */
  protected $_gallery_id;

  /**
   * Cache class instance.
   *
   * @var object
   */
  protected $_cache;

  /**
   * [$theme_supports_settings description]
   *
   * @var boolean
   */
  public $theme_supports_settings = FALSE;

  /**
   * Only set if the theme supports settings.
   * @var [type]
   */
  protected $_settings_file;

  public function __construct() {

    // Backwards compatibility
    if ( ! isset($this->_gallery[0]) ) {
      $this->_gallery[0] = new StdClass();
    }

    $this->_gallery_id = intval( $_GET['id'] );

    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $this->_cache = new Vimeography_Cache( $this->_gallery_id );

    add_action('vimeography_action_refresh_gallery_cache', array($this, 'vimeography_refresh_gallery_cache') );
    add_action('vimeography_action_refresh_gallery_appearance', array($this, 'vimeography_refresh_gallery_appearance') );
    add_action('vimeography_action_set_gallery_theme', array($this, 'vimeography_set_gallery_theme') );
    add_action('vimeography_action_validate_basic_gallery_settings', array($this, 'vimeography_validate_basic_gallery_settings') );
    add_action('vimeography_action_validate_theme_settings', array($this, 'vimeography_validate_theme_settings') );

    if ( isset( $_GET['created'] ) && $_GET['created'] == 1) {
      $this->messages[] = array(
        'type' => 'success',
        'heading' => __('Gallery created.', 'vimeography'),
        'message' => __('Well, that was easy.', 'vimeography')
      );
    }

  }

  /**
   * Removes the cache file associated with the loaded gallery.
   *
   * @return void
   */
  public function vimeography_refresh_gallery_cache() {
    if ( $this->_cache->exists() ) {
      $this->_cache->delete();
    }

    $this->messages[] = array(
      'type' => 'success',
      'heading' => __('So fresh.', 'vimeography'),
      'message' => __('Your videos have been refreshed.', 'vimeography')
    );
  }

  /**
   * Removes the custom CSS file associated with
   * the current gallery, if it exists.
   *
   * @return void
   */
  public function vimeography_refresh_gallery_appearance() {
    if ( file_exists( VIMEOGRAPHY_CUSTOMIZATIONS_PATH . 'vimeography-gallery-' . $this->_gallery_id . '-custom.css' ) ) {
      unlink( VIMEOGRAPHY_CUSTOMIZATIONS_PATH . 'vimeography-gallery-' . $this->_gallery_id . '-custom.css' );
    }

    $this->messages[] = array(
      'type' => 'success',
      'heading' => __('Theme settings cleared.', 'vimeography'),
      'message' => __('Your gallery appearance has been reset.', 'vimeography')
    );
  }

  /**
   * Returns the theme settings form to the admin panel.
   *
   * @access public
   * @return void
   */
  public function vimeography_theme_settings() {
    if ($this->theme_supports_settings == TRUE) {
      // If so, include it here and loop through each setting.
      include_once $this->_settings_file;
      $results = array();

      foreach ($settings as $setting) {
        // If the setting type isn't set, throw an error.
        if (! isset($setting['type']))
          throw new Vimeography_Exception(__('One of your active theme settings does not specify the type of setting it is.', 'vimeography'));

        if ($setting['pro'] === TRUE AND $this->has_pro() === FALSE)
          continue;

        if (file_exists(VIMEOGRAPHY_PATH . 'lib/admin/view/theme/settings/'.$setting['type'].'.php'))
        {
          require_once VIMEOGRAPHY_PATH . 'lib/admin/view/theme/settings/'.$setting['type'].'.php';
          $template_dir = VIMEOGRAPHY_PATH;
        }
        elseif ( defined('VIMEOGRAPHY_PRO_PATH') )
        {
          if (file_exists(VIMEOGRAPHY_PRO_PATH . 'lib/admin/view/theme/settings/'.$setting['type'].'.php'))
          {
            require_once VIMEOGRAPHY_PRO_PATH . 'lib/admin/view/theme/settings/'.$setting['type'].'.php';
            $template_dir = VIMEOGRAPHY_PRO_PATH;
          }
        }
        else
        {
          continue;
        }

        // Otherwise, include the setting if there are no errors with the class.
        $class = 'Vimeography_Theme_Settings_'.ucfirst($setting['type']);

        if (!class_exists($class)) {
          throw new Vimeography_Exception( sprintf(__('The "%s" setting type does not exist or is improperly structured.', 'vimeography'), $setting['type'] ) );
        }

        // Load the template file for the current theme setting.
        $mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader($template_dir . 'lib/admin/templates/theme/settings'),));

        // Populate the setting type class
        $view = new $class($setting);
        $template = $mustache->loadTemplate($setting['type']);

        //and render the results from the template.
        $results[]['setting'] = $template->render($view);
      }

      return $results;
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Generates a nonce field for the Vimeography basic gallery settings.
   *
   * @return string  html
   */
  public static function basic_nonce() {
    return wp_nonce_field('vimeography-basic-action','vimeography-basic-verification');
  }

  /**
   * Generates a nonce field for the Vimeography theme gallery settings.
   *
   * @return string  html
   */
  public static function theme_nonce() {
    return wp_nonce_field('vimeography-theme-action','vimeography-theme-verification');
  }

  /**
   * Generates a nonce field for the Vimeography theme settings.
   *
   * @return string  html
   */
  public static function theme_settings_nonce() {
    return wp_nonce_field('vimeography-theme-settings-action','vimeography-theme-settings-verification');
  }

  /**
   * Returns all of the gallery parameters to be used in the admin.
   *
   * @return array containing gallery object
   */
  public function gallery() {

    global $wpdb;

    $this->_gallery = $wpdb->get_results('
      SELECT * FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta
      JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery
      ON meta.gallery_id = gallery.id
      WHERE meta.gallery_id = '.$this->_gallery_id.'
      LIMIT 1;
    ');

    if ( ! $this->_gallery ) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Uh oh.', 'vimeography'),
        'message' => __("That gallery no longer exists. It's gone. Kaput!", 'vimeography')
      );
    } else {
      $vimeography = Vimeography::get_instance();

      if ( ! $vimeography->addons->active_theme ) {
        $vimeography->addons->set_active_theme($this->_gallery[0]->theme_name);
      }

      $theme = $vimeography->addons->active_theme;

      if ( file_exists( $theme['settings_file'] ) ) {
        $this->theme_supports_settings = TRUE;
        $this->_settings_file = $theme['settings_file'];
      }
    }

    $this->_gallery[0]->featured_video = $this->_gallery[0]->featured_video === 0 ? '' : $this->_gallery[0]->featured_video;

    // Backwards compatibility
    $this->_gallery = apply_filters('vimeography/deprecated/reload-pro-gallery-settings', $this, $this->_gallery);

    return apply_filters('vimeography/gallery-settings', $this->_gallery);
  }

  /**
   * [selected description]
   * @return [type] [description]
   */
  public function selected() {
    return array(
      $this->_gallery[0]->cache_timeout => TRUE,
    );
  }

  /**
   * [_vimeography_validate_appearance_settings description]
   * @param  [type] $id    [description]
   * @param  [type] $theme [description]
   * @return [type]        [description]
   */
  public function vimeography_set_gallery_theme($input) {
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-theme-action','vimeography-theme-verification') ) {
      try {
        $theme = $input['theme_name'];

        global $wpdb;

        $result = $wpdb->update(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          array( 'theme_name' => $theme),
          array( 'gallery_id' => $this->_gallery_id ),
          array('%s'),
          array('%d')
        );

        if ($result === FALSE) {
          throw new Exception(__('Your theme could not be updated.', 'vimeography') );
        }

        if ( file_exists(VIMEOGRAPHY_CUSTOMIZATIONS_PATH . 'vimeography-gallery-' . $this->_gallery_id . '-custom.css') ) {
          unlink(VIMEOGRAPHY_CUSTOMIZATIONS_PATH . 'vimeography-gallery-' . $this->_gallery_id . '-custom.css');
        }

        $this->messages[] = array(
          'type' => 'success',
          'heading' => __('Theme updated.', 'vimeography'),
          'message' => sprintf( __('You are now using the "%s" theme.', 'vimeography'), $theme )
        );
      } catch (Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Ruh roh.', 'vimeography'),
          'message' => $e->getMessage()
        );
      }
    }
  }

  /**
   * [_vimeography_validate_basic_settings description]
   *
   * @param  array  $input unsanitized POST data
   * @return [type]        [description]
   */
  public function vimeography_validate_basic_gallery_settings($input) {
    if (check_admin_referer('vimeography-basic-action','vimeography-basic-verification') ) {
      try {
        global $wpdb;

        $input['vimeography_basic_settings']['video_limit'] = intval($input['vimeography_basic_settings']['video_limit']) <= 25 ? $input['vimeography_basic_settings']['video_limit'] : 25;

        if ( ! empty( $input['vimeography_basic_settings']['gallery_width'] ) ) {
          preg_match('/(\d*)(px|%?)/', $input['vimeography_basic_settings']['gallery_width'], $matches);
          // If a number value is set...
          if ( ! empty( $matches[1] ) ) {
            // If a '%' or 'px' is set...
            if ( ! empty( $matches[2] ) ) {
              // Accept the valid matching string
              $input['vimeography_basic_settings']['gallery_width'] = $matches[0];
            } else {
              // Append a 'px' value to the matching number
              $input['vimeography_basic_settings']['gallery_width'] = $matches[1] . 'px';
            }
          } else {
            // Not a valid width
            $input['vimeography_basic_settings']['gallery_width'] = '';
          }
        } else {
          // blank setting
          $input['vimeography_basic_settings']['gallery_width'] = '';
        }

        $result = $wpdb->update(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          array(
            'cache_timeout'  => $input['vimeography_basic_settings']['cache_timeout'],
            'video_limit'    => $input['vimeography_basic_settings']['video_limit'],
            'featured_video' => $input['vimeography_basic_settings']['featured_video'],
            'gallery_width'  => $input['vimeography_basic_settings']['gallery_width']
          ),
          array( 'gallery_id' => $this->_gallery_id ),
          array(
            '%d',
            '%d',
            '%s',
            '%s'
          ),
          array('%d')
        );

        if ($result === FALSE) {
          throw new Exception( __('Your settings could not be updated.', 'vimeography') );
          //$wpdb->print_error();
        }

        if ( $this->_cache->exists() ) {
          $this->_cache->delete();
        }

        $this->messages[] = array(
          'type' => 'success',
          'heading' => __('Settings updated.', 'vimeography'),
          'message' => __('Nice work. You are pretty good at this.', 'vimeography')
        );
      } catch (Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Ruh roh.', 'vimeography'),
          'message' => $e->getMessage()
        );
      }
    }
  }

  /**
   * Creates a static custom stylesheet based on any customizations
   * the user has made to their gallery. This allows for selective namespacing
   * of the CSS selectors, giving much more control over what we target.
   *
   * This is super rad.
   *
   * @return void
   */
  public function vimeography_validate_theme_settings($input) {

    if ( isset( $input['vimeography_theme_settings_serialized'] ) AND ! empty($input['vimeography_theme_settings_serialized']) ) {
      $input = unserialize( stripslashes( $input['vimeography_theme_settings_serialized'] ) );
    } else {
      $input = $input['vimeography_theme_settings'];
    }

    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-theme-settings-action','vimeography-theme-settings-verification') ) {
      try {
        $settings = array();

        foreach ($input as $setting) {
          $attributes = array();

          foreach ($setting['attributes'] as $attribute) {
            $attributes[] = self::_convert_jquery_css_attribute($attribute);
          }

          $setting['attributes'] = $attributes;

          if (isset($setting['expressions']) AND ! empty($setting['expressions'])) {

            foreach ($setting['expressions'] as $selector => $attributes) {
              foreach ($attributes as $attribute => $value) {
                $new_attr = self::_convert_jquery_css_attribute($attribute);

                unset($setting['expressions'][$selector][$attribute]);
                $setting['expressions'][$selector][$new_attr] = $value;
              }
            }
          }

          $targets = array();

          foreach ($setting['targets'] as $target) {
            $targets[] = esc_attr($target);
          }

          $setting['targets'] = $targets;
          $setting['value'] = esc_attr($setting['value']);
          $settings[] = $setting;
        }

        // Settings are ready to be generated.
        $css = '';
        $filename = 'vimeography-gallery-' . $this->_gallery_id . '-custom.css';
        $filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $filename;

        foreach ($settings as $setting) {
          $namespace = $setting['namespace'] == TRUE  ? '#vimeography-gallery-'.$this->_gallery_id : '';
          $important = isset( $setting['important'] ) ? ' !important' : '';

          for ($i = 0; $i < count($setting['targets']); $i++) {
            // If this is an expression, change the value to the expression value calculated by the appearance widget.
            if (isset($setting['expressions']) AND array_key_exists($setting['targets'][$i], $setting['expressions']))
            {
              if (array_key_exists($setting['attributes'][$i], $setting['expressions'][$setting['targets'][$i]]))
              {
                $setting['value'] = $setting['expressions'][$setting['targets'][$i]][$setting['attributes'][$i]];
              }
            }

            $css .= $namespace . $setting['targets'][$i] . ' { ' . $setting['attributes'][$i] . ': ' . $setting['value'] . $important . "; } \n";
          }
        }

        // Do filesystem stuffs here… a simple file_put_contents would be nice.
        $_POST['vimeography_theme_settings_serialized'] = serialize($input);

        $url = wp_nonce_url(
                network_admin_url(
                  add_query_arg(
                    array( 'page' => 'vimeography-edit-galleries', 'id' => $this->_gallery_id ),
                    'admin.php'
                  )
                ),
                'vimeography-theme-settings-action',
                'vimeography-theme-settings-verification'
              );

        $filesystem = new Vimeography_Filesystem( $url, array('vimeography_theme_settings_serialized', 'vimeography-action') );

        if ( $filesystem->connect() ) {
          global $wp_filesystem;

          if ( ! $wp_filesystem->exists( VIMEOGRAPHY_CUSTOMIZATIONS_PATH ) ) {
            if ( ! wp_mkdir_p( VIMEOGRAPHY_CUSTOMIZATIONS_PATH ) ) {
              throw new Exception(
                __('Vimeography could not create the customizations directory.', 'vimeography')
              );
            }
          }

          // If there is an error, output a message for the user to see
          if ( ! $wp_filesystem->put_contents( $filepath, $css, FS_CHMOD_FILE ) ) {
            throw new Exception( __('There was an error writing your file. Please try again!', 'vimeography') );
          }
        } else {
          exit;
        }

        $this->messages[] = array(
          'type' => 'success',
          'heading' => __('Theme updated.', 'vimeography'),
          'message' => __("I didn't know that you were such a great designer!", 'vimeography')
        );
      } catch (Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Oh no!', 'vimeography'),
          'message' => $e->getMessage()
        );
      }
    }
  }

  /**
   * Convert the jQuery attribute selector to an actual css property
   *
   * @param  [type] $attribute [description]
   * @return [type]            [description]
   */
  private static function _convert_jquery_css_attribute($attribute) {
    $number_of_matches = preg_match_all('/[A-Z]/', esc_attr($attribute), $capitals, PREG_OFFSET_CAPTURE);

    if ($number_of_matches == 0) {
      return $attribute;
    }

    $i = 0; // offset in case of multiple capitals
    foreach ($capitals[0] as $capital) {
      $attribute = strtolower(substr_replace($attribute, '-', $capital[1]+$i, 0));
      $i++;
    }
    return $attribute;
  }
}
