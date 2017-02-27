<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Admin_Menu {

  protected $_mustache;
  protected $_controller;
  protected $_view;

  public function __construct() {
    add_action( 'admin_menu', array($this, 'vimeography_add_menu') );

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

    add_menu_page( 'Vimeography Page Title', 'Vimeography', $capability, 'vimeography-edit-galleries', '', VIMEOGRAPHY_URL.'lib/admin/assets/img/vimeography-icon.png' );
    $hooks['edit_galleries'] = add_submenu_page( 'vimeography-edit-galleries', __('Edit Galleries', 'vimeography'), __('Edit Galleries', 'vimeography'), $capability, 'vimeography-edit-galleries', array(&$this, 'vimeography_render_template' ));
    $hooks['new_gallery'] = add_submenu_page( 'vimeography-edit-galleries', __('New Gallery', 'vimeography'), __('New Gallery', 'vimeography'), $capability, 'vimeography-new-gallery', array(&$this, 'vimeography_render_template' ));
    $hooks['manage_licenses'] = add_submenu_page( 'vimeography-edit-galleries', __('Manage Licenses', 'vimeography'), __('Manage Licenses', 'vimeography'), $capability, 'vimeography-manage-activations', array(&$this, 'vimeography_render_template' ));
    if ( current_user_can( $capability ) )
      $submenu['vimeography-edit-galleries'][500] = array( __('Vimeography Themes', 'vimeography'), $capability , 'http://vimeography.com/themes' );
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
    if ( ! current_user_can( 'manage_options' ) ) {
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
}
