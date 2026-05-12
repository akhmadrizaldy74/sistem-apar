(function () {
    const defaultCenter = [106.8456, -6.2088];

    const osmRasterStyle = {
        version: 8,
        sources: {
            'osm-raster': {
                type: 'raster',
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '&copy; OpenStreetMap contributors',
            },
        },
        layers: [
            {
                id: 'osm-raster',
                type: 'raster',
                source: 'osm-raster',
                minzoom: 0,
                maxzoom: 19,
            },
        ],
    };

    function createPinElement() {
        const wrapper = document.createElement('div');
        wrapper.style.width = '28px';
        wrapper.style.height = '34px';
        wrapper.style.cursor = 'grab';
        wrapper.style.position = 'relative';

        const pin = document.createElement('div');
        pin.style.width = '22px';
        pin.style.height = '22px';
        pin.style.background = '#ef4444';
        pin.style.borderRadius = '50% 50% 50% 0';
        pin.style.transform = 'rotate(-45deg)';
        pin.style.border = '3px solid white';
        pin.style.boxShadow = '0 2px 12px rgba(0,0,0,.35)';
        pin.style.position = 'absolute';
        pin.style.left = '3px';
        pin.style.top = '3px';

        wrapper.appendChild(pin);
        return wrapper;
    }

    function waitForMapLibre(callback, attempt) {
        const currentAttempt = attempt || 0;
        if (window.maplibregl && typeof window.maplibregl.Map === 'function') {
            callback();
            return;
        }

        if (currentAttempt < 50) {
            setTimeout(() => waitForMapLibre(callback, currentAttempt + 1), 100);
        }
    }

    function createMap(container, options) {
        const mapOptions = options || {};
        const lat = Number(mapOptions.lat) || defaultCenter[1];
        const lng = Number(mapOptions.lng) || defaultCenter[0];
        const zoom = Number(mapOptions.zoom) || 13;

        const map = new maplibregl.Map({
            container,
            style: mapOptions.style || osmRasterStyle,
            center: [lng, lat],
            zoom,
            attributionControl: true,
        });

        if (mapOptions.zoomControl !== false) {
            map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
        }

        if (mapOptions.scrollWheelZoom === false) {
            map.scrollZoom.disable();
        }

        return map;
    }

    function resizeMap(map) {
        if (!map) return;
        [100, 300, 600].forEach((delay) => {
            setTimeout(() => {
                if (map) map.resize();
            }, delay);
        });
    }

    function setCenter(map, lat, lng, zoom) {
        if (!map) return;
        map.jumpTo({
            center: [Number(lng), Number(lat)],
            zoom: Number(zoom) || map.getZoom(),
        });
    }

    function createMarker(map, lat, lng, options) {
        const markerOptions = options || {};
        const marker = new maplibregl.Marker({
            element: markerOptions.element || createPinElement(),
            draggable: !!markerOptions.draggable,
            anchor: 'bottom',
        })
            .setLngLat([Number(lng), Number(lat)])
            .addTo(map);

        if (typeof markerOptions.onDrag === 'function') {
            marker.on('drag', () => {
                const pos = marker.getLngLat();
                markerOptions.onDrag(pos.lat, pos.lng, marker);
            });
        }

        if (typeof markerOptions.onDragEnd === 'function') {
            marker.on('dragend', () => {
                const pos = marker.getLngLat();
                markerOptions.onDragEnd(pos.lat, pos.lng, marker);
            });
        }

        return marker;
    }

    function removeMarker(marker) {
        if (marker && typeof marker.remove === 'function') {
            marker.remove();
        }
    }

    window.AppMapLibre = {
        createMap,
        createMarker,
        removeMarker,
        resizeMap,
        setCenter,
        waitForMapLibre,
    };
})();
