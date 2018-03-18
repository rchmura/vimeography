<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Menu {

  protected $_mustache;
  protected $_controller;
  protected $_view;

  public function __construct() {
    add_action( 'admin_menu', array($this, 'vimeography_add_menu') );
    add_filter('set-screen-option', array($this, 'set_galleries_per_page'), 10, 3);

    $this->_mustache = new Mustache_Engine( array(
      'loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates'),
    ) );
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

    $capability = apply_filters('vimeography.capabilities.menu', 'manage_options');

    add_menu_page( 'Vimeography Page Title', 'Vimeography', $capability, 'vimeography-edit-galleries', '', 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiAgIHZpZXdCb3g9IjAgMCA1NDAuOCA0MjAuOSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTQwLjggNDIwLjk7IiB4bWw6c3BhY2U9InByZXNlcnZlIj48Zz4gIDxwYXRoIGZpbGw9IiMyQTJFMzUiIGQ9Ik01NDAuOCwzMDMuN0gwVjIxLjFDMCw5LjQsOS40LDAsMjEuMSwwaDQ5OC42YzExLjYsMCwyMS4xLDkuNCwyMS4xLDIxLjFWMzAzLjd6Ii8+ICA8cGF0aCBmaWxsPSIjMkEyRTM1IiBkPSJNMTYyLjgsNDIwLjlIMjEuMUM5LjQsNDIwLjksMCw0MTEuNSwwLDM5OS45di03MC4zaDE2Mi44VjQyMC45eiIvPiAgPHJlY3QgeD0iMTg5IiB5PSIzMjkuNSIgZmlsbD0iIzQxQjNDMCIgd2lkdGg9IjE2Mi44IiBoZWlnaHQ9IjkxLjQiLz4gIDxwYXRoIGZpbGw9IiMyQTJFMzUiIGQ9Ik01MTkuNyw0MjAuOUgzNzh2LTkxLjRoMTYyLjh2NzAuM0M1NDAuOCw0MTEuNSw1MzEuMyw0MjAuOSw1MTkuNyw0MjAuOXoiLz48L2c+PC9zdmc+' );
    $hooks['edit_galleries'] = add_submenu_page( 'vimeography-edit-galleries', __('Edit Galleries', 'vimeography'), __('Edit Galleries', 'vimeography'), $capability, 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
    $hooks['new_gallery'] = add_submenu_page( 'vimeography-edit-galleries', __('New Gallery', 'vimeography'), __('New Gallery', 'vimeography'), $capability, 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
    $hooks['manage_licenses'] = add_submenu_page( 'vimeography-edit-galleries', __('Manage Licenses', 'vimeography'), __('Manage Licenses', 'vimeography'), $capability, 'vimeography-manage-activations', array(&$this, 'vimeography_render_template' ));
    if ( current_user_can( $capability ) )
      $submenu['vimeography-edit-galleries'][500] = array( __('Preview Themes', 'vimeography'), $capability , 'http://vimeography.com/themes' );
    $hooks['vimeography_pro'] = add_submenu_page( 'vimeography-edit-galleries', 'Vimeography Pro', 'Vimeography Pro', $capability, 'vimeography-pro', array(&$this, 'vimeography_render_template' ));
    $hooks['vimeography_help'] = add_submenu_page( 'vimeography-edit-galleries', __('Help', 'vimeography'), __('Help', 'vimeography'), $capability, 'vimeography-help', array(&$this, 'vimeography_render_template' ));
    $hooks['vimeography_welcome'] = add_submenu_page( 'options.php', __('Welcome to Vimeography', 'vimeography'), __('Welcome to Vimeography', 'vimeography'), $capability, 'vimeography-welcome', array(&$this, 'vimeography_render_template') );

    foreach ($hooks as $page => $hook) {
      // Runs before any output
      add_action( 'load-' . $hook, array($this, 'load_' . $page . '_page') );

      // Render whatever you want at the bottom
      // add_action( $hook, array($this, 'render_' . $page . '_page') );
    }
  }


  /**
   * [load_edit_galleries_page description]
   * @return [type] [description]
   */
  public function load_edit_galleries_page() {
    if ( isset( $_GET['id'] ) ) {
      require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/gallery/edit.php';

      if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
        do_action('vimeography-pro/load-editor');
        $this->_controller = new Vimeography_Pro_Gallery_Edit;
      } else {
        $this->_controller = new Vimeography_Gallery_Edit;
      }

      $this->_mustache->setPartialsLoader( new Mustache_Loader_CascadingLoader( array(
          new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'),
        ) )
      );

      apply_filters('vimeography-pro/load-edit-partials', $this->_mustache->getPartialsLoader());

      //$this->_mustache->setPartialsLoader(new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials'));

      $this->_view = $this->_mustache->loadTemplate('gallery/edit/layout');

    } else {

      $args = array(
        'label' => __('Galleries to show per page', 'vimeography'),
        'default' => 10,
        'option' => 'vimeography_galleries_per_page'
      );

      add_screen_option( 'per_page', $args );

      require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/gallery/list.php';
      if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
        do_action('vimeography-pro/load-list');
        $this->_controller = new Vimeography_Pro_Gallery_List;
      } else {
        $this->_controller = new Vimeography_Gallery_List;
      }
      $this->_view = $this->_mustache->loadTemplate('gallery/list');
    }
    self::vimeography_process_actions();
  }


  /**
   * [load_new_gallery_page description]
   * @return [type] [description]
   */
  public function load_new_gallery_page() {

    require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/gallery/new.php';

    if ( is_plugin_active('vimeography-pro/vimeography-pro.php') ) {
      do_action('vimeography-pro/load-new');
      $this->_controller = new Vimeography_Pro_Gallery_New;
    } else {
      $this->_controller = new Vimeography_Gallery_New;
    }

    $this->_view = $this->_mustache->loadTemplate('gallery/new');
    self::vimeography_process_actions();
  }


  /**
   * [load_manage_licenses_page description]
   * @return [type] [description]
   */
  public function load_manage_licenses_page() {
    require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/theme/list.php';
    $this->_controller = new Vimeography_Theme_List;
    $this->_view = $this->_mustache->loadTemplate('theme/list');
    self::vimeography_process_actions();
  }


  /**
   * [load_vimeography_pro_page description]
   * @return [type] [description]
   */
  public function load_vimeography_pro_page() {
    require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/vimeography/pro.php';
    $this->_controller = new Vimeography_Pro_About;
    $this->_view = $this->_mustache->loadTemplate('vimeography/pro');
    self::vimeography_process_actions();
  }


  /**
   * [load_vimeography_help_page description]
   * @return [type] [description]
   */
  public function load_vimeography_help_page() {
    require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/vimeography/help.php';
    $this->_controller = new Vimeography_Help;
    $this->_view = $this->_mustache->loadTemplate('vimeography/help');
    self::vimeography_process_actions();
  }


  /**
   * [load_vimeography_welcome_page description]
   * @return [type] [description]
   */
  public function load_vimeography_welcome_page() {
    require_once VIMEOGRAPHY_PATH . 'lib/admin/controllers/vimeography/welcome.php';
    $this->_controller = new Vimeography_Welcome;

    switch ( $_GET['step'] ) {
      case 2:
        $step = 'welcome-step-2';
        break;
      case 3:
        $step = 'welcome-step-3';
        break;
      default:
        $step = 'welcome-step-1';
        break;
    }

    $this->_controller->step = $this->_mustache->loadTemplate('vimeography/partials/' . $step)->render($this->_controller);

    $this->_view = $this->_mustache->loadTemplate('vimeography/welcome');
    self::vimeography_process_actions();
  }

  /**
   * Renders the admin template for the current page.
   *
   * @access public
   * @return void
   */
  public function vimeography_render_template() {
    $capability = apply_filters('vimeography.capabilities.menu', 'manage_options');

    if ( ! current_user_can( $capability ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.', 'vimeography' ) );
    }

    echo $this->_view->render($this->_controller);
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

  /**
   * Sets the galleries per page in the screen options on the gallery list page.
   *
   * @param [type] $status [description]
   * @param [type] $option [description]
   * @param [type] $value  [description]
   */
  public function set_galleries_per_page( $status, $option, $value ) {
    if ( 'vimeography_galleries_per_page' == $option ) {
      return $value;
    }
  }
}
