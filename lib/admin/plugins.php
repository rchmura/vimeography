<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Plugins {

  public function __construct() {
    add_filter( 'plugin_action_links', array($this, 'vimeography_filter_plugin_actions'), 10, 2 );
  }

  /**
   * Add Settings link to "installed plugins" admin page.
   *
   * @access public
   * @param array $links
   * @param string $file
   * @return array $links
   */
  public function vimeography_filter_plugin_actions($links, $file) {
    if ( $file == VIMEOGRAPHY_BASENAME ) {
      $settings_link = '<a href="admin.php?page=vimeography-edit-galleries">' . __('Settings', 'vimeography') . '</a>';
      if ( ! in_array( $settings_link, $links ) ) {
        array_unshift( $links, $settings_link ); // before other links
      }
    }
    return $links;
  }

}
