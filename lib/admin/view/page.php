<?php

class Vimeography_Page extends Mustache {
  public $content;

  protected $_partials = array(
  	'content' => 'duggy',
  );

	public function __construct() { }
}
