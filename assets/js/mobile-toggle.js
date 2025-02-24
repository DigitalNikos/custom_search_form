(function($, window, document, undefined) {
    'use strict';
  
    // Function to adjust layout based on window width.
    function adjustLayout() {
      if ($(window).width() < 768) {
        console.log("Mobile view activated.");
        // On mobile, hide inline form and map; show only the property list.
        $('.inline-search-form-container').hide();
        $('#map-container').hide();
        $('.property-results-list').show();
        $('#mobile-view-btn').html('<i class="fa-solid fa-map"></i> Map View');
        $('.mobile-controls').show();
      } else {
        console.log("Desktop view activated.");
        // On desktop, show all elements.
        $('.inline-search-form-container').show();
        $('#map-container').show();
        $('#property-results-list').show();
        $('.mobile-controls').hide();
      }
    }
  
    $(document).ready(function() {
      // Initial layout adjustment.
      adjustLayout();
  
      // Re-adjust layout when window is resized.
      $(window).resize(function() {
        adjustLayout();
      });
  
      // Cache selectors for efficiency.
      var $filterBtn    = $('#mobile-filter-btn'),
          $viewBtn      = $('#mobile-view-btn').html('<i class="fa-solid fa-map"></i> Map View'),
          $inlineForm   = $('.inline-search-form-container'),
          $propertyList = $('.property-results-list'),
          $mapContainer = $('#map-container');
  
      // Log to confirm that the elements exist.
      console.log("Property list count:", $propertyList.length);
      console.log("Map container count:", $mapContainer.length);
      console.log("Inline form container count:", $inlineForm.length);
  
      // Toggle the inline form when the "Filter" button is clicked.
      $filterBtn.on('click', function() {
        console.log("Filter button clicked.");
        $inlineForm.slideToggle(function() {
          console.log("Inline form is now:", $inlineForm.is(':visible') ? "visible" : "hidden");
        });
      });
  
      // Toggle between property list and map when the "View" button is clicked.
      $viewBtn.on('click', function() {
        console.log("View button clicked.");
        if ($propertyList.is(':visible')) {
          console.log("Switching to map view.");
          $propertyList.hide();
          $mapContainer.show();
          $viewBtn.html('<i class="fa-solid fa-list"></i> List View');
          // Optional: update map markers if needed.
          if (window.MRFS_Map && typeof MRFS_Map.updateMarkers === 'function') {
            MRFS_Map.updateMarkers();
          }
        } else {
          console.log("Switching to list view.");
          $mapContainer.hide();
          $propertyList.show();
          $viewBtn.html('<i class="fa-solid fa-map"></i> Map View');
        }
      });
    });
  })(jQuery, window, document);
  