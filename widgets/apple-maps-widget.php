<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Apple_Maps_Widget extends Widget_Base {

	public function get_script_depends() {
		return [ 'apple-maps-script' ];
	}

	public function get_name() {
		return 'apple_maps_widget';
	}

	public function get_title() {
		return __( 'Apple Maps', 'apple-maps' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'locations',
			[
				'label' => __( 'Map locations', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'map_locations',
			[
				'label'       => __( 'Locations', 'apple-maps' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => [
					[
						'name'        => 'admin_label',
						'label'       => __( 'Item Label', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => __( 'New Location', 'apple-maps' ),
					],
					[
						'name'        => 'location_type',
						'label'       => __( 'Location Type', 'apple-maps' ),
						'type'        => Controls_Manager::SELECT,
													'label_block' => true,
							'default'     => 'manual',
						'options'     => [
							'manual'   => __( 'Manual Lat/Lng', 'apple-maps' ),
							'place_id' => __( 'Place ID', 'apple-maps' ),
						],
					],
					[
						'name'        => 'place_id',
						'label'       => __( 'Place ID', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => '',
						'description' => __( 'Add a valid Apple Maps Place ID.', 'apple-maps' ),
						'condition'   => [
							'location_type' => 'place_id',
						],
					],
					[
						'name'        => 'place_id_lookup',
						'type'        => Controls_Manager::RAW_HTML,
						'raw'         => '<div class="apple-maps-place-lookup" style="padding:8px 0;">'
							. '<div style="margin-bottom:6px; font-weight:600;">Place Finder</div>'
							. '<input type="text" class="apple-maps-place-lookup__query" placeholder="Search for a place (name, address, etc)" style="width:100%;" />'
							. '<div class="apple-maps-place-lookup__status" style="margin-top:6px; font-size:12px; opacity:.8;"></div>'
							. '<div class="apple-maps-place-lookup__results" style="margin-top:8px;"></div>'
							. '</div>',
						'condition'   => [
							'location_type' => 'place_id',
						],
					],
					[
						'name'        => 'enable_place_name_override',
						'label'       => __( 'Override Place Name', 'apple-maps' ),
						'type'        => Controls_Manager::SWITCHER,
						'label_on'    => __( 'Yes', 'apple-maps' ),
						'label_off'   => __( 'No', 'apple-maps' ),
						'default'     => 'no',
						'condition'   => [
							'location_type' => 'place_id',
						],
					],
					[
						'name'        => 'place_name_override',
						'label'       => __( 'Place Name Override', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => '',
						'description' => __( 'Optional name to display on the map instead of the place name.', 'apple-maps' ),
						'condition'   => [
							'location_type' => 'place_id',
							'enable_place_name_override' => 'yes',
						],
					],
					[
						'name'        => 'location_name',
						'label'       => __( 'Location Name', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => __( 'New Location', 'apple-maps' ),
						'condition'   => [
							'location_type' => 'manual',
						],
					],
					[
						'name'        => 'latitude',
						'label'       => __( 'Latitude', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => '37.3349',
						'condition'   => [
							'location_type' => 'manual',
						],
					],
					[
						'name'        => 'longitude',
						'label'       => __( 'Longitude', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => '-122.0090',
						'condition'   => [
							'location_type' => 'manual',
						],
					],
					[
						'name'        => 'glyph',
						'label'       => __( 'Glyph', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'description' => __( 'Emojis or short text works best.', 'apple-maps' ),
						'condition'   => [
							'location_type' => 'manual',
						],
					],
					[
						'name'        => 'enable_callout',
						'label'       => __( 'Enable Callout', 'apple-maps' ),
						'type'        => Controls_Manager::SWITCHER,
						'label_on'    => __( 'Yes', 'apple-maps' ),
						'label_off'   => __( 'No', 'apple-maps' ),
						'default'     => 'yes',
					],
					[
						'name'        => 'description',
						'label'       => __( 'Callout text', 'apple-maps' ),
						'type'        => Controls_Manager::TEXTAREA,
						'label_block' => true,
						'condition'   => [
							'enable_callout' => 'yes',
						],
					],
					[
						'name'        => 'enable_link',
						'label'       => __( 'Enable Link', 'apple-maps' ),
						'type'        => Controls_Manager::SWITCHER,
						'condition'   => [
							'enable_callout' => 'yes',
						],
						'label_on'    => __( 'Yes', 'apple-maps' ),
						'label_off'   => __( 'No', 'apple-maps' ),
						'default'     => 'no',
					],

					[
						'name'        => 'link_url',
						'label'       => __( 'Link URL', 'apple-maps' ),
						'type'        => Controls_Manager::URL,
						'label_block' => true,
						'condition'   => [
							'enable_callout' => 'yes',
							'enable_link' => 'yes',
						],
					],

					[
						'name'        => 'link_text',
						'label'       => __( 'Link Text', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'label_block' => true,
						'default'     => __( 'Visit Link', 'apple-maps' ),
						'condition'   => [
							'enable_callout' => 'yes',
							'enable_link' => 'yes',
						],
					],
				],
				'title_field' => '{{{ admin_label }}}',
			]
		);

		$this->end_controls_section();

$this->start_controls_section(
			'map_configuration',
			[
				'label' => __( 'Map settings', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// colorScheme setting
		$this->add_control(
			'color_scheme',
			[
				'label'       => __( 'Color Scheme', 'apple-maps' ),
				'type'        => Controls_Manager::SELECT,
								'label_block' => true,
				'default'     => 'adaptive',
				'options'     => [
					'adaptive' => __( 'Adaptive', 'apple-maps' ),
					'dark'     => __( 'Dark', 'apple-maps' ),
					'light'    => __( 'Light', 'apple-maps' ),
				],
			]
		);

		// cameraDistance setting
		$this->add_control(
			'camera_distance',
			[
				'label'       => __( 'Camera Distance', 'apple-maps' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1000,
			]
		);

		// isZoomEnabled setting
		$this->add_control(
			'is_zoom_enabled',
			[
				'label'       => __( 'Enable Zoom', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'yes',
			]
		);

		// isScrollEnabled setting
		$this->add_control(
			'is_scroll_enabled',
			[
				'label'       => __( 'Enable Scroll', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'yes',
			]
		);

		// isRotationEnabled setting
		$this->add_control(
			'is_rotation_enabled',
			[
				'label'       => __( 'Enable Rotation', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'yes',
			]
		);

		

// Enable Points of Interest Toggle
		$this->add_control(
			'enable_pois',
			[
				'label'       => __( 'Enable Points of Interest', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'yes',
			]
		);

		// cameraZoomRange setting
		$this->add_control(
			'enable_camera_zoom_range',
			[
				'label'       => __( 'Enable Camera Zoom Range', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'no',
			]
		);

		$this->add_control(
			'camera_zoom_range',
			[
				'label'       => __( 'Camera Zoom Range', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '0,10000',
				'description' => __( 'Set the camera zoom range in meters (e.g., "0,10000").', 'apple-maps' ),
				'condition'   => [
					'enable_camera_zoom_range' => 'yes',
				],
			]
		);

		// Enable Camera Boundary Toggle
		$this->add_control(
			'enable_camera_boundary',
			[
				'label'       => __( 'Enable Camera Boundary', 'apple-maps' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => __( 'Yes', 'apple-maps' ),
				'label_off'   => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'     => 'no',
			]
		);

		// Latitude for the center of the camera boundary
		$this->add_control(
			'camera_boundary_latitude',
			[
				'label'       => __( 'Latitude', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 37.0, // Default latitude
				'condition'   => [
					'enable_camera_boundary' => 'yes',
				],
			]
		);

		// Longitude for the center of the camera boundary
		$this->add_control(
			'camera_boundary_longitude',
			[
				'label'       => __( 'Longitude', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => -123.0, // Default longitude
				'condition'   => [
					'enable_camera_boundary' => 'yes',
				],
			]
		);

		// Latitude Span for the camera boundary (height of the region)
		$this->add_control(
			'camera_boundary_span_latitude',
			[
				'label'       => __( 'Span Latitude', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 0.005,
				'condition'   => [
					'enable_camera_boundary' => 'yes',
				],
			]
		);

		// Longitude Span for the camera boundary (width of the region)
		$this->add_control(
			'camera_boundary_span_longitude',
			[
				'label'       => __( 'Span Longitude', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 0.005,
				'condition'   => [
					'enable_camera_boundary' => 'yes',
				],
			]
		);

		// Enable custom map center
		$this->add_control(
			'enable_map_center',
			[
				'label'        => __( 'Enable Custom Map Center', 'apple-maps' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'apple-maps' ),
				'label_off'    => __( 'No', 'apple-maps' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		// Map Center Latitude
		$this->add_control(
			'map_center_latitude',
			[
				'label'       => __( 'Center Latitude', 'apple-maps' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '37.3349',
				'condition'   => [
					'enable_map_center' => 'yes',
				],
			]
		);

		// Map Center Longitude
		$this->add_control(
			'map_center_longitude',
			[
				'label'       => __( 'Center Longitude', 'apple-maps' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '-122.0090',
				'condition'   => [
					'enable_map_center' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		

		$this->start_controls_section(
			'pin_styles',
			[
				'label' => __( 'Pin Styles', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'default_pin_style_type',
			[
				'label'       => __( 'Pin Style Type', 'apple-maps' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'default'     => 'icon',
				'options'     => [
					'icon' => __( 'Icon / SVG', 'apple-maps' ),
					'text' => __( 'Text', 'apple-maps' ),
				],
				'description' => __( 'Choose whether the marker uses a custom icon, or short text.', 'apple-maps' ),
			]
		);

		$this->add_control(
			'default_pin_text',
			[
				'label'       => __( 'Default Pin Text', 'apple-maps' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'e.g. BRI', 'apple-maps' ),
				'dynamic'     => [ 'active' => false ],
				'condition'   => [
					'default_pin_style_type' => 'text',
				],
				'description' => __( 'Up to 6 characters.', 'apple-maps' ),
			]
		);
$this->add_control(
	'default_pin_color',
	[
		'label'       => __( 'Default Pin Color', 'apple-maps' ),
		'type'        => Controls_Manager::COLOR,
		'label_block' => true,
		'default'     => '#ff0000',
		'description' => __( 'Default marker color used for all locations.', 'apple-maps' ),
	]
);

$this->add_control(
	'default_pin_icon',
	[
		'label'       => __( 'Default Pin Icon (SVG/PNG)', 'apple-maps' ),
		'type'        => Controls_Manager::MEDIA,
		'label_block' => true,
		'description' => __( 'Optional default icon used for all locations.', 'apple-maps' ),
				'condition'   => [
					'default_pin_style_type' => 'icon',
				],
	]
);


		$this->end_controls_section();

		$this->start_controls_section(
			'import_export',
			[
				'label' => __( 'Import/Export', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'import_export_ui',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="apple-maps-import-export" style="padding:8px 0;">'
					. '<div style="margin-bottom:6px; font-weight:600;">Export / Import</div>'
					. '<textarea class="apple-maps-import-export__textarea" rows="8" placeholder="Click Export to generate JSON. Paste JSON here and click Import." style="width:100%;"></textarea>'
					. '<div style="display:flex; gap:8px; margin-top:8px;">'
					. '<button type="button" class="elementor-button elementor-button-default apple-maps-import-export__export">Export</button>'
					. '<button type="button" class="elementor-button elementor-button-default apple-maps-import-export__import">Import</button>'
					. '</div>'
					. '<div class="apple-maps-import-export__status" style="margin-top:6px; font-size:12px; opacity:.8;"></div>'
					. '</div>',
			]
		);

		$this->end_controls_section();


// Smart markers and Performance sections removed.
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Style', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// style: map height
		$this->add_responsive_control(
			'map_height',
			[
				'label' => __( 'Map Height', 'apple-maps' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
						'step' => 10,
					],
					'em' => [
						'min' => 5,
						'max' => 80,
						'step' => 1,
					],
					'%' => [
						'min' => 20,
						'max' => 100,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .apple-maps-widget' => 'height: {{SIZE}}{{UNIT}};',
				],
				'default' => [
					'unit' => 'px',
					'size' => 400,
				],
			]
		);

		// Add border radius control
		$this->add_responsive_control(
			'map_border_radius',
			[
				'label' => __( 'Map Border Radius', 'apple-maps' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .apple-maps-container' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$mapkit_token = get_option( 'apple_maps_mapkit_token' );
		if ( ! $mapkit_token ) {
			echo '<div style="color: red;">' . __( 'Please provide a MapKit token in the global settings under "Apple Maps".', 'apple-maps' ) . '</div>';
			return;
		}

		$locations = ! empty( $settings['map_locations'] ) ? $settings['map_locations'] : [];
		if ( empty( $locations ) ) {
			echo '<div style="color: red;">' . __( 'No locations provided.', 'apple-maps' ) . '</div>';
			return;
		}

		foreach ( $locations as &$location ) {
			$location['admin_label'] = ! empty( $location['admin_label'] ) ? sanitize_text_field( $location['admin_label'] ) : '';

			$location_type = ! empty( $location['location_type'] ) ? sanitize_key( $location['location_type'] ) : 'manual';
			if ( $location_type !== 'place_id' ) {
				$location_type = 'manual';
			}
			$location['location_type'] = $location_type;


			if ( $location_type === 'place_id' ) {
				$location['place_id'] = ! empty( $location['place_id'] ) ? sanitize_text_field( $location['place_id'] ) : '';
				$location['enable_place_name_override'] = ( ! empty( $location['enable_place_name_override'] ) && $location['enable_place_name_override'] === 'yes' ) ? 'yes' : 'no';

				if ( $location['enable_place_name_override'] === 'yes' && ! empty( $location['place_name_override'] ) ) {
					$location['place_name_override'] = sanitize_text_field( $location['place_name_override'] );
				} else {
					$location['place_name_override'] = '';
				}
			}

			if ( $location_type === 'manual' ) {
				$location['location_name'] = ! empty( $location['location_name'] ) ? sanitize_text_field( $location['location_name'] ) : '';
				$location['latitude'] = ! empty( $location['latitude'] ) ? sanitize_text_field( $location['latitude'] ) : '';
				$location['longitude'] = ! empty( $location['longitude'] ) ? sanitize_text_field( $location['longitude'] ) : '';
				$location['glyph'] = ! empty( $location['glyph'] ) ? sanitize_text_field( $location['glyph'] ) : '';
			}

			$location['enable_callout'] = ( ! empty( $location['enable_callout'] ) && $location['enable_callout'] === 'yes' ) ? 'yes' : 'no';
			$location['enable_link'] = ( ! empty( $location['enable_link'] ) && $location['enable_link'] === 'yes' ) ? 'yes' : 'no';
			$location['description'] = ! empty( $location['description'] ) ? sanitize_textarea_field( $location['description'] ) : '';
			$location['link_text'] = ! empty( $location['link_text'] ) ? sanitize_text_field( $location['link_text'] ) : '';

			// Default to an empty URL unless a safe HTTP(S) URL is provided.
			if ( empty( $location['link_url'] ) || ! is_array( $location['link_url'] ) || empty( $location['link_url']['url'] ) ) {
				$location['link_url'] = [ 'url' => '' ];
				continue;
			}

			$sanitized_url = esc_url_raw( $location['link_url']['url'], [ 'http', 'https' ] );
			if ( empty( $sanitized_url ) ) {
				$location['link_url']['url'] = '';
				continue;
			}

			$sanitized_scheme = wp_parse_url( $sanitized_url, PHP_URL_SCHEME );
			if ( ! in_array( strtolower( (string) $sanitized_scheme ), [ 'http', 'https' ], true ) ) {
				$location['link_url']['url'] = '';
				continue;
			}

			$location['link_url']['url'] = $sanitized_url;
		}
		unset( $location );

		$color_scheme = ! empty( $settings['color_scheme'] ) ? esc_js( $settings['color_scheme'] ) : 'adaptive';
		$camera_distance = ! empty( $settings['camera_distance'] ) ? esc_js( $settings['camera_distance'] ) : 5000;
		$zoom_enabled = ! empty( $settings['is_zoom_enabled'] ) && $settings['is_zoom_enabled'] === 'yes';
		$scroll_enabled = ! empty( $settings['is_scroll_enabled'] ) && $settings['is_scroll_enabled'] === 'yes';
		$rotation_enabled = ! empty( $settings['is_rotation_enabled'] ) && $settings['is_rotation_enabled'] === 'yes';
		$pois_enabled = ! empty( $settings['enable_pois'] ) && $settings['enable_pois'] === 'yes';

		$default_pin_style_type = ! empty( $settings['default_pin_style_type'] ) ? (string) $settings['default_pin_style_type'] : 'icon';
		if ( ! in_array( $default_pin_style_type, [ 'icon', 'text' ], true ) ) {
			$default_pin_style_type = 'icon';
		}

		$default_pin_text = ! empty( $settings['default_pin_text'] ) ? sanitize_text_field( (string) $settings['default_pin_text'] ) : '';
		$default_pin_text = trim( $default_pin_text );
		if ( strlen( $default_pin_text ) > 6 ) {
			$default_pin_text = substr( $default_pin_text, 0, 6 );
		}

$raw_default_pin_color = ! empty( $settings['default_pin_color'] ) ? trim( (string) $settings['default_pin_color'] ) : '';
$default_pin_color = '';
if ( ! empty( $raw_default_pin_color ) && preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/', $raw_default_pin_color ) ) {
	$default_pin_color = $raw_default_pin_color;
}
if ( empty( $default_pin_color ) && ! empty( $raw_default_pin_color ) ) {
	$default_pin_color = sanitize_hex_color( $raw_default_pin_color );
}
if ( empty( $default_pin_color ) ) {
	$default_pin_color = '#ff0000';
}

$default_pin_icon_url = '';
if ( ! empty( $settings['default_pin_icon'] ) && is_array( $settings['default_pin_icon'] ) && ! empty( $settings['default_pin_icon']['url'] ) ) {
	$raw_default_pin_icon_url = esc_url_raw( $settings['default_pin_icon']['url'], [ 'http', 'https' ] );
	if ( ! empty( $raw_default_pin_icon_url ) ) {
		$default_pin_icon_url = $raw_default_pin_icon_url;
	}
}

$camera_zoom_range = null;
		if ( ! empty( $settings['enable_camera_zoom_range'] ) && $settings['enable_camera_zoom_range'] === 'yes' ) {
			$camera_zoom_range = ! empty( $settings['camera_zoom_range'] ) ? array_map( 'floatval', explode( ',', $settings['camera_zoom_range'] ) ) : [ 0, 1000 ];
		}

		$camera_boundary = null;
		if ( ! empty( $settings['enable_camera_boundary'] ) && $settings['enable_camera_boundary'] === 'yes' ) {
			$camera_boundary = [
				'latitude' => ! empty( $settings['camera_boundary_latitude'] ) ? $settings['camera_boundary_latitude'] : 37.0,
				'longitude' => ! empty( $settings['camera_boundary_longitude'] ) ? $settings['camera_boundary_longitude'] : -123.0,
				'span_latitude' => ! empty( $settings['camera_boundary_span_latitude'] ) ? $settings['camera_boundary_span_latitude'] : 1.0,
				'span_longitude' => ! empty( $settings['camera_boundary_span_longitude'] ) ? $settings['camera_boundary_span_longitude'] : 1.0,
			];
		}

		$map_center = null;
		if ( ! empty( $settings['enable_map_center'] ) && $settings['enable_map_center'] === 'yes' ) {
			$map_center = [
				'latitude'  => ! empty( $settings['map_center_latitude'] ) ? $settings['map_center_latitude'] : null,
				'longitude' => ! empty( $settings['map_center_longitude'] ) ? $settings['map_center_longitude'] : null,
			];
		}

		$map_center_js = $map_center ? wp_json_encode( $map_center ) : 'undefined';
		$camera_zoom_range_js = $camera_zoom_range ? wp_json_encode( $camera_zoom_range ) : 'undefined';
		$camera_boundary_js = $camera_boundary ? wp_json_encode( $camera_boundary ) : 'undefined';
		$map_container_id = 'apple-maps-widget-' . $this->get_id();
		echo '<div class="apple-maps-container" style="overflow: hidden;">';
		echo '<div id="' . esc_attr( $map_container_id ) . '" class="apple-maps-widget" style="width: 100%; min-height: 400px;"></div>';
		echo '</div>';

		echo '<script>';
		echo '(function waitForInitializeAppleMaps(attempt) {';
		echo 'if (typeof initializeAppleMaps !== "function") {';
		echo 'if (attempt >= 100) { console.error("Apple Maps init stopped: initializeAppleMaps was not available after 100 attempts."); return; }';
		echo 'setTimeout(function() { waitForInitializeAppleMaps(attempt + 1); }, 100);';
		echo 'return;';
		echo '}';
		echo 'const mapContainerId = "' . esc_js( $map_container_id ) . '";';
		echo 'const mapLocations = ' . wp_json_encode( $locations ) . ';';
		echo 'const mapKitToken = "' . esc_js( $mapkit_token ) . '";';
		echo 'const cameraDistance = ' . esc_js( $camera_distance ) . ';';
		echo 'const colorScheme = "' . esc_js( $color_scheme ) . '";';
		echo 'const cameraZoomRange = ' . $camera_zoom_range_js . ';';
		echo 'const cameraBoundary = ' . $camera_boundary_js . ';';
		echo 'const mapCenter = ' . $map_center_js . ';';
		echo 'const defaultPinStyleType = "' . esc_js( $default_pin_style_type ) . '";';
		echo 'const defaultPinText = "' . esc_js( $default_pin_text ) . '";';
		echo 'const defaultPinColor = "' . esc_js( $default_pin_color ) . '";';
		echo 'const defaultPinIconUrl = "' . esc_js( $default_pin_icon_url ) . '";';
		echo 'const mapSettings = {';
		echo 'isZoomEnabled: ' . ( $zoom_enabled ? 'true' : 'false' ) . ',';
		echo 'isScrollEnabled: ' . ( $scroll_enabled ? 'true' : 'false' ) . ',';
		echo 'isRotationEnabled: ' . ( $rotation_enabled ? 'true' : 'false' ) . ',';
		echo 'showPOIs: ' . ( $pois_enabled ? 'true' : 'false' );
		echo '};';
		echo 'initializeAppleMaps(mapContainerId, mapLocations, mapKitToken, cameraDistance, colorScheme, mapSettings, cameraZoomRange, cameraBoundary, mapCenter, defaultPinStyleType, defaultPinText, defaultPinColor, defaultPinIconUrl);';
		echo '})(0);';
		echo '</script>';
	}
}
