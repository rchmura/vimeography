<?php
namespace Vimeography_Unit_Tests;

//require dirname( __FILE__ ) . '/factory.php';

class Vimeography_UnitTestCase extends \WP_UnitTestCase {
  public function setUp() {
    parent::setUp();
    set_current_screen( 'dashboard' );
    //$this->factory = new EDD_UnitTest_Factory;
    
    //require_once dirname(__FILE__) . '/../subclasses/test_shortcode.php';
  }

  public function clean_up_global_scope() {
    parent::clean_up_global_scope();
  }

  public function assertPreConditions() {
    parent::assertPreConditions();
  }

  public function set_current_user( $user_id ) {
    wp_set_current_user( $user_id );
  }
}