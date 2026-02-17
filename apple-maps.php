<?php
/*
Plugin Name: Apple Maps Elementor Widget
Plugin URI:  https://github.com/stevennoad/apple-maps-plugin
Description: A plugin to add Apple Maps with multiple locations as an Elementor widget.
Version:     3.0.0
Author:      Steve Noad
License:     MIT
Text Domain: apple-maps
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register the Elementor Widget
function register_apple_maps_widget( $widgets_manager ) {
	require_once( __DIR__ . '/widgets/apple-maps-widget.php' );
	$widgets_manager->register( new \Apple_Maps_Widget() );
}
add_action( 'elementor/widgets/register', 'register_apple_maps_widget' );

// Enqueue frontend scripts
add_action( 'elementor/frontend/after_enqueue_scripts', function() {
	wp_enqueue_script(
		'mapkit-js',
		'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js',
		[],
		null,
		true
	);

	wp_enqueue_script(
		'apple-maps-script',
		plugins_url( 'assets/js/apple-maps.js', __FILE__ ),
		[ 'jquery', 'elementor-frontend', 'mapkit-js' ],
		filemtime( __DIR__ . '/assets/js/apple-maps.js' ),
		true
	);

	$mapkit_token = get_option('apple_maps_mapkit_token', '');
	wp_localize_script( 'apple-maps-script', 'AppleMapsSettings', [
		'mapkitToken' => $mapkit_token,
	] );
});

// Add a new top-level menu item for Apple Maps
add_action( 'admin_menu', 'add_apple_maps_menu_item' );

function add_apple_maps_menu_item() {
	// Create a new menu item
	add_menu_page(
		__( 'Apple Maps', 'apple-maps' ),
		__( 'Apple Maps', 'apple-maps' ),
		'manage_options',
		'apple_maps_settings',
		'apple_maps_render_settings_page',
		'dashicons-location',
		30
	);
}

// Render the Apple Maps settings page
function apple_maps_render_settings_page() {
	?>
	<div class="wrap">
		<h1><?php _e( 'Apple Maps Settings', 'apple-maps' ); ?></h1>
		<form method="POST" action="options.php">
			<?php
				// Display the settings fields and sections
				settings_fields( 'apple_maps_settings_group' );
				do_settings_sections( 'apple_maps_settings' );
			?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// Register settings for the plugin
add_action( 'admin_init', 'apple_maps_register_settings' );

function apple_maps_register_settings() {
	// Register a setting and create a settings group
	register_setting(
		'apple_maps_settings_group',
		'apple_maps_mapkit_token',
		[
			'sanitize_callback' => 'apple_maps_sanitize_mapkit_token',
		]
	);

	// Optionally add settings sections and fields here
	add_settings_section(
		'apple_maps_general_settings',
		__( 'General Settings', 'apple-maps' ),
		null,
		'apple_maps_settings'
	);

	// Add the field for the MapKit Token
	add_settings_field(
		'apple_maps_mapkit_token',
		__( 'MapKit Token', 'apple-maps' ),
		'apple_maps_mapkit_token_callback',
		'apple_maps_settings',
		'apple_maps_general_settings'
	);
}

function apple_maps_sanitize_mapkit_token( $mapkit_token ) {
	if ( ! is_string( $mapkit_token ) ) {
		return '';
	}

	$mapkit_token = wp_strip_all_tags( $mapkit_token );
	$mapkit_token = preg_replace( '/\s+/', '', $mapkit_token );

	return $mapkit_token;
}

// Callback function for the MapKit Token field
function apple_maps_mapkit_token_callback() {
		$value = get_option( 'apple_maps_mapkit_token' );
		echo '<textarea id="apple_maps_mapkit_token" name="apple_maps_mapkit_token" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
}


// WordPress auto updater
require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/stevennoad/apple-maps-plugin/',
	__FILE__,
	'apple-maps'
);
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
