<?php
namespace Vimeography\Api;

class Galleries extends \WP_REST_Controller
{
  public function __construct()
  {
    // needed for is_plugin_active() calls
    if (!function_exists('is_plugin_active')) {
      include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // needed for duplicate gallery calls
    if (!function_exists('request_filesystem_credentials')) {
      include_once ABSPATH . 'wp-admin/includes/file.php';
    }

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
        'args' => array(
          'allow_downloads' => array(
            'description' => esc_html__(
              'Enable downloads for a gallery. Requires Vimeography Pro.',
              'vimeography'
            ),
            'default' => false,
            'type' => 'boolean'
          ),
          'cache_timeout' => array(
            'description' => esc_html__(
              'Specifies how often to refresh the cache, in seconds.',
              'vimeography'
            ),
            'default' => 3600,
            'type' => 'integer',
            'sanitize_callback' => 'absint'
          ),
          'direction' => array(
            'description' => esc_html__(
              'Specifies whether the results should be returned in ascending or descending order.',
              'vimeography'
            ),
            'default' => "desc",
            'type' => 'string',
            'validate_callback' => function ($value, $request, $param) {
              if ($value !== "asc" && $value !== "desc") {
                return false;
              }
            }
          ),
          'enable_playlist' => array(
            'description' => esc_html__(
              'Enable auto-advance playlists for a gallery. Requires Vimeography Pro.',
              'vimeography'
            ),
            'default' => false,
            'type' => 'boolean'
          ),
          'enable_search' => array(
            'description' => esc_html__(
              'Enable search capabilities for a gallery. Requires Vimeography Pro.',
              'vimeography'
            ),
            'default' => false,
            'type' => 'boolean'
          ),
          'featured_video' => array(
            'description' => esc_html__(
              'Link to the video which should appear first in the gallery.',
              'vimeography'
            ),
            "default" => ''
          ),
          'gallery_width' => array(
            'description' => esc_html__(
              'Maximum width of the rendered DOM container of the gallery.',
              'vimeography'
            ),
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => function ($value, $request, $param) {
              preg_match('/(\d*)(px|%?)/', $value, $matches);
              // If a number value is set...
              if (!empty($matches[1])) {
                // If a '%' or 'px' is set...
                if (!empty($matches[2])) {
                  // Accept the valid matching string
                  $value = $matches[0];
                } else {
                  // Append a 'px' value to the matching number
                  $value = $matches[1] . 'px';
                }
              } else {
                // Not a valid width
                $value = '';
              }

              return $value;
            }
          ),
          'source_url' => array(
            'description' => esc_html__(
              'Vimeo source for the gallery. Usually a showcase link.',
              'vimeography'
            ),
            'type' => 'string'
          ),
          'sort' => array(
            'description' => esc_html__(
              'Sorting method to use for the gallery.',
              'vimeography'
            ),
            'type' => 'string'
          ),
          'theme_name' => array(
            'description' => esc_html__(
              'Gallery theme which is applied to the current gallery.',
              'vimeography'
            ),
            'type' => 'string'
          ),
          'title' => array(
            'description' => esc_html__(
              'Gallery title. Only appears in the admin panel.',
              'vimeography'
            ),
            'type' => 'string'
          ),
          'video_limit' => array(
            'description' => esc_html__(
              'Suppresses the number of videos which appear in the gallery. Set to 0 for no suppression.',
              'vimeography'
            ),
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'validate_callback' => function ($value, $request, $param) {
              if ($value > 25) {
                return false;
              }
            }
          ),
          'videos_per_page' => array(
            'description' => esc_html__(
              'Sets the number of videos that should be fetched on each pagination request to Vimeo.',
              'vimeography'
            ),
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'validate_callback' => function ($value, $request, $param) {
              if ($value > 100) {
                return false;
              }
            }
          )
        )
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

    register_rest_route(
      $namespace,
      '/' . $base . '/(?P<id>[\d]+)/appearance',
      array(
        array(
          'methods' => \WP_REST_Server::EDITABLE,
          'callback' => array($this, 'update_gallery_appearance'),
          'permission_callback' => array(
            $this,
            'update_item_permissions_check'
          ),
          'args' => array(
            'css' => array(
              'default' => '',
              'required' => true,
              'validate_callback' => function ($param, $request, $key) {
                // Markup is not allowed in CSS.
                $has_invalid_tags = preg_match('#</?\w+#', $param);
                return !$has_invalid_tags;
              }
            )
          )
        ),
        array(
          'methods' => \WP_REST_Server::DELETABLE,
          'callback' => array($this, 'delete_gallery_appearance'),
          'permission_callback' => array(
            $this,
            'delete_item_permissions_check'
          ),
          'args' => array(
            'force' => array(
              'default' => false
            )
          )
        )
      )
    );

    register_rest_route($namespace, '/' . $base . '/schema', array(
      'methods' => \WP_REST_Server::READABLE,
      'callback' => array($this, 'get_public_item_schema'),
      'permission_callback' => '__return_true'
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
          $filter = $wpdb->prepare('WHERE gallery.title LIKE %s ', '%' . $term . '%');
      } else {
          $filter = $wpdb->prepare('WHERE gallery.id = %d ', $term);
      }
    } else {
        $filter = '';
    }
    
    $sort = $this->_get_sort();
    $result = $wpdb->get_results(
        'SELECT * FROM ' . $wpdb->vimeography_gallery_meta . ' AS meta JOIN ' . $wpdb->vimeography_gallery . ' AS gallery ON meta.gallery_id = gallery.id ' . $filter . $sort . ';',
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
      $wpdb->prepare(
          '
          SELECT *
          FROM ' . $wpdb->vimeography_gallery_meta . ' AS meta
          JOIN ' . $wpdb->vimeography_gallery . ' AS gallery
          ON meta.gallery_id = gallery.id
          WHERE meta.gallery_id = %d
          LIMIT 1;
          ',
          $gallery_id
      )
  );

    $settings = $result[0];

    $settings = new \stdClass();
    $settings->id = intval($result[0]->gallery_id);
    $settings->date_created = $result[0]->date_created;
    $settings->cache_timeout = intval($result[0]->cache_timeout);
    $settings->featured_video = $result[0]->featured_video;
    $settings->gallery_width = $result[0]->gallery_width;
    $settings->resource_uri = $result[0]->resource_uri;
    $settings->source_url = $result[0]->source_url;
    $settings->theme_name = $result[0]->theme_name;
    $settings->title = $result[0]->title;
    $settings->video_limit = intval($result[0]->video_limit);

    // need to add pro settings like this
    // apply_filters('vimeography/gallery-settings', $this->_gallery);

    if (is_plugin_active('vimeography-pro/vimeography-pro.php')) {
      $pro_settings = $wpdb->get_results(
          $wpdb->prepare(
              '
              SELECT *
              FROM ' . $wpdb->vimeography_pro_meta . ' AS pro
              WHERE pro.gallery_id = %d
              LIMIT 1;
              ',
              $gallery_id
          )
      );

      if (empty($pro_settings)) {
        $pro_settings = \Vimeography_Pro()->database->add_default_settings(
          $gallery_id
        );
      }

      $settings->allow_downloads = (bool) $pro_settings[0]->allow_downloads;
      $settings->sort = $pro_settings[0]->sort;
      $settings->direction = $pro_settings[0]->direction;
      $settings->enable_search = (bool) $pro_settings[0]->enable_search;
      $settings->enable_tags = (bool) $pro_settings[0]->enable_tags;
      $settings->enable_playlist = (bool) $pro_settings[0]->playlist;
      $settings->videos_per_page = intval($pro_settings[0]->per_page);
    }

    return $settings;
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
    $params = $request->get_params();
    $data = array();
    $format = array();
    global $wpdb;

    if (isset($params['title']) && $params['title'] !== "") {
      $result = $wpdb->update(
        $wpdb->vimeography_gallery,
        array('title' => $params['title']),
        array('id' => $params['id']),
        array('%s'),
        array('%d')
      );

      if ($result === false) {
        return new \WP_Error('cant-update', __('Could not update gallery title', 'vimeography'), array(
          'status' => 500
        ));
      }
    }

    if (isset($params['cache_timeout'])) {
      $data['cache_timeout'] = $params['cache_timeout'];
      $format[] = '%d';
    }

    if (isset($params['video_limit'])) {
      $data['video_limit'] = $params['video_limit'];
      $format[] = '%d';
    }

    if (isset($params['featured_video'])) {
      $data['featured_video'] = $params['featured_video'];
      $format[] = '%s';
    }

    if (isset($params['gallery_width'])) {
      $data['gallery_width'] = $params['gallery_width'];
      $format[] = '%s';
    }

    if (isset($params['source_url']) && $params['source_url'] !== "") {
      try {
        $data['resource_uri'] = \Vimeography::validate_vimeo_source( $params['source_url'] );
        $format[] = '%s';

        $data['source_url'] = $params['source_url'];
        $format[] = '%s';
      } catch (Error $e) {
        return new \WP_Error('cant-update', __('Invalid Vimeo collection URL.', 'vimeography'), array(
          'status' => 500
        ));
      }
    }

    $result = $wpdb->update(
      $wpdb->vimeography_gallery_meta,
      $data,
      array('gallery_id' => $params['id']),
      $format,
      array('%d')
    );

    if ($result === false) {
      return new \WP_Error('cant-update', __('Could not update gallery metadata', 'vimeography'), array(
        'status' => 500
      ));
    }

    if (is_plugin_active('vimeography-pro/vimeography-pro.php')) {
      try {
        $data = array();
        $format = array();

        if (isset($params['videos_per_page'])) {
          $data['per_page'] = $params['videos_per_page'];
          $format[] = '%d';
        }

        if (isset($params['sort'])) {
          $data['sort'] = $params['sort'];
          $format[] = '%s';
        }

        if (isset($params['direction'])) {
          $data['direction'] = $params['direction'];
          $format[] = '%s';
        }

        if (isset($params['enable_playlist'])) {
          $data['playlist'] = $params['enable_playlist'] === true ? 1 : 0;
          $format[] = '%d';
        }

        if (isset($params['allow_downloads'])) {
          $data['allow_downloads'] =
            $params['allow_downloads'] === true ? 1 : 0;
          $format[] = '%d';
        }

        if (isset($params['enable_search'])) {
          $data['enable_search'] = $params['enable_search'] === true ? 1 : 0;
          $format[] = '%d';
        }

        if (isset($params['enable_tags'])) {
          $data['enable_tags'] = $params['enable_tags'] === true ? 1 : 0;
          $format[] = '%d';
        }

        $result = $wpdb->update(
          $wpdb->vimeography_pro_meta,
          $data,
          array('gallery_id' => $params['id']),
          $format,
          array('%d')
        );

        if ($result === false) {
          return new \WP_Error(
            'cant-update',
            __('message', 'text-domain'),
            array(
              'status' => 500
            )
          );
        }
      } catch (Exception $e) {
        return new \WP_Error('cant-update', __('message', 'text-domain'), array(
          'status' => 500
        ));
      }
    }

    require_once VIMEOGRAPHY_PATH . 'lib/deprecated/cache.php';
    $cache = new \Vimeography_Cache($params['id']);

    if ($cache->exists()) {
      $cache->delete();
    }

    return new \WP_REST_Response($result, 200);
  }

  /**
   * Delete one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request)
  {
    $params = $request->get_params();
    $id = intval($params['id']);

    try {
      global $wpdb;
      $result = $wpdb->query(
          $wpdb->prepare(
              'DELETE gallery, meta FROM ' . $wpdb->vimeography_gallery . ' gallery, ' . $wpdb->vimeography_gallery_meta . ' meta WHERE gallery.id = %d AND meta.gallery_id = %d;',
              $id,
              $id
          )
      );

      if ($result === false) {
        return new \WP_Error(
          'cant-delete',
          __('Your gallery could not be deleted.', 'vimeography'),
          array(
            'status' => 500
          )
        );
      }

      do_action('vimeography-pro/delete-gallery', $id);

      require_once VIMEOGRAPHY_PATH . 'lib/deprecated/cache.php';
      $cache = new \Vimeography_Cache($id);
      if ($cache->exists()) {
        $cache->delete();
      }

      return new \WP_REST_Response(true, 204);
    } catch (Exception $e) {
      return new \WP_Error(
        'cant-delete',
        __('Your gallery could not be deleted.', 'vimeography'),
        array(
          'status' => 500
        )
      );
    }
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
    return current_user_can('manage_options'); // admin
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
   * Stores custom CSS for a gallery in the db using
   * a key that matches the incoming gallery id
   */
  public function update_gallery_appearance($request)
  {
    $params = $request->get_params();
    $gallery_id = intval($params['id']);

    $stylesheet_key = "vimeography_gallery_" . $gallery_id;

    // Requires WordPress 4.7
    // https://github.com/WordPress/wordpress-develop/blob/6aca60d33a6f4c1e7e38dacdfcc6fb171af81831/tests/phpunit/tests/customize/custom-css-setting.php

    // this is an upsert operation.
    // https://github.com/WordPress/WordPress/blob/7ced0efbf4afa3c0eab3289596bcea9a4e367fca/wp-includes/theme.php#L1939
    if (function_exists('wp_update_custom_css_post')) {
      $r = wp_update_custom_css_post($params['css'], array(
        'stylesheet' => $stylesheet_key // should be unique per gallery, copy on galleryduplicate
      ));

      return true;
    } else {
      return new \WP_Error(
        'cant-update',
        __('Your css could not be saved.', 'vimeography'),
        array(
          'status' => 500
        )
      );
    }
  }

  public function delete_gallery_appearance($request)
  {
    $params = $request->get_params();
    $gallery_id = intval($params['id']);
    $stylesheet_key = "vimeography_gallery_" + $gallery_id;

    $css_post = wp_get_custom_css_post($stylesheet_key);
    wp_delete_post($css_post->post_id);
    return true;
  }

  /**
   * Duplicate gallery via REST API
   */
  public function duplicate_gallery($request)
  {
    $params = json_decode($request->get_body());

    if ( !empty( $request->get_param( 'vimeography_duplicate_gallery_serialized' ) ) ) {
      $params = json_decode( $request->get_param( 'vimeography_duplicate_gallery_serialized' ) );
	  $params = ( is_object( $params ) || is_array( $params ) ) ? (array) $params : [];
    }

    if ($params->copy_appearance === true) {
      // Do filesystem stuffs here… a simple file_put_contents would be nice.
      $_POST['vimeography_duplicate_gallery_serialized'] = json_encode($params);

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

      $filesystem = new \Vimeography_Filesystem($url, array(
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
          $wpdb->prepare(
              '
              SELECT *
              FROM ' . $wpdb->vimeography_gallery_meta . ' AS meta
              JOIN ' . $wpdb->vimeography_gallery . ' AS gallery
              ON meta.gallery_id = gallery.id
              WHERE meta.gallery_id = %d
              LIMIT 1;
              ',
              $id
          )
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
        } elseif (function_exists('wp_update_custom_css_post')) {
          // if 4.7+, find styles in db and copy to new gallery.
          // These are saved in the `wp_posts` table with a `post_title`
          // of vimeography_gallery_{n}
          $styles = wp_get_custom_css('vimeography_gallery_' . $id); // returns css string

          if ($styles) {
            $search_string = '#vimeography-gallery-' . $id;
            $replace_string = '#vimeography-gallery-' . $gallery_id;

            $new_css = str_ireplace($search_string, $replace_string, $styles);

            $stylesheet_key = "vimeography_gallery_" . $gallery_id;

            $r = wp_update_custom_css_post($new_css, array(
              'stylesheet' => $stylesheet_key
            ));
          }
        }
      }

      do_action('vimeography-pro/duplicate-gallery', $id, $gallery_id);
      do_action('vimeography/reload-galleries');

      return new \WP_REST_Response(null, 201);
    } catch (\Vimeography_Exception $e) {
      $message = $e->getMessage();
      return new \WP_Error(
        'cant-update',
        __(
          "Your gallery could not be duplicated. $message",
          'vimeography'
        ),
        array(
          'status' => 500
        )
      );
    }
  }
}
