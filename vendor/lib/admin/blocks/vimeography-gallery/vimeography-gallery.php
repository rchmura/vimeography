<?php

function vimeography_gallery_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "vimeography/gallery" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'vimeography-gallery-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);

	// Add all galleries to window for dropdown population
	global $wpdb;
	$galleries = $wpdb->get_results('SELECT id, title FROM '. $wpdb->vimeography_gallery);

	wp_add_inline_script( 'vimeography-gallery-block-editor', 'var vimeography_galleries = ' . json_encode($galleries), 'before' );

	$editor_css = 'editor.css';
	wp_register_style(
		'vimeography-gallery-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'style.css';
	wp_register_style(
		'vimeography-gallery-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	// Registered in lib/shortcode instead to leverage shortcode render_callback

	// register_block_type( 'vimeography/gallery', array(
	// 	'editor_script' => 'vimeography-gallery-block-editor',
	// 	'editor_style'  => 'vimeography-gallery-block-editor',
	// 	'style'         => 'vimeography-gallery-block',
	// ) );
}
add_action( 'init', 'vimeography_gallery_block_init' );
