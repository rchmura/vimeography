<?php

namespace Vimeography_Unit_Tests;

class Tests_Admin_Gallery_Edit extends Vimeography_UnitTestCase {

  protected $_class;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp()
  {
    parent::setUp();

    require_once VIMEOGRAPHY_PATH . 'lib/admin/base.php';
    require_once VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php';
    //$this->_class = new \Vimeography_Gallery_Edit;
  }

  /**
   * @covers Vimeography_Base::admin_url()
   */
  public function test_vimegraphy_admin_url_returns_plugin_basesss() {
    global $current_screen, $menu;
    $this->assertTrue(true);
  }

  public function tearDown() { }

}
