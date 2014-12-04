<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Vimeography_Gallery_List_Table extends WP_List_Table {

  /**
   * Row limit per page
   * @var integer
   */
  protected $_per_page = 10;

  public function __construct() {
    parent::__construct();
  }

  /**
   * Set the pagination vars based on the preferred per_page
   * and the total number of galleries.
   */
  public function set_pagination() {
    global $wpdb;
    $number_of_galleries = $wpdb->get_results('SELECT COUNT(*) as count from '. VIMEOGRAPHY_GALLERY_TABLE);

    $this->set_pagination_args( array(
      'total_items' => $number_of_galleries[0]->count,
      'per_page'    => $this->_per_page
    ) );
  }

  /**
   * Retrieves the result set of galleries defined by the
   * current page and the row limit
   *
   * @return array
   */
  protected function _get_galleries() {
    global $wpdb;
    $offset = ($this->get_pagenum() - 1) * $this->_per_page;

    if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
      $filter = 'WHERE gallery.title LIKE "%' . sanitize_text_field( $_GET['s'] ) . '%" ';
    } else {
      $filter = '';
    }

    $sort = $this->_get_sort();
    $result = $wpdb->get_results('SELECT * from '.VIMEOGRAPHY_GALLERY_META_TABLE.' AS meta JOIN '.VIMEOGRAPHY_GALLERY_TABLE.' AS gallery ON meta.gallery_id = gallery.id ' . $filter . $sort . ' LIMIT '.$this->_per_page.' OFFSET '.$offset.';', ARRAY_A);
    // echo '<pre>';
    // var_dump($result);
    // echo '</pre>';
    // die;
    return $result;
  }

  /**
   * Returns whitelisted the ORDERBY string to use in the SQL query.
   *
   * @return string
   */
  private function _get_sort() {
    if ( ! empty( $_GET['orderby'] ) ) {
      switch ( $_GET['orderby'] ) {
        case 'id':
        case 'title':
        case 'date_created':
          $orderby = 'ORDER BY gallery.' . $_GET['orderby'];
          break;
        case 'theme_name':
          $orderby = 'ORDER BY meta.' . $_GET['orderby'];
          break;
        default:
          $orderby = 'ORDER BY gallery.id';
          break;
      }
    } else {
      $orderby = 'ORDER BY gallery.id';
    }

    if ( ! empty( $_GET['order'] ) ) {
      if ( $_GET['order'] == 'asc' OR $_GET['order'] == 'desc' ) {
        $order = strtoupper( $_GET['order'] );
      }
    } else {
      $order = 'ASC';
    }
    return $orderby . ' ' . $order;
  }

  /**
   * Returns the column mapping used in the WP Gallery List Table
   *
   * @return array
   */
  public function get_columns(){
    return array(
      'cb'           => '<input type="checkbox" />',
      'id'           => 'ID',
      'title'        => 'Title',
      'source_url'   => 'Video Source  <div class="wp_bootstrap" style="display:inline-block;"><span class="vimeography-question" href="#" data-toggle="tooltip" title="The location of the videos that your gallery is using."><i class="icon-question-sign"></i></span></div>',
      'shortcode'    => 'Shortcode  <div class="wp_bootstrap" style="display:inline-block;"><span class="vimeography-question" href="#" rel="tooltip" title="Copy and paste this on to your post or page to show the gallery!"><i class="icon-question-sign"></i></span></div>',
      'theme_name'   => 'Gallery Theme',
      'date_created' => 'Created on'
    );
  }

  /**
   * [prepare_items description]
   * @return [type] [description]
   */
  public function prepare_items() {
    $columns = $this->get_columns();
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = array($columns, $hidden = array(), $sortable);
    $this->items = $this->_get_galleries();
  }

  /**
   * Returns the data to show in the column
   * Used for columns that don't require any formatting.
   * @param  [type] $item        [description]
   * @param  [type] $column_name [description]
   * @return [type]              [description]
   */
  public function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'id':
        return $item[ $column_name ];
      default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }

  /**
   * Add actions to the Title column
   *
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_title($item) {
    $actions = array(
      'edit'      => sprintf('<a href="?page=vimeography-edit-galleries&id=%s">Edit</a>', $item['id'] ),
      'duplicate' => sprintf('<a class="vimeography-duplicate-gallery" data-title="%1$s" data-source="%2$s" data-gallery-id="%3$s" href="?page=vimeography-edit-galleries&vimeography-action=duplicate_gallery&gallery_id=%3$s">Duplicate</a>', $item['title'], $item['source_url'], $item['id'] ),
      'delete'    => sprintf('<a href="?page=vimeography-edit-galleries&vimeography-action=%s&gallery_id=%s">Delete</a>', 'delete_gallery', $item['id'] ),
    );

    return sprintf('<a href="?page=vimeography-edit-galleries&id=%1$s">%2$s</a> %3$s', $item['id'], $item['title'], $this->row_actions($actions) );
  }

  /**
   * [column_theme_name description]
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_theme_name($item) {
    return ucfirst($item['theme_name']);
  }

  /**
   * Formats the data for the date column.
   *
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_date_created($item) {
    return date('F jS, Y', strtotime($item['date_created'] ) );
  }

  /**
   * [column_shortcode description]
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_shortcode($item) {
    return sprintf('[vimeography id="%1$s"]', $item['id'] );
  }

  /**
   * [column_source_url description]
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_source_url($item) {
    return sprintf('<a href="%1$s" target="_blank">%2$s</a> ', $item['source_url'], $item['source_url']);
  }

  /**
   * Return the available bulk actions for this table.
   *
   * @return array
   */
  public function get_bulk_actions() {
    return array(
      'delete'    => 'Delete'
    );
  }

  /**
  * Process our bulk actions
  *
  * @since 1.2
  */
  public function process_bulk_action() {
    echo '<pre>';
    var_dump('1111');
    echo '</pre>';
    die;

    $entry_id = ( is_array( $_REQUEST['entry'] ) ) ? $_REQUEST['entry'] : array( $_REQUEST['entry'] );

    if ( 'delete' === $this->current_action() ) {
      global $wpdb;

      foreach ( $entry_id as $id ) {
        $id = absint( $id );
        $wpdb->query( "DELETE FROM $this->entries_table_name WHERE entries_id = $id" );
      }
    }
  }


  /**
   * [get_sortable_columns description]
   * @return [type] [description]
   */
  public function get_sortable_columns() {
    return array(
      'id'           => array('id', FALSE),
      'title'        => array('title', FALSE),
      'theme_name'   => array('theme_name', FALSE),
      'date_created' => array('date_created', FALSE),
    );
  }

  /**
   * Creates a checkbox column
   *
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="gallery[]" value="%s" />', $item['id']
    );
  }

}
