# Apple Maps Plugin

A WordPress plugin to add Apple Maps with multiple locations using shortcodes.

## Description

The Apple Maps Plugin allows you to easily integrate Apple Maps into your WordPress site. You can add multiple locations with custom markers and information. This plugin also provides an admin settings page to configure default map settings, such as latitude, longitude, title, address, phone, and URL.

## Features

- Embed Apple Maps in posts, pages, or widgets using a shortcode.
- Configure default map settings via the admin settings page.
- Customise map location details such as latitude, longitude, title, address, phone, and URL.
- Supports multiple maps on a single page.

## Installation

### Manual Installation

1. **Download the Plugin:**
   - Download the plugin zip file from the [GitHub repository](https://github.com/stevennoad/apple-maps-plugin).
   - Rename the zip file to `apple-maps-plugin`.

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

Use the `[apple_map]` shortcode to embed a map in your posts or pages.

#### Example Single Location Shortcode

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
    }
  ]
}
[/apple_map]
```

#### Example Multiple Locations Shortcode

Use the `[apple_map]` shortcode to embed a map with multiple locations. The locations should be provided in JSON format within the shortcode.
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
  ]
}
[/apple_map]
```

#### Custom Settings
You can pass additional settings to the map canvas by including a settings array in the JSON

```html
"settings": {
   "colorScheme": "light",
   "cameraDistance": 1000,
   "isZoomEnabled": true,
   "isScrollEnabled": true,
   "isRotationEnabled": true,
   "center": {
     "latitude": 37.3318,
     "longitude": -122.0312
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