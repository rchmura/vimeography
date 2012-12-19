<?php

/**
 * Controller for the New Gallery admin page.
 * 
 * @extends Vimeography_Base
 */
class Vimeography_Gallery_New extends Vimeography_Base 
{	

	public function __construct()
	{
  	if (isset($_POST['vimeography_basic_settings']))
		  $this->_validate_form($_POST['vimeography_basic_settings']);
	}
					
	/**
	 * Returns several security form fields for the new gallery form.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function nonce()
	{
	   return wp_nonce_field('vimeography-gallery-action','vimeography-gallery-verification');
	}
		
	/**
	 * Checks the incoming form to make sure it is completed.
	 * 
	 * @access private
	 * @return void
	 */
	private function _validate_form($input)
	{
		if (check_admin_referer('vimeography-gallery-action','vimeography-gallery-verification'))
		{
			try
			{											
				if (empty($input['gallery_title']) OR empty($input['source_url']))
					throw new Vimeography_Exception(__('Make sure you fill out all of the fields below!'));

				if (($gallery_id = $this->_create_vimeography_gallery($input)) == FALSE)
  				throw new Vimeography_Exception(__('We couldn\'t create a new gallery. Try upgrading or reinstalling the Vimeography plugin.'));
				
				wp_redirect( get_admin_url().'admin.php?page=vimeography-edit-galleries&id='.$gallery_id.'&created=1' ); exit;
			}
			catch (Vimeography_Exception $e)
			{
				require_once(ABSPATH . 'wp-admin/admin-header.php');
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
			}
		}
	}
	
	/**
	 * Creates a new gallery entry in the database.
	 * 
	 * @access private
	 * @static
	 * @return mixed gallery ID if success, FALSE if failure
	 */
	private static function _create_vimeography_gallery($input)
	{
		global $wpdb;
		
		$settings['gallery_title'] = $wpdb->escape(wp_filter_nohtml_kses($input['gallery_title']));
		$settings['source_url'] = $wpdb->escape(wp_filter_nohtml_kses($input['source_url']));
						
		$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_TABLE, array( 'title' => $settings['gallery_title'], 'date_created' => current_time('mysql'),  'is_active' => 1 ) );
		
		if (!$result)
		{
		  return FALSE;
		}
		else
		{
			$gallery_id = $wpdb->insert_id;
			$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_META_TABLE, array( 'gallery_id' => $gallery_id, 'source_url' => $settings['source_url'], 'video_limit' => 20, 'featured_video' => NULL, 'gallery_width' => NULL, 'cache_timeout' => 3600, 'theme_name' => 'bugsauce' ) );
			
			if (!$result)
			 return FALSE;
		}
		
		return $gallery_id;
	}	           
}