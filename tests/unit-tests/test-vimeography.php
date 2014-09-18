<?php

namespace Vimeography_Unit_Tests;

class Tests_Vimeography extends Vimeography_UnitTestCase {

  protected $_vimeography;

  /**
   * Runs before every test.
   * Think of it as emulating what would usually happen once the plugin is activated on a Wordpress site.
   *
   */
  public function setUp()
  {
    parent::setUp();
    $this->_vimeography = \Vimeography::get_instance();

    // $z->installation_housekeeping();
    // update_option('zam_options', array(
    //     'zam_twitter_id' => 'Wern_Ancheta'
    // ));

    //update_option('vimeography_db_version', VIMEOGRAPHY_VERSION);
  }

  public function test_vimeography_instance() {
    $this->assertClassHasStaticAttribute( 'instance', 'Vimeography' );
  }

  /**
   * @covers Vimeography Contants
   */
  public function test_constants()
  {
    // Plugin Folder URL
    $path = str_replace( 'tests/unit-tests/', '', plugin_dir_url( __FILE__ ) );
    $this->assertSame( VIMEOGRAPHY_URL, $path );

    // Plugin Folder Path
    $path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
    $this->assertSame( VIMEOGRAPHY_PATH, $path );

    // URL to common assets shared by Vimeography themes
    $this->assertSame( VIMEOGRAPHY_ASSETS_URL, VIMEOGRAPHY_URL . 'lib/shared/assets/' );

    // Path to common assets shared by Vimeography themes
    $this->assertSame( VIMEOGRAPHY_ASSETS_PATH, VIMEOGRAPHY_PATH . 'lib/shared/assets/' );

    // Path to Vimeography cache files
    $this->assertSame( VIMEOGRAPHY_CACHE_PATH, WP_CONTENT_DIR . '/vimeography/cache/' );

    // Basename of the Vimeography plugin.
    $this->markTestIncomplete('The basename is not working properly, see https://github.com/wp-cli/wp-cli/issues/1037');
    //$this->assertSame( VIMEOGRAPHY_BASENAME, 'vimeography/vimeography.php' );

    // Current Version
    $this->assertSame( VIMEOGRAPHY_VERSION, '1.1.9');

    // Basename of current page
    $this->assertSame( VIMEOGRAPHY_CURRENT_PAGE, 'index.php' );
  }

  public function test_vimeography_database_tables_constants()
  {
    global $wpdb;
    $this->assertSame( VIMEOGRAPHY_GALLERY_TABLE, $wpdb->prefix . "vimeography_gallery");
    $this->assertSame( VIMEOGRAPHY_GALLERY_META_TABLE, $wpdb->prefix . "vimeography_gallery_meta");
  }

  public function test_vimeography_client_id_constant()
  {
    $this->assertSame( VIMEOGRAPHY_CLIENT_ID, 'fc0927c077cb47345eadf7c513d70f4aa564f30d' );
  }

  /**
   * @covers Easy_Digital_Downloads::includes
   */
  public function test_includes()
  {
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/init.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/cache.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/ajax.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/core.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/database.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/exception.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/helpers.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/renderer.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/shortcode.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/update.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/videos.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/core/basic.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/base.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/page.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/new.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/edit.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/gallery/list.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/theme/list.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/theme/settings/colorpicker.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/pro.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/mce.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/view/vimeography/help.php' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/page.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/new.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/list.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/layout.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials/appearance_group.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials/settings_container.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials/settings_group.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/gallery/edit/partials/themes_container.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/theme/list.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/theme/settings/colorpicker.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/vimeography/pro.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/vimeography/mce.mustache' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/templates/vimeography/help.mustache' );

    /** Check Admin Assets Exist */
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/admin.css' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/bootstrap.min.css' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/jquery-ui-1.8.19.custom.css' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_flat_0_aaaaaa_40x100.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_flat_75_ffffff_40x100.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_glass_55_fbf9ee_1x400.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_glass_65_ffffff_1x400.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_glass_75_dadada_1x400.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_glass_75_e6e6e6_1x400.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_glass_95_fef1ec_1x400.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-bg_highlight-soft_75_cccccc_1x100.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-icons_2e83ff_256x240.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-icons_222222_256x240.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-icons_454545_256x240.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-icons_888888_256x240.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jquery-ui/smoothness/images/ui-icons_cd0a0a_256x240.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/css/plugins/jScrollPane/jquery.jscrollpane.css' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/glyphicons-halflings-white.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/glyphicons-halflings.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/vimeography-icon.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/binoculars.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/binoculars.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/control_panel.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/control_panel.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/grid.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/grid.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/infinity.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/infinity.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/new_badge.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/new_badge.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/playlist.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/playlist.svg' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/img/icons/vimeo-icon.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/admin.js' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/bootstrap.min.js' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/jquery.jscrollpane.min.js' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/jquery.mousewheel.min.js' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/mce-button.png' );
    $this->assertFileExists( VIMEOGRAPHY_PATH . 'lib/admin/assets/js/mce.js' );
  }

  public function test_vimeography_shared_assets()
  {
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'css/jquery.jscrollpane.css');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'css/kendo.bootstrap.min.css');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'css/kendo.common.min.css');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'css/vimeography-common.css');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'css/textures');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'img/arrows.png');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'img/blank.png');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'img/loader.gif');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'js/pagination.js');
    $this->assertFileExists( VIMEOGRAPHY_ASSETS_PATH . 'js/utilities.js');
  }

  public function test_vimeography_cache_path_created()
  {
    require_once VIMEOGRAPHY_PATH . 'lib/cache.php';
    new \Vimeography_Cache( 1 );
    $this->assertFileExists( VIMEOGRAPHY_CACHE_PATH );
  }

  public function test_invalid_vimeo_user_url_gives_exception() {
    $this->setExpectedException('Vimeography_Exception');
    $this->_vimeography->validate_vimeo_source('http://video.com/davekiss/videos');
  }


  public function tearDown() {
    delete_site_option('vimeography_db_version');
  }

}