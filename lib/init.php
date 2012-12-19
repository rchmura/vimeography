<?php

class Vimeography_Init extends Vimeography
{
  public function __construct() {}

  /**
   * Registers the jQuery library if it isn't already registered.
   *
   * @access public
   * @return void
   */
  public function vimeography_register_jquery()
  {
    if (! wp_script_is('jquery'))
    {
      wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js", false, null);
      wp_enqueue_script('jquery');
    }
    return TRUE;
  }

  public function vimeography_add_gallery_helper()
  {
    if (in_array(VIMEOGRAPHY_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php')))
    {
      add_action('admin_footer',  array(&$this, 'vimeography_add_mce_popup'));
    }

    if ( get_user_option('rich_editing') == 'true' )
    {
      add_filter( 'mce_external_plugins', array(&$this, 'vimeography_add_editor_plugin' ));
      add_filter( 'mce_buttons', array(&$this, 'vimeography_register_editor_button') );
    }
    return TRUE;
  }

  /**
   * Writes to the user's ACTUAL robots.txt file (if it exists) to block directories.
   *
   * @access public
   * @return void
   */
  public function vimeography_written_block_robots()
  {
    // Let's check if the user has a custom robots.txt file.
    if (file_exists(ABSPATH.'/robots.txt'))
    {
      // See if our rule already exists inside of it.
      $robotstxt = file_get_contents(ABSPATH.'/robots.txt');
      $blocked_theme_path = str_ireplace(site_url(), '', VIMEOGRAPHY_THEME_URL);
      $blocked_asset_path = str_ireplace(site_url(), '', VIMEOGRAPHY_ASSETS_URL);

      if (strpos($robotstxt, 'Disallow: '.$blocked_theme_path === FALSE))
      {
        // Write our rule.
        $robotstxt .= "\nDisallow: ".$blocked_theme_path."\n";
        file_put_contents(ABSPATH.'/robots.txt', $robotstxt);
      }
      if (strpos($robotstxt, 'Disallow: '.$blocked_asset_path === FALSE))
      {
        // Write our rule.
        $robotstxt .= "\nDisallow: ".$blocked_asset_path."\n";
        file_put_contents(ABSPATH.'/robots.txt', $robotstxt);
      }
    }
    return TRUE;
  }

  public function vimeography_register_editor_button($buttons)
  {
    array_push( $buttons, "|", "vimeography" );
    return $buttons;
  }

  public function vimeography_add_editor_plugin( $plugin_array ) {
    $plugin_array['vimeography'] = VIMEOGRAPHY_URL . 'media/js/mce.js';
    return $plugin_array;
  }

  /**
   * Action target that displays the popup to insert a form to a post/page.
   *
   * @access public
   * @return void
   */
  public function vimeography_add_mce_popup()
  {
    require_once(VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/mce.php');
    $mustache = new Vimeography_MCE();
    $template = $this->_load_template('vimeography/mce');
    echo $mustache->render($template);
  }

}