(function (window, $) {
    'use strict';

    var map = null;
    var markerMan = null;
    var defaultCenter = { lat: -31.3987552, lng: -64.1868587 };

    function buildRasterStyle() {
        return {
            version: 8,
            sources: {
                osm: {
                    type: 'raster',
                    tiles: [
                        'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
                        'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
                    ],
                    tileSize: 256,
                    attribution: '© OpenStreetMap contributors'
                }
            },
            layers: [
                {
                    id: 'osm-layer',
                    type: 'raster',
                    source: 'osm'
                }
            ]
        };
    }

    function normalizeText(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function resolveCountry(entity, countryCode, countryName) {
        if (!entity || typeof entity.Countries !== 'function') {
            return {
                id: '',
                code: String(countryCode || '').toUpperCase(),
                name: countryName || ''
            };
        }

        var codeNorm = normalizeText(countryCode).toUpperCase();
        var nameNorm = normalizeText(countryName);
        var countries = entity.Countries() || [];

        var byCode = countries.find(function (c) {
            return normalizeText(c.code).toUpperCase() === codeNorm;
        });
        if (byCode) {
            return {
                id: String(byCode.id || ''),
                code: String(byCode.code || '').toUpperCase(),
                name: String(byCode.text || countryName || '')
            };
        }

        var byName = countries.find(function (c) {
            var textNorm = normalizeText(c.text);
            return textNorm === nameNorm || textNorm.indexOf(nameNorm) !== -1 || nameNorm.indexOf(textNorm) !== -1;
        });
        if (byName) {
            return {
                id: String(byName.id || ''),
                code: String(byName.code || countryCode || '').toUpperCase(),
                name: String(byName.text || countryName || '')
            };
        }

        return {
            id: '',
            code: String(countryCode || '').toUpperCase(),
            name: countryName || ''
        };
    }

    function asNumber(value) {
        var n = parseFloat(value);
        return Number.isFinite(n) ? n : null;
    }

    function getVmEntity() {
        if (window.E && window.E.Entity) {
            return window.E.Entity;
        }
        return window.E || null;
    }

    function setObservable(entity, key, value) {
        if (!entity || typeof entity[key] !== 'function') {
            return;
        }
        entity[key](value);
    }

    function getObservable(entity, key) {
        if (!entity || typeof entity[key] !== 'function') {
            return '';
        }
        return entity[key]();
    }

    function buildAddressLine(address) {
        var road = address.road || address.pedestrian || '';
        var houseNumber = address.house_number || '';
        return (road + ' ' + houseNumber).trim();
    }

    function normalizeLocality(address) {
        return address.city || address.town || address.village || address.municipality || '';
    }

    function syncCountrySelectUi(countryCode) {
        var code = String(countryCode || '').toUpperCase();
        if (!code) {
            return;
        }

        var $country = $('#solped-country-select');
        if ($country.length === 0) {
            return;
        }

        // Ensure visual sync even when KO/select2 update order differs.
        setTimeout(function () {
            if (String($country.val() || '').toUpperCase() !== code) {
                $country.val(code).trigger('change').trigger('change.select2');
            }
        }, 0);
    }

    function updateEntityFromReverseGeocode(lat, lng) {
        var entity = getVmEntity();
        if (!entity) {
            return;
        }

        var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat=' +
            encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);

        $.getJSON(url)
            .done(function (data) {
                var address = (data && data.address) ? data.address : {};
                var country = address.country || '';
                var countryCode = address.country_code ? String(address.country_code).toUpperCase() : '';
                var province = address.state || address.region || '';
                var locality = normalizeLocality(address);
                var zipCode = address.postcode || '';
                var streetAddress = buildAddressLine(address);

                var resolvedCountry = resolveCountry(entity, countryCode, country);

                setObservable(entity, 'Pais', resolvedCountry.name || country);
                setObservable(entity, 'CountrySelectedId', resolvedCountry.id || '');
                setObservable(entity, 'CountrySelected', resolvedCountry.code || countryCode);
                setObservable(entity, 'Provincia', province);
                setObservable(entity, 'Localidad', locality);
                setObservable(entity, 'Ciudad', locality);
                setObservable(entity, 'Cp', zipCode);

                var currentAddress = getObservable(entity, 'Direccion');
                var manOnTheMap = !!getObservable(entity, 'ManOnTheMap');
                if (streetAddress && (!currentAddress || manOnTheMap)) {
                    setObservable(entity, 'Direccion', streetAddress);
                }

                setObservable(entity, 'Latitud', String(lat));
                setObservable(entity, 'Longitud', String(lng));

                syncCountrySelectUi(resolvedCountry.code || countryCode);
            });
    }

    function setMarkerAndSync(lat, lng) {
        if (!map || !markerMan) {
            return;
        }

        markerMan.setLngLat([lng, lat]);
        map.easeTo({ center: [lng, lat], duration: 500 });
        updateEntityFromReverseGeocode(lat, lng);
    }

    function resolveInitialCoords(cb) {
        var entity = getVmEntity();
        var lat = asNumber(getObservable(entity, 'Latitud'));
        var lng = asNumber(getObservable(entity, 'Longitud'));

        if (lat !== null && lng !== null) {
            cb(lat, lng);
            return;
        }

        if (!navigator.geolocation) {
            cb(defaultCenter.lat, defaultCenter.lng);
            return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            cb(position.coords.latitude, position.coords.longitude);
        }, function () {
            cb(defaultCenter.lat, defaultCenter.lng);
        });
    }

    function geocodeAddressFromForm() {
        var entity = getVmEntity();
        if (!entity) {
            return;
        }

        var country = getObservable(entity, 'Pais') || '';
        var province = getObservable(entity, 'Provincia') || '';
        var locality = getObservable(entity, 'Localidad') || getObservable(entity, 'Ciudad') || '';
        var address = getObservable(entity, 'Direccion') || '';
        var query = [address, locality, province, country].filter(Boolean).join(', ');

        if (!query) {
            return;
        }

        var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(query);
        $.getJSON(url)
            .done(function (result) {
                if (!Array.isArray(result) || result.length === 0) {
                    return;
                }
                var lat = asNumber(result[0].lat);
                var lng = asNumber(result[0].lon);
                if (lat === null || lng === null) {
                    return;
                }
                setMarkerAndSync(lat, lng);
            });
    }

    function initMapsolped() {
        var canvas = document.getElementById('map-canvas-1');
        if (!canvas || typeof window.mapboxgl === 'undefined') {
            return;
        }

        if (map) {
            map.resize();
            return;
        }

        if (window.MapboxToken) {
            window.mapboxgl.accessToken = window.MapboxToken;
        }

        resolveInitialCoords(function (lat, lng) {
            var hasToken = !!window.MapboxToken;
            var fallbackApplied = false;
            var initialStyle = hasToken ? 'mapbox://styles/mapbox/streets-v12' : buildRasterStyle();

            map = new window.mapboxgl.Map({
                container: 'map-canvas-1',
                style: initialStyle,
                center: [lng, lat],
                zoom: 13
            });

            map.on('load', function () {
                map.resize();
            });

            map.on('style.load', function () {
                map.resize();
            });

            map.on('error', function (event) {
                var errMsg = '';
                if (event && event.error && event.error.message) {
                    errMsg = String(event.error.message).toLowerCase();
                }
                var unauthorized = errMsg.indexOf('unauthorized') !== -1 || errMsg.indexOf('access token') !== -1;

                if (!fallbackApplied && unauthorized) {
                    fallbackApplied = true;
                    map.setStyle(buildRasterStyle());
                    return;
                }

                // Non-auth map errors are ignored to avoid console noise.
            });

            map.addControl(new window.mapboxgl.NavigationControl(), 'top-right');

            markerMan = new window.mapboxgl.Marker({ draggable: true })
                .setLngLat([lng, lat])
                .addTo(map);

            map.on('click', function (event) {
                setMarkerAndSync(event.lngLat.lat, event.lngLat.lng);
            });

            markerMan.on('dragend', function () {
                var pos = markerMan.getLngLat();
                setMarkerAndSync(pos.lat, pos.lng);
            });

            updateEntityFromReverseGeocode(lat, lng);
        });
    }

    window.initMapsolped = initMapsolped;
    window.initMapEmpresa = initMapsolped;
    window.setAddress = geocodeAddressFromForm;
})(window, jQuery);
