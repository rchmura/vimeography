<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Rewrite {

  public function __construct() {
    register_activation_hook( VIMEOGRAPHY_BASENAME, array($this, 'vimeography_flush_rewrite_rules') );
    add_filter( 'query_vars',             array($this, 'vimeography_add_query_vars') );
    add_action( 'generate_rewrite_rules', array($this, 'vimeography_add_rewrite_rules' ) );
    add_action( 'parse_request',          array($this, 'vimeography_parse_request') );
  }

  /**
   * [vimeography_flush_rewrite_rules description]
   * @return [type] [description]
   */
  public function vimeography_flush_rewrite_rules() {
    return flush_rewrite_rules();
  }

  /**
   * [vimeography_add_query_vars description]
   * @param  [type] $vars [description]
   * @return [type]       [description]
   */
  public function vimeography_add_query_vars($vars)
  {
    $vars[] = 'vimeography_action';
    $vars[] = 'vimeography_gallery_id';
    return $vars;
  }

  /**
   * Adds custom rewrite rules.
   * @param  [type] $wp_rewrite [description]
   * @return [type]             [description]
   */
  function vimeography_add_rewrite_rules($wp_rewrite)
  {
    $wp_rewrite->rules = array(
        'vimeography/([0-9]{1,4})+/refresh\/?' => $wp_rewrite->index . '?vimeography_action=refresh&vimeography_gallery_id=' . $wp_rewrite->preg_index( 1 ),
        //'vimeography/notify\/?' => $wp_rewrite->index . '?vimeography_action=' . $wp_rewrite->preg_index( 1 ),
    ) + $wp_rewrite->rules;
  }

  /**
   * [vimeography_parse_request description]
   * @param  [type] $wp [description]
   * @return [type]     [description]
   */
  public function vimeography_parse_request($wp)
  {
    if (array_key_exists('vimeography_action', $wp->query_vars) AND $wp->query_vars['vimeography_action'] == 'refresh')
    {
      require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
      $cache = new Vimeography_Cache($wp->query_vars['vimeography_gallery_id']);
      if ($cache->exists())
        $cache->delete();
      die('Thanks, Vimeo. Cache busted.');
    }
  }

}