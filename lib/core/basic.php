<?php

namespace Vimeography\Core;

class Basic extends \Vimeography\Core {

  public function __construct( $engine ) {
    parent::__construct( $engine );

    $this->_auth  = VIMEOGRAPHY_ACCESS_TOKEN;
    $this->_vimeo = new \Vimeo\Vimeo( null, null, $this->_auth );
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
