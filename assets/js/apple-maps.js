let apple_maps_mapkit_load_promise = null;
let apple_maps_mapkit_initialized = false;
let apple_maps_mapkit_token = "";
let apple_maps_mapkit_libraries_loaded = false;

function load_mapkit_script() {
	if (typeof mapkit !== "undefined") {
		return Promise.resolve();
	}

	if (apple_maps_mapkit_load_promise) {
		return apple_maps_mapkit_load_promise;
	}

	apple_maps_mapkit_load_promise = new Promise((resolve, reject) => {
		const existing_script = document.querySelector('script[src*="cdn.apple-mapkit.com/mk/5.x.x/mapkit.js"]');
		if (existing_script) {
			existing_script.addEventListener("load", () => resolve(), { once: true });
			existing_script.addEventListener("error", () => reject(new Error("MapKit script failed to load.")), { once: true });
			return;
		}

		const script = document.createElement("script");
		script.src = "https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js";
		script.async = true;
		script.defer = true;
		script.onload = () => resolve();
		script.onerror = () => reject(new Error("MapKit script failed to load."));
		document.head.appendChild(script);
	});

	return apple_maps_mapkit_load_promise;
}

function ensure_mapkit(map_kit_token) {
	return load_mapkit_script().then(() => {
		if (typeof mapkit === "undefined") {
			throw new Error("MapKit is unavailable.");
		}

		if (apple_maps_mapkit_initialized && apple_maps_mapkit_token === map_kit_token) {
			return;
		}

		mapkit.init({
			authorizationCallback: function(done) {
				done(map_kit_token);
			}
		});

		// MapKit JS splits features into libraries. The map library is available by default,
		// but Search / PlaceLookup live in optional libraries in newer versions.
		// If these libraries are already loaded, calling load again is harmless.
		if (!apple_maps_mapkit_libraries_loaded && typeof mapkit.load === "function") {
			try {
				mapkit.load(["search", "services"]);
				apple_maps_mapkit_libraries_loaded = true;
			} catch (e) {
				// Fail silently; the editor UI will surface availability.
			}
		}

		apple_maps_mapkit_initialized = true;
		apple_maps_mapkit_token = map_kit_token;
	});
}

function is_safe_http_url(url) {
	if (!url || typeof url !== "string") {
		return false;
	}

	const trimmed_url = url.trim();
	if (!/^https?:\/\//i.test(trimmed_url)) {
		return false;
	}

	try {
		const parsed_url = new URL(trimmed_url);
		return parsed_url.protocol === "http:" || parsed_url.protocol === "https:";
	} catch (error) {
		return false;
	}
}



function parse_color_to_mapkit(color) {
	if (!color || typeof color !== "string") {
		return color;
	}

	if (typeof mapkit === "undefined" || !mapkit.Color) {
		return color;
	}

	const value = color.trim();

	let match = value.match(/^#?([0-9a-f]{6})([0-9a-f]{2})?$/i);
	if (match) {
		const hex = match[1];
		const alpha_hex = match[2];
		const r = parseInt(hex.substring(0, 2), 16);
		const g = parseInt(hex.substring(2, 4), 16);
		const b = parseInt(hex.substring(4, 6), 16);
		const a = alpha_hex ? parseInt(alpha_hex, 16) / 255 : 1;
		return new mapkit.Color(r / 255, g / 255, b / 255, a);
	}

	match = value.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*(0|1|0?\.\d+))?\s*\)$/i);
	if (match) {
		const r = Math.min(255, parseInt(match[1], 10));
		const g = Math.min(255, parseInt(match[2], 10));
		const b = Math.min(255, parseInt(match[3], 10));
		const a = typeof match[4] !== "undefined" ? Math.min(1, parseFloat(match[4])) : 1;
		return new mapkit.Color(r / 255, g / 255, b / 255, a);
	}

	return color;
}

