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
    
    const mapOptions = {
      colorScheme: getColorScheme(settings.colorScheme),
      isZoomEnabled: settings.isZoomEnabled ?? true,
      isScrollEnabled: settings.isScrollEnabled ?? true,
      isRotationEnabled: settings.isRotationEnabled ?? true,
    };
    
    const map = new mapkit.Map(mapContainer, mapOptions);
    
    const annotations = [];
    let pendingLookups = locations.length;

    locations.forEach(location => {
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

          const accessory = new mapkit.PlaceSelectionAccessory();
          annotation.selectionAccessory = accessory;
        }

        if (pendingLookups === 0) {
          map.showItems(annotations);
          
          if (settings.center?.latitude !== undefined && settings.center?.longitude !== undefined) {
            map.center = new mapkit.Coordinate(settings.center.latitude, settings.center.longitude);
          }
          
          // Set camera distance if provided in settings
          if (settings.cameraDistance !== undefined) {
            map.cameraDistance = settings.cameraDistance;
          }
        }
      });
    });
  }
});
