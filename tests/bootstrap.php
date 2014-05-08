<?php

ini_set('display_errors','on');
error_reporting(E_ALL);

echo "======================================" . PHP_EOL;
echo "Welcome to the Vimeography Test Suite" . PHP_EOL;
echo "Version: 1.0" . PHP_EOL;
echo "Author: Dave Kiss" . PHP_EOL;
echo "======================================" . PHP_EOL;

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = dirname( __FILE__ ) . '/../../../../../wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

// Force WP_ADMIN to be true
define( 'WP_ADMIN', true );

function _manually_load_plugins() {
  require dirname( __FILE__ ) . '/../vimeography.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugins' );

require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/framework/testcase.php';

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

echo "Installing Vimeography...\n";

// Install Easy Digital Downloads
//edd_install();

global $current_user;
$current_user = new WP_User(1);
$current_user->set_role('administrator');