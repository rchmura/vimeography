<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Database extends Vimeography {
  /**
   * Vimeography DB Version
   *
   * @var [type]
   */
  protected static $_version;

  /**
   * [__construct description]
   */
  public function __construct() {
    add_action( 'plugins_loaded', array($this, 'vimeography_update_db_version_if_not_exists'), 1 );
    add_action( 'plugins_loaded', array($this, 'vimeography_update_database'), 11 );

    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_update_tables') );
    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_update_db_version') );
  }

  /**
   * Create tables and define defaults when plugin is activated.
   *
   * @access public
   * @return void
   */
  public static function vimeography_update_tables() {
    global $wpdb;

    delete_option('vimeography_default_settings');

    add_option('vimeography_default_settings', array(
      'source_url'     => 'https://vimeo.com/channels/staffpicks/',
      'resource_uri'   => '/channels/staffpicks',
      'featured_video' => '',
      'video_limit'    => 25,
      'cache_timeout'  => 3600,
      'theme_name'     => 'bugsauce',
    ));

    $sql = 'CREATE TABLE '.VIMEOGRAPHY_GALLERY_TABLE.' (
    id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    title varchar(150) NOT NULL,
    date_created datetime NOT NULL,
    is_active tinyint(1) NOT NULL,
    PRIMARY KEY  (id)
    );
    CREATE TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' (
    id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    gallery_id mediumint(8) unsigned NOT NULL,
    source_url varchar(100) NOT NULL,
    resource_uri varchar(100) NOT NULL,
    featured_video varchar(100) DEFAULT NULL,
    video_limit mediumint(7) NOT NULL,
    gallery_width varchar(10) DEFAULT NULL,
    cache_timeout mediumint(7) NOT NULL,
    theme_name varchar(50) NOT NULL,
    PRIMARY KEY  (id)
    );
    ';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  /**
   * Gets Vimeography version number if it exists in the database.
   *
   * @since 1.2
   * @return bool
   */
  public static function vimeography_get_db_version() {
    return get_site_option('vimeography_db_version');
  }

  /**
   * Updates the Vimeography version number stored in the database.
   *
   * @access public
   * @static
   * @return bool
   */
  public static function vimeography_update_db_version() {
    return update_site_option('vimeography_db_version', VIMEOGRAPHY_VERSION);
  }

  /**
   * [vimeography_update_db_version_if_not_exists description]
   * @return [type] [description]
   */
  public static function vimeography_update_db_version_if_not_exists() {
    if (self::vimeography_get_db_version() === FALSE) {
      self::vimeography_update_db_version();
      self::$_version = self::vimeography_get_db_version();
    }
  }

  /**
   * [vimeography_update_database description]
   * @return [type] [description]
   */
  public function vimeography_update_database() {
    self::$_version = self::vimeography_get_db_version();

    self::vimeography_update_db_to_0_6();
    self::vimeography_update_db_to_0_7();
    self::vimeography_update_db_to_0_8();
    $this->vimeography_update_db_to_1_0();
    self::vimeography_update_db_to_1_0_7();
    self::vimeography_update_db_to_1_1_4();
    self::vimeography_update_db_to_1_1_6();
    self::vimeography_update_db_to_1_2();
    self::vimeography_update_db_to_1_2_8();
    self::vimeography_update_db_version();
  }


  /**
   * Check if the Vimeography database structure needs updated to version 0.6 based on the stored db version.
   *
   * @access public
   * @return void
   */
  public static function vimeography_update_db_to_0_6() {
    if ( version_compare(self::$_version, '0.6', '<') ) {
      global $wpdb;
      $old_galleries = $wpdb->get_results('SELECT * FROM '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id;');
      $new_galleries = array();

      if ( is_array($old_galleries) )
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

      self::vimeography_update_tables();

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
  public static function vimeography_update_db_to_0_7()
  {
    if ( version_compare(self::$_version, '0.7', '<') )
    {
      self::vimeography_update_tables();
    }
  }

  /**
   * Check if the Vimeography database structure needs updated to version 0.8 based on the stored db version.
   * In this update, we're converting the featured video field to contain an entire URL, not just the video ID.
   *
   * @access public
   * @return void
   */
  public static function vimeography_update_db_to_0_8()
  {
    if ( version_compare(self::$_version, '0.8', '<') )
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

      self::vimeography_update_tables();

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
   * Database changes for version 1.0
   *
   *  - Adds a resource_uri column, which contains the resource
   *    being fetched by the new API
   *
   *  - Converts the existing source URL to the resource
   *
   *  - Drops the video limit
   *
   * @return [type] [description]
   */
  public function vimeography_update_db_to_1_0()
  {

    if ( version_compare(self::$_version, '1.0', '<') )
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

      } // end row manipulation

      // Drop the video limit. Edit: 7/28/13 - decided to keep this. The next function will add it if it doesn't exist.
      // $result = $wpdb->query('ALTER TABLE '. VIMEOGRAPHY_GALLERY_META_TABLE .' DROP COLUMN video_limit');

      self::vimeography_update_tables();
    }
  }

  public static function vimeography_update_db_to_1_0_7()
  {
    if ( version_compare(self::$_version, '1.0.7', '<') )
    {
      global $wpdb;
      $wpdb->hide_errors();

      $wpdb->query('ALTER TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' ADD video_limit MEDIUMINT(7) NOT NULL AFTER featured_video;');
      self::vimeography_update_tables();
    }
  }

  public static function vimeography_update_db_to_1_1_4()
  {
    if ( version_compare(self::$_version, '1.1.4', '<') )
    {
      global $wpdb;
      $wpdb->hide_errors();

      $wpdb->query('ALTER TABLE '.VIMEOGRAPHY_GALLERY_META_TABLE.' MODIFY resource_uri VARCHAR(100) NOT NULL;');
      self::vimeography_update_tables();
    }
  }

  public static function vimeography_update_db_to_1_1_6()
  {
    if ( version_compare(self::$_version, '1.1.6', '<') )
    {
      $activation_keys = get_option('vimeography_activation_keys');

      if ($activation_keys)
      {
        foreach ($activation_keys as $entry)
        {
          $activation_key_plugin_path = str_replace('vimeography/', trailingslashit($entry->activation_key), VIMEOGRAPHY_PATH);
          $corrected_plugin_path      = str_replace('vimeography/', trailingslashit($entry->plugin_name), VIMEOGRAPHY_PATH);

          if (file_exists($activation_key_plugin_path))
          {
            // Temporarily deactivate plugin
            $old_basename = trailingslashit($entry->activation_key) . $entry->plugin_name . '.php';
            $new_basename = trailingslashit($entry->plugin_name) . $entry->plugin_name . '.php';

            // Rename folder to the correct plugin name
            rename($activation_key_plugin_path, $corrected_plugin_path);
          }
        }
      }
    }
  }

  /**
   * Loop through all of the keys and remove the lot if there are
   * false positives that have been saved. We're also moving
   * to site_option instead of just option in this release.
   *
   * @return void
   */
  public static function vimeography_update_db_to_1_2() {
    if ( version_compare(self::$_version, '1.2', '<') ) {

      $keys = get_option('vimeography_activation_keys');

      if ($keys) {
        delete_option('vimeography_activation_keys');
        update_site_option('vimeography_activation_keys', $keys);

        foreach ($keys as $key) {
          if ($key === FALSE OR $key === NULL) {
            delete_site_option('vimeography_activation_keys');
            update_site_option('vimeography_corrupt_keys_found', TRUE);
            break;
          }
        }
      }
    }
  }

  /**
   * Retrieve and store more information about any saved license keys.
   *
   * @return void
   */
  public static function vimeography_update_db_to_1_2_8() {
    if ( version_compare( self::$_version, '1.2.8', '<' ) ) {
      $licenses = get_site_option('vimeography_activation_keys');

      if ( ! class_exists('Vimeography_Update') ) {
        require_once VIMEOGRAPHY_PATH . 'lib/update.php';
        $updater = new Vimeography_Update;
      } else {
        $updater = Vimeography::get_instance()->updater;
      }

      if ($licenses) {
        foreach ($licenses as $index => $license) {

          // Retrieve more information about the license
          $result = $updater->check_license( $license );
          $license->status  = $result->license;
          $license->expires = $result->expires;
          $license->limit   = $result->license_limit;
          $license->activations_left = $result->activations_left;

          $licenses[$index] = $license;
        }
        update_site_option('vimeography_activation_keys', $licenses);
      }
    }
  }

}
