<?php

namespace Vimeography_Unit_Tests;

class Tests_Addons extends Vimeography_UnitTestCase {

  protected $_class;
  protected $_bugsauce_path;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp() {
    parent::setUp();
    $this->_class = new \Vimeography_Addons;

    $this->_bugsauce_path = realpath( dirname( __FILE__ ) . '/../../vimeography-bugsauce/vimeography-bugsauce.php' );
  }

  /**
   * @covers Vimeography_Addons::_construct()
   */
  public function test_legacy_theme_load_hook_was_added() {
    $this->assertGreaterThan(0, has_action('vimeography/load-theme', array($this->_class, 'vimeography_load_addon_plugin') ) );
  }

  /**
   * @covers Vimeography_Addons::_construct()
   */
  public function test_load_addon_hook_was_added() {
    $this->assertGreaterThan(0, has_action('vimeography/load-addon-plugin', array($this->_class, 'vimeography_load_addon_plugin') ) );
  }

  /**
   * @covers Vimeography_Addons::vimeography_load_addon_plugin()
   */
  public function test_addon_plugin_loaded() {
    $this->_class->vimeography_load_addon_plugin($this->_bugsauce_path);

    $this->assertNotEmpty($this->_class->themes, 'Theme was not added to themes array');
    $this->assertNotEmpty($this->_class->installed_addons, 'Theme was not added to installed addons array');
    $this->assertEquals('theme', $this->_class->installed_addons[0]['type'], 'Theme was not marked as type "theme"');
  }

  /**
   * @covers Vimeography_Addons::set_active_theme()
   */
  public function test_active_theme_set() {
    $this->_class->vimeography_load_addon_plugin($this->_bugsauce_path);

    $class = $this->_class->set_active_theme('bugsauce');
    $this->assertEquals($class->themes[0], $class->active_theme, 'Bugsauce not set as active theme.');
  }

  /**
   * @covers Vimeography_Addons::set_active_theme()
   */
  public function test_exception_when_active_theme_not_set() {
    $this->_class->vimeography_load_addon_plugin($this->_bugsauce_path);
    $this->setExpectedException('Vimeography_Exception');
    $this->_class->set_active_theme('bridge');
  }

  public function tearDown() { }
}
