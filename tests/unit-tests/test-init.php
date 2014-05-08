<?php

namespace Vimeography_Unit_Tests;

class Tests_Vimeography_Init extends Vimeography_UnitTestCase{

  protected $_object;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   *
   */
  public function setUp()
  {
    parent::setUp();
    $this->_object = new \Vimeography_Init;
  }

  public function test_true_is_true() {
    $this->assertTrue(true);
  }

  // public function test_vimeography_admin_plugins_instance() {
  //   $this->assertClassHasStaticAttribute( 'instance', 'Vimeography_Admin_Plugins' );
  // }

  // public function test_plugin_action_links_hook_was_added()
  // {
  //   $this->assertGreaterThan(0, has_filter('plugin_action_links', array($this->_object, 'vimeography_filter_plugin_actions') ) );
  // }

  // public function tearDown() {

  // }

}



