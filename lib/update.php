<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Once new plugin update is out, we should return a 401 from
//
// http://vimeography.com/api/activate/[key]
//
// with a JSON response that has a "message" key which will be displayed
// when a user tries to activate a plugin in an old version of Vimeography.
//
// http_response_code(401);
// status_header(401);
// $response['status'] = 'error';
// $response['message'] = __('Please update to the latest version of Vimeography to activate your Vimeography Addon');
// $to_send = json_encode($response);
// echo $to_send;
// die;
//
// Always return a 304 response code from the old update endpoint API
// updates will only work if you have the latest version of the Vimeography plugin.
// This also will take care of the remote info request for the view_version_details screen
//
// http://vimeography.com/api/update/[key]

class Vimeography_Update {
  /**
   * All of the Vimeography activation keys that the user has stored.
   *
   * Example:
   *
   *   array(1) {
   *     [0]=>
   *     object(stdClass)#472 (3) {
   *       ["activation_key"]=>
   *       string(16) "n0Ae9UP49sNfw5aFGxiyn7mzi09c1Ua7"
   *       ["plugin_name"]=>
   *       string(19) "vimeography-journey"
   *       ["product_name"]=>
   *       string(7) "Journey"
   *     }
   *   }
   *
   * @var array
   */
  private $_activation_keys;

  /**
   * The endpoint of the Vimeography Updater API.
   * this is the URL our updater / license checker pings.
   *
   * This should be the URL of the site with EDD installed
   *
   * @var string
   */
  private $_endpoint = 'http://vimeography.com';

  /**
   * [__construct description]
   */
  public function __construct() {
    //delete_site_option('vimeography_activation_keys');
    $activation_keys = get_site_option('vimeography_activation_keys');
    $this->_activation_keys  = $activation_keys ? $activation_keys : array();

		// Setup hooks
		$this->_includes();
		$this->_hooks();
		$this->_vimeography_auto_updater();
  }

	/**
	 * Include the EDD updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function _includes() {
		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
      require_once 'EDD_SL_Plugin_Updater.php';
    }
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function _hooks() {

    // Add activation key message for plugins with missing keys
    add_action( 'load-plugins.php', array( $this, 'vimeography_check_for_missing_activation_keys' ) );

	}

  /**
   * Activate the license key
   *
   * @access  public
   * @return  void
   */
  public function activate_license($license) {

    $key = str_replace('-', '', strtoupper( sanitize_text_field( $license ) ) );

    // Ignore if this is a duplicate incoming key.
    if ( $this->vimeography_check_if_activation_key_exists( $key ) ) {
      return;
    }

    // Data to send to the API
    $api_params = array(
      'edd_action' => 'activate_license',
      'license'    => $key,
      //'item_name'  => urlencode( $this->item_name ), // the name of our product in EDD **IMPORTANT need to set EDD_BYPASS_NAME_CHECK on vimeography.com to true if omitting
      'url'        => urlencode( home_url() ),
    );

    // Call the API
    $response = wp_remote_get(
      add_query_arg( $api_params, $this->_endpoint),
      array(
        'timeout'   => 15,
        'sslverify' => false
      )
    );

    // Make sure there are no errors
    if ( is_wp_error( $response ) )
      return;

    // Decode license data
    $license_data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( $license_data->success AND $license_data->license == 'valid' ) {
      $this->_vimeography_add_activation_key( $key, $license_data );
      return TRUE;
    } else {
      // Add failed message
      switch ($license_data->error) {
        case 'missing': case 'revoked':
          throw new Exception( __('That license key could not be found in our system.', 'vimeography') );
        case 'no_activations_left':
          throw new Exception( __('You have reached the max number of sites that this license can be used on.', 'vimeography') );
        case 'expired':
          throw new Exception( __('The license key you entered has expired. Please visit http://vimeography.com to renew it.', 'vimeography') );
        case 'key_mismatch':
          throw new Exception( __('The license key you entered does not match the one we have on file.', 'vimeography') );
        default:
          throw new Exception( __('Unknown error: ' . $license_data->error, 'vimeography') );
      }
    }
  }