function initializeAppleMaps(mapContainerId, mapLocations, mapKitToken, cameraDistance, colorScheme = "adaptive", mapSettings = {}, cameraZoomRange = undefined, cameraBoundary = undefined, center = undefined, defaultPinStyleType = "icon", defaultPinText = "", defaultPinColor = "#ff0000", defaultPinIconUrl = "") {
	if (!mapKitToken || !mapLocations || mapLocations.length === 0) {
		console.error("Missing MapKit token or map locations.");
		return Promise.resolve(null);
	}

	const map_container = document.getElementById(mapContainerId);
	if (!map_container) {
		console.error("Apple Maps container not found.");
		return Promise.resolve(null);
	}

	return ensure_mapkit(mapKitToken).then(() => {
		const map = new mapkit.Map(map_container, {
			colorScheme: colorScheme,
			isZoomEnabled: mapSettings.isZoomEnabled ?? true,
			isScrollEnabled: mapSettings.isScrollEnabled ?? true,
			isRotationEnabled: mapSettings.isRotationEnabled ?? true,
			showsPointsOfInterest: mapSettings.showPOIs ?? true,
			...(cameraZoomRange !== undefined && { cameraZoomRange: new mapkit.CameraZoomRange(cameraZoomRange[0], cameraZoomRange[1]) }),
		});

		let all_annotations = [];

		const apply_camera_boundary = function() {
			const has_camera_boundary = cameraBoundary && typeof cameraBoundary === "object" && !Array.isArray(cameraBoundary) && Object.keys(cameraBoundary).length > 0;
			if (!has_camera_boundary) {
				return;
			}

			const { latitude, longitude, span_latitude, span_longitude } = cameraBoundary;
			if (latitude == null || longitude == null || span_latitude == null || span_longitude == null) {
				console.warn("cameraBoundary properties are missing or undefined:", cameraBoundary);
				return;
			}

			const parsed_latitude = parseFloat(latitude);
			const parsed_longitude = parseFloat(longitude);
			const parsed_span_latitude = parseFloat(span_latitude);
			const parsed_span_longitude = parseFloat(span_longitude);
			if (isNaN(parsed_latitude) || isNaN(parsed_longitude) || isNaN(parsed_span_latitude) || isNaN(parsed_span_longitude)) {
				console.warn("Invalid cameraBoundary values:", cameraBoundary);
				return;
			}

			const region = new mapkit.CoordinateRegion(
				new mapkit.Coordinate(parsed_latitude, parsed_longitude),
				new mapkit.CoordinateSpan(parsed_span_latitude, parsed_span_longitude)
			);
			map.cameraBoundary = region.toMapRect();
		};

		const fit_map_to_annotations = function(annotations) {
			if (!annotations || annotations.length === 0) {
				return;
			}

			if (annotations.length > 1) {
				map.showItems(annotations);
				return;
			}

			const annotation = annotations[0];
			const coordinate = annotation.coordinate;
			const distance_in_degrees = cameraDistance / 111000;
			map.region = new mapkit.CoordinateRegion(
				new mapkit.Coordinate(coordinate.latitude, coordinate.longitude),
				new mapkit.CoordinateSpan(distance_in_degrees, distance_in_degrees)
			);
		};

		const callout_delegate = {
			calloutContentForAnnotation: function(annotation) {
				const element = document.createElement("div");
				element.className = "custom-callout-content";
				element.style.padding = "10px";

				const title = document.createElement("h4");
				title.textContent = annotation.title;
				title.style.margin = "0";
				title.style.fontSize = "16px";
				title.style.color = "#333";
				element.appendChild(title);

				const annotation_data = annotation && annotation.data ? annotation.data : {};
				const description = document.createElement("p");
				description.textContent = annotation_data.description || "No additional information.";
				description.style.margin = "5px 0 0";
				description.style.fontSize = "14px";
				description.style.color = "#555";
				element.appendChild(description);

				if (annotation_data.link && is_safe_http_url(annotation_data.link)) {
					const link_element = document.createElement("a");
					link_element.href = annotation_data.link;
					link_element.textContent = annotation_data.link_text || "Visit Link";
					link_element.style.display = "block";
					link_element.style.marginTop = "10px";
					link_element.style.fontSize = "14px";
					link_element.style.color = "#0066cc";
					link_element.style.textDecoration = "none";
					link_element.addEventListener("mouseover", function() {
						link_element.style.textDecoration = "underline";
					});
					link_element.addEventListener("mouseout", function() {
						link_element.style.textDecoration = "none";
					});
					element.appendChild(link_element);
				}

				return element;
			}
		};

		const add_marker_annotation = function(options) {
			const coordinate = new mapkit.Coordinate(options.latitude, options.longitude);
			const annotation_options = {
				title: options.title,
				glyphText: options.glyph_text,
				color: parse_color_to_mapkit(options.color),
				animates: true,
				data: options.data,
			};

			if (options.enable_callout) {
				annotation_options.callout = callout_delegate;
			}

			if (is_safe_http_url(options.icon_url)) {
				annotation_options.glyphImage = { 1: options.icon_url };
			}

			const annotation = new mapkit.MarkerAnnotation(coordinate, annotation_options);
			map.addAnnotation(annotation);
			all_annotations.push(annotation);
		};

		const place_lookup = new mapkit.PlaceLookup();
		const place_promises = [];

		mapLocations.forEach((location) => {
			const location_type = location && location.location_type ? location.location_type : "manual";
			const pin_color = defaultPinColor;
			const use_text_pin = String(defaultPinStyleType || "icon") === "text";
			const glyph_text = use_text_pin ? String(defaultPinText || "").trim() : "";
			const icon_url = use_text_pin ? "" : defaultPinIconUrl;

			if (location_type === "place_id") {
				const place_id = location && location.place_id ? location.place_id : "";
				if (!place_id) {
					return;
				}

				place_promises.push(new Promise((resolve) => {
					place_lookup.getPlace(place_id, (error, place) => {
						if (error || !place || !place.coordinate) {
							resolve(null);
							return;
						}

						// Use Apple's default place presentation (selection accessory -> PlaceDetail).
						// This restores the native place card behavior when a person taps a place pin.
						const override_title = location && location.place_name_override ? String(location.place_name_override).trim() : "";
						const title = override_title || place.name || place_id;

					const annotation_options = {
							title: title,
						glyphText: glyph_text,
						color: parse_color_to_mapkit(pin_color),
							animates: true,
							place: place,
						};

						if (is_safe_http_url(icon_url)) {
							annotation_options.glyphImage = { 1: icon_url };
						}

						const coordinate = new mapkit.Coordinate(place.coordinate.latitude, place.coordinate.longitude);
						const annotation = new mapkit.MarkerAnnotation(coordinate, annotation_options);
						if (typeof mapkit.PlaceSelectionAccessory === "function") {
							annotation.selectionAccessory = new mapkit.PlaceSelectionAccessory();
						}

						map.addAnnotation(annotation);
						all_annotations.push(annotation);
						resolve(true);
					});
				}));
				return;
			}

			const raw_latitude = parseFloat(location.latitude);
			const raw_longitude = parseFloat(location.longitude);
			if (isNaN(raw_latitude) || isNaN(raw_longitude)) {
				return;
			}

			const raw_link_value = location.link_url && location.link_url.url ? location.link_url.url : null;
			const link_value = is_safe_http_url(raw_link_value) ? raw_link_value : null;

			add_marker_annotation({
				latitude: raw_latitude,
				longitude: raw_longitude,
				title: location.location_name,
				glyph_text: glyph_text,
				color: pin_color,
				icon_url: icon_url,
				enable_callout: location.enable_callout === "yes",
				data: {
					description: location.enable_callout === "yes" ? location.description : null,
					link: location.enable_link === "yes" ? link_value : null,
					link_text: location.enable_link === "yes" ? location.link_text : null,
				},
			});
		});

		return Promise.all(place_promises).then(() => {
			if (center && center.latitude != null && center.longitude != null) {
				const center_coordinate = new mapkit.Coordinate(parseFloat(center.latitude), parseFloat(center.longitude));
				const distance_in_degrees = cameraDistance / 111000;
				map.region = new mapkit.CoordinateRegion(
					center_coordinate,
					new mapkit.CoordinateSpan(distance_in_degrees, distance_in_degrees)
				);
			} else {
				fit_map_to_annotations(all_annotations);
			}

			apply_camera_boundary();
			return map;
		});
	}).catch((error) => {
		console.error("Error initializing Apple Maps:", error);
		return null;
	});
}
