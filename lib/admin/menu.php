<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Menu {

  public function __construct() {
    add_action( 'admin_menu', array($this, 'vimeography_add_menu') );
  }

  /**
   * Adds a new top level menu to the admin menu.
   *
   * @access public
   * @return void
   */
  public function vimeography_add_menu() {
    global $submenu;
    $hooks = array();

    add_menu_page( 'Vimeography Page Title', 'Vimeography', 'manage_options', 'vimeography-edit-galleries', '', VIMEOGRAPHY_URL.'lib/admin/assets/img/vimeography-icon.png' );
    $hooks[] = add_submenu_page( 'vimeography-edit-galleries', __('Edit Galleries', 'vimeography'), __('Edit Galleries', 'vimeography'), 'manage_options', 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
    $hooks[] = add_submenu_page( 'vimeography-edit-galleries', __('New Gallery', 'vimeography'), __('New Gallery', 'vimeography'), 'manage_options', 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
    $hooks[] = add_submenu_page( 'vimeography-edit-galleries', __('Manage Licenses', 'vimeography'), __('Manage Licenses', 'vimeography'), 'manage_options', 'vimeography-manage-activations', array(&$this, 'vimeography_render_template' ));
    if ( current_user_can( 'manage_options' ) )
      $submenu['vimeography-edit-galleries'][500] = array( __('Vimeography Themes', 'vimeography'), 'manage_options' , 'http://vimeography.com/themes' );
    $hooks[] = add_submenu_page( 'vimeography-edit-galleries', 'Vimeography Pro', 'Vimeography Pro', 'manage_options', 'vimeography-pro', array(&$this, 'vimeography_render_template' ));
    $hooks[] = add_submenu_page( 'vimeography-edit-galleries', __('Help', 'vimeography'), __('Help', 'vimeography'), 'manage_options', 'vimeography-help', array(&$this, 'vimeography_render_template' ));
    // add_action('load-$hook', array($this, 'on_pageload') );
  }

  /**
   * Renders the admin template for the current page.
   *
   * @access public
   * @return void
   */
  public function vimeography_render_template() {
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.', 'vimeography' ) );
    }

    wp_register_style( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'lib/admin/assets/css/bootstrap.min.css');
    wp_register_style( 'vimeography-admin',     VIMEOGRAPHY_URL.'lib/admin/assets/css/admin.css');

    wp_register_script( 'vimeography-bootstrap', VIMEOGRAPHY_URL.'lib/admin/assets/js/bootstrap.min.js');
    wp_register_script( 'vimeography-admin', VIMEOGRAPHY_URL.'lib/admin/assets/js/admin.js', 'jquery');

    wp_enqueue_style( 'vimeography-bootstrap');
    wp_enqueue_style( 'vimeography-admin');

    wp_enqueue_script( 'vimeography-bootstrap');
    wp_enqueue_script( 'vimeography-admin');

    $mustache = new Mustache_Engine( array(
      'loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates'),
    ) );

    require_once VIMEOGRAPHY_PATH . 'lib/admin/base.php';

    // May want to add actions instead of doing this big switch eg:
    //add_action('load-vimeography-edit-galleries_page_vimeography-upload', array( $this, 'vimeography_upload_on_upload_pageload') );

    switch( current_filter() ) {
      case 'vimeography_page_vimeography-new-gallery':
        require_once VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/new.php';

        if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
          do_action('vimeography-pro/load-new');
          $view = new Vimeography_Pro_Gallery_New;
        } else {
          $view = new Vimeography_Gallery_New;
        }

        $template = $mustache->loadTemplate('gallery/new');
        break;
      case 'toplevel_page_vimeography-edit-galleries':
        if ( isset( $_GET['id'] ) )
        {
          if (! wp_script_is('jquery-ui')) {
            wp_register_script('jquery-ui', "//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js", false, null);
            wp_enqueue_script('jquery-ui');
          }

          require_once VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php';

          if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
            do_action('vimeography-pro/load-editor');
            $view = new Vimeography_Pro_Gallery_Edit;
          } else {
            $view = new Vimeography_Gallery_Edit;
          }

          $mustache->setPartialsLoader( new Mustache_Loader_CascadingLoader( array(
              new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'),
            ) )
          );

          apply_filters('vimeography-pro/load-edit-partials', $mustache->getPartialsLoader());

          //$mustache->setPartialsLoader(new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'));

          $template = $mustache->loadTemplate('gallery/edit/layout');

        } else {
          wp_enqueue_script('jquery-ui-dialog');
          wp_enqueue_style ('wp-jquery-ui-dialog');

          require_once VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/list.php';
          if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
            do_action('vimeography-pro/load-list');
            $view = new Vimeography_Pro_Gallery_List;
          } else {
            $view = new Vimeography_Gallery_List;
          }
          $template = $mustache->loadTemplate('gallery/list');
        }
        break;
      case 'vimeography_page_vimeography-manage-activations':
        require_once VIMEOGRAPHY_PATH . 'lib/admin/view/theme/list.php';
        $view = new Vimeography_Theme_List;
        $template = $mustache->loadTemplate('theme/list');
        break;
      case 'vimeography_page_vimeography-pro':
        wp_register_script('jquery-slick', '//cdn.jsdelivr.net/jquery.slick/1.3.8/slick.min.js', array('jquery') );
        wp_enqueue_script('jquery-slick');
        wp_register_style('jquery-slick', '//cdn.jsdelivr.net/jquery.slick/1.3.8/slick.css');
        wp_enqueue_style('jquery-slick');

        require_once VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/pro.php';
        $view = new Vimeography_Pro_About;
        $template = $mustache->loadTemplate('vimeography/pro');
        break;
      case 'vimeography_page_vimeography-help':
        require_once VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/help.php';
        $view = new Vimeography_Help;
        $template = $mustache->loadTemplate('vimeography/help');
        break;
      default:
        wp_die( sprintf( __('The admin template for "%s" cannot be found.', 'vimeography'), current_filter() ) );
      break;
    }

    self::vimeography_process_actions();
    echo $template->render($view);
  }

  /**
   * Processes all Vimeography actions sent via POST and GET by looking for the 'vimeography-action'
   * request and running do_action() to call the function
   *
   * @return void
   */
  public static function vimeography_process_actions() {
    if ( isset( $_POST['vimeography-action'] ) ) {
      do_action( 'vimeography_action_' . $_POST['vimeography-action'], $_POST );
    }

    if ( isset( $_GET['vimeography-action'] ) ) {
      do_action( 'vimeography_action_' . $_GET['vimeography-action'], $_GET );
    }
  }
}
