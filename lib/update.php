<?php

class Vimeography_Update extends Vimeography
{
  /**
   * [$_activation_keys description]
   * @var [type]
   */
  private $_activation_keys;

  /**
   * [$_endpoint description]
   * @var string
   */
  private $_endpoint = 'http://vimeography.com/api/';

  /**
   * [$action description]
   * @var [type] update | activate
   */
  public $action;

  /**
   * [__construct description]
   */
  public function __construct()
  {
    $this->_activation_keys = get_option('vimeography_activation_keys');

    if (! empty($this->_activation_keys))
    {
      add_filter('pre_set_site_transient_update_plugins', array($this, 'vimeography_check_update'));
      add_filter('plugins_api', array($this, 'vimeography_view_version_details'), 10, 3);
    }

  }

  /**
   * Check if there is an update available for Vimeography PRO.
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
        try {
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
          throw new Vimeography_Exception(__('304 Not Modified'));
          break;
        case 401: case 500:
          $response = json_decode($request['body']);
          throw new Vimeography_Exception(__($response->message));
          break;

        default:
          throw new Vimeography_Exception('Unknown HTTP response code: ' . $response_code);
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
        return new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'));
    }

    return $original;
  }

  /**
   * [vimeography_activate description]
   * @param  [type] $key [description]
   * @return [type]      [description]
   */
  public function activate($key)
  {
    $plugins = array(
        array('name' => $response->slug, 'path' => $response->url, 'install' => $response->basename),
    );
    $this->_vimeography_mm_get_plugins($plugins);
  }

  /**
   * [_vimeography_mm_get_plugins description]
   *
   * @link http://stackoverflow.com/questions/10353859/is-it-possible-to-programmatically-install-plugins-from-wordpress-theme
   * @param  [type] $plugins [description]
   * @return [type]          [description]
   */
  private function _vimeography_mm_get_plugins($plugins)
  {
    $args = array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => false
    );

    foreach($plugins as $plugin)
    {
      $this->_vimeography_mm_plugin_download($plugin['path'], $args['path'].$plugin['name'].'.zip');
      $this->_vimeography_mm_plugin_unpack($args, $args['path'].$plugin['name'].'.zip');
      //$this->_vimeography_mm_plugin_activate($plugin['install']);
    }
  }

  /**
   * [_vimeography_mm_plugin_download description]
   * @param  [type] $url  [description]
   * @param  [type] $path [description]
   * @return [type]       [description]
   */
  private function _vimeography_mm_plugin_download($url, $path)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    if(file_put_contents($path, $data))
      return true;
    else
      return false;
  }

  /**
   * [_vimeography_mm_plugin_unpack description]
   * @param  [type] $args   [description]
   * @param  [type] $target [description]
   * @return [type]         [description]
   */
  private function _vimeography_mm_plugin_unpack($args, $target)
  {
    if($zip = zip_open($target))
    {
      while($entry = zip_read($zip))
      {
        $is_file = substr(zip_entry_name($entry), -1) == '/' ? false : true;
        $file_path = $args['path'].zip_entry_name($entry);
        if($is_file)
        {
          if(zip_entry_open($zip,$entry,"r"))
          {
            $fstream = zip_entry_read($entry, zip_entry_filesize($entry));
            file_put_contents($file_path, $fstream );
            chmod($file_path, 0777);
            //echo "save: ".$file_path."<br />";
          }
          zip_entry_close($entry);
        }
        else
        {
          if(zip_entry_name($entry))
          {
            mkdir($file_path);
            chmod($file_path, 0777);
            //echo "create: ".$file_path."<br />";
          }
        }
      }
      zip_close($zip);
    }
    if($args['preserve_zip'] === false)
    {
      unlink($target);
    }
  }

  /**
   * [_vimeography_mm_plugin_activate description]
   * @param  [type] $installer [description]
   * @return [type]            [description]
   */
  private function _vimeography_mm_plugin_activate($installer)
  {
    $current = get_option('active_plugins');
    $plugin = plugin_basename(trim($installer));

    if(!in_array($plugin, $current))
    {
      $current[] = $plugin;
      sort($current);
      do_action('activate_plugin', trim($plugin));
      update_option('active_plugins', $current);
      do_action('activate_'.trim($plugin));
      do_action('activated_plugin', trim($plugin));
      return true;
    }
    else
      return false;
  }

}