<?php

class Vimeography_Theme_List extends Vimeography_Base
{
	/**
	 * Checks if there is an incoming form submission.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		self::_remove_duplicate_keys();

		if (isset($_GET['remove-activation-key']))
			$this->_remove_activation_key( strtoupper( sanitize_key( $_GET['remove-activation-key'] ) ) );

		if (isset($_POST['vimeography-activation-key']))
			$this->_validate_form();
	}

	/**
	 * Returns several security form fields for the new gallery form.
	 *
	 * @access public
	 * @return mixed
	 */
	public static function nonce() {
	   return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
	}

	/**
	 * [activation_keys description]
	 * @return [type] [description]
	 */
	public static function activation_keys() {
		return get_option('vimeography_activation_keys');
	}

	private static function _remove_duplicate_keys() {
		if ( get_option('vimeography_activation_keys') ) {
			$activation_keys = array_map("unserialize", array_unique(array_map("serialize", get_option('vimeography_activation_keys'))));
			update_option('vimeography_activation_keys', $activation_keys);
		}
	}

	/**
	 * [_remove_activation_key description]
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	private function _remove_activation_key($key) {
		$activation_keys = get_option('vimeography_activation_keys');

		if (! empty($activation_keys)) {
			foreach ($activation_keys as $i => $entry) {
				if ($entry->activation_key === $key)
					unset($activation_keys[$i]);
			}

			update_option('vimeography_activation_keys', $activation_keys);
	  	$this->messages[] = array('type' => 'success', 'heading' => __('Activation Key Removed.', 'vimeography'), 'message' => __('Your activation key has been removed from this site.', 'vimeography'));
		}
	}

	/**
	 * [_validate_form description]
	 * @return [type] [description]
	 */
	private function _validate_form() {
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( check_admin_referer('vimeography-install-theme','vimeography-theme-verification') ) {
			$key = sanitize_key($_POST['vimeography-activation-key']);
			$updater = new Vimeography_Update;
			$updater->action = 'activate';

			try {
				$response = $updater->vimeography_get_remote_info($key);

				// Get existing keys
				$activation_keys = get_option('vimeography_activation_keys');

				// Merge new key
				if ($activation_keys) {
					// Check to make sure not already activated.
					$match = FALSE;

					foreach ($activation_keys as $entry)
					{
						if ($entry->activation_key === $response->body->activation_key)
							$match = TRUE;
					}

					if ($match === FALSE)
						$activation_keys[] = $response->body;
				} else {
					$activation_keys = array( $response->body );
				}

		    $result = update_option('vimeography_activation_keys', $activation_keys );
		  	$this->messages[] = array('type' => 'success', 'heading' => __('Yee-haw!', 'vimeography'), 'message' => __($response->message));

			} catch (Vimeography_Exception $e) {
				$this->messages[] = array('type' => 'error', 'heading' => __('Uh oh.', 'vimeography'), 'message' => __($e->getMessage()));
			}
		}
	}

}
