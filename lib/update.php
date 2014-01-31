<?php

class Vimeography_Update extends Vimeography
{
  /**
   * All of the activation keys that the user has stored.
   * @var array
   */
  private $_activation_keys;

  /**
   * The endpoint of the Vimeography Updater API.
   * @var string
   */
  private $_endpoint = 'http://vimeography.com/api/';

  /**
   * Whether we are updating or activating.
   * @var string update | activate
   */
  public $action;

  /**
   * [$_installed_themes description]
   * @var array
   */
  private $_installed_themes = array();

  /**
   * [__construct description]
   * @param [type] $installed_themes [description]
   */
  public function __construct()
  {
    $this->_activation_keys  = get_option('vimeography_activation_keys');

    if (! empty($this->_activation_keys))
    {
      add_filter('pre_set_site_transient_update_plugins', array($this, 'vimeography_check_update'));
      add_filter('plugins_api', array($this, 'vimeography_view_version_details'), 10, 3);
    }

  }

  /**
   * [vimeography_check_installed_theme_activations description]
   * @param  [type] $installed_themes [description]
   * @return [type]                   [description]
   */
  public function vimeography_check_installed_theme_activations($installed_themes)
  {
    global $pagenow;

    if ($pagenow === 'plugins.php' AND ! empty($installed_themes))
    {
      foreach ($installed_themes as $theme)
      {
        $this->_installed_themes[$theme['slug']] = $theme['name'];

        $hook = 'after_plugin_row_' . $theme['basename'];
        add_action( $hook, array($this, 'vimeography_theme_update_message') );
      }
    }
  }

  /**
   * Check if there is an update available for each activation key saved.
   *
   * @param  object $transient Contents of the ‘update_plugins‘ site transient.
   * @return object $transient Modified or Original transient.
   */
  public function vimeography_check_update( $transient )
  {
    // checked (array) – The list of checked plugins and their version
    if (empty($transient->checked))
      return $transient;

      $this->action = 'update';

      foreach ($this->_activation_keys as $plugin)
      {
        try
        {
          $remote_info = self::vimeography_get_remote_info($plugin->activation_key);

          // create new object for update
          $obj = new stdClass();
          $obj->slug        = $remote_info->slug;
          $obj->new_version = $remote_info->version;
          $obj->url         = $remote_info->homepage;
          $obj->package     = $remote_info->download_link;

          // add to transient
          $transient->response[ $remote_info->basename ] = $obj;
        }
        catch (Vimeography_Exception $e)
        {
          // Log $e->getMessage(); to see why updates aren't occuring.
          continue;
        }
      }

    return $transient;
  }

  /**
   * Retrieves the current release info from the remote server.
   *
   * @return string|bool JSON String or FALSE
   */
  public function vimeography_get_remote_info($key)
  {
    $request = wp_remote_post( $this->_endpoint . $this->action . '/' . $key );

    if( !is_wp_error($request) )
    {
      $response_code = wp_remote_retrieve_response_code( $request );

      switch($response_code)
      {
        case 200:

          $response = json_decode($request['body']);

          if ($this->action == 'update')
            $response->sections = (array) $response->sections;

          return $response;
          break;

        case 304:
          // Plugin up to date.
          throw new Vimeography_Exception(__('304 Not Modified', 'vimeography'));
          break;
        case 401: case 500:
          $response = json_decode($request['body']);
          throw new Vimeography_Exception(__($response->message));
          break;

        default:
          throw new Vimeography_Exception(__('Unknown HTTP response code: ', 'vimeography') . $response_code);
          break;
      }

    }
    else
    {
      return FALSE;
    }

  }

  /**
   * Add a custom, self-hosted description to the plugin info/version details screen.
   *
   * @link http://wp.tutsplus.com/tutorials/plugins/a-guide-to-the-wordpress-http-api-automatic-plugin-updates/
   * @param  bool $original
   * @param  string $action [description]
   * @param  object $args    [description]
   * @return bool|object    [description]
   */
  public function vimeography_view_version_details( $original, $action, $args )
  {
    foreach ($this->_activation_keys as $plugin)
    {
      // Is the current plugin API calling our plugin?
      if ($args->slug != $plugin->plugin_name)
        continue;

      $this->action = 'update';

      if ($action == 'plugin_information')
        $original = self::vimeography_get_remote_info($plugin->activation_key);

      if (! $original)
        return new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', 'vimeography'));
    }

    return $original;
  }

  /**
   * Add a reminder to add the activation key to receives updates for the installed Vimeography theme.
   * @param  [type] $plugin_file [description]
   * @return [type]              [description]
   */
  public function vimeography_theme_update_message($plugin_file)
  {
    $match = FALSE;
    $plugin_basename = substr($plugin_file, 0, strpos($plugin_file, "/"));

    if (! empty($this->_activation_keys))
    {
      foreach ($this->_activation_keys as $key)
      {
        if ($key->plugin_name === $plugin_basename)
          $match = TRUE;
      }
    }

    if ( in_array($plugin_basename, array('vimeography-bugsauce', 'vimeography-single', 'vimeography-ballistic')) )
      $match = TRUE;

    if (! $match)
    {
      $name = $this->_installed_themes[$plugin_basename];

      echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">';
      echo '<span style="border-right: 1px solid #DFDFDF; margin-right: 5px;">';
      printf( __('Hey! Don\'t forget to <a title="Activate my Vimeography Product" href="%1$sadmin.php?page=vimeography-manage-activations">enter your activation key</a> to receive the latest updates for the %2$s plugin.', 'vimeography'), get_admin_url(), $name );
      echo '</span>';
      echo '</div></td></tr>';
    }
  }

}