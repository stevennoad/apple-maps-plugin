<?php
/*
Plugin Name: Apple Maps
Plugin URI:  https://github.com/stevennoad/apple-maps-plugin
Description: A plugin to add Apple Maps with multiple locations.
Version:     2.2.0
Author:      Steve Noad
License:     MIT
Text Domain: apple-maps
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Register the shortcodes
function register_apple_map_shortcodes() {
	add_shortcode('apple_map', 'apple_map_shortcode');
	add_shortcode('apple_map_places', 'apple_map_places_shortcode');
}
add_action('init', 'register_apple_map_shortcodes');

// Enqueue scripts and styles based on the shortcode used
function apple_maps_enqueue_scripts() {
	if (is_singular()) {
		global $post;
		$apple_map_settings = get_option('apple_map_settings');
		$localize_data = [
			'token' => esc_js($apple_map_settings['apple_map_token'] ?? ''),
			'glyph' => esc_js($apple_map_settings['apple_map_glyph'] ?? '')
		];

		if (has_shortcode($post->post_content, 'apple_map') || has_shortcode($post->post_content, 'apple_map_places')) {
			wp_enqueue_script('apple-mapkit', 'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js', [], '5.x.x', true);
			$script_handle = has_shortcode($post->post_content, 'apple_map') ? 'standard.js' : 'places.js';
			wp_enqueue_script('apple-maps-custom-js', plugins_url('assets/js/' . $script_handle, __FILE__), ['apple-mapkit'], null, true);
			wp_localize_script('apple-maps-custom-js', 'appleMapsSettings', $localize_data);
			wp_enqueue_style('apple-maps-custom-css', plugins_url('assets/css/map.css', __FILE__), [], '1.0.0');
		}
	}
}
add_action('wp_enqueue_scripts', 'apple_maps_enqueue_scripts');

// Shortcode for the standard Apple Map
function apple_map_shortcode($atts, $content = null) {
	return generate_map_shortcode('apple-map', $content, false);
}

// Shortcode for the places Apple Map
function apple_map_places_shortcode($atts, $content = null) {
	return generate_map_shortcode('apple-map-places', $content, true);
}

// Generate the map shortcode
function generate_map_shortcode($base_id, $content, $is_places) {
	static $map_counter = 0;
	$map_counter++;

	$map_id = sanitize_html_class($base_id . '-' . $map_counter);
	$locations = json_decode($content, true);

	if (json_last_error() !== JSON_ERROR_NONE) {
		return '<p>Invalid JSON format.</p>';
	}

	if ($is_places) {
		$settings = $locations['settings'] ?? [];

		wp_localize_script('apple-maps-custom-js', 'appleMapsSettings', [
			'token' => esc_js(get_option('apple_map_settings')['apple_map_token'] ?? ''),
			'glyph' => esc_js(get_option('apple_map_settings')['apple_map_glyph'] ?? ''),
			'settings' => $settings
		]);
	}

	ob_start();
	?>
	<div id="<?php echo esc_attr($map_id); ?>" class="apple-map-container"
		data-landmarks='<?php echo esc_attr(json_encode($locations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>'>
	</div>
	<?php
	return ob_get_clean();
}

// Add a settings link on the plugins page
function apple_maps_add_settings_link($links) {
	$settings_link = '<a href="admin.php?page=apple_map">' . __('Settings', 'apple_map') . '</a>';
	$links[] = $settings_link;
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'apple_maps_add_settings_link');

// Admin menu and settings page
add_action('admin_menu', 'apple_map_add_admin_menu');
add_action('admin_init', 'apple_map_settings_init');

function apple_map_add_admin_menu() {
	add_options_page('Apple Map Settings', 'Apple Map', 'manage_options', 'apple_map', 'apple_map_options_page');
}

function apple_map_settings_init() {
	register_setting('apple_map', 'apple_map_settings', [
		'sanitize_callback' => 'apple_map_settings_sanitize'
	]);

	add_settings_section(
		'apple_map_section',
		__('', 'apple_map'),
		null,
		'apple_map'
	);

	$fields = [
		'apple_map_token' => __('MapKit Token', 'apple_map'),
		'apple_map_glyph' => __('Map Glyph Text', 'apple_map')
	];

	foreach ($fields as $id => $label) {
		add_settings_field(
			$id,
			$label,
			'apple_map_' . $id . '_render',
			'apple_map',
			'apple_map_section'
		);
	}
}

function apple_map_settings_sanitize($input) {
	$output = [];
	foreach ($input as $key => $value) {
		if ($key === 'apple_map_token') {
			$output[$key] = sanitize_textarea_field($value);
		} else {
			$output[$key] = sanitize_text_field($value);
		}
	}
	return $output;
}

function apple_map_render_field($field_id, $type = 'text', $description = '') {
	$options = get_option('apple_map_settings');
	$value = $options[$field_id] ?? '';

	if ($type === 'textarea') {
		echo "<textarea name='apple_map_settings[{$field_id}]' rows='5' cols='50'>" . esc_textarea($value) . "</textarea>";
	} else {
		echo "<input type='{$type}' name='apple_map_settings[{$field_id}]' size='50' value='" . esc_attr($value) . "'>";
	}

	if ($description) {
		echo "<p class='description'>" . __($description, 'apple_map') . "</p>";
	}
}

function apple_map_apple_map_token_render() {
	$token_url = '<a href="https://developer.apple.com/account/resources/services/maps-tokens" target="_blank">Apple Developer Account</a>';
	apple_map_render_field('apple_map_token', 'textarea', 'Create a token within your ' . $token_url);
}

function apple_map_apple_map_glyph_render() {
	apple_map_render_field('apple_map_glyph', 'text', 'Enter the glyph for the map here (e.g., "i", "M", "A").');
}

function apple_map_options_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	// Add nonce for security and authentication
	wp_nonce_field('apple_map_options_verify', 'apple_map_options_nonce');
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action='options.php' method='post'>
			<?php
				settings_fields('apple_map');
				do_settings_sections('apple_map');
				submit_button();
			?>
		</form>
	</div>
	<?php
}

// Verify nonce before saving options
function apple_map_verify_nonce() {
	if (!isset($_POST['apple_map_options_nonce']) || !wp_verify_nonce($_POST['apple_map_options_nonce'], 'apple_map_options_verify')) {
		wp_die(__('Verification failed', 'apple_map'), __('Error', 'apple_map'), ['response' => 403]);
	}
}
add_action('admin_post_update', 'apple_map_verify_nonce');

// Wordpress auto updater
require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/stevennoad/apple-maps-plugin/',
	__FILE__,
	'apple-maps'
);

// Set the branch that contains the stable release.
$myUpdateChecker->getVcsApi()->enableReleaseAssets();
