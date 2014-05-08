<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Init extends Vimeography {
  public function __construct() {
    add_action( 'init', array($this, 'vimeography_load_text_domain') );
    add_action( 'init', array($this, 'vimeography_written_block_robots') );
    add_action( 'init', array($this, 'vimeography_add_gallery_helper') );
  }

  /**
   * Localization
   * @return [type] [description]
   */
  public function vimeography_load_text_domain() {
    load_plugin_textdomain('vimeography', false, dirname( VIMEOGRAPHY_BASENAME ) . '/languages/');
  }

  /**
   * Writes to the user's ACTUAL robots.txt file (if it exists) to block directories.
   *
   * @access public
   * @return void
   */
  public function vimeography_written_block_robots() {
    // Let's check if the user has a custom robots.txt file.
    if ( file_exists( ABSPATH . '/robots.txt' ) ) {
      // See if our rule already exists inside of it.
      $robotstxt = file_get_contents( ABSPATH . '/robots.txt' );
      $blocked_asset_path = str_ireplace( site_url(), '', VIMEOGRAPHY_ASSETS_URL );

      if ( strpos( $robotstxt, 'Disallow: ' . $blocked_asset_path === FALSE ) ) {
        // Write our rule.
        $robotstxt .= "\nDisallow: " . $blocked_asset_path."\n";
        file_put_contents(ABSPATH . '/robots.txt', $robotstxt);
      }
    }
    return TRUE;
  }

  /**
   * [vimeography_add_gallery_helper description]
   * @return [type] [description]
   */
  public function vimeography_add_gallery_helper() {
    if ( in_array(VIMEOGRAPHY_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php')) ) {
      add_action('admin_footer',  array(&$this, 'vimeography_add_mce_popup'));
    }

    if ( get_user_option('rich_editing') == 'true' ) {
      add_filter( 'mce_external_plugins', array(&$this, 'vimeography_add_editor_plugin' ));
      add_filter( 'mce_buttons', array(&$this, 'vimeography_register_editor_button') );
    }
    return TRUE;
  }

  /**
   * Action target that displays the popup to insert a form to a post/page.
   *
   * @access public
   * @return void
   */
  public function vimeography_add_mce_popup() {
    $mustache = new Mustache_Engine(
      array(
        'loader' => new Mustache_Loader_FilesystemLoader(VIMEOGRAPHY_PATH . 'lib/admin/templates'),
      )
    );
    require_once VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/mce.php';
    $view = new Vimeography_MCE;
    $template = $mustache->loadTemplate('vimeography/mce');
    echo $template->render($view);
  }

  /**
   * [vimeography_add_editor_plugin description]
   * @param  [type] $plugin_array [description]
   * @return [type]               [description]
   */
  public function vimeography_add_editor_plugin( $plugin_array ) {
    $plugin_array['vimeography'] = VIMEOGRAPHY_URL . 'lib/admin/assets/js/mce.js';
    return $plugin_array;
  }

  /**
   * [vimeography_register_editor_button description]
   * @param  [type] $buttons [description]
   * @return [type]          [description]
   */
  public function vimeography_register_editor_button($buttons) {
    array_push( $buttons, "|", "vimeography" );
    return $buttons;
  }

}
