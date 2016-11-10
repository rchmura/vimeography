<?php

class Vimeography_Ajax extends Vimeography {
  public function __construct() {
    add_action( 'wp_ajax_vimeography_ajax_get_cached_videos', array( &$this, 'vimeography_ajax_get_cached_videos' ) );
    add_action( 'wp_ajax_vimeography_toggle_signup_form', array( &$this, 'vimeography_toggle_signup_form' ) );
  }

  public function vimeography_ajax_get_cached_videos() {
    // This will automatically die; if it fails
    check_ajax_referer('vimeography-get-cached-videos');

    $gallery_id = intval( $_POST['gallery_id'] );

    $data = $this->get_vimeography_cache($gallery_id);
    $videos = json_decode($data[0]);

    if (isset($data[1])) {
      // featured video option is set
      $featured = json_decode($data[1]);
      array_unshift($videos, $featured[0]);
    }

    echo json_encode($videos);

    die;
  }
  
  //Flags the user if they have signed up to the email list
  public function vimeography_toggle_signup_form() {
    update_site_option('vimeography_has_user_signed_up',true);
    wp_die();
  }
}
