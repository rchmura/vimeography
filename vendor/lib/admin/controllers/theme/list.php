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
    $this->_check_licenses();
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

  /**
   * Make sure licenses are up to date with current information.
   * Here's what a successful response looks like:
   *
   * public 'success' => boolean true
   * public 'license' => string 'site_inactive' (length=13)
   * public 'item_name' => string '' (length=0)
   * public 'expires' => string '2018-07-22 11:49:33' (length=19)
   * public 'payment_id' => string '1234' (length=4)
   * public 'customer_name' => string ' ' (length=1)
   * public 'customer_email' => string 'email@gmail.com' (length=21)
   * public 'license_limit' => string '0' (length=1)
   * public 'site_count' => int 2
   * public 'activations_left' => string 'unlimited' (length=9)
   *
   * @since  1.3.2
   * @return [type] [description]
   */
  private function _check_licenses() {
    $licenses = get_site_option('vimeography_activation_keys');

    if ($licenses) {
      foreach ($licenses as $index => $license) {

        // Retrieve up-to-date
        $result = $this->updater->check_license( $license );

        // remove license if not authorized on this site.
        if ( $result->license == 'site_inactive' ) {
          unset( $licenses[$index] );
          continue;
        }

        $license->status  = $result->license;
        $license->expires = $result->expires;
        $license->limit   = $result->license_limit;
        $license->activations_left = $result->activations_left;

        $licenses[$index] = $license;
      }

      update_site_option('vimeography_activation_keys', $licenses);
    }
  }
}
