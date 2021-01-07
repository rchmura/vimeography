<?php
namespace Vimeography\Api;

class Galleries extends \WP_REST_Controller
{
  public function __construct()
  {
    add_action('rest_api_init', function () {
      $this->register_routes();
    });
  }
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {
    $version = '1';
    $namespace = 'vimeography/v' . $version;
    $base = 'galleries';
    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_items'),
        'permission_callback' => array($this, 'get_items_permissions_check'),
        'args' => array()
      ),
      array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => array($this, 'create_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args' => $this->get_endpoint_args_for_item_schema(true)
      )
    ));
    register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
      array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_item'),
        'permission_callback' => array($this, 'get_item_permissions_check'),
        'args' => array(
          'context' => array(
            'default' => 'view'
          )
        )
      ),
      array(
        'methods' => \WP_REST_Server::EDITABLE,
        'callback' => array($this, 'update_item'),
        'permission_callback' => array($this, 'update_item_permissions_check'),
        'args' => $this->get_endpoint_args_for_item_schema(false)
      ),
      array(
        'methods' => \WP_REST_Server::DELETABLE,
        'callback' => array($this, 'delete_item'),
        'permission_callback' => array($this, 'delete_item_permissions_check'),
        'args' => array(
          'force' => array(
            'default' => false
          )
        )
      )
    ));
    register_rest_route(
      $namespace,
      '/' . $base . '/(?P<id>[\d]+)/duplicate',
      array(
        array(
          'methods' => \WP_REST_Server::EDITABLE,
          'callback' => array($this, 'duplicate_gallery'),
          'permission_callback' => array(
            $this,
            'update_item_permissions_check'
          ),
          'args' => $this->get_endpoint_args_for_item_schema(false)
        )
      )
    );
    register_rest_route($namespace, '/' . $base . '/schema', array(
      'methods' => \WP_REST_Server::READABLE,
      'callback' => array($this, 'get_public_item_schema')
    ));
  }

  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request)
  {
    $items = array(); //do a query, call another class, etc
    $data = array();

    global $wpdb;

    if (isset($_GET['s']) && !empty($_GET['s'])) {
      $term = sanitize_text_field($_GET['s']);

      if (intval($term) == 0) {
        $filter = 'WHERE gallery.title LIKE "%' . $term . '%" ';
      } else {
        $filter = 'WHERE gallery.id = "' . $term . '" ';
      }
    } else {
      $filter = '';
    }

    $sort = $this->_get_sort();
    $result = $wpdb->get_results(
      'SELECT * from ' .
        $wpdb->vimeography_gallery_meta .
        ' AS meta JOIN ' .
        $wpdb->vimeography_gallery .
        ' AS gallery ON meta.gallery_id = gallery.id ' .
        $filter .
        $sort .
        ';',
      ARRAY_A
    );
    // echo '<pre>';
    // var_dump($result);
    // echo '</pre>';
    // die;
    return $result;

    foreach ($items as $item) {
      $itemdata = $this->prepare_item_for_response($item, $request);
      $data[] = $this->prepare_response_for_collection($itemdata);
    }

    return new WP_REST_Response($data, 200);
  }

  /**
   * Get one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request)
  {
    $params = $request->get_params();
    $gallery_id = intval($params['id']);

    global $wpdb;

    $result = $wpdb->get_results(
      '
    SELECT * FROM ' .
        $wpdb->vimeography_gallery_meta .
        ' AS meta
    JOIN ' .
        $wpdb->vimeography_gallery .
        ' AS gallery
    ON meta.gallery_id = gallery.id
    WHERE meta.gallery_id = ' .
        $gallery_id .
        '
    LIMIT 1;
  '
    );

    // need to add pro settings like this
    // apply_filters('vimeography/gallery-settings', $this->_gallery);

    return $result[0];

    //get parameters from request
    $params = $request->get_params();
    $item = array(); //do a query, call another class, etc
    $data = $this->prepare_item_for_response($item, $request);

    //return a response or error based on some conditional
    if (1 == 1) {
      return new WP_REST_Response($data, 200);
    } else {
      return new WP_Error('code', __('message', 'text-domain'));
    }
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    if (function_exists('slug_some_function_to_create_item')) {
      $data = slug_some_function_to_create_item($item);
      if (is_array($data)) {
        return new WP_REST_Response($data, 200);
      }
    }

    return new WP_Error('cant-create', __('message', 'text-domain'), array(
      'status' => 500
    ));
  }

  /**
   * Update one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    if (function_exists('slug_some_function_to_update_item')) {
      $data = slug_some_function_to_update_item($item);
      if (is_array($data)) {
        return new WP_REST_Response($data, 200);
      }
    }

    return new WP_Error('cant-update', __('message', 'text-domain'), array(
      'status' => 500
    ));
  }

  /**
   * Delete one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    if (function_exists('slug_some_function_to_delete_item')) {
      $deleted = slug_some_function_to_delete_item($item);
      if ($deleted) {
        return new WP_REST_Response(true, 200);
      }
    }

    return new WP_Error('cant-delete', __('message', 'text-domain'), array(
      'status' => 500
    ));
  }

  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request)
  {
    return true;
  }

  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request)
  {
    return $this->get_items_permissions_check($request);
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request)
  {
    return current_user_can('edit_posts'); // admin
  }

  /**
   * Check if a given request has access to update a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request)
  {
    return $this->create_item_permissions_check($request);
  }

  /**
   * Check if a given request has access to delete a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request)
  {
    return $this->create_item_permissions_check($request);
  }

  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database($request)
  {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response($item, $request)
  {
    return array();
  }

  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params()
  {
    return array(
      'page' => array(
        'description' => 'Current page of the collection.',
        'type' => 'integer',
        'default' => 1,
        'sanitize_callback' => 'absint'
      ),
      'per_page' => array(
        'description' =>
          'Maximum number of items to be returned in result set.',
        'type' => 'integer',
        'default' => 10,
        'sanitize_callback' => 'absint'
      ),
      'search' => array(
        'description' => 'Limit results to those matching a string.',
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field'
      )
    );
  }

  /**
   * Returns whitelisted the ORDERBY string to use in the SQL query.
   *
   * @return string
   */
  private function _get_sort()
  {
    if (!empty($_GET['orderby'])) {
      switch ($_GET['orderby']) {
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

    if (!empty($_GET['order'])) {
      if ($_GET['order'] == 'asc' or $_GET['order'] == 'desc') {
        $order = strtoupper($_GET['order']);
      }
    } else {
      $order = 'ASC';
    }
    return $orderby . ' ' . $order;
  }

  /**
   * Duplicate gallery via REST API
   */
  public function duplicate_gallery($request)
  {
    $params = json_decode($request->get_body());

    if (
      isset($_POST['vimeography_duplicate_gallery_serialized']) &&
      !empty($_POST['vimeography_duplicate_gallery_serialized'])
    ) {
      $params = unserialize(
        stripslashes($_POST['vimeography_duplicate_gallery_serialized'])
      );
    }

    if ($params->copy_appearance === true) {
      // Do filesystem stuffs here… a simple file_put_contents would be nice.
      $_POST['vimeography_duplicate_gallery_serialized'] = serialize($params);

      $url = wp_nonce_url(
        network_admin_url(
          add_query_arg(
            array('page' => 'vimeography-edit-galleries'),
            'admin.php'
          )
        ),
        'vimeography-duplicate-gallery-action',
        'vimeography-duplicate-gallery-verification'
      );

      $filesystem = new Vimeography_Filesystem($url, array(
        'vimeography_duplicate_gallery_serialized',
        'vimeography-action'
      ));

      if ($filesystem->connect()) {
        $filesystem_connection = true;
      } else {
        exit();
      }
    }

    try {
      $id = intval($params->id);
      $title = sanitize_text_field($params->title);
      $source_url = sanitize_text_field($params->source_url);
      $resource_uri = \Vimeography::validate_vimeo_source($params->source_url);

      if (empty($title)) {
        return new \WP_Error(
          'cant-update',
          __('Make sure to give your new gallery a name!', 'vimeography'),
          array(
            'status' => 500
          )
        );
      }

      global $wpdb;
      $duplicate = $wpdb->get_results(
        'SELECT * from ' .
          $wpdb->vimeography_gallery_meta .
          ' AS meta JOIN ' .
          $wpdb->vimeography_gallery .
          ' AS gallery ON meta.gallery_id = gallery.id WHERE meta.gallery_id = ' .
          $id .
          ' LIMIT 1;'
      );

      $result = $wpdb->insert($wpdb->vimeography_gallery, array(
        'title' => $title,
        'date_created' => current_time('mysql'),
        'is_active' => 1
      ));

      if ($result === false) {
        return new \WP_Error(
          'cant-update',
          __('Your gallery could not be duplicated.', 'vimeography'),
          array(
            'status' => 500
          )
        );
      }

      $gallery_id = $wpdb->insert_id;
      $result = $wpdb->insert($wpdb->vimeography_gallery_meta, array(
        'gallery_id' => $gallery_id,
        'source_url' => $source_url,
        'video_limit' => $duplicate[0]->video_limit,
        'featured_video' => $duplicate[0]->featured_video,
        'cache_timeout' => $duplicate[0]->cache_timeout,
        'theme_name' => $duplicate[0]->theme_name,
        'resource_uri' => $resource_uri
      ));

      if ($result === false) {
        return new \WP_Error(
          'cant-update',
          __('Your gallery could not be duplicated.', 'vimeography'),
          array(
            'status' => 500
          )
        );
      }

      if (isset($filesystem_connection)) {
        $old_filename = 'vimeography-gallery-' . $id . '-custom.css';
        $old_filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $old_filename;
        $search_string = '#vimeography-gallery-' . $id;

        $new_filename = 'vimeography-gallery-' . $gallery_id . '-custom.css';
        $new_filepath = VIMEOGRAPHY_CUSTOMIZATIONS_PATH . $new_filename;
        $replace_string = '#vimeography-gallery-' . $gallery_id;

        global $wp_filesystem;

        if ($wp_filesystem->exists(VIMEOGRAPHY_CUSTOMIZATIONS_PATH)) {
          if (
            $wp_filesystem->exists($old_filepath) and
            $wp_filesystem->is_file($old_filepath)
          ) {
            $old_css = $wp_filesystem->get_contents($old_filepath);
            $new_css = str_ireplace($search_string, $replace_string, $old_css);

            // If there is an error, output a message for the user to see
            if (
              !$wp_filesystem->put_contents(
                $new_filepath,
                $new_css,
                FS_CHMOD_FILE
              )
            ) {
              return new \WP_Error(
                'cant-update',
                __(
                  'There was an error writing your file. Please try again!',
                  'vimeography'
                ),
                array(
                  'status' => 500
                )
              );
            }
          }
        }
      }

      do_action('vimeography-pro/duplicate-gallery', $id, $gallery_id);
      do_action('vimeography/reload-galleries');

      return new \WP_REST_Response(null, 201);
    } catch (\Vimeography_Exception $e) {
      return new \WP_Error(
        'cant-update',
        __(
          'Your gallery could not be duplicated. ' . $e->getMessage(),
          'vimeography'
        ),
        array(
          'status' => 500
        )
      );
    }
  }
}
