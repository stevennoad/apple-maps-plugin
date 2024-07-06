<?php
/*
Plugin Name: Apple Maps Plugin
Description: A plugin to add Apple Maps with multiple locations.
Version: 1.3
Author: Steve Noad
*/

// Enqueue scripts and styles
function apple_maps_enqueue_scripts() {
    wp_enqueue_script('apple-mapkit', 'https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js', array(), null, true);

    $apple_map_settings = array(
        'token' => get_option('apple_map_settings')['apple_map_token'],
        'glyph' => get_option('apple_map_settings')['apple_map_glyph']
    );
    wp_enqueue_script('apple-maps-custom-js', plugins_url('apple-maps.js', __FILE__), array('apple-mapkit'), null, true);
    wp_localize_script('apple-maps-custom-js', 'appleMapsSettings', $apple_map_settings);

    wp_enqueue_style('apple-maps-custom-css', plugins_url('apple-maps.css', __FILE__));

    // Debug logs
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Apple Maps scripts and styles enqueued.');
    }
}
add_action('wp_enqueue_scripts', 'apple_maps_enqueue_scripts');

// Shortcode for displaying Apple Map
function apple_maps_shortcode($atts) {
    static $map_counter = 0;
    $map_counter++;

    $options = get_option('apple_map_settings');

    $default_atts = array(
        'lat' => isset($options['apple_map_default_lat']) ? $options['apple_map_default_lat'] : '',
        'lng' => isset($options['apple_map_default_lng']) ? $options['apple_map_default_lng'] : '',
        'title' => isset($options['apple_map_default_title']) ? $options['apple_map_default_title'] : '',
        'address' => isset($options['apple_map_default_address']) ? $options['apple_map_default_address'] : '',
        'phone' => isset($options['apple_map_default_phone']) ? $options['apple_map_default_phone'] : '',
        'hours' => '',
        'url' => isset($options['apple_map_default_url']) ? $options['apple_map_default_url'] : '',
    );

    $atts = shortcode_atts($default_atts, $atts, 'apple_map');

    $map_id = 'apple-map-' . $map_counter;

    // Debug log
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Apple Maps shortcode rendered with map ID: ' . $map_id);
    }

    ob_start();
    ?>
    <div id="<?php echo esc_attr($map_id); ?>" class="apple-map-container"
         data-lat="<?php echo esc_attr($atts['lat']); ?>"
         data-lng="<?php echo esc_attr($atts['lng']); ?>"
         data-title="<?php echo esc_attr($atts['title']); ?>"
         data-address="<?php echo esc_attr($atts['address']); ?>"
         data-phone="<?php echo esc_attr($atts['phone']); ?>"
         data-hours="<?php echo esc_attr($atts['hours']); ?>"
         data-url="<?php echo esc_attr($atts['url']); ?>"></div>
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

    add_settings_field(
        'apple_map_token',
        __('MapKit Token', 'apple_map'),
        'apple_map_token_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_glyph',
        __('Map Glyph Text', 'apple_map'),
        'apple_map_glyph_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_lat',
        __('Default Latitude', 'apple_map'),
        'apple_map_default_lat_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_lng',
        __('Default Longitude', 'apple_map'),
        'apple_map_default_lng_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_title',
        __('Default Title', 'apple_map'),
        'apple_map_default_title_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_address',
        __('Default Address', 'apple_map'),
        'apple_map_default_address_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_phone',
        __('Default Phone', 'apple_map'),
        'apple_map_default_phone_render',
        'apple_map',
        'apple_map_section'
    );

    add_settings_field(
        'apple_map_default_url',
        __('Default URL', 'apple_map'),
        'apple_map_default_url_render',
        'apple_map',
        'apple_map_section'
    );
}

function apple_map_token_render() {
    $options = get_option('apple_map_settings');
    $token_value = isset($options['apple_map_token']) ? $options['apple_map_token'] : '';
    ?>
    <textarea name='apple_map_settings[apple_map_token]' rows="5" cols="100"><?php echo esc_textarea($token_value); ?></textarea>
    <?php
}

function apple_map_glyph_render() {
    $options = get_option('apple_map_settings');
    $glyph_value = isset($options['apple_map_glyph']) ? $options['apple_map_glyph'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_glyph]' size="50" value='<?php echo esc_attr($glyph_value); ?>'>
    <p class="description"><?php _e('Enter the glyph for the map here (e.g., "i", "M", "A").', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_lat_render() {
    $options = get_option('apple_map_settings');
    $default_lat = isset($options['apple_map_default_lat']) ? $options['apple_map_default_lat'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_lat]' size="50" value='<?php echo esc_attr($default_lat); ?>'>
    <p class="description"><?php _e('Enter the default latitude for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_lng_render() {
    $options = get_option('apple_map_settings');
    $default_lng = isset($options['apple_map_default_lng']) ? $options['apple_map_default_lng'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_lng]' size="50" value='<?php echo esc_attr($default_lng); ?>'>
    <p class="description"><?php _e('Enter the default longitude for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_title_render() {
    $options = get_option('apple_map_settings');
    $default_title = isset($options['apple_map_default_title']) ? $options['apple_map_default_title'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_title]' size="50" value='<?php echo esc_attr($default_title); ?>'>
    <p class="description"><?php _e('Enter the default title for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_address_render() {
    $options = get_option('apple_map_settings');
    $default_address = isset($options['apple_map_default_address']) ? $options['apple_map_default_address'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_address]' size="50" value='<?php echo esc_attr($default_address); ?>'>
    <p class="description"><?php _e('Enter the default address for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_phone_render() {
    $options = get_option('apple_map_settings');
    $default_phone = isset($options['apple_map_default_phone']) ? $options['apple_map_default_phone'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_phone]' size="50" value='<?php echo esc_attr($default_phone); ?>'>
    <p class="description"><?php _e('Enter the default phone for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_default_url_render() {
    $options = get_option('apple_map_settings');
    $default_url = isset($options['apple_map_default_url']) ? $options['apple_map_default_url'] : '';
    ?>
    <input type='text' name='apple_map_settings[apple_map_default_url]' size="50" value='<?php echo esc_attr($default_url); ?>'>
    <p class="description"><?php _e('Enter the default URL for the map.', 'apple_map'); ?></p>
    <?php
}

function apple_map_settings_section_callback() {
    echo __('Enter your Apple Maps settings here.', 'apple_map');
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
?>
