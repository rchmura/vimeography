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
            $devserver_path = defined('VIMEOGRAPHY_ADMIN_JS_URL') ? VIMEOGRAPHY_ADMIN_JS_URL : 'http://localhost:8024';
            wp_enqueue_script(
              'vimeography_admin_react',
              $devserver_path . '/index.js',
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

            // There was a bug introduced in Vimeography Pro 2.1 where Pro JS mistakenly depended
            // on `vimeography_admin_react` to be loaded before itself, which led to a
            // race condition of scripts loading as expected. We've corrected this
            // issue in Pro 2.1.1, so let's check to see if the user has the corrected
            // version, and if so, we can safely avoid a circular dependency loop
            // Otherwise, the user will have to live with the race condition bug
            // until they update to 2.1.1

            // Ref: https://github.com/davekiss/vimeography-pro/commit/08099c072928df96a7df6cc5b4050fe9390b35b3
            if (
              is_plugin_active('vimeography-pro/vimeography-pro.php') &&
              defined('VIMEOGRAPHY_PRO_VERSION') &&
              version_compare(VIMEOGRAPHY_PRO_VERSION, '2.1.1', '>=')
            ) {
              $deps = array('vimeography_pro_admin');
            } else {
              $deps = array();
            }

            wp_enqueue_script(
              'vimeography_admin_react',
              $script_url,
              $deps,
              "1.0",
              true
            );
          }

          wp_add_inline_script(
            'vimeography_admin_react',
            'var vimeographyThemeNonce = "' .
              wp_create_nonce("vimeography-theme-action") .
              '";',
            'before'
          );

          wp_add_inline_script(
            'vimeography_admin_react',
            'var vimeographyApiSettings = ' .
              json_encode(array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest')
              )),
            'before'
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
