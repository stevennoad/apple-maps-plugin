(function($) {
	"use strict";

	// Only export/import the widget's own settings (not Elementor's Advanced/Style internals).
	const APPLE_MAPS_ALLOWED_KEYS = [
		'map_locations',
		'color_scheme',
		'camera_distance',
		'is_zoom_enabled',
		'is_scroll_enabled',
		'is_rotation_enabled',
		'enable_pois',
		'enable_camera_zoom_range',
		'camera_zoom_range',
		'enable_camera_boundary',
		'camera_boundary_latitude',
		'camera_boundary_longitude',
		'camera_boundary_span_latitude',
		'camera_boundary_span_longitude',
		'enable_map_center',
		'map_center_latitude',
		'map_center_longitude',
		'default_pin_style_type',
		'default_pin_text',
		'default_pin_color',
		'default_pin_icon'
	];

	// Keys that are valid per repeater row.
	const APPLE_MAPS_LOCATION_ALLOWED_KEYS = [
		'_id',
		'admin_label',
		'location_type',
		'place_id',
		'enable_place_name_override',
		'place_name_override',
		'location_name',
		'latitude',
		'longitude',
		'glyph',
		'enable_callout',
		'description',
		'enable_link',
		'link_url',
		'link_text'
	];

	function is_allowed_key(key) {
		if (APPLE_MAPS_ALLOWED_KEYS.indexOf(key) !== -1) {
			return true;
		}
		// Responsive variants created by Elementor.
		if (key.indexOf('map_height') === 0) {
			return true;
		}
		if (key.indexOf('map_border_radius') === 0) {
			return true;
		}
		return false;
	}

	function is_allowed_location_key(key) {
		return APPLE_MAPS_LOCATION_ALLOWED_KEYS.indexOf(key) !== -1;
	}

	function random_id() {
		return Math.random().toString(16).slice(2, 9);
	}

	function filter_location_item(item) {
		if (!item || typeof item !== 'object') {
			return null;
		}

		const filtered = {};
		Object.keys(item).forEach(function(k) {
			if (is_allowed_location_key(k)) {
				filtered[k] = item[k];
			}
		});

		// Ensure Elementor repeater row id exists.
		if (!filtered._id) {
			filtered._id = random_id();
		}

		// Normalise some common values (Elementor switchers typically expect 'yes'/'no').
		if (!filtered.location_type) {
			filtered.location_type = 'manual';
		}

		if (filtered.enable_callout === '') {
			// Keep empty as falsey (Elementor treats it as off), but keep explicit 'no' when missing.
			filtered.enable_callout = 'no';
		}
		if (typeof filtered.enable_callout === 'undefined') {
			filtered.enable_callout = 'yes';
		}

		if (typeof filtered.enable_link === 'undefined' || filtered.enable_link === '') {
			filtered.enable_link = 'no';
		}
		if (typeof filtered.enable_place_name_override === 'undefined' || filtered.enable_place_name_override === '') {
			filtered.enable_place_name_override = 'no';
		}

		// Clear fields that don't apply to the chosen mode to avoid Elementor confusion.
		if (filtered.location_type === 'place_id') {
			filtered.location_name = '';
			filtered.latitude = '';
			filtered.longitude = '';
			filtered.glyph = '';
		}
		if (filtered.location_type === 'manual') {
			filtered.place_id = '';
			filtered.enable_place_name_override = 'no';
			filtered.place_name_override = '';
		}

		return filtered;
	}

	function filter_settings_payload(payload) {
		if (!payload || typeof payload !== 'object') {
			return payload;
		}

		const filtered = {};
		Object.keys(payload).forEach(function(key) {
			if (!is_allowed_key(key)) {
				return;
			}

			if (key === 'map_locations' && Array.isArray(payload[key])) {
				const locs = [];
				payload[key].forEach(function(item) {
					const row = filter_location_item(item);
					if (row) {
						locs.push(row);
					}
				});
				filtered[key] = locs;
				return;
			}

			filtered[key] = payload[key];
		});

		// Preserve globals only for keys we keep.
		if (payload.__globals__ && typeof payload.__globals__ === 'object') {
			const g = {};
			Object.keys(payload.__globals__).forEach(function(gk) {
				if (is_allowed_key(gk)) {
					g[gk] = payload.__globals__[gk];
				}
			});
			if (Object.keys(g).length) {
				filtered.__globals__ = g;
			}
		}

		return filtered;
	}

	function get_edited_element_model() {
		// Elementor editor internals differ between versions; try several paths.
		try {
			if (window.elementor && elementor.panel && typeof elementor.panel.getCurrentPageView === 'function') {
				const page_view = elementor.panel.getCurrentPageView();
				if (page_view && typeof page_view.getOption === 'function') {
					const edited_view = page_view.getOption('editedElementView');
					if (edited_view) {
						if (typeof edited_view.getEditModel === 'function') {
							return edited_view.getEditModel();
						}
						if (edited_view.model) {
							return edited_view.model;
						}
					}
				}
			}
		} catch (e) {
			// ignore
		}

		try {
			if (window.elementor && typeof elementor.getPanelView === 'function') {
				const panel_view = elementor.getPanelView();
				if (panel_view && typeof panel_view.getCurrentPageView === 'function') {
					const page_view = panel_view.getCurrentPageView();
					if (page_view && typeof page_view.getOption === 'function') {
						const edited_view = page_view.getOption('editedElementView');
						if (edited_view) {
							if (typeof edited_view.getEditModel === 'function') {
								return edited_view.getEditModel();
							}
							if (edited_view.model) {
								return edited_view.model;
							}
						}
					}
				}
			}
		} catch (e) {
			// ignore
		}

		return null;
	}

	function get_settings_json() {
		const model = get_edited_element_model();
		if (!model) {
			return null;
		}

		const settings = model.get ? model.get('settings') : null;
		if (!settings) {
			return null;
		}

		if (typeof settings.toJSON === 'function') {
			return filter_settings_payload(settings.toJSON());
		}

		// Fallback for plain objects.
		return filter_settings_payload(settings.attributes ? settings.attributes : null);
	}

	function apply_settings_json(data) {
		const model = get_edited_element_model();
		if (!model) {
			return { ok: false, error: 'Unable to find the edited widget model.' };
		}

		const settings = model.get ? model.get('settings') : null;
		if (!settings || typeof settings.set !== 'function') {
			return { ok: false, error: 'Unable to access widget settings.' };
		}

		try {
			const filtered = filter_settings_payload(data);

			// Apply keys one-by-one to play nicely with Elementor/Backbone internals.
			Object.keys(filtered).forEach(function(k) {
				settings.set(k, filtered[k]);
			});

			// Best-effort refresh.
			try {
				settings.trigger('change');
			} catch (e) {}
			try {
				model.trigger('change');
			} catch (e) {}

			// Mark editor as "dirty" so Elementor enables Update/Publish.
			// Elementor internals differ between versions, so try several mechanisms.
			try {
				if (window.elementor && elementor.channels && elementor.channels.editor) {
					elementor.channels.editor.trigger('change');
				}
			} catch (e) {}
			try {
				if (window.elementor && elementor.saver) {
					if (typeof elementor.saver.setFlagEditorChange === 'function') {
						elementor.saver.setFlagEditorChange(true);
					}
					if (typeof elementor.saver.setFlagChanged === 'function') {
						elementor.saver.setFlagChanged(true);
					}
					if (typeof elementor.saver.updateApplyButton === 'function') {
						elementor.saver.updateApplyButton();
					}
					if (typeof elementor.saver.updateSaveButton === 'function') {
						elementor.saver.updateSaveButton();
					}
				}
			} catch (e) {}
			try {
				if (window.elementor && elementor.settings && elementor.settings.page && elementor.settings.page.model) {
					elementor.settings.page.model.trigger('change');
				}
			} catch (e) {}

			try {
				if (window.elementor && typeof elementor.reloadPreview === 'function') {
					elementor.reloadPreview();
				} else if (window.elementor && elementor.previewView && typeof elementor.previewView.render === 'function') {
					elementor.previewView.render();
				}
			} catch (e) {}

			return { ok: true };
		} catch (e) {
			return { ok: false, error: 'Import failed: ' + (e && e.message ? e.message : 'unknown error') };
		}
	}

	function bind_import_export($box) {
		if ($box.data('apple-maps-bound')) {
			return;
		}
		$box.data('apple-maps-bound', true);

		const $textarea = $box.find('.apple-maps-import-export__textarea');
		const $status = $box.find('.apple-maps-import-export__status');
		const $export_btn = $box.find('.apple-maps-import-export__export');
		const $import_btn = $box.find('.apple-maps-import-export__import');

		$export_btn.on('click', function() {
			$status.text('Exporting…');
			const json = get_settings_json();
			if (!json) {
				$status.text('Unable to export: widget settings not available.');
				return;
			}

			try {
				$textarea.val(JSON.stringify(json, null, 2));
				$textarea.trigger('input').trigger('change');
				$status.text('Export ready.');
			} catch (e) {
				$status.text('Unable to export: ' + (e && e.message ? e.message : 'unknown error'));
			}
		});

		$import_btn.on('click', function() {
			const raw = ($textarea.val() || '').trim();
			if (!raw) {
				$status.text('Paste JSON to import.');
				return;
			}

			let data = null;
			try {
				data = JSON.parse(raw);
			} catch (e) {
				$status.text('Invalid JSON.');
				return;
			}

			$status.text('Importing…');
			const result = apply_settings_json(data);
			if (result.ok) {
				$status.text('Import complete.');
			} else {
				$status.text(result.error || 'Import failed.');
			}
		});
	}

	function scan_and_bind() {
		$('.apple-maps-import-export').each(function() {
			bind_import_export($(this));
		});
	}

	function setup_mutation_observer() {
		if (typeof MutationObserver === 'undefined') {
			return;
		}

		const target = document.body || document.documentElement;
		if (!target) {
			return;
		}

		const observer = new MutationObserver(function(mutations) {
			let should_scan = false;
			mutations.forEach(function(mutation) {
				if (should_scan || !mutation.addedNodes || !mutation.addedNodes.length) {
					return;
				}

				mutation.addedNodes.forEach(function(node) {
					if (should_scan || !node || node.nodeType !== 1) {
						return;
					}

					try {
						if (node.classList && node.classList.contains('apple-maps-import-export')) {
							should_scan = true;
							return;
						}
						if (node.querySelector && node.querySelector('.apple-maps-import-export')) {
							should_scan = true;
						}
					} catch (e) {}
				});
			});

			if (should_scan) {
				scan_and_bind();
			}
		});

		observer.observe(target, { childList: true, subtree: true });
	}

	$(window).on('elementor:init', function() {
		scan_and_bind();
		setup_mutation_observer();

		try {
			if (window.elementor && elementor.hooks && typeof elementor.hooks.addAction === 'function') {
				elementor.hooks.addAction('panel/open_editor/widget/apple_maps_widget', function() {
					scan_and_bind();
				});
			}
		} catch (e) {}
	});

	$(document).ready(function() {
		scan_and_bind();
		setup_mutation_observer();
	});
})(jQuery);
