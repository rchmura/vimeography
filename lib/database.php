<?php

class Vimeography_Database extends Vimeography
{
  public function __construct() { }

  /**
   * Check if the Vimeography database structure needs updated to version 0.6 based on the stored db version.
   *
   * @access public
   * @return void
   */
  public function vimeography_update_db_to_0_6()
  {
    if (get_option('vimeography_db_version') < 0.6)
    {
      global $wpdb;
      $old_galleries = $wpdb->get_results('SELECT * FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id;');
      $new_galleries = array();

      if (is_array($old_galleries))
      {
        foreach ($old_galleries as $old_gallery)
        {
          $new_gallery = array();

          $new_gallery['gallery_id'] = $old_gallery->gallery_id;
          $new_gallery['video_limit']  = $old_gallery->video_count;
          $new_gallery['featured_video'] = $old_gallery->featured_video;
          $new_gallery['cache_timeout']  = $old_gallery->cache_timeout;
          $new_gallery['theme_name']     = $old_gallery->theme_name;
          switch ($old_gallery->source_type)
          {
            case 'user':
              $new_gallery['source_url'] = 'https://vimeo.com/'.$old_gallery->source_name;
              break;
            case 'album':
              $new_gallery['source_url'] = 'https://vimeo.com/album/'.$old_gallery->source_name;
              break;
            case 'group':
              $new_gallery['source_url'] = 'https://vimeo.com/groups/'.$old_gallery->source_name;
              break;
            case 'channel':
              $new_gallery['source_url'] = 'https://vimeo.com/channels/'.$old_gallery->source_name;
              break;
          }
          $new_galleries[] = $new_gallery;
        }
      }
      $wpdb->query('DROP TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.';');

      $this->vimeography_update_tables();

      foreach ($new_galleries as $new_gallery)
      {
        $wpdb->insert(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          $new_gallery
        );
      }
    }
  }

  /**
   * Check if the Vimeography database structure needs updated to version 0.7 based on the stored db version.
   *
   * @access public
   * @return void
   */
  public function vimeography_update_db_to_0_7()
  {
    if (get_option('vimeography_db_version') < 0.7)
    {
      $this->vimeography_update_tables();
    }
  }

  /**
   * Check if the Vimeography database structure needs updated to version 0.8 based on the stored db version.
   * In this update, we're converting the featured video field to contain an entire URL, not just the video ID.
   *
   * @access public
   * @return void
   */
  public function vimeography_update_db_to_0_8()
  {
    if (get_option('vimeography_db_version') < 0.8)
    {
      global $wpdb;
      $old_galleries = $wpdb->get_results('SELECT * FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id;');
      $new_galleries = array();

      if (is_array($old_galleries))
      {
        foreach ($old_galleries as $old_gallery)
        {
          $new_gallery = array();

          $new_gallery['gallery_id']     = $old_gallery->gallery_id;
          $new_gallery['source_url']     = $old_gallery->source_url;
          $new_gallery['video_limit']    = $old_gallery->video_limit;
          $new_gallery['featured_video'] = empty($old_gallery->featured_video) ? '' : 'https://vimeo.com/'.$old_gallery->featured_video;
          $new_gallery['cache_timeout']  = $old_gallery->cache_timeout;
          $new_gallery['theme_name']     = $old_gallery->theme_name;
          $new_gallery['gallery_width']  = $old_gallery->gallery_width;
          $new_galleries[] = $new_gallery;
        }
      }
      $wpdb->query('DROP TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.';');

      $this->vimeography_update_tables();

      foreach ($new_galleries as $new_gallery)
      {
        $wpdb->insert(
          VIMEOGRAPHY_GALLERY_META_TABLE,
          $new_gallery
        );
      }
    }
  }

  /**
   * [vimeography_update_db_to_1_0 description]
   * This is being called twice due to other functions in this class calling vimeography_update_tables() (i think?)
   * dbdelta makes the modifications before the query below is even run.
   * @return [type] [description]
   */
  public function vimeography_update_db_to_1_0()
  {
    if (get_option('vimeography_db_version') < 1)
    {
      global $wpdb;
      $wpdb->hide_errors();

      $wpdb->query('ALTER TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' ADD resource_uri VARCHAR(50) NOT NULL AFTER source_url;');

      $rows = $wpdb->get_results('SELECT gallery_id, source_url FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' WHERE 1');

      if (!empty($rows))
      {
        foreach ($rows as $row)
        {
          try
          {
            // Convert source_url to resource, then update with value
            $resource_uri = $this->validate_vimeo_source($row->source_url);
            $wpdb->update( VIMEOGRAPHY_GALLERY_META_TABLE, array('resource_uri' => $resource_uri), array('gallery_id' => $row->gallery_id) );

          }
          catch (Vimeography_Exception $e)
          {
            // source_url was not valid, delete row from database
            $wpdb->query(
              $wpdb->prepare(
                '
                DELETE gallery, meta
                FROM '.VIMEOGRAPHY_GALLERY_TABLE.' gallery, '.VIMEOGRAPHY_GALLERY_META_TABLE.' meta
                WHERE gallery.id = %d
                AND meta.gallery_id = %d
                ',
                $row->gallery_id, $row->gallery_id
              )
            );
          }

        }
      }
    }
  }

}