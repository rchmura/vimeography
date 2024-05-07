<?php

namespace Vimeography\Basic;

class Core extends \Vimeography\Core {

  public function __construct( $engine ) {
    parent::__construct( $engine );

    $token = trim( get_option('vimeography_access_token') );

    $this->_auth  = $token ? $token : VIMEOGRAPHY_ACCESS_TOKEN;
    $this->_vimeo = new \Vimeography\Vimeo( NULL, NULL, $this->_auth );
  }

  /**
   * Checks if the provided resource is a valid resource.
   *
   * @param  string $resource  A Vimeo API resource
   * @return int               1 if match found, 0 if not.
   */
  protected function _verify_vimeo_resource( $resource ) {
    return preg_match( '#^/(users|channels|albums|groups)/(.+)#', $resource );
  }

}
