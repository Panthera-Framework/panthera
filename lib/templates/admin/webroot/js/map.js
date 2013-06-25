    var geocoder = undefined;
    var currentBounds = '';
    var map = '';

    function createMap(id, mapOptions)
    {
        map = new google.maps.Map(document.getElementById(id),mapOptions);
    }

    function getLocation(location)
    {
        if (!geocoder)
            geocoder = new google.maps.Geocoder();

        // get places localisations
        geocoder.geocode( {
                    'address': location
                }, function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {
                      var searchLoc = results[0].geometry.location;
                      var lat = results[0].geometry.location.lat();
                      var lng = results[0].geometry.location.lng();
                      var latlng = new google.maps.LatLng(lat, lng);
                      var bounds = results[0].geometry.bounds;
                      currentBounds = bounds;
                      map.fitBounds(bounds);
                }
        });
    }
