<?php

if (!defined('WP_UNINSTALL_PLUGIN'))
	wp_die(__('Plugin uninstallation can not be executed in this fashion.'));
	
global $wpdb;
	
define( 'VIMEOGRAPHY_GALLERY_TABLE', $wpdb->prefix . "vimeography_gallery");
define( 'VIMEOGRAPHY_GALLERY_META_TABLE', $wpdb->prefix . "vimeography_gallery_meta");

delete_option('vimeography_advanced_settings');
delete_option('vimeography_default_settings');
delete_option('vimeography_db_version');
	
$wpdb->query('DROP TABLE '.VIMEOGRAPHY_GALLERY_TABLE.', '.VIMEOGRAPHY_GALLERY_META_TABLE);

$wpdb->query('DELETE a, b
FROM
    wp_options a, wp_options b
WHERE
 a.option_name LIKE "_transient_vimeography_%" AND
 b.option_name LIKE "_transient_timeout_vimeography_%";
');

// Hell, let's do the user a favor by cleaning up ALL stale transients.

$wpdb->query('DELETE a, b
FROM
    wp_options a, wp_options b
 
WHERE
 a.option_name LIKE "_transient_%" AND
 a.option_name NOT LIKE "_transient_timeout_%" AND
 b.option_name = CONCAT(
        "_transient_timeout_",
        SUBSTRING(
            a.option_name,
            CHAR_LENGTH("_transient_") + 1
        )
    )
 AND b.option_value < UNIX_TIMESTAMP();
');

$wpdb->query('DELETE a, b
FROM
    wp_options a, wp_options b
 
WHERE
 a.option_name LIKE "_site_transient_%" AND
 a.option_name NOT LIKE "_site_transient_timeout_%" AND
 b.option_name = CONCAT(
        "_site_transient_timeout_",
        SUBSTRING(
            a.option_name,
            CHAR_LENGTH("_site_transient_") + 1
        )
    )
 AND b.option_value < UNIX_TIMESTAMP()
');