<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Cache extends Vimeography {
  /**
   * The user's cache expiration setting for the current gallery.
   *
   * @var int | NULL
   */
  private $_expiration;

  /**
   * The path to the cache file, built in the constructor based on the
   * provided gallery id.
   *
   * @var string
   */
  private $_cache_file;

  /**
   * [__construct description]
   * @param [type] $settings [description]
   */
  public function __construct($gallery_id, $expiration = NULL) {
    wp_mkdir_p( VIMEOGRAPHY_CACHE_PATH );
    $this->_cache_file = VIMEOGRAPHY_CACHE_PATH . 'vimeography-gallery-' . $gallery_id . '.cache';

    if ( isset($expiration) )
      $this->_expiration = intval($expiration);
  }

  /**
   * Checks if a cache file exists.
   *
   * @param  string $file Path to the cache file.
   * @return bool         TRUE if exists, FALSE if not.
   */
  public function exists() {
    return file_exists($this->_cache_file);
  }

  /**
   * [expired description]
   * @param  [type] $file [description]
   * @return [type]       [description]
   */
  public function expired() {
    if (! isset($this->_expiration))
      return FALSE;

    // Check if the cache is expired
    $last_modified = @filemtime($this->_cache_file);

    if (substr($this->_cache_file, -6) == '.cache' && ($last_modified + $this->_expiration) < time()) {
      // The cache is expired, but we don't want to kill it here,
      // We only want to remove it if we do not receive a 304 response or the user forces a cache refresh.
      // Return $last_modified to make the request along with the modified time in the header.

      // $est = new DateTimeZone("America/New_York");
      // $date = new DateTime();
      // $date->setTimestamp($last_modified);
      // $date->setTimezone($est);
      // return $date->format(DateTime::ISO8601);

      return date(DATE_ISO8601, $last_modified);
    }

    return FALSE;
  }

  /**
   * Get the serialized data stored in the Vimeography cache
   *
   * @access public
   * @static
   * @param string  A cache filename
   * @return object/bool
   */
  public function get() {
    if (file_exists($this->_cache_file)) {
      return unserialize(file_get_contents($this->_cache_file));
    } else {
      return FALSE;
    }
  }

  /**
   * Writes the video set to a cache file.
   *
   * @access public
   * @static
   * @param string $file      A cache filename, equal to tokenized shortcode settings.
   * @param object $video_set Vimeo collection data
   */
  public function set($data) {
    return @file_put_contents($this->_cache_file, serialize($data));
  }

  /**
   * If there is a 304 Not Changed, update the modified time to reset the expiration
   * Changes the file modified time to current
   *
   * @param  string $file A cache filename, equal to tokenized shortcode settings.
   * @return bool Whether the file was updated or not.
   */
  public function renew() {
    if ( touch($this->_cache_file) ) {
      return $this;
    } else {
      return FALSE;
    }
  }

  /**
   * Delete a cache file if it is old or the user forces a refresh.
   *
   * @param  string $token A cache filename, equal to tokenized shortcode settings.
   * @return bool          Success or failure deleting file.
   */
  public function delete() {
    if ( substr($this->_cache_file, -6) == '.cache' )
      return unlink($this->_cache_file);
  }

  /**
   * Remove any expired cache files from the cache directory.
   * We can safely do this after the request was made, but we really shouldn't
   * get rid of valid, expired files if the 304 is working properly.
   *
   * @return void
   */
  protected function cleanup() {
    $files = scandir(VIMEOGRAPHY_CACHE_PATH);

    foreach ($files as $file) {
      $last_modified = @filemtime(VIMEOGRAPHY_CACHE_PATH . $file);

      if (substr($file, -6) == '.cache' && ($last_modified + $this->_expiration) < time())
        unlink(VIMEOGRAPHY_CACHE_PATH . $file);
    }
  }
}
