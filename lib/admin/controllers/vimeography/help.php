<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Help extends Vimeography_Base {
  public function __construct() { }

  /**
   * Show the Vimeography tutorials in a gallery on the Help page.
   * Pluginception!!!!!!!
   *
   * @return string
   */
  public function tutorials_gallery() {
    return do_shortcode("[vimeography source='https://vimeo.com/channels/vimeography' width='700px']");
  }
}
