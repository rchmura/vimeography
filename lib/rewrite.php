<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Rewrite {

  public function __construct() {
    add_filter( 'query_vars',             array($this, 'add_query_vars') );
    add_action( 'generate_rewrite_rules', array($this, 'add_rewrite_rules' ) );
    add_action( 'parse_request',          array($this, 'parse_request') );
    add_action( 'init', array( $this, 'flush_rewrite_rules' ) );
  }

  /**
   * Flush rewrite rules on init if our rules are not yet included/registered.
   *
   * @return void
   */
  public function flush_rewrite_rules() {
    $rules = get_site_option( 'rewrite_rules' );

    if ( ! isset( $rules['(.?.+?)(?:/([0-9]+))?/#vimeography/(.+)$'] ) ) {
      global $wp_rewrite;
      $wp_rewrite->flush_rules();
    }
  }

  /**
   * [add_query_vars description]
   * @param  [type] $vars [description]
   * @return [type]       [description]
   */
  public function add_query_vars($vars) {
    $vars[] = 'vimeography_action';
    $vars[] = 'vimeography_gallery_id';
    $vars[] = 'vimeography_request';
    return $vars;
  }

  /**
   * Adds custom rewrite rules.
   * @param  [type] $wp_rewrite [description]
   * @return [type]             [description]
   */
  public function add_rewrite_rules($wp_rewrite) {
    $wp_rewrite->rules = array(
        'vimeography/([0-9]{1,4})+/refresh\/?' => $wp_rewrite->index . '?vimeography_action=refresh&vimeography_gallery_id=' . $wp_rewrite->preg_index( 1 ),
        '(.?.+?)(?:/([0-9]+))?/#vimeography/(.+)$' => $wp_rewrite->index . '?pagename=' . $wp_rewrite->preg_index( 1 )  . '&page=' . $wp_rewrite->preg_index( 2 ) . '&vimeography_request=' . $wp_rewrite->preg_index( 3 ),
        '([^/]+)(?:/([0-9]+))?/#vimeography/(.+)$' => $wp_rewrite->index . '?name=' . $wp_rewrite->preg_index( 1 )  . '&page=' . $wp_rewrite->preg_index( 2 ) . '&vimeography_request=' . $wp_rewrite->preg_index( 3 ),
        //'vimeography/notify\/?' => $wp_rewrite->index . '?vimeography_action=' . $wp_rewrite->preg_index( 1 ),
    ) + $wp_rewrite->rules;
  }

  /**
   * [parse_request description]
   * @param  [type] $wp [description]
   * @return [type]     [description]
   */
  public function parse_request($wp) {
    if ( array_key_exists('vimeography_action', $wp->query_vars) && $wp->query_vars['vimeography_action'] == 'refresh' ) {
      require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
      $cache = new Vimeography_Cache( $wp->query_vars['vimeography_gallery_id'] );

      if ( $cache->exists() ) {
        $cache->delete();
      }

      die('Thanks, Vimeo. Cache busted.');
    }
  }

}