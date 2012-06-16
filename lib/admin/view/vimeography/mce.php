<?php

class Vimeography_MCE extends Mustache 
{	
	public function __construct()
	{
		//wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		//wp_enqueue_style('cloud.css');
	}
	
	public function galleries()
	{
		global $wpdb;
		
		$galleries = $wpdb->get_results('SELECT id, title from '. VIMEOGRAPHY_GALLERY_TABLE);
		
		return $galleries;
	}
}