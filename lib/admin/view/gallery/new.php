<?php

require_once(VIMEOGRAPHY_PATH . 'vendor/vimeo.php-master/vimeo.php');

/**
 * Controller for the New Gallery admin page.
 *
 * @extends Vimeography_Base
 */
class Vimeography_Gallery_New extends Vimeography_Base
{
  private $_vimeo;
  private $_token;

	public function __construct()
	{
    if ( ( $this->_token = get_option('vimeography_pro_access_token') ) === FALSE ) :
      $this->_vimeo = new Vimeo( VIMEOGRAPHY_CLIENT_ID );
    else :
      $this->_vimeo = new Vimeo(null, null, $this->_token );
    endif;

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

        $input['resource_uri'] = Vimeography::validate_vimeo_source($input['source_url']);

        //$trigger = $this->_vimeography_subscribe_to_trigger($input['resource_uri']);

				if (($gallery_id = $this->_create_vimeography_gallery($input)) == FALSE)
  				throw new Vimeography_Exception(__('We couldn\'t create a new gallery. Try upgrading or reinstalling the Vimeography plugin.'));

				wp_redirect( get_admin_url().'admin.php?page=vimeography-edit-galleries&id='.$gallery_id.'&created=1' ); exit;
			}
			catch (Vimeography_Exception $e)
			{
				require_once(ABSPATH . 'wp-admin/admin-header.php');
				$this->messages[] = array('type' => 'warn', 'heading' => 'Heads up!', 'message' => $e->getMessage());
			}
		}
	}

	/**
	 * Creates a new gallery entry in the database.
	 *
	 * @access private
	 * @static
	 * @return int gallery ID if success, FALSE if failure
	 */
	private static function _create_vimeography_gallery($input)
	{
		global $wpdb;

		$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_TABLE, array( 'title' => $input['gallery_title'], 'date_created' => current_time('mysql'),  'is_active' => 1 ) );

		if (! $result)
		{
		  return FALSE;
		}
		else
		{
			$gallery_id = $wpdb->insert_id;
			$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_META_TABLE, array(
                              'gallery_id'     => $gallery_id,
                              'source_url'     => $input['source_url'],
                              'resource_uri'   => $input['resource_uri'],
                              'video_limit'    => 20,
                              'featured_video' => NULL,
                              'gallery_width'  => NULL,
                              'cache_timeout'  => 3600,
                              'theme_name'     => 'bugsauce' ) );

			if (! $result)
			 return FALSE;
		}

		return $gallery_id;
	}

  /**
   * When the user enters the source location when creating a new gallery.
   *
   * Won't work publically yet, because the user needs to be authenticated to subscribe to push notifications.
   * Also, does not currently work with albums.
   *
   * @return [type] [description]
   */
  private function _vimeography_subscribe_to_trigger($resource)
  {
    $response = $this->_vimeo->request( '/triggers', array(
      'actions' => 'added, removed',
      'callback' => 'http://requestb.in/xzszvixz',
      'resource_uri' => $resource .'/videos'
    ), 'POST' );

    switch ($response['status'])
    {
      case 201:
        //successful
        return TRUE;
        break;
      case 403:
        if ($this->_token === FALSE) :
          // Trigger unsuccessful, rely on 304 headers.
          break;
          // This line will only work when the Vimeo API supports triggers without being authenticated
          // Though, the user could technically be subscribing to a collection that isn't actually supported in PRO, either.
          // So be specific in which sources are currently supported.
          //throw new Vimeography_Exception('Vimeography PRO allows you to show videos from all of your users, channels, albums, & groups.');
        else:
          throw new Vimeography_Exception('Looks like you don\'t have the permission to subscribe to this collection.');
        endif;
        break;
      case 500:
        // Unsupported container uri
        throw new Vimeography_Exception('The resource that was entered is currently unsupported.');
        break;
      default:
        throw new Vimeography_Exception(serialize($response));
        break;
    }
  }

}