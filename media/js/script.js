/**
 * @version     __DEPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
ModSellaciousUsersOnMap = function () {
	this.markers = [];
};

jQuery(function ($) {
	ModSellaciousUsersOnMap.prototype = {
		initialize: function (markers, zoom, location) {
			var $this = this;
			var mapOptions = {
				center: new google.maps.LatLng(location[0], location[1]),
				zoom: zoom,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			var map = new google.maps.Map(document.getElementById("dvMap"), mapOptions);
			var infoWindow = new google.maps.InfoWindow();
			var latLngBounds = new google.maps.LatLngBounds();
			for (i = 0; i < markers.length; i++) {
				var data = markers[i];

				if (data.lat.length > 0 && data.lng.length > 0) {
					var myLatLng = new google.maps.LatLng(data.lat, data.lng);
					$this.setMarker(myLatLng, map, latLngBounds, infoWindow, data);
				} else {
					$this.GetCoordinates(data, map, latLngBounds, infoWindow);
				}
			}

			/*google.maps.event.addListener(map, 'tilesloaded', function () {
				$this.adjustMarkers();
			});*/
		},

		/*adjustMarkers: function () {
			var $this = this;
			if ($this.markers.length) {
				$($this.markers).each(function (i, marker) {
					var u = marker.icon ? marker.icon.url : null;
					var im = $('img[src="'+u+'"]');
					if (u && im.length) {
						im.addClass('markup-pointer');
					}
				});
			}
		},*/

		GetCoordinates: function (data, map, latLngBounds, infoWindow) {
			var $this = this;
			var geocoder = new google.maps.Geocoder();
			var address = data.address;
			geocoder.geocode({'address': address}, function (results, status) {
				if (status === 'OK') {
					var myLatLng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());

					$this.setMarker(myLatLng, map, latLngBounds, infoWindow, data)
				} else {
					return null
				}
			});
		},

		setMarker: function (myLatLng, map, latLngBounds, infoWindow, data) {
			var $this = this;
			var icon = '';

			if (data.icon.length > 0 && data.icon.indexOf('?s-map-icon') !== -1) {
				icon = {
					url: data.icon, // url
					scaledSize: new google.maps.Size(50, 60), // scaled size
					origin: new google.maps.Point(-2, 0), // origin
					anchor: new google.maps.Point(25, 35),// anchor
					optimized: true
				};
			}else if (data.icon.length > 0 && data.icon.indexOf('?s-map-google-icon') !== -1) {
				icon = {
					url: data.icon, // url
					origin: new google.maps.Point(-2, -2), // origin
					anchor: new google.maps.Point(15, 5),// anchor
					optimized: true
				};
			}

			var marker = new google.maps.Marker({
				position: myLatLng,
				map: map,
				title: data.title,
				icon: icon
			});

			latLngBounds.extend(marker.position);

			//$this.markers.push(marker);

			google.maps.event.addListener(marker, "click", function (e) {
				infoWindow.setContent(data.description);
				infoWindow.open(map, marker);
			});
		}
	};
});