  /**
   * Deactivate the license key
   *
   * @access  public
   * @return  void
   */
  public function deactivate_license( $license ) {
    $key = str_replace('-', '', strtoupper( sanitize_text_field( $license ) ) );

    // Data to send to the API
    $api_params = array(
      'edd_action' => 'deactivate_license',
      'license'    => $key,
      //'item_name'  => urlencode( $this->item_name ), // the name of our product in EDD
      'url'        => urlencode( home_url() )
    );

    // Call the API
    $response = wp_remote_get(
      add_query_arg( $api_params, $this->_endpoint ),
      array(
        'timeout'   => 15,
        'sslverify' => false
      )
    );

    // Make sure there are no errors
    if ( is_wp_error( $response ) )
      return;

    // Decode the license data
    $license_data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( $license_data->license == 'deactivated' ) {
      if ( $this->_vimeography_remove_activation_key( $key ) ) {
        return TRUE;
      } else {
        throw new Exception( __('That license key could not be deactivated.', 'vimeography') );
      }
    }
  }

  /**
   * [check_license description]
   * @return [type] [description]
   */
  public function check_license( $license ) {

    // Data to send to the API
    $api_params = array(
      'edd_action' => 'check_license',
      'license'    => $license->activation_key,
      //'item_name'  => urlencode( $this->item_name ), // the name of our product in EDD
      'url'        => urlencode( home_url() )
    );

    // Call the API
    $response = wp_remote_get(
      add_query_arg( $api_params, $this->_endpoint ),
      array(
        'timeout'   => 15,
        'sslverify' => false
      )
    );

    // Make sure there are no errors
    if ( is_wp_error( $response ) ) {
      return FALSE;
    }

    // Decode the license data
    return json_decode( wp_remote_retrieve_body( $response ) );
  }

  /**
   * Add a plugins page message to any Vimeography add-ons that are installed,
   * but that do not have an activation key associated with the installation.
   *
   * @return void
   */
  public function vimeography_check_for_missing_activation_keys() {

    $addons = Vimeography::get_instance()->addons->installed_addons;

    if ( ! empty( $addons ) ) {
      // If the activation key is not found for the installed plugin,
      // add the plugin message hook
      $addons_with_missing_keys = array_filter($addons, array($this, 'vimeography_is_addon_missing_activation_key') );

      if ( ! empty( $addons_with_missing_keys ) ) {
        foreach ( $addons_with_missing_keys as $plugin ) {
          $hook = 'after_plugin_row_' . $plugin['basename'];
          add_action( $hook, array($this, 'vimeography_addon_update_message'), 10, 2 );
        }
      }
    }
  }

  /**
   * Loop through the activations keys to check if one exists for the
   * given Vimeography addon plugin headers.
   *
   * @var    $plugin  Meta headers for a Vimeography addon plugin.
   * @return bool     TRUE if missing, FALSE if found
   */
  public function vimeography_is_addon_missing_activation_key($plugin) {
    $plugins_with_keys = array();

    if ( ! empty($this->_activation_keys) ) {
      foreach ($this->_activation_keys as $key) {
        $plugins_with_keys[] = $key->plugin_name;
      }
    }

    return !in_array( $plugin['slug'], $plugins_with_keys );
  }

  /**
   * Loop through the activations keys to check if one exists for the
   * given Vimeography activation key.
   *
   * @var    $key  string  Activation key.
   * @return bool          TRUE if exists, FALSE if not found
   */
  public function vimeography_check_if_activation_key_exists($key) {
    $result = FALSE;

    if ( ! empty($this->_activation_keys) ) {
      foreach ( $this->_activation_keys as $entry ) {
        if ( $entry->activation_key == $key ) {
          $result = TRUE;
        }
      }
    }

    return $result;
  }

