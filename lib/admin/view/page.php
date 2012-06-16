<?php

class Vimeography_Page extends Mustache 
{
    public $content;
    
    protected $_partials = array(
    	'content' => 'duggy',
    );
    
	public function __construct()
	{
		//wp_register_style('cloud.css', plugins_url('media/css/cloud.css', __FILE__ ));
		//wp_enqueue_style('cloud.css');
	}
	           
}