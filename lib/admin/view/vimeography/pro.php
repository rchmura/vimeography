<?php

class Vimeography_Pro_About extends Vimeography_Base
{
  public $messages;
  public $has_pro;

	public function __construct()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
			$this->_validate_form();

    $this->has_pro = class_exists( 'Vimeography_Pro' ) ? TRUE : FALSE;
	}

	/**
	 * A common function which returns the home URL.
	 *
	 * @access public
	 * @return string
	 */
	public function home_url()
	{
		return home_url();
	}

	/**
	 * Creates a nonce for the Vimeography PRO registration form.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function registration_nonce()
	{
	   return wp_nonce_field('vimeography-pro-registration','vimeography-pro-registration-verification');
	}

	/**
	 * Creates a nonce for the Vimeography PRO app settings form.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function settings_nonce()
	{
	   return wp_nonce_field('vimeography-pro-settings','vimeography-pro-settings-verification');
	}

	/**
	 * Controls any incoming POST requests.
	 *
	 * @access private
	 * @return void
	 */
	private function _validate_form()
	{
		if (!empty($_POST['vimeography_pro_registration']))
			$this->_vimeography_pro_validate_registration($_POST);

		if (!empty($_POST['vimeography_pro_settings']))
			$this->_vimeography_pro_validate_settings($_POST);
	}

	/**
	 * Returns any saved app settings.
	 *
	 * @access public
	 * @return void
	 */
	public function access_token()
	{
    return get_option('vimeography_pro_access_token');
	}

  private function _vimeography_pro_validate_registration($input)
  {
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-pro-registration','vimeography-pro-registration-verification') )
    {
      $data['key']   = wp_filter_nohtml_kses($input['vimeography_pro_registration']['key']);
      $data['key'] = 'E25P2B0B9154';

      $request = wp_remote_post( 'http://vimeography.com/pro/register/' . $data['key'] );

      if( !is_wp_error($request) OR wp_remote_retrieve_response_code($request) === 200)
      {
        $response = json_decode($request['body']);
      }
      else
      {
        return FALSE;
      }

      if ($response->status == 'error')
      {
        foreach ($response->messages as $message)
          $this->messages[] = array('type' => 'error', 'heading' => __('Whoops!'), 'message' => __($message));

        return FALSE;
      }

      $plugins = array(
          array('name' => 'vimeography-pro', 'path' => $response->url, 'install' => 'vimeography-pro/vimeography-pro.php'),
      );
      $this->_vimeography_mm_get_plugins($plugins);

      $this->messages[] = array('type' => 'success', 'heading' => __('Congratulations!'), 'message' => __('Vimeography PRO is now installed and ready to rock!'));
    }
  }

	/**
	 * Checks the tokens provided by the user and saves them if they are valid.
	 *
	 * @access private
	 * @param array $input
	 * @return void
	 */
	private function _vimeography_pro_validate_settings($input)
	{
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if (check_admin_referer('vimeography-pro-settings','vimeography-pro-settings-verification') )
		{
		  if (isset($input['vimeography_pro_settings']['remove_token']))
		  {
  		  $result = delete_option('vimeography_pro_access_token');
        $this->messages[] = array('type' => 'success', 'heading' => __('Poof!'), 'message' => __('Your Vimeo access token have been removed.'));
        return TRUE;
		  }

      $output['access_token'] = wp_filter_nohtml_kses($input['vimeography_pro_settings']['access_token']);

  		if ($output['access_token'] == '')
  		{
        $this->messages[] = array('type' => 'error', 'heading' => __('Whoops!'), 'message' => __('Don\'t forget to enter your Vimeo access token!'));
        return FALSE;
  		}

  		try
  		{
				require_once(VIMEOGRAPHY_PATH . 'vendor/vimeo.php-master/vimeo.php');

				$vimeo = new Vimeo(NULL, NULL, $output['access_token']);
				$response = $vimeo->request('/me');

        if (! $response)
        {
          $this->messages[] = array('type' => 'error', 'heading' => __('Woah!'), 'message' => __('Looks like the Vimeo API is having some issues right now. Try this again in a little bit.'));
          return FALSE;
        }

				switch ($response['status'])
				{
					case 200:
						update_option('vimeography_pro_access_token', $output['access_token']);
						$this->messages[] = array('type' => 'success', 'heading' => __('Yeah!'), 'message' => __('Success! Your Vimeo access token for ') . $response['body']->name . __(' have been added and saved.'));
						return $output;
						break;
					case 401:
						throw new Vimeography_Exception(__('Your Vimeo access token didn\'t validate. Try again, and double check that you are entering the correct token.'));
						break;
					case 404:
						throw new Vimeography_Exception('how the heck did you score a 404?'. $response['body']->error);
						break;
					default:
						throw new Vimeography_Exception('Unknown response status from the Vimeo API: '. $response['body']->error);
						break;
				}

  		}
  		catch (Vimeography_Exception $e)
  		{
        $this->messages[] = array('type' => 'error', 'heading' => __('Dangit.'), 'message' => $e->getMessage());
        return FALSE;
  		}

    }
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