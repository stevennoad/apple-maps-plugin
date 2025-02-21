document.addEventListener('DOMContentLoaded', () => {
	const mapContainers = document.querySelectorAll('.apple-map-container');

	if (mapContainers.length > 0) {
		mapkit.init({
			authorizationCallback: function(done) {
				done(appleMapsSettings.token);
			}
		});

		// Create a single map instance
		const mapContainer = mapContainers[0];

		// Extract and parse locations data
		const locationsData = mapContainer.dataset.landmarks;
		let locations = [];
		try {
			locations = JSON.parse(locationsData).locations;
		} catch (error) {
			console.error('Error parsing locations data:', error);
			return;
		}

		// Settings for the map
		const settings = appleMapsSettings.settings;

		const getColorScheme = (colorScheme) => {
			switch (colorScheme) {
				case 'light':
					return mapkit.Map.ColorSchemes.Light;
				case 'dark':
					return mapkit.Map.ColorSchemes.Dark;
				case 'hybrid':
					return mapkit.Map.ColorSchemes.Hybrid;
				default:
					return mapkit.Map.ColorSchemes.Light;
			}
		};

		const mapOptions = {
			colorScheme: getColorScheme(settings.colorScheme),
			isZoomEnabled: settings.isZoomEnabled ?? true,
			isScrollEnabled: settings.isScrollEnabled ?? true,
			isRotationEnabled: settings.isRotationEnabled ?? true,
			cameraZoomRange: new mapkit.CameraZoomRange(settings.cameraZoomRange?.min, settings.cameraZoomRange?.max)
		};

		const map = new mapkit.Map(mapContainer, mapOptions);

		const annotations = [];
		let pendingLookups = locations.length;

		locations.forEach((location) => {
			const id = location.placeId;
			const glyphText = appleMapsSettings.glyph || '';

			const lookup = new mapkit.PlaceLookup();
			lookup.getPlace(id, (error, place) => {
				pendingLookups--;

				if (error) {
					console.error('Error looking up place:', error);
				} else {
					const annotation = new mapkit.PlaceAnnotation(place);
					annotation.glyphText = glyphText;
					map.addAnnotation(annotation);
					annotations.push(annotation);

					const getPlaceDetailsStyle = (style) => {
						switch (style) {
							case 'automatic':
								return mapkit.PlaceSelectionAccessory.Styles.Automatic;
							case 'callout':
								return mapkit.PlaceSelectionAccessory.Styles.Callout;
							case 'full_callout':
								return mapkit.PlaceSelectionAccessory.Styles.FullCallout;
							case 'compact_callout':
								return mapkit.PlaceSelectionAccessory.Styles.CompactCallout;
							case 'open_in_maps':
								return mapkit.PlaceSelectionAccessory.Styles.OpenInMaps;
							default:
								return mapkit.PlaceSelectionAccessory.Styles.Automatic;
						}
					};

					const accessory = new mapkit.PlaceSelectionAccessory({
						style: getPlaceDetailsStyle(settings.style),
					});
					annotation.selectionAccessory = accessory;
				}

				if (pendingLookups === 0) {
					map.showItems(annotations);

					if (settings.cameraBoundary?.latitude !== undefined && settings.cameraBoundary?.longitude !== undefined && settings.cameraBoundary?.spanLatitude !== undefined && settings.cameraBoundary?.spanLongitude !== undefined) {
						const cameraBoundary = new mapkit.CoordinateRegion(
							new mapkit.Coordinate(settings.cameraBoundary.latitude, settings.cameraBoundary.longitude),
							new mapkit.CoordinateSpan(settings.cameraBoundary.spanLatitude, settings.cameraBoundary.spanLongitude)
						);

						map.cameraBoundary = cameraBoundary.toMapRect();
					}

					if (settings.center?.latitude !== undefined && settings.center?.longitude !== undefined) {
						map.center = new mapkit.Coordinate(settings.center.latitude, settings.center.longitude);
					}

					if (settings.cameraDistance !== undefined) {
						map.cameraDistance = settings.cameraDistance;
					}

					mapContainer.style.height = `${settings.mapHeight || 500}px`;
				}
			});
		});
	}
});
