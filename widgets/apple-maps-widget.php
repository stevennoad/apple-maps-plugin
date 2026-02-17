<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Apple_Maps_Widget extends Widget_Base {

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

	protected function _register_controls() {
		$this->start_controls_section(
			'locations',
			[
				'label' => __( 'Map locations', 'apple-maps' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'location_input_mode',
			[
				'label'       => __( 'Location Input Mode', 'apple-maps' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'standard',
				'options'     => [
					'standard' => __( 'Standard', 'apple-maps' ),
					'place_id' => __( 'Places', 'apple-maps' ),
				],
			]
		);

		$this->add_control(
			'map_locations',
			[
				'label'       => __( 'Standard Locations', 'apple-maps' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => [
					[
						'name'        => 'location_name',
						'label'       => __( 'Location Name', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'default'     => __( 'New Location', 'apple-maps' ),
					],
					[
						'name'        => 'latitude',
						'label'       => __( 'Latitude', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'default'     => '37.3349',
					],
					[
						'name'        => 'longitude',
						'label'       => __( 'Longitude', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'default'     => '-122.0090',
					],
					[
						'name'        => 'glyph',
						'label'       => __( 'Glyph', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'description' => __( 'Emojis or short text works best.', 'apple-maps' ),
					],
					[
							'name'        => 'enable_callout',
							'label'       => __( 'Enable Callout', 'apple-maps' ),
							'type'        => Controls_Manager::SWITCHER,
							'label_on'    => __( 'Yes', 'apple-maps' ),
							'label_off'   => __( 'No', 'apple-maps' ),
							'default'     => 'no',
					],
					[
						'name'        => 'description',
						'label'       => __( 'Callout text', 'apple-maps' ),
						'type'        => Controls_Manager::TEXTAREA,
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
							'condition'   => [
								'enable_callout' => 'yes',
								'enable_link' => 'yes',
							],
					],

					[
							'name'        => 'link_text',
							'label'       => __( 'Link Text', 'apple-maps' ),
							'type'        => Controls_Manager::TEXT,
							'default'     => __( 'Visit Link', 'apple-maps' ),
							'condition'   => [
								'enable_callout' => 'yes',
								'enable_link' => 'yes',
							],
					],
				],
				'title_field' => '{{{ location_name }}}',
				'condition'   => [
					'location_input_mode' => 'standard',
				],
			]
		);

		$this->add_control(
			'map_locations_place_id',
			[
				'label'       => __( 'Place IDs', 'apple-maps' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => [
					[
						'name'        => 'place_id',
						'label'       => __( 'Place ID', 'apple-maps' ),
						'type'        => Controls_Manager::TEXT,
						'default'     => __( 'Enter Place ID', 'apple-maps' ),
						'description' => __( 'Add a valid Apple Maps Place ID.', 'apple-maps' ),
					],
				],
				'title_field' => '{{{ place_id }}}',
				'condition'   => [
					'location_input_mode' => 'place_id',
				],
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

		// Change pin background color
		$this->add_control(
			'pin_color',
			[
				'label'   => __( 'Pin Color', 'apple-maps' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '#ff0000',
				'description' => 'Updates will take place after you refresh the page.',
				'selectors' => [
					'{{WRAPPER}} .map-pin' => 'color: {{VALUE}};',
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
		if (!$mapkit_token) {
			echo '<div style="color: red;">' . __( 'Please provide a MapKit token in the global settings under "Apple Maps".', 'apple-maps' ) . '</div>';
			return;
		}

		if ( ! empty( $settings['location_input_mode'] ) ) {
			$location_input_mode = $settings['location_input_mode'];

			if ( $location_input_mode === 'standard' && ! empty( $settings['map_locations'] ) ) {
				$locations = $settings['map_locations'];
			} elseif ( $location_input_mode === 'place_id' && ! empty( $settings['map_locations_place_id'] ) ) {
				$locations = $settings['map_locations_place_id'];
			} else {
				echo '<div style="color: red;">' . __( 'No locations provided for the selected mode.', 'apple-maps' ) . '</div>';
				return;
			}

			$color_scheme = ! empty( $settings['color_scheme'] ) ? esc_js( $settings['color_scheme'] ) : 'adaptive';
			$camera_distance = ! empty( $settings['camera_distance'] ) ? esc_js( $settings['camera_distance'] ) : 5000;
			$zoom_enabled = ! empty( $settings['is_zoom_enabled'] ) && $settings['is_zoom_enabled'] === 'yes';
			$scroll_enabled = ! empty( $settings['is_scroll_enabled'] ) && $settings['is_scroll_enabled'] === 'yes';
			$rotation_enabled = ! empty( $settings['is_rotation_enabled'] ) && $settings['is_rotation_enabled'] === 'yes';
			$pois_enabled = ! empty( $settings['enable_pois'] ) && $settings['enable_pois'] === 'yes';
			$pin_color = ! empty( $settings['pin_color'] ) ? esc_js( $settings['pin_color'] ) : '#ff0000';

			$camera_zoom_range = null;
			if ( ! empty( $settings['enable_camera_zoom_range'] ) && $settings['enable_camera_zoom_range'] === 'yes' ) {
				$camera_zoom_range = ! empty( $settings['camera_zoom_range'] ) ? array_map( 'floatval', explode( ',', $settings['camera_zoom_range'] ) ) : [0, 1000];
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
			$map_container_id = 'apple-maps-widget-' . $this->get_id();
			$map_wrapper_id = 'apple-maps-container-' . $this->get_id();

			echo '<div id="' . esc_attr( $map_wrapper_id ) . '" class="apple-maps-container" style="overflow: hidden;">';
			echo '<div id="' . esc_attr( $map_container_id ) . '" class="apple-maps-widget" style="width: 100%; min-height: 400px;"></div>';
			echo '</div>';

			echo '<script>';
			echo '(function waitForInitializeAppleMaps() {';
			echo 'if (typeof initializeAppleMaps === "function") {';
			echo 'const mapContainerId = "' . esc_js( $map_container_id ) . '";';
			echo 'const locationInputMode = "' . esc_js( $location_input_mode ) . '";';
			echo 'const mapLocations = ' . wp_json_encode( $locations ) . ';';
			echo 'const mapKitToken = "' . esc_js( $mapkit_token ) . '";';
			echo 'const cameraDistance = ' . esc_js( $camera_distance ) . ';';
			echo 'const pinColor = "' . esc_js( $pin_color ) . '";';
			echo 'const colorScheme = "' . esc_js( $color_scheme ) . '";';

			$cameraZoomRangeJs = $camera_zoom_range ? json_encode( $camera_zoom_range ) : 'undefined';

			echo 'const cameraZoomRange = ' . $cameraZoomRangeJs . ';';
			$camera_boundary_js = $camera_boundary ? wp_json_encode( $camera_boundary ) : 'undefined';
			echo 'const cameraBoundary = ' . $camera_boundary_js . ';';
			echo 'const mapSettings = {';
			echo 'isZoomEnabled: ' . ( $zoom_enabled ? 'true' : 'false' ) . ',';
			echo 'isScrollEnabled: ' . ( $scroll_enabled ? 'true' : 'false' ) . ',';
			echo 'isRotationEnabled: ' . ( $rotation_enabled ? 'true' : 'false' ) . ',';
			echo 'showPOIs: ' . ( $pois_enabled ? 'true' : 'false' );
			echo '};';

			echo 'const mapCenter = ' . $map_center_js . ';';
			echo 'initializeAppleMaps(mapContainerId, locationInputMode, mapLocations, mapKitToken, cameraDistance, colorScheme, mapSettings, cameraZoomRange, cameraBoundary, pinColor, mapCenter);';
			echo '} else {';
			echo 'console.log("Waiting for initializeAppleMaps...");';
			echo 'setTimeout(waitForInitializeAppleMaps, 100);';
			echo '}';
			echo '})();';
			echo '</script>';
		}
	}
}
