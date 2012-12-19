<?php

class Vimeography_Gallery_List extends Vimeography_Base 
{
	public $_galleries;
	public $pagination;
    
	public function __construct()
	{
		if (isset($_POST['vimeography-list']))
			$this->_validate_form($_POST['vimeography-list']);

		wp_register_script( 'bootstrap-tooltip', VIMEOGRAPHY_URL.'media/js/bootstrap-tooltip.js');
		wp_register_script( 'bootstrap-popover', VIMEOGRAPHY_URL.'media/js/bootstrap-popover.js');
		wp_enqueue_script( 'bootstrap-tooltip');
		wp_enqueue_script( 'bootstrap-popover');
		
		$this->_galleries = $this->_get_galleries_to_display();	
	}
		
	/**
	 * Returns the URL to the new gallery page.
	 * 
	 * @access public
	 * @return string
	 */
	public function new_gallery_url()
	{
		return get_admin_url().'admin.php?page=vimeography-new-gallery';
	}
	
	/**
	 * Returns several security form fields for the new gallery form.
	 * 
	 * @access public
	 * @return mixed
	 */
	public function nonce()
	{
	   return wp_nonce_field('vimeography-list-action','vimeography-verification');
	}
		
	/**
	 * Determines if the user sees an empty list or a list of galleries.
	 * 
	 * @access public
	 * @return bool
	 */
	public function galleries_to_show()
	{
		return (empty($this->_galleries)) ? FALSE : TRUE;
	}
	
	/**
	 * If galleries exist, return the details about them.
	 * 
	 * @access public
	 * @return array
	 */
	public function galleries()
	{
		$galleries = array();
		
		foreach ($this->_galleries as $gallery)
		{
			$gallery->edit_url = get_admin_url().'admin.php?page=vimeography-edit-galleries&id='.$gallery->id;
			$gallery->theme_name = ucfirst($gallery->theme_name);
									
			$galleries[] = $gallery;
		}
		
		return $galleries;
	}
	
	/**
	 * Checks the incoming form to make sure it is completed.
	 * 
	 * @access private
	 * @return void
	 */
	private function _validate_form($input)
	{
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( check_admin_referer('vimeography-list-action','vimeography-verification') )
		{
			global $wpdb;
			$id = $wpdb->escape(intval($input['id']));
			$action = $wpdb->escape(wp_filter_nohtml_kses($input['action']));
			
			if ($action === 'delete')
				$this->_delete_gallery($id);
				
			if ($action === 'duplicate')
				$this->_duplicate_gallery($id);
		}
	}
		
	/**
	 * Get any galleries that might exist.
	 * 
	 * @access private
	 * @return array
	 */
	private function _get_galleries_to_display()
	{
		global $wpdb;
		$number_of_galleries = $wpdb->get_results('SELECT COUNT(*) as count from '. VIMEOGRAPHY_GALLERY_TABLE);
		$limit = 10;
				
		$number_of_pages = ceil($number_of_galleries[0]->count / $limit);
				 
		$current_page = isset($_GET['p']) ? $wpdb->escape(intval($_GET['p'])) : 1;
				
		$offset = ($current_page - 1) * $limit;
				
		$this->pagination = $this->_do_pagination($current_page, $number_of_pages);

		return $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id LIMIT '.$limit.' OFFSET '.$offset.';');
	}
		
	/**
	 * Creates a copy of the given gallery id in the database.
	 * 
	 * @access private
	 * @param mixed $id
	 * @return void
	 */
	private function _duplicate_gallery($id)
	{
		try
		{
			global $wpdb;
			$duplicate = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$id.' LIMIT 1;');
			$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_TABLE, array( 'title' => $duplicate[0]->title, 'date_created' => current_time('mysql'),  'is_active' => 1 ) );
			if ($result === FALSE)
				throw new Exception(__('Your gallery could not be duplicated.'));
				
			$gallery_id = $wpdb->insert_id;
			$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_META_TABLE, array( 'gallery_id' => $gallery_id, 'source_url' => $duplicate[0]->source_url, 'video_limit' => $duplicate[0]->video_limit, 'featured_video' => $duplicate[0]->featured_video, 'cache_timeout' => $duplicate[0]->cache_timeout, 'theme_name' => $duplicate[0]->theme_name ) );
			
			if ($result === FALSE)
				throw new Exception(__('Your gallery could not be duplicated.'));
				
			$this->messages[] = array('type' => 'success', 'heading' => __('Gallery duplicated.'), 'message' => __('You now have a clone of your own.'));

		}
		catch (Exception $e)
		{
			$this->messages[] = array('type' => 'error', 'heading' => __('Ruh Roh.'), 'message' => $e->getMessage());
		}
	}
	
	/**
	 * Deletes the gallery of the given ID in the database.
	 * 
	 * @access private
	 * @param mixed $id
	 * @return void
	 */
	private function _delete_gallery($id)
	{
		try
		{
			global $wpdb;
			$result = $wpdb->query('DELETE gallery, meta FROM '.VIMEOGRAPHY_GALLERY_TABLE.' gallery, '.VIMEOGRAPHY_GALLERY_META_TABLE.' meta WHERE gallery.id = '.$id.' AND meta.gallery_id = '.$id.';');
						
			if ($result === FALSE)
				throw new Exception(__('Your gallery could not be deleted.'));
				
			// Delete the cache separately
			$this->delete_vimeography_cache($id);
				
			$this->messages[] = array('type' => 'success', 'heading' => __('Gallery deleted.'), 'message' => __('See you later, sucker.'));

		}
		catch (Exception $e)
		{
			$this->messages[] = array('type' => 'error', 'heading' => __('Ruh Roh.'), 'message' => $e->getMessage());
		}
	}
	
	/**
	 * This just creates a huge list of numbered pages at the bottom.. not
	 * pretty if someone creates 100 galleries, but not sure who might actually
	 * do that.
	 * 
	 * @access protected
	 * @param mixed $current_page
	 * @param mixed $number_of_pages
	 * @return void
	 */
	private static function _do_pagination($current_page, $number_of_pages)
	{
		if ($number_of_pages <= 1) return FALSE;
				
		$pagination = array();
		
		$pagination['previous-page'] = $current_page - 1 > 0 ? $current_page - 1 : FALSE;
		$pagination['next-page'] = $current_page == $number_of_pages ? FALSE : $current_page + 1;
		
		for ($i = 1; $i <= $number_of_pages; $i++)
		{
			$page = array();
			$page['number'] = $i;
			if ($i == $current_page) $page['active'] = TRUE;
			$pagination['pages'][] = $page;
		}
						
		return $pagination;
	}	

}