<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Actions {

  public function __construct() {
    add_action( 'admin_init',     array($this, 'vimeography_requires_wordpress_version') );
    add_action( 'admin_init',     array($this, 'vimeography_check_if_just_updated'));
    add_action( 'admin_init',     array($this, 'vimeography_activate_bugsauce'));
  }

  /**
   * Check the wordpress version is compatible, and disable plugin if not.
   *
   * @access public
   * @return void
   */
  public function vimeography_requires_wordpress_version() {
    global $wp_version;
    $plugin_data = get_plugin_data( __FILE__, false );

    if ( version_compare($wp_version, "3.3", "<" ) )
    {
      if( is_plugin_active( VIMEOGRAPHY_BASENAME ) )
      {
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

    if ( $plugins )
    {
      activate_plugins($plugins);
      delete_option('vimeography_reactivate_plugins');
    }
  }

  /**
   * [vimeography_activate_plugin description]
   * @param  [type] $basename [description]
   * @return [type]           [description]
   */
  public function vimeography_activate_bugsauce() {
    $bugsauce = str_replace('vimeography/', 'vimeography-bugsauce/', VIMEOGRAPHY_PATH);
    if ( is_plugin_inactive('vimeography-bugsauce/vimeography-bugsauce.php') AND file_exists($bugsauce) )
      activate_plugin('vimeography-bugsauce/vimeography-bugsauce.php');
  }

}
