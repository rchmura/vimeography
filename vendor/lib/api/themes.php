<?php
namespace Vimeography\Api;

class Themes extends \WP_REST_Controller
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
    $base = 'themes';
    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => array($this, 'get_items'),
        'permission_callback' => array($this, 'get_items_permissions_check'),
        'args' => array()
      )
    ));

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
    $themes = \Vimeography::get_instance()->addons->themes;
    $activated_themes = get_option('vimeography_activation_keys');

    $items = array();
    foreach ($themes as $theme) {
      if (is_array($activated_themes)) {
        foreach ($activated_themes as $activation) {
          if (
            strtolower($activation->plugin_name) == strtolower($theme['slug'])
          ) {
            $theme['is_licensed'] = true;
          } else {
            $theme['is_licensed'] = false;
          }
        }
      } else {
        $theme['is_licensed'] = false;
      }

      $theme["settings"] = array();

      if (file_exists($theme['settings_file'])) {
        $this->theme_supports_settings = true;
        include $theme['settings_file'];

        /* $settings is defined in the theme settings file */
        $theme["settings"] = $settings;
      }

      $out = array(
        "name" => $theme['name'],
        "description" => $theme['description'],
        "version" => $theme['version'],
        "thumbnail" => $theme['thumbnail'],
        "is_licensed" => $theme['is_licensed'],
        "settings" => $theme["settings"]
      );

      $items[] = $out;
    }

    return $items;
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
}
