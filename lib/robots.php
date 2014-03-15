<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Robots {

  public function __construct() {
    add_action( 'do_robots',      array($this, 'vimeography_block_robots') );
  }

  /**
   * Adds the VIMEOGRAPHY_ASSETS_URL to the virtual robots.txt restricted list.
   * Fired when the template loader determines a robots.txt request.
   *
   * @access public
   * @static
   * @return void
   */
  public static function vimeography_block_robots() {
    $blocked_asset_path = str_ireplace(site_url(), '', VIMEOGRAPHY_ASSETS_URL);
    echo 'Disallow: '.$blocked_asset_path."\n";
  }

}
