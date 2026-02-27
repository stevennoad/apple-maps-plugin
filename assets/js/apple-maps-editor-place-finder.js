(function($) {
	"use strict";

	let init_promise = null;

	function get_mapkit_token() {
		if (typeof AppleMapsSettings === "undefined") {
			return "";
		}

		if (!AppleMapsSettings.mapkitToken) {
			return "";
		}

		return AppleMapsSettings.mapkitToken;
	}

	function ensure_mapkit_ready() {
		if (init_promise) {
			return init_promise;
		}

		const token = get_mapkit_token();
		if (!token) {
			init_promise = Promise.reject(new Error("Missing MapKit token."));
			return init_promise;
		}

		if (typeof ensure_mapkit !== "function") {
			init_promise = Promise.reject(new Error("MapKit helper is unavailable."));
			return init_promise;
		}

		init_promise = ensure_mapkit(token);
		return init_promise;
	}

	function get_place_id_from_place(place) {
		if (!place || typeof place !== "object") {
			return "";
		}

		return place.placeId || place.place_id || place.id || place.identifier || "";
	}

	function get_place_name_from_place(place) {
		if (!place || typeof place !== "object") {
			return "";
		}

		return place.name || place.title || place.displayName || "";
	}

	function set_control_value($row, control_name, value) {
		if (!$row || !$row.length) {
			return;
		}

		// Elementor repeater DOM varies between versions. Prefer the closest repeater controls container.
		let $scope = $row;
		const $controls = $row.closest('.elementor-repeater-row-controls');
		if ($controls && $controls.length) {
			$scope = $controls;
		}

		const $input = $scope.find('[data-setting="' + control_name + '"]');
		if (!$input.length) {
			return;
		}

		// Switchers are rendered as checkboxes in many Elementor versions.
		if ($input.is(':checkbox')) {
			$input.prop('checked', String(value) === 'yes');
			$input.trigger('change');
			return;
		}

		$input.val(value);
		$input.trigger('input');
		$input.trigger('change');
	}

	function render_results($lookup, places, $row) {
		const $results = $lookup.find('.apple-maps-place-lookup__results');
		$results.empty();
		$results.off('click.applemaps');

		if (!places || !places.length) {
			$results.html('<div style="font-size:12px; opacity:.8;">No results.</div>');
			return;
		}

		const $list = $('<div />');

		places.forEach(function(place) {
			const place_id = get_place_id_from_place(place);
			const place_name = get_place_name_from_place(place);

			if (!place_id) {
				return;
			}

			const $item = $('<div />');
			$item.addClass('apple-maps-place-lookup__result');
			$item.attr('data-place-id', place_id);
			$item.attr('data-place-name', place_name || '');
			$item.css({
				padding: '8px',
				border: '1px solid rgba(0,0,0,.08)',
				borderRadius: '6px',
				marginBottom: '6px',
				cursor: 'pointer'
			});

			$item.append('<div style="font-weight:600;">' + $('<div>').text(place_name || place_id).html() + '</div>');
			$item.append('<div style="font-size:12px; opacity:.75; margin-top:3px;">' + $('<div>').text(place_id).html() + '</div>');

			$list.append($item);
		});

		$results.append($list);

		// Use delegated events because Elementor frequently re-renders repeater rows.
		$results.on('click.applemaps', '.apple-maps-place-lookup__result', function() {
			const $item = $(this);
			const place_id = $item.attr('data-place-id') || '';
			const place_name = $item.attr('data-place-name') || '';

			if (!place_id) {
				return;
			}

			set_control_value($row, 'place_id', place_id);

			if (place_name) {
				// Pre-fill an override value, but keep it optional via the toggle.
				set_control_value($row, 'enable_place_name_override', 'yes');
				set_control_value($row, 'place_name_override', place_name);
				set_control_value($row, 'admin_label', place_name);
			}
		});
	}

	function do_search(query, $lookup, $row) {
		query = (query || '').trim();
		const $status = $lookup.find('.apple-maps-place-lookup__status');

		if (!query) {
			$lookup.find('.apple-maps-place-lookup__results').empty();
			$status.text('');
			return;
		}

		// Give immediate feedback so it's obvious the input is wired up.
		$status.text('Preparing…');

		ensure_mapkit_ready().then(function() {
			if (!mapkit) {
				$status.text('MapKit is unavailable.');
				return;
			}

			// Some MapKit JS builds require optional libraries for Search.
			if (!mapkit.Search && typeof mapkit.load === 'function') {
				try {
					mapkit.load(['search']);
				} catch (e) {
					// ignore
				}
			}

			if (!mapkit.Search) {
				$status.text('Search is not available (missing MapKit JS search library).');
				return;
			}

			$status.text('Searching…');

			const search = new mapkit.Search();
			const callback = function(error, data) {
				if (error) {
					const message = (error && (error.message || error.toString)) ? (error.message || error.toString()) : '';
					$status.text(message ? ('Search error: ' + message) : 'Search error.');
					return;
				}

				const places = (data && (data.places || data.results || data.items)) ? (data.places || data.results || data.items) : [];
				$status.text(places.length ? ('Found ' + places.length + ' result(s).') : 'No results.');
				render_results($lookup, places, $row);
			};

			try {
				// Newer MapKit JS supports an options argument; older builds ignore it.
				search.search(query, callback, { limitToCountries: [], language: (navigator.language || 'en-GB') });
			} catch (e) {
				// Fallback to older signature.
				try {
					search.search(query, callback);
				} catch (inner) {
					$status.text('Search error: unable to execute search.');
				}
			}
		}).catch(function() {
			$status.text('MapKit token is missing or invalid.');
		});
	}

	function bind_lookup($lookup) {
		if ($lookup.data('apple-maps-bound')) {
			return;
		}

		$lookup.data('apple-maps-bound', true);

		// Elementor repeater wrappers vary across versions.
		const $row = $lookup.closest('.elementor-repeater-row-controls, .elementor-repeater-row, .elementor-repeater-item');
		const $query = $lookup.find('.apple-maps-place-lookup__query');

		let debounce_timer = null;
		$query.on('input', function() {
			const value = $(this).val();

			if (debounce_timer) {
				clearTimeout(debounce_timer);
			}

			debounce_timer = setTimeout(function() {
				do_search(value, $lookup, $row);
			}, 350);
		});
	}

	function scan_and_bind() {
		$('.apple-maps-place-lookup').each(function() {
			bind_lookup($(this));
		});
	}

	function setup_mutation_observer() {
		if (typeof MutationObserver === 'undefined') {
			return;
		}

		let observer_target = document.body;
		try {
			// Elementor editor controls live in the admin document.
			// Observing body is enough and avoids depending on internal panel selectors.
			observer_target = document.body || document.documentElement;
		} catch (e) {
			observer_target = document.documentElement;
		}

		if (!observer_target) {
			return;
		}

		const observer = new MutationObserver(function(mutations) {
			let should_scan = false;
			mutations.forEach(function(mutation) {
				if (should_scan) {
					return;
				}

				if (!mutation.addedNodes || !mutation.addedNodes.length) {
					return;
				}

				mutation.addedNodes.forEach(function(node) {
					if (should_scan || !node) {
						return;
					}

					try {
						if (node.nodeType === 1) {
							if (node.classList && node.classList.contains('apple-maps-place-lookup')) {
								should_scan = true;
								return;
							}

							if (node.querySelector && node.querySelector('.apple-maps-place-lookup')) {
								should_scan = true;
							}
						}
					} catch (e) {
						// ignore
					}
				});
			});

			if (should_scan) {
				scan_and_bind();
			}
		});

		observer.observe(observer_target, { childList: true, subtree: true });
	}

	// Elementor updates the panel DOM frequently, so we re-scan.
	$(window).on('elementor:init', function() {
		scan_and_bind();
		setup_mutation_observer();

		// If Elementor hooks are available, bind when the widget panel opens.
		try {
			if (window.elementor && elementor.hooks && typeof elementor.hooks.addAction === 'function') {
				elementor.hooks.addAction('panel/open_editor/widget/apple_maps_widget', function() {
					scan_and_bind();
				});
			}
		} catch (e) {
			// ignore
		}
	});

	$(document).ready(function() {
		scan_and_bind();
		setup_mutation_observer();
	});
})(jQuery);
