<?php

class Vimeography_Pro_About extends Vimeography_Base
{
  public $messages;

  public function __construct()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
      $this->_validate_form();
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
    return substr(get_option('vimeography_pro_access_token'), -6);
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
        $this->messages[] = array('type' => 'success', 'heading' => __('Poof!'), 'message' => __('Your Vimeo access token has been removed.'));
        return TRUE;
      }

      $output['access_token'] = wp_filter_nohtml_kses($input['vimeography_pro_settings']['access_token']);

      if ($output['access_token'] == '')
      {
        $this->messages[] = array('type' => 'error', 'heading' => __('Whoops!'), 'message' => __('Don\'t forget to enter your Vimeo OAuth 2 access token!'));
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
            $this->messages[] = array('type' => 'success', 'heading' => __('Yeah!'), 'message' => __('Success! Your Vimeo access token for ') . $response['body']->name . __(' has been added and saved.'));
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

}