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
		if (isset($_POST['vimeography-activation-key']))
			$this->_validate_form();
	}

	/**
	 * Returns several security form fields for the new gallery form.
	 *
	 * @access public
	 * @return mixed
	 */
	public static function nonce()
	{
	   return wp_nonce_field('vimeography-install-theme','vimeography-theme-verification');
	}

	/**
	 * [_validate_form description]
	 * @return [type] [description]
	 */
	private function _validate_form()
	{
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( check_admin_referer('vimeography-install-theme','vimeography-theme-verification') )
		{
			$key = sanitize_key($_POST['vimeography-activation-key']);
			$updater = new Vimeography_Update;
			$updater->action = 'activate';

			try
			{
				$response = $updater->vimeography_get_remote_info($key);

				// Get existing keys
				$activation_keys = get_option('vimeography_activation_keys');

				// Merge new key
				if ($activation_keys)
				{
					$activation_keys[] = $response->body;
				}
				else
				{
					$activation_keys = array( $response->body );
				}

		    $result = update_option('vimeography_activation_keys', $activation_keys );
		  	$this->messages[] = array('type' => 'success', 'heading' => 'Yee-haw!', 'message' => __($response->message));

			}
			catch (Vimeography_Exception $e)
			{
				$this->messages[] = array('type' => 'error', 'heading' => 'Uh oh.', 'message' => __($e->getMessage()));
			}
		}
	}

}