# Apple Maps Plugin

A WordPress plugin to add Apple Maps with multiple locations using shortcodes.

## Description

The Apple Maps Plugin allows you to easily integrate Apple Maps into your WordPress site. You can add multiple locations with custom markers and information.

## Features

- Embed Apple Maps in posts, pages, or widgets using a shortcode.
- Configure map via the admin settings page.
- Customise map location details such as latitude, longitude, title, address, phone, and URL.
- Supports multiple locations on a single map.
- Use PlaceIDs to display the location pin and details card

## Installation

### Manual Installation

1. **Download the Plugin:**
   - Download the plugin zip file from the [GitHub repository](https://github.com/stevennoad/apple-maps-plugin).
   - Rename the zip file to `apple-maps`.

2. **Upload the Plugin:**
   - Navigate to `Plugins > Add New` in your WordPress admin dashboard.
   - Click on the `Upload Plugin` button.
   - Choose the downloaded zip file and click `Install Now`.

3. **Activate the Plugin:**
   - After the plugin is installed, click on `Activate Plugin`.

## Configuration

### API Key

1. **Get Your MapKit JS Token:**
   - Obtain your Apple MapKit JS token from the Apple Developer portal.

2. **Enter the Token in Plugin Settings:**
   - Navigate to `Settings > Apple Map` in your WordPress admin dashboard.
   - Enter your MapKit JS token in the designated field.

### Shortcode Usage

Use the `[apple_map]` shortcode to customise your location data.

Use the `[apple_map_places]` shortcode to use a locations place ID.

#### Examples
Use the `[apple_map]` shortcode to display locations based on JSON data you provide. The plugin will iterate through all the locations and display pins accordingly.

```html
<!-- Single location -->
[apple_map]
{
  "locations": [
    {
      "lat": "37.3349",
      "lng": "-122.0090",
      "title": "Apple Headquarters",
      "address": "One Apple Park Way, Cupertino, CA 95014, USA",
      "phone": "+1-408-996-1010",
      "url": "https://www.apple.com"
    }
  ]
}
[/apple_map]


<!-- Multiple locations -->
[apple_map]
{
  "locations": [
    {
      "lat": "37.3349",
      "lng": "-122.0090",
      "title": "Apple Headquarters",
      "address": "One Apple Park Way, Cupertino, CA 95014, USA",
      "phone": "+1-408-996-1010",
      "url": "https://www.apple.com"
    },
    {
      "lat": "37.3318",
      "lng": "-122.0312",
      "title": "Apple Campus",
      "address": "1 Infinite Loop, Cupertino, CA 95014, USA",
      "phone": "+1-408-996-1010",
      "url": "https://www.apple.com"
    }
  ]
}
[/apple_map]


<!-- Using place Ids -->
[apple_map_places]
{
  "locations": [
    {
      "placeId": "I63802885C8189B2B"
    },
    {
      "placeId": "I92DB6EB7006183F4"
    }
  ]
}
[/apple_map_places]
```

## Custom Settings
You can customise the map further by including a `settings` object within your shortcode. Below is the current list of available settings:

```html
"settings": {
	 "colorScheme": "light",
	 "cameraDistance": 1000,
	 "isZoomEnabled": true,
	 "isScrollEnabled": true,
	 "isRotationEnabled": true,
	 "style": "compact_callout",
	 "center": {
		 "latitude": 37.3318,
		 "longitude": -122.0312
	 },
	 "cameraZoomRange": {
			"min": 5000,
			"max": 10000
		},
	 "cameraBoundary": {
		 "latitude": 37.3349,
		 "longitude": -122.0090,
		 "spanLatitude": 0.05,
		 "spanLongitude": 0.05
	 }
 }
```

#### Full example of multiple locations with custom setting
```html
[apple_map]
{
  "locations": [
    {
      "lat": "37.3349",
      "lng": "-122.0090",
      "title": "Apple Headquarters",
      "address": "One Apple Park Way, Cupertino, CA 95014, USA",
      "phone": "+1-408-996-1010",
      "url": "https://www.apple.com"
    },
    {
      "lat": "37.3318",
      "lng": "-122.0312",
      "title": "Apple Campus",
      "address": "1 Infinite Loop, Cupertino, CA 95014, USA",
      "phone": "+1-408-996-1010",
      "url": "https://www.apple.com"
    }
  ],
  "settings": {
     "colorScheme": "dark",
     "isZoomEnabled": true,
     "isScrollEnabled": true,
     "isRotationEnabled": true
   }
}
[/apple_map]
```
