<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Welcome extends Vimeography_Base {
  public function __construct() { }

  /**
   * Returns several security form fields for the new gallery form.
   *
   * @access public
   * @return mixed
   */
  public function nonce() {
     return wp_nonce_field('vimeography-gallery-action','vimeography-gallery-verification');
  }

  /**
   * [gallery description]
   * @return [type] [description]
   */
  public function gallery() {
    if ( function_exists('do_shortcode') ) {

      if ( isset( $_GET['id'] ) ) {
        $shortcode = sprintf('[vimeography id="%d" width="700px"]', absint( $_GET['id'] ) );
      } else {
        $shortcode = "[vimeography source='https://vimeo.com/channels/picks' width='700px' theme='harvestone']";
      }

      return do_shortcode( $shortcode );
    }
  }

  /**
   * [gallery_source description]
   * @return [type] [description]
   */
  public function gallery_source() {
    global $wpdb;

    if ( isset( $_GET['id'] ) ) {
      $row = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT source_url FROM $wpdb->vimeography_gallery_meta WHERE gallery_id = %d",
          absint( $_GET['id'] )
        )
      );

      if ( $row ) {
        return $row[0]->source_url;
      }
    }

    return '';
  }

  /**
   * Returns the base admin url for the plugin.
   *
   * @access public
   * @return string
   */
  public function step_3_welcome_url() {
    $url = get_admin_url().'options.php?page=vimeography-welcome&step=3';

    if ( isset( $_GET['id'] ) ) {
      $url .= sprintf('&id=%d', absint( $_GET['id'] ) );
    }

    return $url;
  }

  /**
   * [edit_gallery_url description]
   * @return [type] [description]
   */
  public function edit_gallery_url() {
    $base = parent::admin_url();
    $url = sprintf( '%sedit-galleries&id=%s', $base, absint( $_GET['id'] ) );

    return $url;
  }

  /**
   * [admin_email description]
   * @return [type] [description]
   */
  public function admin_email() {
    $user = wp_get_current_user();
    return $user->user_email;
  }
}
