<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Theme_List extends Vimeography_Base {

  public $messages = array();
  public $updater;

  public function __construct() {
    $this->updater = Vimeography::get_instance()->updater;
    add_action('vimeography_action_activate_license', array( $this, 'activate_license' ) );
    add_action('vimeography_action_deactivate_license', array( $this, 'deactivate_license' ) );

    self::_remove_duplicate_keys();
  }

  /**
   * Returns a security form field for the form.
   *
   * @access public
   * @return mixed
   */
  public static function nonce() {
     return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
  }


  /**
   * Process an activate license request.
   *
   * @return [type] [description]
   */
  public function activate_license() {

    // Ignore if key is not set
    if ( ! isset( $_POST['vimeography-activation-key'] ) )
      return;

    try {
      $this->updater->activate_license( $_POST['vimeography-activation-key'] );
      $this->messages[] = array(
        'type' => 'updated',
        'heading' => __('License Key added.', 'vimeography'),
        'message' => __('Your license key has been added to this site.', 'vimeography')
      );
    } catch (Exception $e) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Dangit.', 'vimeography'),
        'message' => $e->getMessage()
      );
    }
  }

  /**
   * Process an activate license request.
   *
   * @return [type] [description]
   */
  public function deactivate_license() {

    // Ignore if key is not set
    if ( ! isset( $_POST['vimeography-activation-key'] ) )
      return;

    try {
      $this->updater->deactivate_license( $_POST['vimeography-activation-key'] );
      $this->messages[] = array(
        'type' => 'updated',
        'heading' => __('License Key deactivated.', 'vimeography'),
        'message' => __('Your license key has been removed from this site.', 'vimeography')
      );
    } catch (Exception $e) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Dangit.', 'vimeography'),
        'message' => $e->getMessage()
      );
    }

  }


  /**
   * Get the activation keys for the current site for use in the admin template.
   *
   * array(1) {
   *   [0]=>
   *   object(stdClass)#636 (7) {
   *     ["activation_key"]=>
   *     string(16) "3XAMP13"
   *     ["plugin_name"]=>
   *     string(28) "vimeography-developer-bundle"
   *     ["product_name"]=>
   *     string(16) "Developer Bundle"
   *     ["status"]=>
   *     string(5) "valid"
   *     ["expires"]=>
   *     string(19) "2018-07-22 11:49:33"
   *     ["limit"]=>
   *     string(1) "0"
   *     ["activations_left"]=>
   *     string(9) "unlimited"
   *   }
   * }
   *
   * @return [type] [description]
   */
  public static function activation_keys() {
    $licenses = get_site_option('vimeography_activation_keys');
    if ($licenses) {
      foreach ($licenses as $index => $license) {
        $license->expires = date('F j, Y', strtotime($license->expires) );
        $license->status  = ucfirst( $license->status );
        $licenses[$index] = $license;
      }
    }
    return $licenses;
  }

  /**
   * [_remove_duplicate_keys description]
   * @return [type] [description]
   */
  private static function _remove_duplicate_keys() {
    if ( get_option('vimeography_activation_keys') ) {
      $activation_keys = array_map("unserialize", array_unique(array_map("serialize", get_option('vimeography_activation_keys'))));
      update_option('vimeography_activation_keys', $activation_keys);
    }
  }
}
