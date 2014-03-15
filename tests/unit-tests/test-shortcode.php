<?php

namespace Vimeography_Unit_Tests;

class Tests_Shortcode extends Vimeography_UnitTestCase {

  protected $_class;

  protected $_gallery_atts;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   */
  public function setUp()
  {
    parent::setUp();
    $this->_class = new \Vimeography_Shortcode;  

    $this->_gallery_atts = array(
      'theme' => 'bugsauce',
      'featured' => '',
      'source' => 'https://vimeo.com/channels/staffpicks/',
      'limit' => 25,
      'cache' => 3600,
      'width' => '',
    );
  }

  public function test_shortcodes_are_registered() {
    global $shortcode_tags;
    $this->assertArrayHasKey( 'vimeography', $shortcode_tags );
  }

  /**
   * @covers Vimeography_Shortcode::_apply_shortcode_gallery_settings()
   */
  public function test_apply_shortcode_gallery_settings() {
    $expected = array(
      'theme' => 'bugsauce',
      'featured' => '',
      'source' => '/channels/staffpicks/videos',
      'limit' => 25,
      'cache' => 3600,
      'width' => '',
    );

    // Get a handle to the private method
    $apply_shortcode_gallery_settings = new \ReflectionMethod(
      'Vimeography_Shortcode',
      '_apply_shortcode_gallery_settings'
    );

    $apply_shortcode_gallery_settings->setAccessible( true );
    $actual = $apply_shortcode_gallery_settings->invoke( $this->_class, $this->_gallery_atts );
    
    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers Vimeography_Shortcode::_validate_gallery_width()
   */
  public function test_gallery_width_when_giving_pixel_amount_without_suffix() {

    // Get a handle to the private method
    $validate_gallery_width = new \ReflectionMethod(
      'Vimeography_Shortcode',
      '_validate_gallery_width'
    );

    $validate_gallery_width->setAccessible( true );
    $actual = $validate_gallery_width->invoke( $this->_class, '600' );

    $this->assertEquals('600px', $actual );
  }

  public function test_gallery_width_when_giving_pixel_amount_with_suffix() {
    // Get a handle to the private method
    $validate_gallery_width = new \ReflectionMethod(
      'Vimeography_Shortcode',
      '_validate_gallery_width'
    );

    $validate_gallery_width->setAccessible( true );
    $actual = $validate_gallery_width->invoke( $this->_class, '600px' );

    $this->assertEquals('600px', $actual);
  }

  public function test_gallery_width_when_giving_percentage_value() {
    // Get a handle to the private method
    $validate_gallery_width = new \ReflectionMethod(
      'Vimeography_Shortcode',
      '_validate_gallery_width'
    );

    $validate_gallery_width->setAccessible( true );
    $actual = $validate_gallery_width->invoke( $this->_class, '60%' );
    $this->assertEquals('60%', $actual );
  }

  public function test_invalid_gallery_width() {
    // Get a handle to the private method
    $validate_gallery_width = new \ReflectionMethod(
      'Vimeography_Shortcode',
      '_validate_gallery_width'
    );

    $validate_gallery_width->setAccessible( true );
    $actual = $validate_gallery_width->invoke( $this->_class, 'fat' );
    $this->assertEquals('', $actual );
  }

  /**
   * @covers Vimeography_Shortcode::vimeography_shortcode
   */
  public function test_shortcode_output_is_a_string()
  {
    $this->assertInternalType( 'string', $this->_class->vimeography_shortcode( array( 'id' => 1 ) ) );
  }

  public function tearDown() {
  }

}



