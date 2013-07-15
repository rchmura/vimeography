<?php

class Vimeography_MCE
{
	public function __construct() { }

	public function galleries()
	{
		global $wpdb;

		$galleries = $wpdb->get_results('SELECT id, title FROM '. VIMEOGRAPHY_GALLERY_TABLE);

		return $galleries;
	}
}