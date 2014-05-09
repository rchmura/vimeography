<?php

class Vimeography_Core_Basic extends Vimeography_Core
{
  public function __construct($settings) {
    parent::__construct($settings);

    $this->_auth  = VIMEOGRAPHY_CLIENT_ID;
    $this->_vimeo = new Vimeography_Vimeo( $this->_auth );
    $this->_vimeo->set_user_agent( sprintf( 'Vimeography loves you (%s)', home_url() ) );
  }

  /**
   *
   * @param  string $resource  A Vimeo API Endpoint
   * @return int               1 if match found, 0 if not.
   */
  protected function _verify_vimeo_endpoint($resource) {
    return preg_match('#^/(users|channels|albums|groups)/(.+)#', $resource);
  }

}
