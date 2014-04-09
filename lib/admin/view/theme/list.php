<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Theme_List extends Vimeography_Base
{
	public function __construct() {
		self::_remove_duplicate_keys();
	}

	/**
	 * Returns a security form field for the form.
	 *
	 * @access public
	 * @return mixed
	 */
	public static function nonce() {
	   return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
	}

	/**
	 * Get the activation keys for the current site.
	 *
	 * array(1) {
	 *  [0]=>
	 *  object(stdClass)#758 (3) {
	 *	    ["activation_key"]=>
	 *	    string(16) "3XAMP13"
	 *	    ["plugin_name"]=>
	 *	    string(28) "vimeography-developer-bundle"
	 *	    ["product_name"]=>
	 *	    string(16) "Developer Bundle"
	 *	  }
	 *	}
	 *	
	 * @return array
	 */
	public static function activation_keys() {
		return get_site_option('vimeography_activation_keys');
	}

	/**
	 * [_remove_duplicate_keys description]
	 * @return [type] [description]
	 */
	private static function _remove_duplicate_keys() {
		if ( get_option('vimeography_activation_keys') ) {
			$activation_keys = array_map("unserialize", array_unique(array_map("serialize", get_option('vimeography_activation_keys'))));
			update_option('vimeography_activation_keys', $activation_keys);
		}
	}
}
