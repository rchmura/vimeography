<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Upgrade {

  public function __construct() {
    add_filter( 'upgrader_pre_install',   array($this, 'vimeography_pre_upgrade'), 10, 2 );
  }

  /**
   * Perform any actions just before Vimeography is updated.
   *
   * @param  bool   $true       TRUE
   * @param  array  $hook_extra Slug of the plugin being updated
   * @return void
   */
  public function vimeography_pre_upgrade($true, $hook_extra) {
    // Vimeography *might* be updating, deactivate all Vimeography plugins until we are back.
    if ( isset( $hook_extra['plugin'] ) AND $hook_extra['plugin'] === 'vimeography/vimeography.php') {
      $plugins = get_option('active_plugins');
      $vimeography_plugins = array();

      foreach($plugins as $plugin) {
        if (strpos($plugin, 'vimeography-') !== FALSE) {
          $vimeography_plugins[] = $plugin;
        }
      }

      deactivate_plugins($vimeography_plugins);
      update_option('vimeography_reactivate_plugins', $vimeography_plugins);
    }
  }

}
