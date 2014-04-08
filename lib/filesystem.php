<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Filesystem {

  /**
   * Set to ftp for testing
   * @var string
   */
  protected $_method = '';

  /**
   * [$_url description]
   * @var string
   */
  protected $_url = '';

  /**
   * [$_fields description]
   * @var array
   */
  protected $_fields = array();

  /**
   * [__construct description]
   * @param string $url    [description]
   * @param array  $fields [description]
   */
  public function __construct( $url = '', $fields = array() ) {
    $this->_url = $url;
    $this->_fields = $fields;
  }

  /**
   * [connect description]
   * @return [type]         [description]
   */
  public function connect() {
    // Try to setup WP_Filesystem
    if ( FALSE === ( $creds = request_filesystem_credentials( $this->_url, $this->_method, FALSE, FALSE, $this->_fields ) ) )
      // A form has just been output asking the user to verify file ownership
      return FALSE;

    // If the user enters the credentials but the credentials can't be verified to setup WP_Filesystem, output the form again
    if ( ! WP_Filesystem( $creds ) ) {
      // This time produce the error that tells the user there was an error connecting
      request_filesystem_credentials( $this->_url, $this->_method, TRUE, FALSE, $this->_fields );
      return FALSE;
    }
    
    return TRUE;
  }

}
