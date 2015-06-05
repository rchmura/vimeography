<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Welcome {

  public function __construct() {
    
    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'set_welcome_screen_flag') );

    add_action( 'admin_init', array($this, 'welcome_screen_do_activation_redirect') );

  }


  /**
   * Set a transient that expires in 30 seconds.
   * 
   * @return [type] [description]
   */
  public function set_welcome_screen_flag() {
    set_transient( '_vimeography_welcome_screen_activation_redirect', true, 30 );
  }


  /**
   * Check to see if we should perform the redirect
   * 
   * @return [type] [description]
   */
  public function welcome_screen_do_activation_redirect() {

    // Bail if no activation redirect
    if ( ! get_transient( '_vimeography_welcome_screen_activation_redirect' ) ) {
      return;
    }

    // Delete the redirect transient
    delete_transient( '_vimeography_welcome_screen_activation_redirect' );
    
    // Bail if activating from network, or bulk
    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
      return;
    }

    // Redirect
    wp_safe_redirect( add_query_arg( array( 'page' => 'vimeography-welcome' ), admin_url( 'options.php' ) ) );
  }

}