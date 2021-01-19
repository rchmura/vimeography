<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

class Vimeography_Admin_Scripts
{
  public function __construct()
  {
    add_action('admin_enqueue_scripts', array($this, 'add_scripts'), 10, 1);
  }

  /**
   * Load the common admin scripts across the Vimeography plugin.
   *
   * @param string $hook  slug of the current admin page
   */
  public function add_scripts($hook)
  {
    if (
      strpos($hook, 'vimeography') !== false &&
      strpos($hook, 'vimeography-stats') == false
    ) {
      wp_register_style(
        'vimeography-bootstrap',
        VIMEOGRAPHY_URL . 'lib/admin/assets/css/bootstrap.min.css'
      );
      wp_register_style(
        'vimeography-admin',
        VIMEOGRAPHY_URL . 'lib/admin/assets/css/admin.css'
      );

      wp_register_script(
        'vimeography-bootstrap',
        VIMEOGRAPHY_URL . 'lib/admin/assets/js/bootstrap.min.js'
      );

      switch ($hook) {
        case 'vimeography_page_vimeography-new-gallery':
          wp_register_script(
            'fullpage',
            'https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.9.2/jquery.fullPage.min.js',
            array('jquery')
          );
          wp_register_script(
            'select2',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
            array('jquery')
          );
          wp_register_style(
            'fullpage',
            'https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.9.2/jquery.fullPage.min.css'
          );
          wp_register_style(
            'select2',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css'
          );
          wp_enqueue_script('fullpage');
          wp_enqueue_style('fullpage');
          wp_enqueue_script('select2');
          wp_enqueue_style('select2');
          break;
        case "toplevel_page_vimeography-edit-galleries":
          if (defined('VIMEOGRAPHY_DEV') && VIMEOGRAPHY_DEV) {
            wp_enqueue_script(
              'vimeography_admin_react',
              'https://localhost:8024/index.js',
              [],
              "1.0",
              true
            );
          } else {
            $manifest = VIMEOGRAPHY_PATH . 'lib/admin/app/dist/manifest.json';
            $manifest = file_get_contents($manifest);
            $manifest = (array) json_decode($manifest);

            $script_url =
              VIMEOGRAPHY_URL . 'lib/admin/app/dist/' . $manifest['index.js'];
            wp_enqueue_script(
              'vimeography_admin_react',
              $script_url,
              [],
              "1.0",
              true
            );
          }

          wp_localize_script(
            "vimeography_admin_react",
            "vimeographyThemeNonce",
            wp_create_nonce("vimeography-theme-action")
          );

          wp_localize_script(
            'vimeography_admin_react',
            'vimeographyApiSettings',
            array(
              'root' => esc_url_raw(rest_url()),
              'nonce' => wp_create_nonce('wp_rest')
            )
          );

          break;
        default:
          wp_enqueue_style('vimeography-bootstrap');
          wp_enqueue_script('vimeography-bootstrap');
          break;
      }

      wp_enqueue_style('vimeography-admin');
      wp_enqueue_script('vimeography-admin');
    }
  }
}
