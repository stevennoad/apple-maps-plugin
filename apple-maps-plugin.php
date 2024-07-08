<?php
/*
Plugin Name: Apple Maps Plugin
Description: A plugin to add Apple Maps with multiple locations.
Version: 1.5.0
Author: Steve Noad
*/

// Enqueue scripts and styles
function apple_maps_enqueue_scripts() {
  wp_enqueue_script('apple-mapkit', 'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js', [], null, true);

  $apple_map_settings = get_option('apple_map_settings');
  $localize_data = [
    'token' => $apple_map_settings['apple_map_token'] ?? '',
    'glyph' => $apple_map_settings['apple_map_glyph'] ?? ''
  ];

  wp_enqueue_script('apple-maps-custom-js', plugins_url('apple-maps.js', __FILE__), ['apple-mapkit'], null, true);
  wp_localize_script('apple-maps-custom-js', 'appleMapsSettings', $localize_data);
  wp_enqueue_style('apple-maps-custom-css', plugins_url('apple-maps.css', __FILE__));

  // Debug logs
  if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Apple Maps scripts and styles enqueued.');
  }
}
add_action('wp_enqueue_scripts', 'apple_maps_enqueue_scripts');

// Add a settings link on the plugins page
function apple_maps_add_settings_link($links) {
  $settings_link = '<a href="admin.php?page=apple_map">' . __('Settings', 'apple_map') . '</a>';
  $links[] = $settings_link;
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'apple_maps_add_settings_link');

function apple_maps_shortcode($atts, $content = null) {
  static $map_counter = 0;
  $map_counter++;

  $map_id = 'apple-map-' . $map_counter;
  $locations = json_decode($content, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    return '<p>Invalid JSON format.</p>';
  }

  // Debug log
  if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Apple Maps shortcode rendered with map ID: ' . $map_id);
  }

  ob_start();
  ?>
  <div id="<?php echo esc_attr($map_id); ?>" class="apple-map-container"
       data-landmarks='<?php echo esc_attr(json_encode($locations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>'>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('apple_map', 'apple_maps_shortcode');



// Admin menu and settings page
add_action('admin_menu', 'apple_map_add_admin_menu');
add_action('admin_init', 'apple_map_settings_init');

function apple_map_add_admin_menu() {
  add_options_page('Apple Map Settings', 'Apple Map', 'manage_options', 'apple_map', 'apple_map_options_page');
}

function apple_map_settings_init() {
  register_setting('apple_map', 'apple_map_settings');

  add_settings_section(
    'apple_map_section',
    __('Apple Maps Settings', 'apple_map'),
    'apple_map_settings_section_callback',
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

function apple_map_render_field($field_id, $type = 'text', $description = '') {
  $options = get_option('apple_map_settings');
  $value = $options[$field_id] ?? '';

  if ($type === 'textarea') {
    echo "<textarea name='apple_map_settings[{$field_id}]' rows='5' cols='100'>" . esc_textarea($value) . "</textarea>";
  } else {
    echo "<input type='{$type}' name='apple_map_settings[{$field_id}]' size='50' value='" . esc_attr($value) . "'>";
  }

  if ($description) {
    echo "<p class='description'>" . __($description, 'apple_map') . "</p>";
  }
}

function apple_map_apple_map_token_render() {
  apple_map_render_field('apple_map_token', 'textarea');
}

function apple_map_apple_map_glyph_render() {
  apple_map_render_field('apple_map_glyph', 'text', 'Enter the glyph for the map here (e.g., "i", "M", "A").');
}

// Dummy callback function for the settings section (adjust as needed)
function apple_map_settings_section_callback() {
  echo '<p>This is the Apple Maps settings section.</p>';
}

function apple_map_options_page() {
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