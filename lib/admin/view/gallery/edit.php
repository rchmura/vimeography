<?php

class Vimeography_Gallery_Edit extends Vimeography_Base
{
  protected $_gallery;

  /**
   * Current gallery ID.
   * @var int
   */
  private $_gallery_id;

  /**
   * Cache class instance.
   * @var singleton
   */
  private $_cache;

  public $theme_supports_settings = FALSE;

  /**
   * Only set if the theme supports settings.
   * @var [type]
   */
  protected $_settings_file;

  public function __construct()
  {
    $this->_gallery_id = intval($_GET['id']);

    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    $this->_cache = new Vimeography_Cache($this->_gallery_id);

    if (! empty($_POST) )
      $this->_validate_form();

    // Without the @, this generates warnings?
    // Notice: Undefined offset: 0 in /Users/davekiss/Sites/vimeography.com/wp-includes/plugin.php on line 762/780
    @add_action('wp_enqueue_scripts', $this->_load_scripts());

    global $wpdb;

    if (isset($_GET['refresh']) AND $_GET['refresh'] == 1)
      $this->_refresh_gallery_cache();

    if (isset($_GET['delete-theme-settings']) AND $_GET['delete-theme-settings'] == 1)
      $this->_refresh_gallery_appearance();

    $this->_gallery = $wpdb->get_results('
      SELECT * FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta
      JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery
      ON meta.gallery_id = gallery.id
      WHERE meta.gallery_id = '.$this->_gallery_id.'
      LIMIT 1;
    ');

    if (! $this->_gallery)
    {
      $this->messages[] = array('type' => 'error', 'heading' => 'Uh oh.', 'message' => __('That gallery no longer exists. It\'s gone. Kaput!') );
    }
    else
    {
      // Check if the active theme has a settings file.
      $settings_file = VIMEOGRAPHY_THEME_PATH . $this->_gallery[0]->theme_name . '/settings.php';

      if (file_exists($settings_file))
      {
        $this->theme_supports_settings = TRUE;
        $this->_settings_file = $settings_file;
      }
    }

    if (isset($_GET['created']) && $_GET['created'] == 1)
      $this->messages[] = array('type' => 'success', 'heading' => __('Gallery created.'), 'message' => __('Well, that was easy.') );

  }

  /**
   * [_refresh_gallery_cache description]
   * @return [type] [description]
   */
  private function _refresh_gallery_cache()
  {
    if ($this->_cache->exists())
      $this->_cache->delete();

    $this->messages[] = array('type' => 'success', 'heading' => 'So fresh.', 'message' => __('Your videos have been refreshed.') );
  }

  /**
   * [_refresh_gallery_appearance description]
   * @param  [type] $gallery_id [description]
   * @return [type]             [description]
   */
  private function _refresh_gallery_appearance()
  {
    if (file_exists(VIMEOGRAPHY_ASSETS_PATH . 'css/vimeography-gallery-' . $this->_gallery_id . '-custom.css'))
      unlink(VIMEOGRAPHY_ASSETS_PATH . 'css/vimeography-gallery-' . $this->_gallery_id . '-custom.css');

    $this->messages[] = array('type' => 'success', 'heading' => __('Theme settings cleared.'), 'message' => __('Your gallery appearance has been reset.'));
  }

  /**
   * Enqueues the scripts and styles to be loaded on the edit gallery page.
   *
   * @access private
   * @return void
   */
  private static function _load_scripts()
  {
    if (! wp_script_is('jquery-ui'))
    {
      wp_register_script('jquery-ui', "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js", false, null);
      wp_enqueue_script('jquery-ui');
    }
    wp_register_script( 'jquery-mousewheel', VIMEOGRAPHY_URL.'media/js/jquery.mousewheel.min.js', 'jquery');
    wp_register_script( 'jquery-custom-scrollbar', VIMEOGRAPHY_URL.'media/js/jquery.mCustomScrollbar.js', 'jquery');

    wp_enqueue_script( 'jquery-mousewheel');
    wp_enqueue_script( 'jquery-custom-scrollbar');
  }

  /**
  * Returns the theme settings form to the admin panel.
  *
  * @access public
  * @return void
  */
  public function vimeography_theme_settings()
  {
    if ($this->theme_supports_settings == TRUE)
    {
      // If so, include it here and loop through each setting.
      include_once $this->_settings_file;
      $results = array();

      foreach ($settings as $setting)
      {
        // If the setting type isn't set, throw an error.
        if (! isset($setting['type']))
          throw new Vimeography_Exception(__('One of your active theme settings does not specify the type of setting it is.'));

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

        if (!class_exists($class))
          throw new Vimeography_Exception( __('The "') . $setting['type'] . __('" setting type does not exist or is improperly structured.') );

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

  public static function basic_nonce()
  {
    return wp_nonce_field('vimeography-basic-action','vimeography-basic-verification');
  }

  public static function theme_nonce()
  {
    return wp_nonce_field('vimeography-theme-action','vimeography-theme-verification');
  }

  public static function theme_settings_nonce()
  {
    return wp_nonce_field('vimeography-theme-settings-action','vimeography-theme-settings-verification');
  }

  public static function get_cached_videos_nonce()
  {
    return wp_create_nonce('vimeography-get-cached-videos');
  }

  public function selected()
  {
    return array(
      $this->_gallery[0]->cache_timeout => TRUE,
    );
  }

  public function gallery()
  {
    $this->_gallery[0]->featured_video = $this->_gallery[0]->featured_video === 0 ? '' : $this->_gallery[0]->featured_video;
    return $this->_gallery;
  }

  /**
   * Controls the POST data and sends it to the proper validation function.
   *
   * @access private
   * @return void
   */
  private function _validate_form()
  {
    $id = intval($_GET['id']);

    if (! empty($_POST['vimeography_appearance_settings']) )
    {
      $messages = $this->_vimeography_validate_appearance_settings($id, $_POST['vimeography_appearance_settings']['theme_name']);
    }
    elseif (! empty($_POST['vimeography_basic_settings']) )
    {
      $messages = $this->_vimeography_validate_basic_settings($id, $_POST);
    }
    elseif (! empty($_POST['vimeography_theme_settings']) )
    {
      $messages = $this->_vimeography_validate_theme_settings($id, $_POST['vimeography_theme_settings']);
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * [_vimeography_validate_appearance_settings description]
   * @param  [type] $id    [description]
   * @param  [type] $theme [description]
   * @return [type]        [description]
   */
  private function _vimeography_validate_appearance_settings($id, $theme)
  {
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-theme-action','vimeography-theme-verification') )
    {
      try
      {
        global $wpdb;

        $result = $wpdb->update(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          array( 'theme_name' => $theme),
          array( 'gallery_id' => $id ),
          array('%s'),
          array('%d')
        );

        if ($result === FALSE)
          throw new Exception('Your theme could not be updated.');

        if (file_exists(VIMEOGRAPHY_ASSETS_PATH . 'css/vimeography-gallery-'.$id.'-custom.css'))
          unlink(VIMEOGRAPHY_ASSETS_PATH . 'css/vimeography-gallery-'.$id.'-custom.css');

        $this->messages[] = array('type' => 'success', 'heading' => __('Theme updated.'), 'message' => __('You are now using the "') . $theme . __('" theme.'));
      }
      catch (Exception $e)
      {
        $this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
      }
    }
  }

  /**
   * [_vimeography_validate_basic_settings description]
   * @param  [type] $id    [description]
   * @param  [type] $input [description]
   * @return [type]        [description]
   */
  private function _vimeography_validate_basic_settings($id, $input)
  {
    if (check_admin_referer('vimeography-basic-action','vimeography-basic-verification') )
    {
      try
      {
        global $wpdb;

        if (!empty($input['vimeography_basic_settings']['gallery_width']))
        {
          preg_match('/(\d*)(px|%?)/', $input['vimeography_basic_settings']['gallery_width'], $matches);
          // If a number value is set...
          if (!empty($matches[1]))
          {
            // If a '%' or 'px' is set...
            if (!empty($matches[2]))
            {
              // Accept the valid matching string
              $input['vimeography_basic_settings']['gallery_width'] = $matches[0];
            }
            else
            {
              // Append a 'px' value to the matching number
              $input['vimeography_basic_settings']['gallery_width'] = $matches[1] . 'px';
            }
          }
          else
          {
            // Not a valid width
            $input['vimeography_basic_settings']['gallery_width'] = '';
          }
        }
        else
        {
          // blank setting
          $input['vimeography_basic_settings']['gallery_width'] = '';
        }

        $result = $wpdb->update(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          array(
            'cache_timeout'  => $input['vimeography_basic_settings']['cache_timeout'],
            'featured_video' => $input['vimeography_basic_settings']['featured_video'],
            'gallery_width'  => $input['vimeography_basic_settings']['gallery_width']
          ),
          array( 'gallery_id' => $id ),
          array(
            '%d',
            '%s',
            '%s'
          ),
          array('%d')
        );

        if ($result === FALSE)
          throw new Exception('Your settings could not be updated.');
          //$wpdb->print_error();

        $this->_cache->delete();
        $this->messages[] = array('type' => 'success', 'heading' => __('Settings updated.'), 'message' => __('Nice work. You are pretty good at this.'));
      }
      catch (Exception $e)
      {
        $this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
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
  private function _vimeography_validate_theme_settings($id, $input)
  {
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-theme-settings-action','vimeography-theme-settings-verification') )
    {
      try
      {
        $settings = array();

        foreach ($input as $setting)
        {
          $attributes = array();

          foreach ($setting['attributes'] as $attribute)
            $attributes[] = self::_convert_jquery_css_attribute($attribute);

          $setting['attributes'] = $attributes;

          if (isset($setting['expressions']) AND ! empty($setting['expressions']))
          {

            foreach ($setting['expressions'] as $selector => $attributes)
            {
              foreach ($attributes as $attribute => $value)
              {
                $new_attr = self::_convert_jquery_css_attribute($attribute);

                unset($setting['expressions'][$selector][$attribute]);
                $setting['expressions'][$selector][$new_attr] = $value;
              }
            }
          }

          $targets = array();

          foreach ($setting['targets'] as $target)
            $targets[] = esc_attr($target);

          $setting['targets'] = $targets;
          $setting['value'] = esc_attr($setting['value']);
          $settings[] = $setting;
        }

        // Settings are ready to be generated.
        $css = '';
        $name = 'vimeography-gallery-' . $id . '-custom';
        $filename = $name . '.css';
        $filepath = VIMEOGRAPHY_ASSETS_PATH . 'css/' . $filename;
        $file_url = VIMEOGRAPHY_ASSETS_URL . 'css/' . $filename;

        foreach ($settings as $setting)
        {
          $namespace = $setting['namespace'] == TRUE ? '#vimeography-gallery-'.$id : '';
          $important = isset($setting['important']) ? ' !important' : '';

          for ($i = 0; $i < count($setting['targets']); $i++)
          {
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

        file_put_contents($filepath, $css);

        //done

        $this->messages[] = array('type' => 'success', 'heading' => __('Theme updated.'), 'message' => __('I didn\'t know that you were such a great designer!'));
      }
      catch (Exception $e)
      {
        $this->messages[] = array('type' => 'error', 'heading' => __('Oh no!'), 'message' => $e->getMessage());
      }
    }
  }

  /**
   * [_convert_jquery_css_selector description]
   * @param  [type] $selector [description]
   * @return [type]           [description]
   */
  private function _convert_jquery_css_attribute($attribute)
  {
    // Convert the jQuery attribute selector to an actual css property
    $number_of_matches = preg_match_all('/[A-Z]/', esc_attr($attribute), $capitals, PREG_OFFSET_CAPTURE);

    if ($number_of_matches == 0)
      return $attribute;

    $i = 0; // offset in case of multiple capitals
    foreach ($capitals[0] as $capital)
    {
      $attribute = strtolower(substr_replace($attribute, '-', $capital[1]+$i, 0));
      $i++;
    }
    return $attribute;
  }

}