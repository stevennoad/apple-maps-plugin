document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM fully loaded and parsed.');

  mapkit.init({
    authorizationCallback: function(done) {
      done(appleMapsSettings.token);
    }
  });
  
  const CALLOUT_OFFSET = new DOMPoint(0, 15);
  const CALL_OUT_ANIMATION = ".4s cubic-bezier(0.4, 0, 0, 1.5) 0s 1 normal scale-and-fadein";

  const landmarkAnnotationCallout = {
    calloutElementForAnnotation: annotation => {
      console.log('Creating callout element for annotation:', annotation);
      return createCalloutElement(annotation.landmark);
    },
    calloutAnchorOffsetForAnnotation: () => CALLOUT_OFFSET,
    calloutAppearanceAnimationForAnnotation: () => CALL_OUT_ANIMATION
  };

  function createCalloutElement(landmark) {
    console.log('Creating callout element for landmark:', landmark);
    const div = document.createElement("div");
    div.className = "landmark";

    div.innerHTML = `
      <h1>${landmark.title}</h1>
      <section>
        <p class="address">${landmark.address}</p>
        <p class="phone">${landmark.phone}</p>
        <p class="hours">${landmark.hours}</p>
        <p class="homepage"><a href="${landmark.url}">Store Details</a></p>
      </section>
    `;
    return div;
  }

  const createAnnotation = landmark => {
    console.log('Creating annotation for landmark:', landmark);
    const annotation = new mapkit.MarkerAnnotation(landmark.coordinate, {
      callout: landmarkAnnotationCallout,
      color: "#1d1d1d4",
      title: landmark.title,
      glyphText: appleMapsSettings.glyph
    });
    annotation.landmark = landmark;
    return annotation;
  };
  
  const getColorScheme = colorScheme => {
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

  const initializeMap = (mapElementId, landmarks, settings) => {
    console.log('Initializing map for element ID: ' + mapElementId);

    const annotations = landmarks.map(createAnnotation);

    const mapOptions = {
      colorScheme: getColorScheme(settings.colorScheme),
      isZoomEnabled: settings.isZoomEnabled ?? true,
      isScrollEnabled: settings.isScrollEnabled ?? true,
      isRotationEnabled: settings.isRotationEnabled ?? true,
    };
    
    // Set camera distance if provided in settings
    if (settings.cameraDistance !== undefined) {
      mapOptions.cameraDistance = settings.cameraDistance;
    }

    const map = new mapkit.Map(mapElementId, mapOptions);
    console.log('Map object created for element ID: ' + mapElementId);

    map.showItems(annotations);
    console.log('Annotations added to map for element ID: ' + mapElementId);

    if (settings.center?.latitude !== undefined && settings.center?.longitude !== undefined) {
      const centerCoordinate = new mapkit.Coordinate(settings.center.latitude, settings.center.longitude);
      map.setCenterAnimated(centerCoordinate);
    }
  };

  const getLandmarksFromJson = jsonString => {
    try {
      const { locations } = JSON.parse(jsonString);
      return locations.map(location => ({
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
  };

  const getSettingsFromJson = jsonString => {
    try {
      const jsonData = JSON.parse(jsonString);
      return jsonData.settings || {};
    } catch (error) {
      console.error('Error parsing JSON:', error);
      return {};
    }
  };

  const mapContainers = document.querySelectorAll('.apple-map-container');
  console.log('Map containers found:', mapContainers);

  mapContainers.forEach(container => {
    const mapElementId = container.id;
    const jsonLandmarks = container.getAttribute('data-landmarks');

    if (jsonLandmarks) {
      const landmarks = getLandmarksFromJson(jsonLandmarks);
      const settings = getSettingsFromJson(jsonLandmarks);
      console.log('Landmarks for ' + mapElementId + ':', landmarks);
      console.log('Settings for ' + mapElementId + ':', settings);
      initializeMap(mapElementId, landmarks, settings);
    } else {
      console.error('No landmarks data found for map container:', mapElementId);
    }
  });

  const getLandmarkFromContainer = container => ({
    coordinate: new mapkit.Coordinate(
      parseFloat(container.getAttribute('data-lat')),
      parseFloat(container.getAttribute('data-lng'))
    ),
    title: container.getAttribute('data-title'),
    address: container.getAttribute('data-address'),
    phone: container.getAttribute('data-phone'),
    hours: container.getAttribute('data-hours'),
    url: container.getAttribute('data-url')
  });
});