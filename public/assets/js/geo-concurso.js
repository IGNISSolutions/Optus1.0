(function (window, $) {
    'use strict';

    var map = null;
    var marker = null;
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

    function asNumber(value) {
        var parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function getEditEntity() {
        return window.E && window.E.Entity ? window.E.Entity : null;
    }

    function getInvitationEntity() {
        return window.E && !window.E.Entity ? window.E : null;
    }

    function setObservable(entity, key, value) {
        if (entity && typeof entity[key] === 'function') {
            entity[key](value);
        }
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

    function resolveCountry(entity, countryCode, countryName) {
        if (!entity || typeof entity.Countries !== 'function') {
            return {
                code: String(countryCode || '').toUpperCase(),
                name: countryName || ''
            };
        }

        var codeNorm = normalizeText(countryCode).toUpperCase();
        var nameNorm = normalizeText(countryName);
        var countries = entity.Countries() || [];

        var byCode = countries.find(function (country) {
            return normalizeText(country.code).toUpperCase() === codeNorm;
        });
        if (byCode) {
            return {
                code: String(byCode.code || '').toUpperCase(),
                name: String(byCode.text || countryName || '')
            };
        }

        var byName = countries.find(function (country) {
            var textNorm = normalizeText(country.text);
            return textNorm === nameNorm || textNorm.indexOf(nameNorm) !== -1 || nameNorm.indexOf(textNorm) !== -1;
        });
        if (byName) {
            return {
                code: String(byName.code || countryCode || '').toUpperCase(),
                name: String(byName.text || countryName || '')
            };
        }

        return {
            code: String(countryCode || '').toUpperCase(),
            name: countryName || ''
        };
    }

    function updateEditEntityFromReverseGeocode(lat, lng) {
        var entity = getEditEntity();
        if (!entity) {
            return;
        }

        var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat='
            + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng);

        $.getJSON(url).done(function (data) {
            var address = data && data.address ? data.address : {};
            var country = address.country || '';
            var countryCode = address.country_code ? String(address.country_code).toUpperCase() : '';
            var province = address.state || address.region || '';
            var locality = normalizeLocality(address);
            var zipCode = address.postcode || '';
            var streetAddress = buildAddressLine(address);
            var resolvedCountry = resolveCountry(entity, countryCode, country);

            setObservable(entity, 'CountrySelected', resolvedCountry.code || countryCode);
            setObservable(entity, 'Pais', resolvedCountry.name || country);
            setObservable(entity, 'Provincia', province);
            setObservable(entity, 'Localidad', locality);
            setObservable(entity, 'Cp', zipCode);

            var currentAddress = getObservable(entity, 'Direccion');
            var manOnTheMap = !!getObservable(entity, 'ManOnTheMap');
            if (streetAddress && (!currentAddress || manOnTheMap)) {
                setObservable(entity, 'Direccion', streetAddress);
            }

            setObservable(entity, 'Latitud', String(lat));
            setObservable(entity, 'Longitud', String(lng));
        });
    }

    function setMarkerPosition(lat, lng, syncEntity) {
        if (!map || !marker) {
            return;
        }

        marker.setLngLat([lng, lat]);
        map.easeTo({ center: [lng, lat], duration: 500 });

        if (syncEntity) {
            updateEditEntityFromReverseGeocode(lat, lng);
        }
    }

    function getEditAddressQuery() {
        var entity = getEditEntity();
        if (!entity) {
            return '';
        }

        var country = getObservable(entity, 'Pais') || '';
        var province = getObservable(entity, 'Provincia') || '';
        var locality = getObservable(entity, 'Localidad') || '';
        var address = getObservable(entity, 'Direccion') || '';
        return [address, locality, province, country].filter(Boolean).join(', ');
    }

    function resolveCoords(preferEditAddress, callback) {
        var editEntity = getEditEntity();
        var sourceEntity = editEntity || getInvitationEntity();
        var lat = asNumber(getObservable(sourceEntity, 'Latitud'));
        var lng = asNumber(getObservable(sourceEntity, 'Longitud'));

        if (lat !== null && lng !== null) {
            callback(lat, lng);
            return;
        }

        if (preferEditAddress) {
            var query = getEditAddressQuery();
            if (query) {
                $.getJSON('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(query))
                    .done(function (result) {
                        if (Array.isArray(result) && result.length > 0) {
                            var geocodedLat = asNumber(result[0].lat);
                            var geocodedLng = asNumber(result[0].lon);
                            if (geocodedLat !== null && geocodedLng !== null) {
                                callback(geocodedLat, geocodedLng);
                                return;
                            }
                        }
                        resolveBrowserCoords(callback);
                    })
                    .fail(function () {
                        resolveBrowserCoords(callback);
                    });
                return;
            }
        }

        resolveBrowserCoords(callback);
    }

    function resolveBrowserCoords(callback) {
        if (!navigator.geolocation) {
            callback(defaultCenter.lat, defaultCenter.lng);
            return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {
            callback(position.coords.latitude, position.coords.longitude);
        }, function () {
            callback(defaultCenter.lat, defaultCenter.lng);
        });
    }

    function createMap(containerId, lat, lng, draggable) {
        var hasToken = !!window.MapboxToken;
        var fallbackApplied = false;

        if (window.MapboxToken) {
            window.mapboxgl.accessToken = window.MapboxToken;
        }

        map = new window.mapboxgl.Map({
            container: containerId,
            style: hasToken ? 'mapbox://styles/mapbox/streets-v12' : buildRasterStyle(),
            center: [lng, lat],
            zoom: 13
        });

        map.addControl(new window.mapboxgl.NavigationControl(), 'top-right');
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
            }
        });

        marker = new window.mapboxgl.Marker({ draggable: draggable })
            .setLngLat([lng, lat])
            .addTo(map);
    }

    function initMapConcurso() {
        var canvas = document.getElementById('map-canvas-1');
        if (!canvas || typeof window.mapboxgl === 'undefined') {
            return;
        }

        if (map) {
            map.resize();
            return;
        }

        resolveCoords(true, function (lat, lng) {
            createMap('map-canvas-1', lat, lng, true);

            map.on('click', function (event) {
                var entity = getEditEntity();
                if (entity) {
                    setObservable(entity, 'ManOnTheMap', true);
                }
                setMarkerPosition(event.lngLat.lat, event.lngLat.lng, true);
            });

            marker.on('dragend', function () {
                var entity = getEditEntity();
                if (entity) {
                    setObservable(entity, 'ManOnTheMap', true);
                }
                var position = marker.getLngLat();
                setMarkerPosition(position.lat, position.lng, true);
            });

            var entity = getEditEntity();
            if (entity && (getObservable(entity, 'Latitud') || getObservable(entity, 'Longitud'))) {
                setObservable(entity, 'Latitud', String(lat));
                setObservable(entity, 'Longitud', String(lng));
            } else {
                updateEditEntityFromReverseGeocode(lat, lng);
            }
        });
    }

    function initMapConcursoInvitacion() {
        var canvas = document.getElementById('map-canvas-1');
        if (!canvas || typeof window.mapboxgl === 'undefined') {
            return;
        }

        if (map) {
            map.resize();
            return;
        }

        resolveCoords(false, function (lat, lng) {
            createMap('map-canvas-1', lat, lng, false);
        });
    }

    function setAddress() {
        var entity = getEditEntity();
        if (!entity) {
            return;
        }

        var query = getEditAddressQuery();
        if (!query) {
            return;
        }

        $.getJSON('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(query))
            .done(function (result) {
                if (!Array.isArray(result) || result.length === 0) {
                    return;
                }

                var lat = asNumber(result[0].lat);
                var lng = asNumber(result[0].lon);
                if (lat === null || lng === null) {
                    return;
                }

                setObservable(entity, 'Latitud', String(lat));
                setObservable(entity, 'Longitud', String(lng));
                if (map && marker) {
                    setMarkerPosition(lat, lng, false);
                }
            });
    }

    window.initMapConcurso = initMapConcurso;
    window.initMapConcursoInvitacion = initMapConcursoInvitacion;
    window.setAddress = setAddress;
})(window, jQuery);