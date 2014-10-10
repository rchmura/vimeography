<?php

class Vimeography_Videos extends Vimeography_Core {
	public function __construct($settings) {
		parent::__construct($settings);
	}

	/**
	 * Get videos from the provided source.
	 *
	 * @access public
	 * @param mixed $type
	 * @return void
	 */
	public function get($type = 'videos') {
		switch ($type)
		{
			//case 'info':
			case 'videos':
				$this->_type = $type;
				break;
			default:
      	throw new Vimeography_Exception('"'.$type.'" is not a valid content type.');
		}

		if (! isset($this->_type))
			throw new Vimeography_Exception('Please specify the type of information to retrieve from Vimeo.');

		/*$data = array(
			'channel_name' => $this->_channel_name,
			'type' => $this->_type,
		);*/

		return $this->_retrieve();
	}
}
