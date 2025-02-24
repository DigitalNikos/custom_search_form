(function($, window, document, undefined) {
    'use strict';
  
    var MRFS_Map = {
      map: null,
      markers: [],
      mapInitialized: false,
      myProperties: [],
  
      initMyMap: function() {
        console.log("=============initMyMap===============");
        var mapDiv = document.getElementById('map-container');
        if (!mapDiv) {
          console.error('Map container not found.');
          return;
        }
    
        // Create the map only once.
        if (!this.mapInitialized) {
          this.map = new google.maps.Map(mapDiv, {
            center: { lat: 38.0, lng: 23.7 },
            zoom: 7,
            styles: [
              { featureType: 'poi', stylers: [{ visibility: 'off' }] },
              { featureType: 'transit', stylers: [{ visibility: 'off' }] }
            ]
          });
          this.mapInitialized = true;
        }
        console.log("myProperties before updateMarkers:", this.myProperties);
        this.updateMarkers();
      },
  
      updateMarkers: function() {
        console.log("==============updateMarkers===============");
        console.log("myProperties:", this.myProperties);
        var self = this;
        // Clear markers
        this.markers.forEach(function(marker) {
          marker.setMap(null);
        });
        this.markers = [];
    
        if (!Array.isArray(this.myProperties) || this.myProperties.length === 0) {
          console.log('No properties to display.');
          return;
        }
    
        var geocoder = new google.maps.Geocoder();
        var bounds = new google.maps.LatLngBounds();
        var pending = this.myProperties.length;
        var anyMarkerPlaced = false;
    
        this.myProperties.forEach(function(prop) {
          if (!prop.address) {
            console.warn('Property missing address:', prop);
            pending--;
            if (pending === 0) self.adjustMapBounds(bounds, anyMarkerPlaced);
            return;
          }
          geocoder.geocode({ address: prop.address }, function(results, status) {
            pending--;
            if (status === 'OK' && results[0]) {
              var location = results[0].geometry.location;
              anyMarkerPlaced = true;
    
              var marker = new google.maps.Marker({
                map: self.map,
                position: location,
                title: prop.kind + ' - ' + prop.price + '€',
                icon: {
                  path: google.maps.SymbolPath.CIRCLE,
                  scale: 10,
                  fillColor: '#228B22',
                  fillOpacity: 1,
                  strokeColor: '#ffffff',
                  strokeWeight: 2
                }
              });

              marker.propertyData = prop;
    
              marker.addListener('click', function() {
                window.open(prop.permalink, '_blank');
              });
    
              var infoWindow = new google.maps.InfoWindow({
                content: self.buildInfoContent(prop, "marker")
              });
              marker.addListener('mouseover', function() {
                infoWindow.open(self.map, marker);
              });
              marker.addListener('mouseout', function() {
                infoWindow.close();
              });
    
              self.markers.push(marker);
              bounds.extend(location);
            } else {
              console.warn('Geocoding failed for:', prop.address, status);
            }
    
            if (pending === 0) {
              self.adjustMapBounds(bounds, anyMarkerPlaced);
            }
          });
        });
      },
    
      adjustMapBounds: function(bounds, anyMarkerPlaced) {
        if (anyMarkerPlaced) {
          this.map.fitBounds(bounds);
        } else {
          this.map.setCenter({ lat: 38.0, lng: 23.7 });
          this.map.setZoom(7);
        }
      },
    
      // Read filter values from the inline form (use inline IDs)
      getInlineFormFilters: function() {
        return {
          deal_type: document.getElementById('deal_type_input_inline_hidden') ? document.getElementById('deal_type_input_inline_hidden').value : '',
          property_type: document.getElementById('inline_property_type') ? document.getElementById('inline_property_type').value : '',
          price_min: document.getElementById('inline_price_min') ? document.getElementById('inline_price_min').value : '',
          price_max: document.getElementById('inline_price_max') ? document.getElementById('inline_price_max').value : '',
          city: document.getElementById('inline_city') ? document.getElementById('inline_city').value : '',
          // Add additional fields if needed (e.g., county, sqm, etc.)
          county: document.getElementById('inline_county') ? document.getElementById('inline_county').value : ''
        };
      },
    
      updateMapWithFilteredProperties: function() {
        console.log("==============updateMapWithFilteredProperties===============");
        var filters = this.getInlineFormFilters();
        console.log("Inline form filters:", filters);
        var data = new URLSearchParams({
          action: 'filter_properties',
          deal_type: filters.deal_type,
          property_type: filters.property_type,
          price_min: filters.price_min,
          price_max: filters.price_max,
          city: filters.city,
          county: filters.county,
          nonce: mySearchData.nonce
        });
    
        var self = this;
        fetch(mySearchData.ajax_url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: data
        })
        .then(function(response) {
          return response.json();
        })
        .then(function(result) {
          if (result.success) {
            console.log('Received properties:', result.data);
            self.myProperties = result.data;
            console.log('Filtered properties:', self.myProperties);
            // Update markers on the existing map.
            self.updateMarkers();
          } else {
            console.error('No properties returned.');
          }
        })
        .catch(function(error) {
          console.error('Error fetching properties:', error);
        });
      },
    
      buildInfoContent: function(prop, context) {
        console.log("buildInfoContent (" + context + "):", prop);
        return `
          <div class="gm-info-window">
            <div class="image-container">
                ${prop.img_url ? `<img src="${prop.img_url}" alt="${prop.kind}">` : '<div class="no-image">No Image</div>'}
            </div>
            <div class="content">
                <div class="gm-kind-price">${prop.kind} - ${prop.price}€</div>
                <div class="address">
                  <label class="map-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></label>
                  <span>${prop.address}</span>
                </div>
                <div class="details">
                  <label class="map-icon"><i class="fa-solid fa-ruler-combined"></i></label>
                  <span class="sqm-value">${prop.sqm} m²</span>
                  <label class="map-icon"><i class="fa-solid fa-bed"></i></label>
                  <span class="bedrooms-value">${prop.bedrooms}</span>
                </div>
            </div>
          </div>
        `;
      },
    
      init: function() {
        document.addEventListener('DOMContentLoaded', function() {
          // Make each property card clickable.
          var propertyItems = document.querySelectorAll('.property-result-item');
          console.log("propertyItems:", propertyItems);
          propertyItems.forEach(function(item) {
            item.addEventListener('click', function() {
              var link = item.getAttribute('data-link');
              if (link) {
                window.open(link, '_blank');
              }
            });
          });
      
          // Initialize the map if Google Maps API is loaded.
          if (typeof google === 'object' && typeof google.maps === 'object') {
            MRFS_Map.initMyMap();
          }
      
          // Attach event listeners to the inline form to update map on changes.
          var inlineForm = document.querySelector('.inline-search-form');
          if (inlineForm) {
            inlineForm.addEventListener('change', function() {
              MRFS_Map.updateMapWithFilteredProperties();
            });
            inlineForm.addEventListener('submit', function(e) {
              e.preventDefault();
              MRFS_Map.updateMapWithFilteredProperties();
            });
      
            // *** NEW: Trigger map update on initial load ***
            // This ensures that the map is updated using the GET parameters prepopulated in the inline form.
            MRFS_Map.updateMapWithFilteredProperties();
          }
        });
      }
      
    };
    
    window.MRFS_Map = MRFS_Map;
    MRFS_Map.init();
    console.log("MRFS_Map:", MRFS_Map);
    window.initMyMap = MRFS_Map.initMyMap.bind(MRFS_Map);
    
  })(jQuery, window, document);
  