<?php

class Vimeography_Core_Basic extends Vimeography_Core
{
  const CLIENT_ID = 'a2beabbcbfc4cf69ae20acab8003df78';

  public function __construct($settings)
  {
    parent::__construct($settings);

    $this->_auth  = self::CLIENT_ID;
    $this->_vimeo = new Vimeo( $this->_auth );
  }

  /**
   * Sets the Vimeo source type and value based on the provided URL.
   *
   * @param   array $url A Parsed Vimeo URL
   * @return  array
   */
  private function _get_vimeo_source_data($url)
  {
    $url = array_filter(explode('/', $url['path']), 'strlen');

    // If the array doesn't contain one of the following strings, it
    // must be either a user or a video
    if (in_array($url[1], array('album', 'channels', 'groups')) !== TRUE)
    {
      if (is_numeric($url[1]))
      {
        array_unshift($url, 'video');
      }
      else
      {
        array_unshift($url, 'users');
      }
    }

    $type  = rtrim(array_shift($url), 's');
    $value = array_shift($url);

    return array('type' => $type, 'value' => $value);
  }

/**
 * Build the Vimeo API resource based on the source type and source value.
 *
 * @param  string $source_type  The source type [eg. channel, album]
 * @param  string $source_value The source value [eg. staffpicks, hd]
 * @return string               Vimeo Simple API URL
 */
  private static function _get_vimeo_endpoint($source_type, $source_value)
  {
    switch ($source_type)
    {
      case 'album':
      case 'channel':
      case 'group':
      case 'user':
        $result = '/' . $source_type . 's/' . $source_value . '/videos';
        break;
      case 'video':
        $result = '/videos/' . $source_value;
        break;
      default:
        throw new Vimeography_Exception($source_type.' is not a valid Vimeo source parameter.');
        break;
    }

    return $result;
  }

}