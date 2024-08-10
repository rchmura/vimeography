<?php

class Vimeography_Gallery_Edit extends Vimeography_Base
{
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
   * The settings defined in the theme settings file
   *
   * @since  1.3
   * @var array
   */
  protected $_theme_settings;

  public function __construct()
  {
    // Backwards compatibility
    if (!isset($this->_gallery[0])) {
      $this->_gallery[0] = new StdClass();
    }

    $this->_gallery_id = intval($_GET['id']);

    if (isset($_GET['debug'])) {
      $logged_contents = get_site_transient(
        "vimeography_gallery_" . $this->_gallery_id . "_response"
      );

      echo "<pre>";
      var_dump($logged_contents);
      echo "</pre>";
      die();
    }

    $this->load_gallery();

    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/cache.php';
    $this->_cache = new \Vimeography_Cache($this->_gallery_id);

    add_action('vimeography_action_refresh_gallery_cache', array(
      $this,
      'vimeography_refresh_gallery_cache'
    ));
    add_action('vimeography_action_refresh_gallery_appearance', array(
      $this,
      'vimeography_refresh_gallery_appearance'
    ));
    add_action('vimeography_action_set_gallery_theme', array(
      $this,
      'vimeography_set_gallery_theme'
    ));

    add_action('vimeography/reload-gallery', array($this, 'load_gallery'));

    if (isset($_GET['created']) && $_GET['created'] == 1) {
      $this->messages[] = array(
        'type' => 'updated',
        'heading' => __('Gallery created.', 'vimeography'),
        'message' => __('Well, that was easy.', 'vimeography')
      );
    }
  }

  /**
   * Load gallery on page load - this is also available through
   * the `vimeography/reload-gallery` action.
   *
   * @since  1.3
   * @return void
   */
  public function load_gallery()
  {
    global $wpdb;

    $this->_gallery = $wpdb->get_results(
        $wpdb->prepare(
            '
            SELECT *
            FROM ' . $wpdb->vimeography_gallery_meta . ' AS meta
            JOIN ' . $wpdb->vimeography_gallery . ' AS gallery
            ON meta.gallery_id = gallery.id
            WHERE meta.gallery_id = %d
            LIMIT 1;
            ',
            $this->_gallery_id
        )
    );

    if (!$this->_gallery) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Uh oh.', 'vimeography'),
        'message' => __(
          "That gallery no longer exists. It's gone. Kaput!",
          'vimeography'
        )
      );
    } else {
      if (strtolower($this->_gallery[0]->theme_name) === 'bugsauce') {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Heads up!', 'vimeography'),
          'message' => __(
            "The Bugsauce gallery theme has been discontinued in Vimeography 2. We recommend switching over to the free Harvestone gallery theme.",
            'vimeography'
          )
        );
      }

      try {
        $theme = self::_set_active_theme($this->_gallery[0]->theme_name);
      } catch (Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Heads up!', 'vimeography'),
          'message' => $e->getMessage()
        );
      }
    }

    $this->_gallery[0]->featured_video =
      $this->_gallery[0]->featured_video === 0
        ? ''
        : $this->_gallery[0]->featured_video;

    // Backwards compatibility
    $this->_gallery = apply_filters(
      'vimeography/deprecated/reload-pro-gallery-settings',
      $this,
      $this->_gallery
    );
  }

  /**
   * [_set_active_theme description]
   * @since   1.3
   * @param   string $theme_name [description]
   * @return  array Theme meta
   */
  private static function _set_active_theme($theme_name)
  {
    $vimeography = Vimeography::get_instance();
    $vimeography->addons->set_active_theme($theme_name);
    return $vimeography->addons->active_theme;
  }

  /**
   * Removes the cache file associated with the loaded gallery.
   *
   * @return void
   */
  public function vimeography_refresh_gallery_cache()
  {
    $this->nonceSecurityCheck("nonce_refresh_gallery_cache");
    if ($this->_cache->exists()) {
      $this->_cache->delete();
    }

    $this->messages[] = array(
      'type' => 'updated',
      'heading' => __('So fresh.', 'vimeography'),
      'message' => __('Your videos have been refreshed.', 'vimeography')
    );
  }
  
  /**
   * nonceSecurityCheck
   * Check nonce value in the session.
   *
   * @param  mixed $nonceKey
   * @return void
   */
  private function nonceSecurityCheck($nonceKey){
        // Vérifier que le nonce existe dans la session
        if (!isset($_SESSION[$nonceKey])) {
          wp_die(__('Security check failed.', 'vimeography'));
      }
  
      // Vérifier le nonce avec wp_verify_nonce pour plus de sécurité
      if (!wp_verify_nonce($_SESSION[$nonceKey], $nonceKey)) {
          wp_die(__('Security check failed.', 'vimeography'));
      }
  
      unset($_SESSION[$nonceKey]);
  }

  /**
   * Removes the custom CSS file associated with
   * the current gallery, if it exists.
   *
   * @return void
   */
  public function vimeography_refresh_gallery_appearance()
  {
    $this->nonceSecurityCheck("nonce_refresh_gallery_appearance");
    if (
      file_exists(
        VIMEOGRAPHY_CUSTOMIZATIONS_PATH .
          'vimeography-gallery-' .
          $this->_gallery_id .
          '-custom.css'
      )
    ) {
      unlink(
        VIMEOGRAPHY_CUSTOMIZATIONS_PATH .
          'vimeography-gallery-' .
          $this->_gallery_id .
          '-custom.css'
      );
    }

    $this->messages[] = array(
      'type' => 'updated',
      'heading' => __('Theme settings cleared.', 'vimeography'),
      'message' => __('Your gallery appearance has been reset.', 'vimeography')
    );
  }

  /**
   * Returns the gallery settings to the gallery editor template
   *
   * @since  1.3
   * @return array
   */
  public function gallery()
  {
    return apply_filters('vimeography/gallery-settings', $this->_gallery);
  }

  /**
   * Switches to the selected gallery theme
   *
   * @param  array $input posted values
   * @return void
   */
  public function vimeography_set_gallery_theme($input)
  {
    $this->nonceSecurityCheck("nonce_set_gallery_theme");
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (
      check_admin_referer(
        'vimeography-theme-action',
        'vimeography-theme-verification'
      )
    ) {
      try {
        $theme = $input['theme_name'];

        global $wpdb;

        $result = $wpdb->update(
          $wpdb->vimeography_gallery_meta,
          array('theme_name' => $theme),
          array('gallery_id' => $this->_gallery_id),
          array('%s'),
          array('%d')
        );

        if ($result === false) {
          throw new Exception(
            __('Your theme could not be updated.', 'vimeography')
          );
        }

        if (
          file_exists(
            VIMEOGRAPHY_CUSTOMIZATIONS_PATH .
              'vimeography-gallery-' .
              $this->_gallery_id .
              '-custom.css'
          )
        ) {
          unlink(
            VIMEOGRAPHY_CUSTOMIZATIONS_PATH .
              'vimeography-gallery-' .
              $this->_gallery_id .
              '-custom.css'
          );
        }

        do_action('vimeography/reload-gallery');

        $this->messages[] = array(
          'type' => 'updated',
          'heading' => __('Theme updated.', 'vimeography'),
          'message' => sprintf(
            /* translators: %s refers to the theme name */
            __( 'You are now using the "%s" theme.',  'vimeography' ),
            $theme
          )
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
}
