(function($, window, document, undefined) {
    'use strict';

    // Helper function that updates the given min and max selects.
    // It rebuilds each select so that:
    // - The max select only includes options with numeric value >= selected min.
    // - The min select only includes options with numeric value <= selected max.
    function updateSelects($minSelect, $maxSelect, minArr, maxArr, labelMin, labelMax) {
        // Store current values.
        var currentMin = $minSelect.val();
        var currentMax = $maxSelect.val();
        var selectedMin = parseInt(currentMin.replace(/\D/g, ''), 10) || 0;
        var selectedMax = parseInt(currentMax.replace(/\D/g, ''), 10) || Infinity;
    
        // Rebuild the max select: only include options with numeric value >= selectedMin.
        $maxSelect.empty().append('<option value="">' + labelMax + '</option>');
        $.each(maxArr, function(i, val) {
            var numVal = parseInt(val.replace(/\D/g, ''), 10) || 0;
            if (numVal >= selectedMin) {
                $maxSelect.append('<option value="' + val + '">' + val + '</option>');
            }
        });
        // If current max is no longer valid, reset it.
        if ($maxSelect.find('option[value="' + currentMax + '"]').length === 0) {
            $maxSelect.val('');
        } else {
            $maxSelect.val(currentMax);
        }
    
        // Rebuild the min select: only include options with numeric value <= selectedMax.
        $minSelect.empty().append('<option value="">' + labelMin + '</option>');
        $.each(minArr, function(i, val) {
            var numVal = parseInt(val.replace(/\D/g, ''), 10) || 0;
            if (numVal <= selectedMax) {
                $minSelect.append('<option value="' + val + '">' + val + '</option>');
            }
        });
        // If current min is no longer valid, reset it.
        if ($minSelect.find('option[value="' + currentMin + '"]').length === 0) {
            $minSelect.val('');
        } else {
            $minSelect.val(currentMin);
        }
    }

    var MRFS_InlineSearch = {

        init: function() {
            console.log("✅ MRFS_InlineSearch: Module initialized.");
            this.populateInlineCountyDropdown();
            this.bindInlineCountyChange();
            this.bindInlineFilterEvents();
            this.bindDealTypeChange();
            this.bindPriceAndSqmChanges();
            this.updateInlinePriceDropdowns(); // Populate price dropdowns on init.
            this.updateInlineSqmDropdowns(); 
            this.setPreselectedValues();
        },

        /**
         * New method: Update inline price dropdowns based on the current deal type.
         */
        updateInlinePriceDropdowns: function() {
            var currentDeal = $('#deal_type_input_inline_hidden').val() || 'rent';
            var $priceMin = $('#inline_price_min');
            var $priceMax = $('#inline_price_max');
            $priceMin.empty().append('<option value="">' + mySearchData.priceFrom + '</option>');
            $priceMax.empty().append('<option value="">' + mySearchData.priceTo + '</option>');
            if (currentDeal === 'buy') {
                $.each(mySearchData.buy_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                $.each(mySearchData.buy_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            } else {
                $.each(mySearchData.rent_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                $.each(mySearchData.rent_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            }
            console.log("Inline price dropdowns updated for deal type:", currentDeal);
        },

        /**
         * Populate the inline county dropdown via AJAX.
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
                        // Set preselected county from form's data attribute and trigger change.
                        var preselectedCounty = $('.inline-search-form').data('selected-county');
                        if (preselectedCounty) {
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
         * Update the inline city dropdown when a county is selected.
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
                        // Preselect city if provided
                        var preselectedCity = $('.inline-search-form').data('selected-city');
                        if (preselectedCity) {
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

        /**
         * Bind change event on inline county dropdown.
         */
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
         * Bind general change events on inline form fields to trigger filtering.
         */
        bindInlineFilterEvents: function() {
            $('.inline-search-form').on('change.MRFS', 'select, input', function() {
                MRFS_InlineSearch.filterProperties();
            });
        },

        /**
         * Bind change event on inline deal type dropdown to update the hidden field.
         */
        bindDealTypeChange: function() {
            $('#inline_deal_type').on('change.MRFS', function() {
                var selectedDeal = $(this).val();
                console.log("Inline deal type selected:", selectedDeal);
                $('#deal_type_input_inline_hidden').val(selectedDeal);
                console.log("Inline deal type updated to:", selectedDeal);
                // Update price dropdowns to reflect new deal type.
                MRFS_InlineSearch.updateInlinePriceDropdowns();
            });
        },

        /**
         * Bind change events on inline price and sqm selects to update valid options.
         */
        bindPriceAndSqmChanges: function() {
            $('#inline_price_min, #inline_price_max').on('change.MRFS', function() {
                var currentDeal = $('#deal_type_input_inline_hidden').val();
                if (currentDeal === 'buy') {
                    updateSelects(
                        $('#inline_price_min'),
                        $('#inline_price_max'),
                        mySearchData.buy_prices_min,
                        mySearchData.buy_prices_max,
                        mySearchData.priceFrom,
                        mySearchData.priceTo
                    );
                } else {
                    updateSelects(
                        $('#inline_price_min'),
                        $('#inline_price_max'),
                        mySearchData.rent_prices_min,
                        mySearchData.rent_prices_max,
                        mySearchData.priceFrom,
                        mySearchData.priceTo
                    );
                }
            });
            $('#inline_sqm_min, #inline_sqm_max').on('change.MRFS', function() {
                updateSelects(
                    $('#inline_sqm_min'),
                    $('#inline_sqm_max'),
                    mySearchData.sqm_min,
                    mySearchData.sqm_max,
                    'τ.μ. Από',
                    'τ.μ. Έως'
                );
            });
        },

        updateInlineSqmDropdowns: function() {
            var $sqmMin = $('#inline_sqm_min'),
                $sqmMax = $('#inline_sqm_max');
            $sqmMin.empty().append('<option value="">' + 'τ.μ. Από' + '</option>');
            $sqmMax.empty().append('<option value="">' + 'τ.μ. Έως' + '</option>');
            // Populate SQM min dropdown
            $.each(mySearchData.sqm_min, function(i, val) {
                 $sqmMin.append('<option value="' + val + '">' + val + '</option>');
            });
            // Populate SQM max dropdown
            $.each(mySearchData.sqm_max, function(i, val) {
                 $sqmMax.append('<option value="' + val + '">' + val + '</option>');
            });
            console.log("Inline SQM dropdowns updated.");
        },

        /**
         * Set preselected values for inline price and sqm selects using form data attributes.
         */
        setPreselectedValues: function() {
            var $form = $('.inline-search-form');
            var preselectedPriceMin = $form.data('selected-price-min');
            var preselectedPriceMax = $form.data('selected-price-max');
            var preselectedSqmMin   = $form.data('selected-sqm-min');
            var preselectedSqmMax   = $form.data('selected-sqm-max');
            console.log("Preselected values:", preselectedPriceMin, preselectedPriceMax, preselectedSqmMin, preselectedSqmMax);
            if (preselectedPriceMin) {
                console.log("Setting preselected price min:", preselectedPriceMin);
                $('#inline_price_min').val(preselectedPriceMin);
            }
            if (preselectedPriceMax) {
                $('#inline_price_max').val(preselectedPriceMax);
            }
            if (preselectedSqmMin) {
                $('#inline_sqm_min').val(preselectedSqmMin);
            }
            if (preselectedSqmMax) {
                $('#inline_sqm_max').val(preselectedSqmMax);
            }

             // Trigger updateSelects to correctly filter options
            var currentDeal = $('#deal_type_input_inline_hidden').val() || 'rent';

            if (currentDeal === 'buy') {
                updateSelects(
                    $('#inline_price_min'),
                    $('#inline_price_max'),
                    mySearchData.buy_prices_min,
                    mySearchData.buy_prices_max,
                    mySearchData.priceFrom,
                    mySearchData.priceTo
                );
            } else {
                updateSelects(
                    $('#inline_price_min'),
                    $('#inline_price_max'),
                    mySearchData.rent_prices_min,
                    mySearchData.rent_prices_max,
                    mySearchData.priceFrom,
                    mySearchData.priceTo
                );
            }

            // Ensure SQM dropdowns also update immediately
            updateSelects(
                $('#inline_sqm_min'),
                $('#inline_sqm_max'),
                mySearchData.sqm_min,
                mySearchData.sqm_max,
                'τ.μ. Από',
                'τ.μ. Έως'
            );

            MRFS_InlineSearch.bindPriceAndSqmChanges();
        },

        /**
         * Sends the inline form data via AJAX to filter properties.
         */
        filterProperties: function() {
            var formData = $('.inline-search-form').serialize();
            console.log("Inline filter data:", formData);
            formData += '&action=filter_properties';
            formData += '&nonce=' + mySearchData.nonce;
            $.ajax({
                url: mySearchData.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log("AJAX response (inline filter):", response);
                    if (response.success) {
                        var properties = response.data;
                        console.log("Filtered properties:", properties);
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
         * @param {Array} properties Array of property objects.
         * @returns {string} HTML output.
         */
        renderProperties: function(properties) {
            console.log("Rendering properties:", properties);
            var output = '';
            if (Array.isArray(properties) && properties.length > 0) {
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
        }
    };

    $(document).ready(function(){
        MRFS_InlineSearch.init();
    });

    window.MRFS_InlineSearch = MRFS_InlineSearch;
})(jQuery, window, document);
