(function($, _) {
	var maxZoom = 16;

	var isNotEmptyString = function(str) {
		if (_.isString(str)) {
			return str.trim().length;
		}
		return 0;
	};

	// Build the info-window / popup HTML for a single location. Shared by both
	// the Google Maps and the Leaflet (OpenStreetMap) rendering paths.
	var infoTemplate = _.template(
		"<% function isNotEmptyString(str) { if (_.isString(str)) {	return str.trim().length;} return 0; }  %>" +

			"<div class='infowindow'>" +

				"<% if (isNotEmptyString(location.thumb)) { %>" +
					"<div class='infowindow-thump'>" +
						"<img src='<%= location.thumb %>' >" +
					"</div> " +
				"<% } %>" +

				"<div class='infowindow-content'>" +
					"<% if ( isNotEmptyString(location.url) || isNotEmptyString(location.title) ) { %>" +
						"<div class='infowindow-title'>" +
							"<% if ( isNotEmptyString(location.url) ) { %><a href='<%- location.url %>'><% } %><%- isNotEmptyString(location.title) ?  location.title : location.url  %><% if ( isNotEmptyString(location.url) ) { %></a><% } %>" +
						"</div>" +
					"<% } %>" +
					"<% if ( isNotEmptyString(location.description) ) { %>" +
						"<div class='infowindow-description'>" +
							"<%= location.description %>" +
						"</div>" +
					"<% } %>" +
				"</div>" +

			"</div>");

	var locationHasContent = function(location) {
		return isNotEmptyString(location.description) || isNotEmptyString(location.title) || isNotEmptyString(location.url) || isNotEmptyString(location.thumb);
	};

	// Great-circle average of the supplied points (used as the map center).
	var calculateCenter = function(locations) {
		var Lng, Hyp, Lat,
			total = locations.length,
			X = 0,
			Y = 0,
			Z = 0;

		locations.forEach(function(location) {
			var lat = location.coordinates.lat * Math.PI / 180,
				lng = location.coordinates.lng * Math.PI / 180,
				x = Math.cos(lat) * Math.cos(lng),
				y = Math.cos(lat) * Math.sin(lng),
				z = Math.sin(lat);

			X += x;
			Y += y;
			Z += z;
		});

		X /= total;
		Y /= total;
		Z /= total;

		Lng = Math.atan2(Y, X);
		Hyp = Math.sqrt(X * X + Y * Y);
		Lat = Math.atan2(Z, Hyp);

		return { lng: (Lng * 180 / Math.PI), lat: (Lat * 180 / Math.PI) };
	};

	var readConfig = function($mapWrapper, $mapCanvas) {
		var locations = $mapWrapper.data('locations');

		// Height arrives as a CSS length string ("400px", "50vh"); apply it
		// verbatim so non-pixel units work. Falls back to a 3:2-ish ratio of the
		// current width (in px) when nothing is set.
		var rawHeight = $mapWrapper.data('map-height');
		rawHeight = (rawHeight === undefined || rawHeight === null) ? '' : ('' + rawHeight).trim();
		var height = rawHeight !== '' ? rawHeight : (parseInt($mapCanvas.width() * 0.66) + 'px');

		return {
			locations: ('undefined' !== locations && locations && locations.length) ? locations : [],
			height: height,
			mapType: $mapWrapper.data('map-type'),
			// jQuery .data() parses the "true"/"false" string into a real boolean.
			disableScroll: !!$mapWrapper.data('disable-scrolling'),
			// OpenStreetMap tile style + the site-wide provider keys (for keyed styles).
			osmStyle: $mapWrapper.data('osm-style'),
			keys: {
				stadia: $mapWrapper.data('stadia-key') || '',
				thunderforest: $mapWrapper.data('thunderforest-key') || '',
				maptiler: $mapWrapper.data('maptiler-key') || ''
			}
		};
	};

	// ---- Google Maps -------------------------------------------------------
	var initGoogle = function($mapWrapper, $mapCanvas, cfg) {
		$mapCanvas.css('height', cfg.height);

		var mapOptions = {
				center: cfg.locations.length ? calculateCenter(cfg.locations) : new google.maps.LatLng(-34, 150),
				mapTypeId: google.maps.MapTypeId[cfg.mapType],
				// 'cooperative' = page scrolls over the map; ctrl/two-finger to zoom.
				// 'greedy' = the wheel zooms the map directly.
				gestureHandling: cfg.disableScroll ? 'cooperative' : 'greedy'
			},
			markerBounds = new google.maps.LatLngBounds(),
			map = new google.maps.Map($mapCanvas.get(0), mapOptions);

		cfg.locations.forEach(function(location) {
			var gMapsCoords = new google.maps.LatLng(location.coordinates.lat, location.coordinates.lng);

			var marker = new google.maps.Marker({ position: gMapsCoords, map: map });
			markerBounds.extend(gMapsCoords);

			if (locationHasContent(location)) {
				var infowindow = new google.maps.InfoWindow({ content: infoTemplate({ location: location }) });
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
			}
		});

		map.fitBounds(markerBounds);

		// clamp to max zoom (so a single pin doesn't zoom in too far)
		var listener = google.maps.event.addListenerOnce(map, 'zoom_changed', function() {
			if (map.getZoom() > maxZoom) map.setZoom(maxZoom);
			google.maps.event.removeListener(listener);
		});

		$mapCanvas.data('map', map);
	};

	// ---- Leaflet / OpenStreetMap ------------------------------------------
	var leafletIconsConfigured = false;
	var configureLeafletIcons = function() {
		if (leafletIconsConfigured) {
			return;
		}
		// Leaflet's default marker icon resolves image paths relative to the
		// CSS; point it at the CDN explicitly so markers aren't broken images.
		var base = 'https://unpkg.com/leaflet@1.9.4/dist/images/';
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: base + 'marker-icon-2x.png',
			iconUrl: base + 'marker-icon.png',
			shadowUrl: base + 'marker-shadow.png'
		});
		leafletIconsConfigured = true;
	};

	// Free tile providers usable with Leaflet. Keyless styles work out of the
	// box; keyed styles carry a {KEY} placeholder + a `provider` whose key comes
	// from the site-wide option fields. All require visible attribution (kept in
	// the layer's `attribution`, shown in the map's bottom-right control).
	var OSM_ATTR      = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
	var CARTO_ATTR    = OSM_ATTR + ' &copy; <a href="https://carto.com/attributions">CARTO</a>';
	var STADIA_ATTR   = '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a> &copy; <a href="https://stamen.com/">Stamen Design</a> &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> ' + OSM_ATTR;
	var TF_ATTR       = '&copy; <a href="https://www.thunderforest.com/">Thunderforest</a>, ' + OSM_ATTR;
	var MAPTILER_ATTR = '&copy; <a href="https://www.maptiler.com/copyright/">MapTiler</a> ' + OSM_ATTR;

	var OSM_TILES = {
		// ---- keyless ----
		standard:       { url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', opts: { maxZoom: 19, attribution: OSM_ATTR } },
		carto_light:    { url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', opts: { maxZoom: 20, subdomains: 'abcd', attribution: CARTO_ATTR } },
		carto_dark:     { url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', opts: { maxZoom: 20, subdomains: 'abcd', attribution: CARTO_ATTR } },
		carto_voyager:  { url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png', opts: { maxZoom: 20, subdomains: 'abcd', attribution: CARTO_ATTR } },
		opentopomap:    { url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', opts: { maxZoom: 17, attribution: 'Map data: ' + OSM_ATTR + ', <a href="https://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)' } },
		cyclosm:        { url: 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', opts: { maxZoom: 20, attribution: '<a href="https://www.cyclosm.org/">CyclOSM</a> | Map data: ' + OSM_ATTR } },
		hot:            { url: 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', opts: { maxZoom: 19, attribution: OSM_ATTR + ', Tiles by <a href="https://www.hotosm.org/">Humanitarian OSM Team</a>' } },
		esri_satellite: { url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', opts: { maxZoom: 19, attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community' } },

		// ---- Stadia Maps (key) ----
		stadia_alidade_smooth:      { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}.png?api_key={KEY}', opts: { maxZoom: 20, attribution: STADIA_ATTR } },
		stadia_alidade_smooth_dark: { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}.png?api_key={KEY}', opts: { maxZoom: 20, attribution: STADIA_ATTR } },
		stadia_outdoors:            { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/outdoors/{z}/{x}/{y}.png?api_key={KEY}', opts: { maxZoom: 20, attribution: STADIA_ATTR } },
		stamen_toner:               { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/stamen_toner/{z}/{x}/{y}.png?api_key={KEY}', opts: { maxZoom: 20, attribution: STADIA_ATTR } },
		stamen_terrain:             { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/stamen_terrain/{z}/{x}/{y}.png?api_key={KEY}', opts: { maxZoom: 18, attribution: STADIA_ATTR } },
		stamen_watercolor:          { provider: 'stadia', url: 'https://tiles.stadiamaps.com/tiles/stamen_watercolor/{z}/{x}/{y}.jpg?api_key={KEY}', opts: { maxZoom: 16, attribution: STADIA_ATTR } },

		// ---- Thunderforest (key) ----
		tf_cycle:     { provider: 'thunderforest', url: 'https://{s}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey={KEY}', opts: { maxZoom: 22, attribution: TF_ATTR } },
		tf_transport: { provider: 'thunderforest', url: 'https://{s}.tile.thunderforest.com/transport/{z}/{x}/{y}.png?apikey={KEY}', opts: { maxZoom: 22, attribution: TF_ATTR } },
		tf_landscape: { provider: 'thunderforest', url: 'https://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey={KEY}', opts: { maxZoom: 22, attribution: TF_ATTR } },
		tf_outdoors:  { provider: 'thunderforest', url: 'https://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey={KEY}', opts: { maxZoom: 22, attribution: TF_ATTR } },

		// ---- MapTiler (key) ----
		maptiler_streets:   { provider: 'maptiler', url: 'https://api.maptiler.com/maps/streets-v2/{z}/{x}/{y}.png?key={KEY}', opts: { maxZoom: 20, attribution: MAPTILER_ATTR } },
		maptiler_satellite: { provider: 'maptiler', url: 'https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key={KEY}', opts: { maxZoom: 20, attribution: MAPTILER_ATTR } },
		maptiler_outdoor:   { provider: 'maptiler', url: 'https://api.maptiler.com/maps/outdoor-v2/{z}/{x}/{y}.png?key={KEY}', opts: { maxZoom: 20, attribution: MAPTILER_ATTR } }
	};

	// Resolve cfg → a Leaflet tile layer. Keyed styles with no key gracefully
	// fall back to keyless OpenStreetMap Standard.
	var buildTileLayer = function(cfg) {
		var id  = (cfg.osmStyle && OSM_TILES[cfg.osmStyle]) ? cfg.osmStyle : 'standard';
		var def = OSM_TILES[id];
		var url = def.url;

		if (def.provider) {
			var key = cfg.keys[def.provider];
			if (!key) {
				def = OSM_TILES.standard;
				url = def.url;
			} else {
				url = url.replace('{KEY}', encodeURIComponent(key));
			}
		}

		return L.tileLayer(url, def.opts);
	};

	var initLeaflet = function($mapWrapper, $mapCanvas, cfg) {
		configureLeafletIcons();

		// Leaflet needs the container sized before init.
		$mapCanvas.css('height', cfg.height);

		var center = cfg.locations.length ? calculateCenter(cfg.locations) : { lat: -34, lng: 150 };

		var map = L.map($mapCanvas.get(0), {
			scrollWheelZoom: !cfg.disableScroll
		}).setView([center.lat, center.lng], 13);

		buildTileLayer(cfg).addTo(map);

		var bounds = [];
		cfg.locations.forEach(function(location) {
			var latlng = [location.coordinates.lat, location.coordinates.lng];
			var marker = L.marker(latlng).addTo(map);
			bounds.push(latlng);

			if (locationHasContent(location)) {
				marker.bindPopup(infoTemplate({ location: location }));
			}
		});

		if (bounds.length) {
			map.fitBounds(bounds, { maxZoom: maxZoom });
		}

		// Container height was set after potential layout; make sure tiles fill it.
		setTimeout(function() { map.invalidateSize(); }, 0);

		$mapCanvas.data('map', map);
	};

	// Run init once the engine's library global is available. The library is
	// enqueued per-render in PHP (enqueue_map_engine), so it may not exist yet
	// at DOM ready when loaded async.
	var whenEngineReady = function(engine, cb) {
		var ready = function() {
			return engine === 'google'
				? (typeof google !== 'undefined' && google.maps && google.maps.Map)
				: (typeof L !== 'undefined' && L.map);
		};
		if (ready()) {
			cb();
			return;
		}
		var tries = 0;
		var poll = setInterval(function() {
			if (ready()) {
				clearInterval(poll);
				cb();
			} else if (++tries > 200) { // ~20s ceiling, then give up
				clearInterval(poll);
			}
		}, 100);
	};

	var init = function($mapWrapper) {
		var $mapCanvas = $mapWrapper.find('.fw-map-canvas'),
			engine = ($mapWrapper.data('map-engine') === 'google') ? 'google' : 'osm',
			cfg = readConfig($mapWrapper, $mapCanvas);

		whenEngineReady(engine, function() {
			if (engine === 'google') {
				initGoogle($mapWrapper, $mapCanvas, cfg);
			} else {
				initLeaflet($mapWrapper, $mapCanvas, cfg);
			}
		});
	};

	// Lazy-init each map only when it scrolls into view, to avoid creating maps
	// (and the associated tile/API cost) for maps far below the fold.
	var observeMap = function(el) {
		var $wrapper = $(el);

		if ($wrapper.hasClass('fw-map-initialized')) {
			return;
		}

		var run = function() {
			if ($wrapper.hasClass('fw-map-initialized')) {
				return;
			}
			$wrapper.addClass('fw-map-initialized');
			init($wrapper);
		};

		if (typeof IntersectionObserver === 'undefined') {
			run();
			return;
		}

		var observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					observer.unobserve(entry.target);
					run();
				}
			});
		}, { rootMargin: '200px' });

		observer.observe(el);
	};

	$(document).ready(function() {
		$('.map').each(function() {
			// Only treat as a map shortcode wrapper if it contains a canvas.
			if ($(this).find('.fw-map-canvas').length) {
				observeMap(this);
			}
		});
	});

}(jQuery, _));
