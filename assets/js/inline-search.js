(function($, window, document, undefined) {
    'use strict';

    var MRFS_InlineSearch = {

        init: function() {
            console.log("✅ MRFS_InlineSearch: Module initialized.");
            this.populateInlineCountyDropdown();
            this.bindInlineFilterEvents();
            this.bindInlineCountyChange();
            this.setPreselectedValues();
        },

        /**
         * Populates the inline county dropdown via AJAX.
         */
        populateInlineCountyDropdown: function() {
            $.ajax({
                url: mySearchData.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_counties',
                    nonce: mySearchData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var counties = response.data;
                        var $county = $('#inline_county');
                        $county.empty().append('<option value="">' + 'Επιλέξτε Νομό' + '</option>');
                        $.each(counties, function(i, county) {
                            $county.append('<option value="' + county + '">' + county + '</option>');
                        });
                        console.log("Inline county dropdown populated:", counties);
                        
                        // Set the pre-selected county from the form's data attribute.
                        var preselectedCounty = $('.inline-search-form').data('selected-county');
                        if ( preselectedCounty ) {
                            $county.val(preselectedCounty).trigger('change');
                        }
                    } else {
                        console.error("Failed to load inline counties.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (inline get_counties):", status, error);
                }
            });
        },

        /**
         * Updates the inline city dropdown for the selected county via AJAX.
         */
        updateInlineCityDropdown: function(county) {
            console.log("Fetching inline cities for county:", county);
            $.ajax({
                url: mySearchData.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_cities',
                    county: county,
                    nonce: mySearchData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var cities = response.data;
                        var $city = $('#inline_city');
                        $city.empty().append('<option value="">' + 'Επιλέξτε Πόλη' + '</option>');
                        $.each(cities, function(i, city) {
                            $city.append('<option value="' + city + '">' + city + '</option>');
                        });
                        $('#inline_city-field-container').slideDown();
                        console.log("Inline city dropdown updated.");
                        
                        // Set preselected city if available.
                        var preselectedCity = $('.inline-search-form').data('selected-city');
                        if ( preselectedCity ) {
                            $city.val(preselectedCity);
                        }
                    } else {
                        console.error("No inline cities returned.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (inline get_cities):", status, error);
                }
            });
        },

        bindInlineCountyChange: function() {
            $('#inline_county').on('change.MRFS', function() {
                var county = $(this).val();
                if (county) {
                    MRFS_InlineSearch.updateInlineCityDropdown(county);
                } else {
                    $('#inline_city-field-container').slideUp();
                    $('#inline_city').empty().append('<option value="">' + 'Επιλέξτε Πόλη' + '</option>');
                }
            });
        },

        /**
         * Binds change events on the inline form to trigger property filtering.
         */
        bindInlineFilterEvents: function() {
            $('.inline-search-form').on('change.MRFS', 'select, input', function() {
                MRFS_InlineSearch.filterProperties();
            });
        },

        /**
         * Sends the inline form data via AJAX to filter properties and updates the property list.
         */
        filterProperties: function() {
            var formData = $('.inline-search-form').serialize();
            formData += '&action=filter_properties';
            formData += '&nonce=' + mySearchData.nonce;
            console.log("Inline filter data:", formData);

            $.ajax({
                url: mySearchData.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var properties = response.data;
                        var output = MRFS_InlineSearch.renderProperties(properties);
                        $('#property-results').html(output);
                    } else {
                        $('#property-results').html('<p>No properties found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (inline filter):", status, error);
                }
            });
        },

        /**
         * Renders HTML for the filtered properties.
         *
         * @param {Array} properties Array of property objects.
         * @returns {string} HTML string.
         */
        renderProperties: function(properties) {
            var output = '';
            if (Array.isArray(properties) && properties.length > 0) {
                output += '<div class="property-results-list">';
                $.each(properties, function(i, property) {
                    output += '<div class="property-item" data-link="' + property.permalink + '">';
                    output += '<h3 class="property-title"><a href="' + property.permalink + '">' + property.title + '</a></h3>';
                    output += '<p class="property-address">' + property.address + '</p>';
                    output += '<p class="property-meta">' + property.kind + ' | ' + property.price + ' | ' + property.sqm + ' τ.μ.</p>';
                    output += '</div>';
                });
                output += '</div>';
            } else {
                output = '<p>No properties found matching your criteria.</p>';
            }
            return output;
        },

        setPreselectedValues: function() {
            var $form = $('.inline-search-form');
            var preselectedPriceMin = $form.data('selected-price-min');
            var preselectedPriceMax = $form.data('selected-price-max');
            var preselectedSqmMin   = $form.data('selected-sqm-min');
            var preselectedSqmMax   = $form.data('selected-sqm-max');

            console.log("Preselected values:", preselectedPriceMin, preselectedPriceMax, preselectedSqmMin, preselectedSqmMax);

            if ( preselectedPriceMin ) {
                $('#inline_price_min').val(preselectedPriceMin);
            }
            if ( preselectedPriceMax ) {
                $('#inline_price_max').val(preselectedPriceMax);
            }
            if ( preselectedSqmMin ) {
                $('#inline_sqm_min').val(preselectedSqmMin);
            }
            if ( preselectedSqmMax ) {
                $('#inline_sqm_max').val(preselectedSqmMax);
            }
        }
    };

    $(document).ready(function(){
        MRFS_InlineSearch.init();
    });

    window.MRFS_InlineSearch = MRFS_InlineSearch;
})(jQuery, window, document);
