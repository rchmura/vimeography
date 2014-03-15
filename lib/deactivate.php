<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Deactivate {

  public function __construct() {
    register_deactivation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_deactivate_bugsauce') );
  }

  /**
   * [vimeography_activate_plugin description]
   * @param  [type] $basename [description]
   * @return [type]           [description]
   */
  public function vimeography_deactivate_bugsauce() {
    if ( is_plugin_active('vimeography-bugsauce/vimeography-bugsauce.php') )
      deactivate_plugins('vimeography-bugsauce/vimeography-bugsauce.php');
  }
}
