<?php

ini_set('display_errors','on');
error_reporting(E_ALL);

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

// Force WP_ADMIN to be true
define( 'WP_ADMIN', true );

function _manually_load_plugins() {
  require dirname( __FILE__ ) . '/../vimeography.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugins' );

require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/framework/testcase.php';

echo "Running Vimeography Tests...\n";

global $current_user;
$current_user = new WP_User(1);
$current_user->set_role('administrator');