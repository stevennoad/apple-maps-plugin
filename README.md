# Apple Maps Plugin (v3.0.0)

A WordPress plugin that adds an **Apple Maps Elementor widget** for building interactive maps with multiple locations.

## Features

- **Elementor widget**: add Apple Maps directly in the Elementor editor
- **Mixed location types in one map**
  - **Place ID** locations (uses Apple’s default place details card / PlaceDetail UI)
  - **Manual Latitude/Longitude** locations
- **Place Finder (in editor)**: search for places and automatically fill the **Place ID**
- **Optional Place Name Override** (toggle per location) for Place ID markers
- **Global Pin Styles**
  - Use **Icon/SVG** (upload SVG/PNG)
  - Or **Text** (short label, recommended ≤ 6 characters)
  - Set a **global pin colour**
- **Import/Export**: copy map locations + settings + pin styles between widget instances (pages or sites)
- Map controls (typical options):
  - Height & border radius (responsive)
  - Light/dark scheme
  - Camera distance
  - Zoom/scroll/rotation toggles
  - Optional camera center / boundary / zoom range

## Installation

### Install from ZIP

1. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
2. Upload the plugin ZIP
3. Click **Install Now**
4. Click **Activate**

## Configuration

### MapKit JS Token

1. In WordPress admin, go to **Apple Maps** (admin menu)
2. Paste your **MapKit JS token** into the Token field
3. Click **Save Changes**

**Important:** Apple token restrictions must allow the domain(s) you use in both the Elementor editor and frontend.
If Place Finder shows no results, it’s usually a token restriction or domain mismatch.

## Usage (Elementor)

1. Open a page in **Elementor**
2. Search for **Apple Maps** in the widget list
3. Drag the **Apple Maps** widget onto the page

### Add locations

Open the **Locations** section and click **Add Item**.

Each location has a **Location Type**:

#### Place ID

- Use **Place Finder** to search (e.g. “iStore Woking”) and click a result
- This fills the **Place ID**
- Optional: enable **Override Place Name** to show custom text on the map
- Clicking a Place ID pin shows Apple’s default **place card** UI

#### Manual Lat/Lng

- Set **Latitude** and **Longitude**
- Use this when you don’t have a Place ID, or want full manual control

> You can combine Place ID and Manual locations on the same map.

## Pin Styles (Global)

Open **Pin Styles** to set marker appearance for all locations:

- **Pin Style Type**
  - **Icon / SVG** → upload an SVG/PNG
  - **Text** → show a short label (recommended ≤ 6 characters)
- **Default Pin Color**
- **Default Pin Icon** (when Icon/SVG selected)
- **Default Pin Text** (when Text selected)

## Import / Export

At the bottom of the widget settings, open **Import/Export**.

### Export

1. Click **Export**
2. Copy the JSON shown in the textarea

### Import

1. Paste exported JSON into the textarea
2. Click **Import**
3. Elementor will mark the page as changed so **Update/Publish** is enabled

**Notes**
- Import sanitises legacy keys and unsupported fields to avoid repeater/import errors.
- If your export references media attachment IDs from another site, those IDs may not exist on the destination site. URLs will still import if present.

## Troubleshooting

### Place Finder shows no results / does nothing
- Verify your token is saved in **Apple Maps → MapKit Token**
- Confirm Apple Developer settings allow your domain(s)
- Check the browser console for MapKit errors (expired token, unauthorized domain, invalid token)

### Clicking a Place ID marker doesn’t show the Apple place card
- Ensure the location **Location Type** is **Place ID**
- Confirm the Place ID is valid

### Import works but you don’t see an Update/Publish button
- This should be fixed in v3.0.0+ (import triggers Elementor “dirty” state)
- If it still occurs, make any small change in the widget and the button should appear

## Upgrading from older versions (shortcodes)

Older versions used shortcodes like `[apple_map]` and `[apple_map_places]`.
In v3.0.0, those are no longer the primary workflow.

To migrate:

1. Add the **Apple Maps** widget in Elementor
2. Re-create your locations using:
   - **Place ID** (recommended where available)
   - **Manual Lat/Lng** for the rest
3. Use **Import/Export** to duplicate maps between pages/sites once you’ve built one
