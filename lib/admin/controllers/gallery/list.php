<?php

class Vimeography_Gallery_List extends Vimeography_Base
{
  public function __construct()
  {
    add_action('vimeography/reload-galleries', array($this, 'load_galleries'));
  }
}
