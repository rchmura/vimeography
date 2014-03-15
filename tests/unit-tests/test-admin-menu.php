<?php

namespace Vimeography_Unit_Tests;

class Tests_Admin_Menu extends Vimeography_UnitTestCase {

  protected $_object;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp()
  {
    parent::setUp();
    $this->_object = new \Vimeography_Admin_Menu;
  }

  public function test_admin_menu_hook_was_added()
  {
    $this->assertGreaterThan(0, has_action('admin_menu', array($this->_object, 'vimeography_add_menu') ) );
  }

  public function test_vimeography_menu_was_added() {
    
    ////$GLOBALS['admin_page_hooks']
  }

  // public function tearDown() {

  // }

}



