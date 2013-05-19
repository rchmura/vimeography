<?php

const ACCESS_TOKEN = '217f7948b5c1c743fd58818045b99513';

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

        $input['source_url'] = $this->_validate_vimeo_source($input['source_url']);

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
   * Checks if the provided Vimeo URL is valid and if so, returns an array
   * containing the URL parts
   *
   * @param  string $source_url Source collection of Vimeo videos.
   * @return array              Schema of URL
   */
  private function _validate_vimeo_source($source_url)
  {
    $scheme = parse_url($source_url);

    if (empty($scheme['scheme']))
      $source_url = 'https://' . $source_url;

    if ((($url = parse_url($source_url)) !== FALSE) && (preg_match('~vimeo[.]com$~', $url['host']) > 0))
    {
      return $url;
    }
    else
    {
      throw new Vimeography_Exception('You must provide a valid Vimeo source from where to retrieve Vimeo videos.');
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

		if (! $result)
		{
		  return FALSE;
		}
		else
		{
			$gallery_id = $wpdb->insert_id;
			$result = $wpdb->insert( VIMEOGRAPHY_GALLERY_META_TABLE, array(
                              'gallery_id' => $gallery_id,
                              'source_url' => $settings['source_url'],
                              'video_limit' => 20,
                              'featured_video' => NULL,
                              'gallery_width' => NULL,
                              'cache_timeout' => 3600,
                              'theme_name' => 'bugsauce' ) );

			if (! $result)
			 return FALSE;
		}

		return $gallery_id;
	}

  /**
   * When the user enters the source location when creating a new gallery.
   *
   * Won't work publically yet, because the user needs to be authenticated to subscribe to push notifications.
   *
   * @return [type] [description]
   */
  private static function _vimeography_subscribe_to_trigger()
  {
    require_once(VIMEOGRAPHY_PATH . 'vendor/vimeo.php-master/vimeo.php');
    $lib = new Vimeo(null, null, self::ACCESS_TOKEN );

    $response = $lib->request( '/triggers', array(
      'actions' => 'added, removed',
      'callback' => 'http://requestb.in/18sgn611',
      'resource_uri' => '/channels/512387/videos'
    ), 'POST' );

    if ($response['status'] == 201)
    {
      // successfully created trigger
    }

    echo '<pre>';
    var_dump($response);
    echo '</pre>';
    die;
  }

}