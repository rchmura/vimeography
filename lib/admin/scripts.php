<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Scripts {

  public function __construct() {
    add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
  }

  /**
   * Load the common admin scripts across the Vimeography plugin.
   * 
   * @param string $hook  slug of the current admin page
   */
  public function add_scripts( $hook ) {

    if ( strpos( $hook, 'vimeography' ) !== FALSE && strpos( $hook, 'vimeography-stats' ) == FALSE ) {
      wp_register_style( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'lib/admin/assets/css/bootstrap.min.css');
      wp_register_style( 'vimeography-admin',     VIMEOGRAPHY_URL.'lib/admin/assets/css/admin.css');

      wp_register_script( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'lib/admin/assets/js/bootstrap.min.js');
      wp_register_script( 'vimeography-admin', VIMEOGRAPHY_URL.'lib/admin/assets/js/admin.js', 'jquery');

      wp_enqueue_style( 'vimeography-bootstrap');
      wp_enqueue_style( 'vimeography-admin');

      wp_enqueue_script( 'vimeography-bootstrap');
      wp_enqueue_script( 'vimeography-admin');
    }
  }

}