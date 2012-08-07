<?php

class Vimeography_Gallery_New extends Mustache 
{
	public $messages;
	
	public function __construct()
	{
		$this->_validate_form();
	}
		
	public function defaults()
	{
		return get_option('vimeography_default_settings');
	}
	
	public function selected()
	{
		$options = get_option('vimeography_default_settings');
		return array($options['source_type'] => TRUE);
	}
	
	public function admin_url()
	{
		return get_admin_url().'admin.php?page=vimeography-';
	}
	
	public function nonce()
	{
	   return wp_nonce_field('vimeography-gallery-action','vimeography-gallery-verification');
	}
	
	protected function _validate_form()
	{
		if (isset($_POST['vimeography_basic_settings']) && check_admin_referer('vimeography-gallery-action','vimeography-gallery-verification'))
		{
			try
			{				
				$input = $_POST['vimeography_basic_settings'];
							
				if (empty($input['gallery_title']) OR empty($input['source_url']))
					throw new Exception(__('Make sure you fill out all of the fields below!'));
												
				global $wpdb;
				
				$settings['gallery_title'] = $wpdb->escape(wp_filter_nohtml_kses($input['gallery_title']));
				$settings['source_url'] = $wpdb->escape(wp_filter_nohtml_kses($input['source_url']));
								
				$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_TABLE, array( 'title' => $settings['gallery_title'], 'date_created' => current_time('mysql'),  'is_active' => 1 ) );
				
				if (!$result)
				{
					throw new Exception(__('We couldn\'t create a new gallery. Try upgrading or reinstalling the Vimeography plugin.'));
				}
				else
				{
					$gallery_id = $wpdb->insert_id;
					$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_META_TABLE, array( 'gallery_id' => $gallery_id, 'source_url' => $settings['source_url'], 'video_limit' => 20, 'featured_video' => NULL, 'gallery_width' => NULL, 'cache_timeout' => 3600, 'theme_name' => 'bugsauce' ) );
					
					if (!$result)
						throw new Exception(__('We couldn\'t save your gallery settings. Try reinstalling the Vimeography plugin.'));
				}
				
				wp_redirect( get_admin_url().'admin.php?page=vimeography-edit-galleries&id='.$gallery_id.'&created=1' ); exit;
			}
			catch (Exception $e)
			{
				require_once(ABSPATH . 'wp-admin/admin-header.php');
				$this->messages[] = array('type' => 'error', 'heading' => 'Ruh roh.', 'message' => $e->getMessage());
			}
		}
	}           
}