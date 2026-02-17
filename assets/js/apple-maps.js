function initializeAppleMaps(mapContainerId, locationInputMode, mapLocations, mapKitToken, cameraDistance, colorScheme = "adaptive", mapSettings = {}, cameraZoomRange = undefined, cameraBoundary = undefined, pinColor = '#ff0000', center = undefined) {
	if (!mapKitToken || !mapLocations || mapLocations.length === 0) {
		console.error("Missing MapKit token or map locations.");
		return;
	}

	let annotations = [];

	try {
		if (typeof mapkit === "undefined") {
			setTimeout(function() {
				initializeAppleMaps(mapContainerId, locationInputMode, mapLocations, mapKitToken, cameraDistance, colorScheme, mapSettings, cameraZoomRange, cameraBoundary, pinColor, center);
			}, 100);
			return;
		}

		mapkit.init({
			authorizationCallback: function(done) {
				done(mapKitToken);
			}
		});

		const mapContainer = document.getElementById(mapContainerId);
		if (!mapContainer) {
			console.error("Apple Maps container not found.");
			return;
		}

		const map = new mapkit.Map(mapContainer, {
			colorScheme: colorScheme,
			isZoomEnabled: mapSettings.isZoomEnabled ?? true,
			isScrollEnabled: mapSettings.isScrollEnabled ?? true,
			isRotationEnabled: mapSettings.isRotationEnabled ?? true,
			showsPointsOfInterest: mapSettings.showPOIs ?? true,
			...(cameraZoomRange !== undefined && { cameraZoomRange: new mapkit.CameraZoomRange(cameraZoomRange[0], cameraZoomRange[1]) }),
		});

		annotations.forEach(annotation => map.removeAnnotation(annotation));
		annotations = [];

		// Define the custom callout delegate at the top level
		const calloutDelegate = {
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

				// Create the description element with inline styles
				const description = document.createElement("p");
				const annotationData = annotation && annotation.data ? annotation.data : {};
				description.textContent = annotationData.description || "No additional information.";
				description.style.margin = "5px 0 0";
				description.style.fontSize = "14px";
				description.style.color = "#555";
				element.appendChild(description);

				// Check if there is a link and create a clickable anchor tag
				if (annotationData.link) {
					const linkElement = document.createElement("a");
					linkElement.href = annotationData.link;
					linkElement.textContent = annotationData.link_text || "Visit Link";
					linkElement.style.display = "block";
					linkElement.style.marginTop = "10px";
					linkElement.style.fontSize = "14px";
					linkElement.style.color = "#0066cc";
					linkElement.style.textDecoration = "none";

					// Add a hover effect for better UI
					linkElement.addEventListener('mouseover', function() {
						linkElement.style.textDecoration = "underline";
					});
					linkElement.addEventListener('mouseout', function() {
						linkElement.style.textDecoration = "none";
					});

					element.appendChild(linkElement);
				}

				return element;
			}
		};

		if (locationInputMode === "standard") {
			let totalLat = 0, totalLng = 0;

			mapLocations.forEach((location) => {
				const { location_name, latitude, longitude, glyph, enable_callout, description, enable_link, link_url, link_text } = location;
				const link_value = link_url && link_url.url ? link_url.url : null;

				const coord = new mapkit.Coordinate(parseFloat(latitude), parseFloat(longitude));
				const annotationOptions = {
						title: location_name,
						glyphText: glyph,
						color: pinColor,
						animates: true,
						data: {
								description: enable_callout === "yes" ? description : null, // Include description only if callout is enabled
								link: enable_link === "yes" ? link_value : null,
								link_text: enable_link === "yes" ? link_text : null,
						},
				};

				// Only set the callout delegate if the callout is enabled
				if (enable_callout === "yes") {
						annotationOptions.callout = calloutDelegate;
				}

				const annotation = new mapkit.MarkerAnnotation(coord, annotationOptions);

				map.addAnnotation(annotation);
				annotations.push(annotation);

				// Accumulate lat/lng for center calculation
				totalLat += parseFloat(latitude);
				totalLng += parseFloat(longitude);
			});

			// Center the map
			if (mapLocations.length > 0) {
				let centerCoord;

				if (center && center.latitude != null && center.longitude != null) {
					// Use provided center coordinate
					centerCoord = new mapkit.Coordinate(
						parseFloat(center.latitude),
						parseFloat(center.longitude)
					);
				} else {
					// Calculate from locations
					const centerLat = totalLat / mapLocations.length;
					const centerLng = totalLng / mapLocations.length;
					centerCoord = new mapkit.Coordinate(centerLat, centerLng);
				}

				const distanceInDegrees = cameraDistance / 111000;
				const region = new mapkit.CoordinateRegion(
					centerCoord,
					new mapkit.CoordinateSpan(distanceInDegrees, distanceInDegrees)
				);

				map.region = region;
			}
		} else if (locationInputMode === "place_id") {
			const placeLookup = new mapkit.PlaceLookup();

			Promise.all(
				mapLocations.map(location =>
					new Promise(resolve => {
						const { place_id } = location;
						if (!place_id) {
							console.error("Place ID missing for location.");
							resolve(null);
							return;
						}

						placeLookup.getPlace(place_id, (error, place) => {
							if (error) {
								resolve(null);
							} else if (place) {
								const annotation = new mapkit.PlaceAnnotation(place);
								annotation.callout = calloutDelegate; // Attach callout delegate

								map.addAnnotation(annotation);
								annotations.push(annotation);
								resolve(annotation);
							} else {
								console.warn(`No place found for Place ID: ${place_id}`);
								resolve(null);
							}
						});
					})
				)
			).then(results => {
				const validAnnotations = results.filter(Boolean);
				annotations = validAnnotations;

				if (validAnnotations.length > 0) {
					let centerCoord;

					if (center && center.latitude != null && center.longitude != null) {
						centerCoord = new mapkit.Coordinate(
							parseFloat(center.latitude),
							parseFloat(center.longitude)
						);
					} else {
						centerCoord = validAnnotations[0].coordinate;
					}
					const distanceInDegrees = cameraDistance / 111000;

					const region = new mapkit.CoordinateRegion(
						centerCoord,
						new mapkit.CoordinateSpan(distanceInDegrees, distanceInDegrees)
					);

					map.region = region;

					if (validAnnotations.length > 1) {
						map.showItems(validAnnotations);
					}
				}
			});
		}

		const has_camera_boundary = cameraBoundary && typeof cameraBoundary === "object" && !Array.isArray(cameraBoundary) && Object.keys(cameraBoundary).length > 0;

		if (has_camera_boundary) {
			// Ensure all required properties exist and are not null/undefined
			const { latitude, longitude, span_latitude, span_longitude } = cameraBoundary;

			if (
				latitude == null || // Checks for null or undefined
				longitude == null ||
				span_latitude == null ||
				span_longitude == null
			) {
				console.warn("cameraBoundary properties are missing or undefined:", cameraBoundary);
			} else {
				try {
					// Log the initial cameraBoundary values for debugging
					console.log("Initial cameraBoundary values:", cameraBoundary);

					// Convert all cameraBoundary properties to numbers
					const parsedLatitude = parseFloat(latitude);
					const parsedLongitude = parseFloat(longitude);
					const parsedSpanLatitude = parseFloat(span_latitude);
					const parsedSpanLongitude = parseFloat(span_longitude);

					// Check if any of the values are invalid (NaN)
					if (
						isNaN(parsedLatitude) ||
						isNaN(parsedLongitude) ||
						isNaN(parsedSpanLatitude) ||
						isNaN(parsedSpanLongitude)
					) {
						throw new Error(
							`Invalid cameraBoundary values: latitude=${latitude}, longitude=${longitude}, span_latitude=${span_latitude}, span_longitude=${span_longitude}`
						);
					}

					// Create the CoordinateRegion with validated numeric values
					const region = new mapkit.CoordinateRegion(
						new mapkit.Coordinate(parsedLatitude, parsedLongitude),
						new mapkit.CoordinateSpan(parsedSpanLatitude, parsedSpanLongitude)
					);

					// Assign the camera boundary
					map.cameraBoundary = region.toMapRect();
					console.log("Camera boundary set successfully:", region);
				} catch (error) {
					console.error("Error setting camera boundary:", error.message, error);
				}
			}
		}

	} catch (error) {
		console.error("Error initializing Apple Maps:", error);
	}
}
