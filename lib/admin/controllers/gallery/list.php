<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Gallery_List extends Vimeography_Base {

  /**
   * [$_table description]
   * @var object
   */
  protected $_table;

  /**
   * [__construct description]
   */
  public function __construct() {

    add_action('vimeography_action_duplicate_gallery', array($this, 'duplicate_gallery') );
    add_action('vimeography_action_delete_gallery', array($this, 'delete_gallery') );
    add_action('vimeography_action_bulk_process_galleries', array($this, 'bulk_process') );
    add_action('vimeography/reload-galleries', array($this, 'load_galleries') );
    add_action('admin_enqueue_scripts', array( $this, 'load_scripts' ) );

    // wp-list-table
    require_once 'table.php';
    $this->_table = new Vimeography_Gallery_List_Table;
    $this->load_galleries();

    if ( isset( $_GET['duplicated'] ) && $_GET['duplicated'] == 1) {
      $this->messages[] = array(
        'type' => 'updated',
        'heading' => __('Gallery duplicated.', 'vimeography'),
        'message' => __('You now have a clone of your own.', 'vimeography')
      );
    }
  }

  /**
   * Enqueues the scripts for the Vimeography gallery list page
   * @return void
   */
  public function load_scripts() {
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style ('wp-jquery-ui-dialog');
  }

  /**
   * Sets the data to display in the gallery table.
   * @return void
   */
  public function load_galleries() {
    $this->_table->set_pagination();
    $this->_table->prepare_items();
  }

  /**
   * display() echo's the table right away, send the output to an object buffer and return the
   * contents for use in mustache
   *
   * @return string HTML form and table
   */
  public function gallery_table() {
    ob_start();
      echo '<form id="vimeography-gallery-list" method="get">';
      echo '<input type="hidden" name="vimeography-action" value="bulk_process_galleries">';
      echo '<input type="hidden" name="page" value="vimeography-edit-galleries" />';
      $this->_table->search_box( 'search', 'search_id' );
      $this->_table->display();
      echo '</form>';
      echo '<style>.hidden {visibility: initial;}</style>';
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  }

  /**
   * Returns the URL to the new gallery page.
   *
   * @access public
   * @return string
   */
  public function new_gallery_url() {
    return get_admin_url().'admin.php?page=vimeography-new-gallery';
  }

  /**
   * Returns several security form fields for the new gallery form.
   *
   * @access public
   * @return mixed
   */
  public function duplicate_gallery_nonce() {
    return wp_nonce_field('vimeography-duplicate-gallery-action','vimeography-duplicate-gallery-verification');
  }

  /**
   * Determines if the user sees an empty list or a list of galleries.
   *
   * @access public
   * @return bool
   */
  public function galleries_to_show() {
    return $this->_table->has_items();
  }

  /**
   * Creates a copy of the given gallery id in the database.
   *
   * @access public
   * @param array $params
   * @return void
   */
  public function duplicate_gallery($params) {

    if ( check_admin_referer( 'vimeography-duplicate-gallery-action', 'vimeography-duplicate-gallery-verification' ) ) {

      if ( isset( $params['vimeography_duplicate_gallery_serialized'] )
          AND ! empty( $params['vimeography_duplicate_gallery_serialized'] ) ) {
        $params = unserialize( stripslashes( $params['vimeography_duplicate_gallery_serialized'] ) );
      }

      if ( isset( $params['duplicate_appearance'] ) ) {
        // Do filesystem stuffs here… a simple file_put_contents would be nice.
        $_POST['vimeography_duplicate_gallery_serialized'] = serialize($params);

        $url = wp_nonce_url(
                network_admin_url(
                  add_query_arg(
                    array( 'page' => 'vimeography-edit-galleries' ),
                    'admin.php'
                  )
                ),
                'vimeography-duplicate-gallery-action',
                'vimeography-duplicate-gallery-verification'
              );

        $filesystem = new Vimeography_Filesystem( $url, array('vimeography_duplicate_gallery_serialized', 'vimeography-action') );

        if ( $filesystem->connect() ) {
          $filesystem_connection = TRUE;
        } else {
          exit;
        }
      }

      try {
        $id           = intval( $params['gallery_id'] );
        $title        = $params['gallery_title'];
        $source_url   = $params['gallery_source'];
        $resource_uri = Vimeography::validate_vimeo_source( $params['gallery_source'] );

        if ( empty( $title ) ) {
          throw new Vimeography_Exception( __('Make sure to give your new gallery a name!', 'vimeography') );
        }

        global $wpdb;
        $duplicate = $wpdb->get_results('SELECT * from '.$wpdb->vimeography_gallery_meta.' AS meta JOIN '.$wpdb->vimeography_gallery.' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = '.$id.' LIMIT 1;');

        $result = $wpdb->insert(
                    $wpdb->vimeography_gallery,
                    array(
                      'title' => $title,
                      'date_created' => current_time('mysql'),
                      'is_active' => 1
                    )
                  );

        if ( $result === FALSE ) {
          throw new Vimeography_Exception( __('Your gallery could not be duplicated.', 'vimeography') );
        }

        $gallery_id = $wpdb->insert_id;
        $result = $wpdb->insert(
                    $wpdb->vimeography_gallery_meta,
                    array(
                      'gallery_id'     => $gallery_id,
                      'source_url'     => $source_url,
                      'video_limit'    => $duplicate[0]->video_limit,
                      'featured_video' => $duplicate[0]->featured_video,
                      'cache_timeout'  => $duplicate[0]->cache_timeout,
                      'theme_name'     => $duplicate[0]->theme_name,
                      'resource_uri'   => $resource_uri
                    )
                  );

        if ( $result === FALSE ) {
          throw new Vimeography_Exception( __('Your gallery could not be duplicated.', 'vimeography') );
        }

        if ( isset( $filesystem_connection ) ) {
          $old_filename = 'vimeography-gallery-' . $id . '-custom.css';
          $old_filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $old_filename;
          $search_string = '#vimeography-gallery-' . $id;

          $new_filename = 'vimeography-gallery-' . $gallery_id . '-custom.css';
          $new_filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $new_filename;
          $replace_string = '#vimeography-gallery-' . $gallery_id;

          global $wp_filesystem;

          if ( $wp_filesystem->exists( VIMEOGRAPHY_CUSTOMIZATIONS_PATH ) ) {
            if ( $wp_filesystem->exists( $old_filepath ) AND $wp_filesystem->is_file( $old_filepath ) ) {
              $old_css = $wp_filesystem->get_contents( $old_filepath );
              $new_css = str_ireplace( $search_string, $replace_string, $old_css );

              // If there is an error, output a message for the user to see
              if ( ! $wp_filesystem->put_contents( $new_filepath, $new_css, FS_CHMOD_FILE ) ) {
                throw new Vimeography_Exception( __('There was an error writing your file. Please try again!', 'vimeography') );
              }
            }
          }
        }

        do_action('vimeography-pro/duplicate-gallery', $id, $gallery_id);
        do_action('vimeography/reload-galleries');

        $args = array(
          'page' => 'vimeography-edit-galleries',
          'duplicated' => 1,
        );

        parse_str( $_REQUEST['_wp_http_referer'], $request );

        if ( isset( $request['paged'] ) && absint( $request['paged'] ) > 1 ) {
          $args['paged'] = absint( $request['paged'] );
        }

        $url = add_query_arg( $args, get_admin_url( null, 'admin.php' ) );
        wp_redirect( $url ); exit;

      } catch (Vimeography_Exception $e) {
        $this->messages[] = array(
          'type' => 'error',
          'heading' => __('Ruh Roh.', 'vimeography'),
          'message' => $e->getMessage()
        );
      }
    }
  }

  /**
   * Deletes the gallery of the given ID in the database.
   *
   * @access public
   * @param array $params
   * @return void
   */
  public function delete_gallery($params) {
    try {
      $id = intval( $params['gallery_id'] );
      global $wpdb;
      $result = $wpdb->query('DELETE gallery, meta FROM '.$wpdb->vimeography_gallery.' gallery, '.$wpdb->vimeography_gallery_meta.' meta WHERE gallery.id = '.$id.' AND meta.gallery_id = '.$id.';');

      if ($result === FALSE)
        throw new Exception(__('Your gallery could not be deleted.', 'vimeography'));

      do_action('vimeography-pro/delete-gallery', $id);

      require_once VIMEOGRAPHY_PATH . 'lib/deprecated/cache.php';
      $cache = new Vimeography_Cache($id);
      if ($cache->exists())
        $cache->delete();

      do_action('vimeography/reload-galleries');

      $this->messages[] = array(
        'type' => 'updated',
        'heading' => __('Gallery deleted.', 'vimeography'),
        'message' => __('See you later, sucker.', 'vimeography')
      );

    } catch (Exception $e) {
      $this->messages[] = array(
        'type' => 'error',
        'heading' => __('Ruh Roh.', 'vimeography'),
        'message' => $e->getMessage()
      );
    }
  }

  /**
   * Handles the bulk deletion of galleries.
   *
   * @param  array $input contents of $_GET
   * @since  1.2.1
   * @return void
   */
  public function bulk_process($input) {
    if ($this->_table->current_action() == 'delete' AND isset( $input['gallery'] ) ) {
      if ( ! empty( $input['gallery'] ) AND is_array( $input['gallery'] ) ) {
        foreach( $input['gallery'] as $id ) {
          $this->delete_gallery( array('gallery_id' => $id ) );
        }
      }
    }
  }

}
