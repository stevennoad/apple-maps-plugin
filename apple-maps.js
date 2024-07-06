document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed.');

    mapkit.init({
        authorizationCallback: function(done) {
            done(appleMapsSettings.token);
        }
    });

    console.log('MapKit initialized.');

    function initializeMap(mapElementId, landmarks) {
        console.log('Initializing map for element ID: ' + mapElementId);

        var CALLOUT_OFFSET = new DOMPoint(0, 15);
        var landmarkAnnotationCallout = {
            calloutElementForAnnotation: function(annotation) {
                return calloutForLandmarkAnnotation(annotation);
            },
            calloutAnchorOffsetForAnnotation: function(annotation, element) {
                return CALLOUT_OFFSET;
            },
            calloutAppearanceAnimationForAnnotation: function(annotation) {
                return ".4s cubic-bezier(0.4, 0, 0, 1.5) 0s 1 normal scale-and-fadein";
            }
        };

        var annotations = landmarks.map(function(landmark) {
            var annotation = new mapkit.MarkerAnnotation(landmark.coordinate, {
                callout: landmarkAnnotationCallout,
                color: "#1d1d1d4",
                title: landmark.title,
                glyphText: appleMapsSettings.glyph
            });
            annotation.landmark = landmark;
            return annotation;
        });

        let map = new mapkit.Map(mapElementId, {
            colorScheme: mapkit.Map.ColorSchemes.Light,
        });

        console.log('Map object created for element ID: ' + mapElementId);

        map.showItems(annotations);
        console.log('Annotations added to map for element ID: ' + mapElementId);
    }

    function calloutForLandmarkAnnotation(annotation) {
        var div = document.createElement("div");
        div.className = "landmark";

        var title = div.appendChild(document.createElement("h1"));
        title.textContent = annotation.landmark.title;

        var section = div.appendChild(document.createElement("section"));
        var address = section.appendChild(document.createElement("p"));
        address.className = "address";
        address.textContent = annotation.landmark.address;

        var phone = section.appendChild(document.createElement("p"));
        phone.className = "phone";
        phone.textContent = annotation.landmark.phone;

        var hours = section.appendChild(document.createElement("p"));
        hours.className = "hours";
        hours.textContent = annotation.landmark.hours;

        var link = section.appendChild(document.createElement("p"));
        link.className = "homepage";
        var a = link.appendChild(document.createElement("a"));
        a.href = annotation.landmark.url;
        a.textContent = "Store Details";

        return div;
    }

    var mapContainers = document.querySelectorAll('.apple-map-container');
    console.log('Map containers found:', mapContainers);

    mapContainers.forEach(function(container) {
        var mapElementId = container.id;
        var landmarks = [{
            coordinate: new mapkit.Coordinate(parseFloat(container.getAttribute('data-lat')), parseFloat(container.getAttribute('data-lng'))),
            title: container.getAttribute('data-title'),
            address: container.getAttribute('data-address'),
            phone: container.getAttribute('data-phone'),
            hours: container.getAttribute('data-hours'),
            url: container.getAttribute('data-url')
        }];
        console.log('Landmarks for ' + mapElementId + ':', landmarks);
        initializeMap(mapElementId, landmarks);
    });
});