  /**
   * Add the activation key to the database.
   *
   * @var    $key          string  Activation key.
   * @var    $license_data array
   * @return bool          TRUE if successful, FALSE if failed
   */
  protected function _vimeography_add_activation_key( $key, $license_data ) {
    $entry = new stdClass();
    $entry->activation_key = $key;
    $entry->plugin_name    = $license_data->vimeography_plugin_slug;
    $entry->product_name   = $license_data->vimeography_product_name;
    $entry->expires        = $license_data->expires;
    $entry->status         = $license_data->license;
    $entry->limit          = $license_data->license_limit;
    $entry->activations_left = $license_data->activations_left;

    $this->_activation_keys[] = $entry;
    return update_site_option('vimeography_activation_keys', array_values( $this->_activation_keys ) );
  }

  /**
   * Remove the activation key to the database.
   *
   * @var    $key  string  Activation key.
   * @return bool          TRUE if successful, FALSE if failed
   */
  protected function _vimeography_remove_activation_key( $key ) {
    if ( ! empty( $this->_activation_keys ) ) {
      foreach ( $this->_activation_keys as $i => $entry ) {
        if ( $entry->activation_key === $key ) {
          unset( $this->_activation_keys[$i] );
        }
      }

      return update_site_option( 'vimeography_activation_keys', array_values( $this->_activation_keys ) );
    }

    return FALSE;
  }

  /**
   * Add a reminder to add the activation key to receives updates
   * for the installed Vimeography theme.
   *
   * @param  string $plugin_basename  Folder and filename, eg:
   * @return [type]              [description]
   */
  public function vimeography_addon_update_message($plugin_basename, $plugin_data) {
    $ineligible = array(
      'Vimeography Theme: Bugsauce',
      'Vimeography Theme: Ballistic',
      'Vimeography Theme: Single'
    );

    if ( in_array( $plugin_data['Name'], $ineligible ) ) {
      return;
    }

    echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">';
    echo '<span style="border-right: 1px solid #DFDFDF; margin-right: 5px;">';
    printf( __('Hey! Don\'t forget to <a title="Activate my Vimeography Addon" href="%1$sadmin.php?page=vimeography-manage-activations">enter your activation key</a> to receive the latest updates for the %2$s plugin.', 'vimeography'), get_admin_url(), $plugin_data['Name'] );
    echo '</span>';
    echo '</div></td></tr>';
  }

  /**
   * Auto updater
   *
   * @access  protected
   * @return  void
   */
  protected function _vimeography_auto_updater() {
    // We only need to check for updates if an activation key exists
    // in the options table.
    if ( ! empty( $this->_activation_keys ) ) {
      foreach ( $this->_activation_keys as $plugin ) {

        $plugin_path = self::_vimeography_get_plugin_path( $plugin->plugin_name );

        if ( $plugin_path ) {
          // Get the plugin headers
          $headers = get_file_data( $plugin_path, array('version' => 'Version') );

          // setup the updater
          new EDD_SL_Plugin_Updater(
            $this->_endpoint,
            $plugin_path,
            array(
              'version'   => $headers['version'],
              'license'   => trim($plugin->activation_key),
              'item_name' => $plugin->product_name,
              'author'    => 'Dave Kiss',
            )
          );
        }
      }
    }
  }

  /**
   * Get the absolute path to the provided plugin name.
   *
   * @access  protected
   * @return  string
   */
  protected function _vimeography_get_plugin_path( $plugin_name ) {
    //return str_replace('vimeography/', trailingslashit($plugin_name), VIMEOGRAPHY_PATH);
    $basename = '/' . trailingslashit( $plugin_name ) . $plugin_name . '.php';

    if ( ! is_file( $dir = WPMU_PLUGIN_DIR . $basename ) ) {
      if ( ! is_file( $dir = WP_PLUGIN_DIR . $basename ) ) {
        return FALSE;
      }
    }

    return $dir;
  }

  /**
   * Sets the activation keys in the class
   *
   * @param  array  $keys [description]
   * @return [type]       [description]
   */
  public function vimeography_set_activation_keys($keys = array()) {
    $this->_activation_keys = $keys;
    return $this;
  }

}
