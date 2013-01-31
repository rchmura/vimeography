<?php

class Vimeography_Cache extends Vimeography
{

  /**
   * Get the JSON data stored in the Vimeography cache for the provided gallery id.
   *
   * @access protected
   * @static
   * @param integer $id
   * @return string/bool
   */
  protected static function get($id)
  {
    return FALSE === ( $vimeography_cache_results = get_transient( 'vimeography_cache_'.$id ) ) ? FALSE : $vimeography_cache_results;
  }

  /**
   * Set the JSON data to the Vimeography cache for the provided gallery id.
   *
   * @access protected
   * @static
   * @param integer $id
   * @param string  $data
   * @param integer $cache_limit
   * @return bool
   */
  protected static function set($id, $data, $cache_limit)
  {
    return set_transient( 'vimeography_cache_'.$id, $data, $cache_limit );
  }

  /**
   * Clear the Vimeography cache for the provided gallery id.
   *
   * @access public
   * @static
   * @param integer $id
   * @return bool
   */
  protected static function delete($id)
  {
    return delete_transient('vimeography_cache_'.$id);
  }
}