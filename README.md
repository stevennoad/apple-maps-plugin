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

### Default Map Settings

Configure the default values for map attributes:

1. **Default Latitude:**
   - Enter the default latitude for your maps.
   
2. **Default Longitude:**
   - Enter the default longitude for your maps.
   
3. **Default Title:**
   - Enter the default title for the map marker.

4. **Default Address:**
   - Enter the default address for the map marker.

5. **Default Phone:**
   - Enter the default phone number for the map marker.

6. **Default URL:**
   - Enter the default URL for the map marker.

### Shortcode Usage

Use the `[apple_map]` shortcode to embed a map in your posts or pages. You can override the default settings by providing specific attributes in the shortcode.

#### Example Shortcode

```html
[apple_map lat="37.7749" lng="-122.4194" title="San Francisco" address="San Francisco, CA" phone="+1-555-555-5555" url="https://example.com"]
