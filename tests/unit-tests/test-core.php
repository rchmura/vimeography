<?php

namespace Vimeography_Unit_Tests;

class Tests_Core extends Vimeography_UnitTestCase {

  protected $_class;
  protected $_gallery_atts;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp()
  {
    parent::setUp();
    require_once VIMEOGRAPHY_PATH . 'lib/core.php';
    require_once VIMEOGRAPHY_PATH . 'lib/core/basic.php';

    $this->_gallery_atts = array(
      'theme' => 'bugsauce',
      'featured' => '',
      'source' => '/channels/staffpicks/videos',
      'limit' => 25,
      'cache' => 3600,
      'width' => '',
    );

    $this->_class = new \Vimeography_Core_Basic($this->_gallery_atts);
  }

  public function test_vimeography_admin_plugins_instance() {
    $this->assertClassHasAttribute( '_vimeo', 'Vimeography_Core_Basic' );
  }

  /**
   * @covers Vimeography_Core::_endpoint
   */
//  public function test_vimeography_endpoint() {
//    $this->assertEquals('/channels/staffpicks/videos', $this->_class->_endpoint);
//  }

  public function tearDown() { }

}
