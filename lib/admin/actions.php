<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Actions {

  public function __construct() {
    add_action( 'admin_init', array( $this, 'vimeography_requires_wordpress_version') );
    add_action( 'admin_init', array( $this, 'vimeography_check_if_just_updated') );
    add_action( 'admin_init', array( $this, 'vimeography_maybe_add_activation_key_reset_nag') );
    add_action( 'admin_init', array( $this, 'vimeography_maybe_add_pro_update_nag') );
  }

  /**
   * Check the wordpress version is compatible, and disable plugin if not.
   *
   * @access public
   * @return void
   */
  public function vimeography_requires_wordpress_version() {
    global $wp_version;

    if ( version_compare($wp_version, "3.3", "<" ) ) {
      if ( is_plugin_active( VIMEOGRAPHY_BASENAME ) ) {
        deactivate_plugins( VIMEOGRAPHY_BASENAME );
        wp_die( sprintf( __('Vimeography requires WordPress 3.3 or higher. Please upgrade WordPress and try again. <a href="%s">Back to WordPress admin</a>', 'vimeography'), admin_url() ) );
      }
    }
  }

  /**
   * If Vimeography was just updated, make sure all the Vimeography plugins are activated.
   *
   * @return void
   */
  public function vimeography_check_if_just_updated() {
    $plugins = get_option('vimeography_reactivate_plugins');

    if ( $plugins ) {
      activate_plugins($plugins);
      delete_option('vimeography_reactivate_plugins');
    }
  }

  /**
   * [vimeography_maybe_add_activation_key_reset_nag description]
   * @return [type] [description]
   */
  public function vimeography_maybe_add_activation_key_reset_nag() {
    if ( get_site_option('vimeography_corrupt_keys_found') ) {
      if ( isset( $_GET['vimeography-cancel-activation-message'] ) OR get_site_option('vimeography_activation_keys') ) {
        delete_site_option('vimeography_corrupt_keys_found');
        return;
      }

      if ( ! isset( $_POST['vimeography-activate-key'] ) ) {
        add_action('admin_notices', array($this, 'vimeography_corrupt_keys_notice') );
      }
    }
  }

  /**
   * [vimeography_corrupt_keys_notice description]
   * @return [type] [description]
   */
  public function vimeography_corrupt_keys_notice() {
    printf( '<div class="update-nag"> <p> %1$s  | <a href="%2$s"> %3$s </a> </p> </div>',
      sprintf( __( "<strong>Notice:</strong> Vimeography found a problem with the way your Vimeography Activation Keys were saved. To resolve this error, please visit the <a href=\"%s\" title=\"Vimeography Manage Activations Page\">Vimeography Manage Activations page</a> and re-enter your activation keys that you received via email while purchasing your Vimeography products.", 'vimeography' ), admin_url('admin.php?page=vimeography-manage-activations') ),
      esc_url( add_query_arg( 'vimeography-cancel-activation-message', wp_create_nonce( 'wptus_nag' ) ) ),
      __( 'Dismiss', 'vimeography' )
    );
  }

  /**
   * Yells at the user to upgrade their
   * Pro plugin if it is installed and out of date.
   *
   * @return void
   */
  public function vimeography_maybe_add_pro_update_nag() {
    $pro_version = get_option('vimeography_pro_db_version');

    if ($pro_version) {
      if ( version_compare($pro_version, '0.7', '<') ) {
        add_action('admin_notices', array($this, 'vimeography_pro_upgrade_notice') );
      }
    }
  }

  /**
   * Adds the update message for outdated Pro versions.
   *
   * @return string | html
   */
  public function vimeography_pro_upgrade_notice() {
    printf( '<div class="update-nag">%1$s</div>',
      sprintf( __( "<strong>Hey!</strong> It looks like there is an update ready for Vimeography Pro. Make sure you've entered your <a href=\"%s\" title=\"Vimeography Manage Activations Page\">activation key</a>, then head on over to the <a href=\"%s\" title=\"Plugins Page\">Plugins page</a> to get the latest compatible version.", 'vimeography' ), admin_url('admin.php?page=vimeography-manage-activations'), admin_url('update-core.php?force-check=1') )
    );
  }

}
