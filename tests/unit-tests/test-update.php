<?php

namespace Vimeography_Unit_Tests;

class Tests_Update extends Vimeography_UnitTestCase {

  protected $_class;
  protected $_addons;

  protected $_fixture_activation_keys = array();

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp() {
    parent::setUp();
    $this->_class  = new \Vimeography_Update;
    $this->_addons = \Vimeography::get_instance()->addons;

    $entry = new \stdClass();
    $entry->activation_key = 'ABCDEFED12345678';
    $entry->product_name   = 'Journey';
    $entry->plugin_name    = 'vimeography-journey';

    $this->_fixture_activation_keys[] = $entry;
    
    $bugsauce = array(
      'name'          => 'Bugsauce',
      'theme-uri'     => 'vimeography.com/themes/bugsauce',
      'version'       => '1.0.2',
      'description'   => 'cool theme.',
      'author'        => 'Dave Kiss',
      'author-uri'    => 'http://davekiss.com',
      'basename'      => 'vimeography-bugsauce/vimeography-bugsauce.php',
      'slug'          => 'vimeography-bugsauce',
      'thumbnail'     => 'path/to/thumbnail.jpg',
      'file_path'     => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/vimeography-journey.php' ),
      'plugin_path'   => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/' ),
      'type'          => 'theme',
      'partials_path' => 'path/to/partials',
      'settings_file' => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/settings.php' ),
    );

    $journey = array(
      'name'        => 'Journey',
      'theme-uri'   => 'vimeography.com/themes/journey',
      'version'     => '1.0.5',
      'description' => 'cool theme.',
      'author'      => 'Dave Kiss',
      'author-uri' => 'http://davekiss.com',
      'basename'   => 'vimeography-journey/vimeography-journey.php',
      'slug'       => 'vimeography-journey',
      'thumbnail'  => 'path/to/thumbnail.jpg',
      'file_path'     => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/vimeography-journey.php' ),
      'plugin_path'   => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/' ),
      'type'       => 'theme',
      'partials_path' => 'path/to/partials',
      'settings_file' => realpath( dirname( __FILE__ ) . '/../fixtures/vimeography-journey/settings.php' ),
    );

    $this->_addons->themes[0] = $bugsauce;
    $this->_addons->themes[]  = $journey;
    $this->_addons->installed_addons[0] = $bugsauce;
    $this->_addons->installed_addons[] = $journey;
  }

  /**
   * @covers Vimeography_Update::_endpoint
   */
  public function test_endpoint_uri_is_correct() {
    $endpoint = new \ReflectionProperty(
      'Vimeography_Update',
      '_endpoint'
    );

    $endpoint->setAccessible( true );
    $val = $endpoint->getValue($this->_class);

    $this->assertEquals( 'http://vimeography.com', $val);
  }

  /**
   * @covers Vimeography_Update::_includes()
   */
  public function test_edd_plugin_updater_included() {
    $this->assertTrue( class_exists('EDD_SL_Plugin_Updater') );
  }

  /**
   * @covers Vimeography_Update::_hooks()
   */
  public function test_plugins_page_hook_was_added() {
    $this->assertGreaterThan(0, has_action('load-plugins.php', array($this->_class, 'vimeography_check_for_missing_activation_keys') ) );
  }

  /**
   * @covers Vimeography_Update::vimeography_is_addon_missing_activation_key()
   */
  public function test_if_missing_plugin_activation_key_returns_true() {
    $bugsauce = $this->_addons->themes[0];
    $updater  = $this->_class->vimeography_set_activation_keys($this->_fixture_activation_keys);
    $result   = $this->_class->vimeography_is_addon_missing_activation_key($bugsauce);
    $this->assertTrue( $result );
  }

  /**
   * @covers Vimeography_Update::vimeography_is_addon_missing_activation_key()
   */
  public function test_if_found_plugin_activation_key_returns_false() {
    $journey = $this->_addons->themes[1];
    $updater = $this->_class->vimeography_set_activation_keys($this->_fixture_activation_keys);
    $result  = $updater->vimeography_is_addon_missing_activation_key($journey);
    $this->assertFalse( $result );
  }

  /**
   * @covers Vimeography_Update::vimeography_check_for_missing_activation_keys()
   */
  public function test_addon_license_message_hook_added_to_plugin_row_for_addons_with_missing_licenses() {
    $this->_class->vimeography_check_for_missing_activation_keys();
    $this->assertGreaterThan(0, has_action('after_plugin_row_vimeography-journey/vimeography-journey.php', array($this->_class, 'vimeography_addon_update_message') ) );
  }

  /**
   * @covers Vimeography_Update::vimeography_check_for_missing_activation_keys()
   */
  public function test_addon_license_message_hook_skipped_for_addons_with_licenses() {
    $updater = $this->_class->vimeography_set_activation_keys($this->_fixture_activation_keys);
    $this->_class->vimeography_check_for_missing_activation_keys();
    $this->assertFalse(has_action('after_plugin_row_vimeography-journey/vimeography-journey.php', array($this->_class, 'vimeography_addon_update_message') ) );
  }

  /**
   * @covers Vimeography_Update::vimeography_check_if_activation_key_exists()
   */
  public function test_duplicate_activation_key_returns_true() {
    $updater = $this->_class->vimeography_set_activation_keys($this->_fixture_activation_keys);
    $result  = $updater->vimeography_check_if_activation_key_exists('ABCDEFED12345678');
    $this->assertTrue( $result );
  }

  /**
   * @covers Vimeography_Update::vimeography_check_if_activation_key_exists()
   */
  public function test_unique_activation_key_returns_false() {
    $result  = $this->_class->vimeography_check_if_activation_key_exists('ABCDEFED12345678');
    $this->assertFalse( $result );
  }

  /**
   * @covers Vimeography_Update::_vimeography_remove_activation_key()
   */

  public function test_remove_activation_key() {
    $this->_class->vimeography_set_activation_keys($this->_fixture_activation_keys);

    // Get a handle to the protected method
    $remove_key = new \ReflectionMethod(
      'Vimeography_Update',
      '_vimeography_remove_activation_key'
    );

    $remove_key->setAccessible( true );
    $actual = $remove_key->invoke( $this->_class, 'ABCDEFED12345678' );
    $this->assertTrue( $actual );
  }

  public function tearDown() {
    delete_site_option('vimeography_activation_keys');
  }

}
