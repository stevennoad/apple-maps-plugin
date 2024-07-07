document.addEventListener('DOMContentLoaded', function () {
		console.log('DOM fully loaded and parsed.');

		mapkit.init({
				authorizationCallback: function(done) {
						done(appleMapsSettings.token);
				}
		});
		console.log('MapKit initialized.');

		const CALLOUT_OFFSET = new DOMPoint(0, 15);
		const CALL_OUT_ANIMATION = ".4s cubic-bezier(0.4, 0, 0, 1.5) 0s 1 normal scale-and-fadein";

		const landmarkAnnotationCallout = {
				calloutElementForAnnotation: function(annotation) {
						console.log('Creating callout element for annotation:', annotation);
						return createCalloutElement(annotation.landmark);
				},
				calloutAnchorOffsetForAnnotation: function() {
						return CALLOUT_OFFSET;
				},
				calloutAppearanceAnimationForAnnotation: function() {
						return CALL_OUT_ANIMATION;
				}
		};

		function createCalloutElement(landmark) {
				console.log('Creating callout element for landmark:', landmark);
				const div = document.createElement("div");
				div.className = "landmark";

				const title = div.appendChild(document.createElement("h1"));
				title.textContent = landmark.title;

				const section = div.appendChild(document.createElement("section"));

				const address = section.appendChild(document.createElement("p"));
				address.className = "address";
				address.textContent = landmark.address;

				const phone = section.appendChild(document.createElement("p"));
				phone.className = "phone";
				phone.textContent = landmark.phone;

				const hours = section.appendChild(document.createElement("p"));
				hours.className = "hours";
				hours.textContent = landmark.hours;

				const link = section.appendChild(document.createElement("p"));
				link.className = "homepage";
				const a = link.appendChild(document.createElement("a"));
				a.href = landmark.url;
				a.textContent = "Store Details";

				return div;
		}

		function createAnnotation(landmark) {
				console.log('Creating annotation for landmark:', landmark);
				const annotation = new mapkit.MarkerAnnotation(landmark.coordinate, {
						callout: landmarkAnnotationCallout,
						color: "#1d1d1d4",
						title: landmark.title,
						glyphText: appleMapsSettings.glyph
				});
				annotation.landmark = landmark;
				return annotation;
		}

		function initializeMap(mapElementId, landmarks) {
				console.log('Initializing map for element ID: ' + mapElementId);

				const annotations = landmarks.map(createAnnotation);

				const map = new mapkit.Map(mapElementId, {
						colorScheme: mapkit.Map.ColorSchemes.Light,
						showsUserLocationControl: true,
						isZoomEnabled: true,
						isScrollEnabled: true,
						isRotationEnabled: true
				});
				console.log('Map object created for element ID: ' + mapElementId);

				map.showItems(annotations);
				console.log('Annotations added to map for element ID: ' + mapElementId);
		}

		function getLandmarksFromJson(jsonString) {
				try {
						const jsonData = JSON.parse(jsonString);
						return jsonData.locations.map(location => ({
								coordinate: new mapkit.Coordinate(parseFloat(location.lat), parseFloat(location.lng)),
								title: location.title,
								address: location.address,
								phone: location.phone,
								hours: location.hours,
								url: location.url
						}));
				} catch (error) {
						console.error('Error parsing JSON:', error);
						return [];
				}
		}

		const mapContainers = document.querySelectorAll('.apple-map-container');
		console.log('Map containers found:', mapContainers);

		mapContainers.forEach(container => {
				const mapElementId = container.id;
				const jsonLandmarks = container.getAttribute('data-landmarks');

				if (jsonLandmarks) {
						const landmarks = getLandmarksFromJson(jsonLandmarks);
						console.log('Landmarks for ' + mapElementId + ':', landmarks);
						initializeMap(mapElementId, landmarks);
				} else {
						const landmarks = [getLandmarkFromContainer(container)];
						console.log('Landmarks for ' + mapElementId + ':', landmarks);
						initializeMap(mapElementId, landmarks);
				}
		});

		function getLandmarkFromContainer(container) {
				return {
						coordinate: new mapkit.Coordinate(
								parseFloat(container.getAttribute('data-lat')),
								parseFloat(container.getAttribute('data-lng'))
						),
						title: container.getAttribute('data-title'),
						address: container.getAttribute('data-address'),
						phone: container.getAttribute('data-phone'),
						hours: container.getAttribute('data-hours'),
						url: container.getAttribute('data-url')
				};
		}
});
