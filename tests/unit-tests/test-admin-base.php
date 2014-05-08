<?php

namespace Vimeography_Unit_Tests;

class Tests_Admin_Base extends Vimeography_UnitTestCase {

  protected $_object;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp()
  {
    parent::setUp();
    require_once VIMEOGRAPHY_PATH . 'lib/admin/base.php';
    $this->_object = new \Vimeography_Base;
  }

  /**
   * @covers Vimeography_Base::admin_url()
   */
  public function test_vimegraphy_admin_url_returns_plugin_base() {
    $this->assertEquals( $this->_object->admin_url(), 'http://example.org/wp-admin/admin.php?page=vimeography-' );
  }

  // public function tearDown() {

  // }

}



