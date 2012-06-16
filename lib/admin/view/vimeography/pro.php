<?php

class Vimeography_Pro extends Mustache 
{	
	public function __construct()
	{
		//wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		//wp_enqueue_style('cloud.css');
	}
	
	public function home_url()
	{
		return home_url();
	}
	
	public function secure_form()
	{
		return settings_fields('vimeography_advanced_settings');
	}
	
	// not done. at all.
	public function vimeography_validate_advanced_settings($input)
	{
		$output['client_id']			= wp_filter_nohtml_kses($input['client_id']);
		$output['client_secret']		= wp_filter_nohtml_kses($input['client_secret']);
		$output['access_token']			= wp_filter_nohtml_kses($input['access_token']);
		$output['access_token_secret']	= wp_filter_nohtml_kses($input['access_token_secret']);
		
		if ($output['client_id'] == '' || $output['client_secret'] == '' || $output['access_token'] == '' || $output['access_token_secret'] == '')
		{
	        add_settings_error( 'vimeography_advanced_settings', 'required', __('Whoops! Make sure you fill out all of the Vimeo tokens!'));
	        return FALSE;
		}
		
		require_once(VIMEOGRAPHY_PATH . 'lib/vimeo-advanced-api-library.php');
		
		if (class_exists('phpVimeo'))
			$vimeo = new phpVimeo($output['client_id'], $output['client_secret'], $output['access_token'], $output['access_token_secret']);
		
	    // Do an authenticated call
	    try
	    {
	        $data = $vimeo->call('vimeo.oauth.checkAccessToken');
	        if (! $data)
	        {
	        	add_settings_error( 'vimeography_advanced_settings', 'invalid', __('Woah! Looks like the Vimeo API is having some issues right now. Try this again in a little bit.'));
	        	return FALSE;
	        }
	        
	        $string = __('Success! Your Vimeo tokens for ') . $data->oauth->user->username . __(' have been added and saved.');
	        
	        // not actually an error, function name is misleading
	        add_settings_error( 'vimeography_advanced_settings', 'valid', $string, 'updated');
	        
	        $output['active'] = TRUE;
			return $output;
	    }
	    catch (VimeoAPIException $e)
	    {
	        //add_settings_error( 'vimeography_advanced_settings', $e->getCode(), "Encountered an API error -- ".$e->getMessage());
	        add_settings_error( 'vimeography_advanced_settings', $e->getCode(), "Uh oh! Your Vimeo tokens didn't validate. Try again, and double check that all of your tokens are in the correct fields!");
	        return FALSE;
	    }
	}
}