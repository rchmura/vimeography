<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Pro_About extends Vimeography_Base {
  /**
   * [$messages description]
   * @var [type]
   */
  public $messages;

  /**
   * [__construct description]
   */
  public function __construct() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $this->_validate_form();
    }
  }

  /**
   * A common function which returns the home URL.
   *
   * @access public
   * @return string
   */
  public function home_url() {
    return home_url();
  }

  /**
   * The path to the Vimeography Pro icon assets
   *
   * @return string
   */
  public function icons_url() {
    return VIMEOGRAPHY_URL . 'lib/admin/assets/img/icons/';
  }

  /**
   * Creates a nonce for the Vimeography PRO app settings form.
   *
   * @access public
   * @static
   * @return void
   */
  public static function settings_nonce() {
    return wp_nonce_field('vimeography-pro-settings','vimeography-pro-settings-verification');
  }

  /**
   * Controls any incoming POST requests.
   *
   * @access private
   * @return void
   */
  private function _validate_form() {
    if ( ! empty( $_POST['vimeography_pro_settings'] ) ) {
      $this->_vimeography_pro_validate_settings( $_POST );
    }
  }

  /**
   * Returns any saved app settings.
   *
   * @access public
   * @return void
   */
  public function access_token() {
    return substr(get_option('vimeography_pro_access_token'), -6);
  }

  /**
   * Checks the tokens provided by the user and saves them if they are valid.
   *
   * @access private
   * @param array $input
   * @return void
   */
  private function _vimeography_pro_validate_settings($input) {
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if (check_admin_referer('vimeography-pro-settings','vimeography-pro-settings-verification') ) {
      if ( isset( $input['vimeography_pro_settings']['remove_token'] ) ) {
        delete_option('vimeography_pro_access_token');
        $this->messages[] = array(
          'type' => 'updated',
          'heading' => __('Poof!', 'vimeography'),
          'message' => __('Your Vimeo access token has been removed.', 'vimeography')
        );
        return TRUE;
      }

      $output = array();
      $output['access_token'] = wp_filter_nohtml_kses($input['vimeography_pro_settings']['access_token']);

      if ($output['access_token'] == '') {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Whoops!', 'vimeography'),
          'message' => __("Don't forget to enter your Vimeo OAuth 2 access token!", 'vimeography')
        );
        return FALSE;
      }

      try {
        $vimeo = new Vimeography_Vimeo(NULL, NULL, $output['access_token']);
        $response = $vimeo->request('/me');

        if (! $response) {
          $this->messages[] = array(
            'type' => 'error',
            'heading' => __('Woah!', 'vimeography'),
            'message' => __('Looks like the Vimeo API is having some issues right now. Try this again in a little bit.', 'vimeography')
          );
          return FALSE;
        }

        switch ( $response['status'] ) {
          case 200:
            update_option('vimeography_pro_access_token', $output['access_token']);
            $this->messages[] = array(
              'type' => 'updated',
              'heading' => __('Success!', 'vimeography'),
              'message' => sprintf(
                __('Your Vimeo access token for %s has been added and saved.', 'vimeography'),
                $response['body']->name
              )
            );
            return $output;
          case 401:
            throw new Vimeography_Exception(
              __("Your Vimeo access token didn't validate. Try again, and double check that you are entering the correct token.", 'vimeography')
            );
          case 404:
            throw new Vimeography_Exception(
              __('how the heck did you score a 404?', 'vimeography'). $response['body']->error
            );
          default:
            throw new Vimeography_Exception(
              __('Unknown response status from the Vimeo API: ', 'vimeography'). $response['body']->error
            );
        }

      } catch (Vimeography_Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Dangit.', 'vimeography'),
          'message' => $e->getMessage()
        );
        return FALSE;
      }
    }
  }
}
