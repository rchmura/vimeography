<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_MCE {
	public function __construct() { }

  /**
   * Get the gallery id and title to show in the MCE selector
   * 
   * @return array
   */
	public function galleries() {
		global $wpdb;
		$galleries = $wpdb->get_results('SELECT id, title FROM '. VIMEOGRAPHY_GALLERY_TABLE);
		return $galleries;
	}
}
