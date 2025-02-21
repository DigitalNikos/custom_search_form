(function($, window, document, undefined) {
    'use strict';

    /**
     * Helper function that updates the given min and max selects.
     * It rebuilds each select so that:
     * - The max select only includes options with numeric value >= selected min.
     * - The min select only includes options with numeric value <= selected max.
     *
     * @param {jQuery Object} $minSelect - The jQuery object for the minimum select.
     * @param {jQuery Object} $maxSelect - The jQuery object for the maximum select.
     * @param {Array} minArr - Array of available min values (strings).
     * @param {Array} maxArr - Array of available max values (strings).
     * @param {string} labelMin - The default prompt for the min select.
     * @param {string} labelMax - The default prompt for the max select.
     */
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
    

    var MRFS_MainSearch = {

        init: function() {
            console.log("✅ MRFS_MainSearch: Module initialized.");
            // Populate county dropdown dynamically.
            this.populateCountyDropdown();
            this.bindToggleButtons();
            this.bindFormSubmit();
            this.bindCountyChange();
            this.bindPriceAndSqmChanges();
            var dealType = $('#deal_type_input').val() || 'rent';
            this.updatePriceDropdowns(dealType);
        },

        /**
         * Populates the county dropdown (main form) via AJAX.
         */
        populateCountyDropdown: function() {
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
                        var $county = $('#county');
                        $county.empty().append('<option value="">' + 'Επιλέξτε Νομό' + '</option>');
                        $.each(counties, function(i, county) {
                            $county.append('<option value="' + county + '">' + county + '</option>');
                        });
                        console.log("County dropdown populated:", counties);
                    } else {
                        console.error("Failed to load counties.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (get_counties):", status, error);
                }
            });
        },

        /**
         * Updates the city dropdown (main form) for the selected county via AJAX.
         */
        updateCityDropdown: function(county) {
            console.log("Fetching cities for county:", county);
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
                    console.log("Response from get_cities:", response);
                    if (response.success) {
                        var cities = response.data;
                        var $city = $('#city');
                        $city.empty().append('<option value="">' + 'Επιλέξτε Πόλη' + '</option>');
                        $.each(cities, function(i, city) {
                            $city.append('<option value="' + city + '">' + city + '</option>');
                        });
                        $('#city-field-container').slideDown();
                        console.log("City dropdown updated:", $city.html());
                    } else {
                        console.error("No cities returned.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error (get_cities):", status, error);
                }
            });
        },

        bindToggleButtons: function() {
            $('.search-form-toggle .toggle-btn').on('click.MRFS', function(e) {
                e.preventDefault();
                $('.search-form-toggle .toggle-btn').removeClass('active');
                $(this).addClass('active');
                var selectedType = $(this).data('type'); // "rent" or "buy"
                console.log("User selected deal type:", selectedType);
                $('#deal_type_input').val(selectedType);
                console.log("Send to updatePriceDropdowns:", selectedType);
                MRFS_MainSearch.updatePriceDropdowns(selectedType);
            });
        },

        updatePriceDropdowns: function(selectedType) {
            var $priceMin = $('#price_min'),
                $priceMax = $('#price_max');
            $priceMin.empty().append('<option value="">' + mySearchData.priceFrom + '</option>');
            $priceMax.empty().append('<option value="">' + mySearchData.priceTo + '</option>');
            if (selectedType === 'rent') {
                console.log("Rent prices selected.");
                console.log("min prices:", mySearchData.rent_prices_min);
                $.each(mySearchData.rent_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                console.log("max prices:", mySearchData.rent_prices_max);
                $.each(mySearchData.rent_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            } else if (selectedType === 'buy') {
                console.log("Buy prices selected.");
                console.log("min prices:", mySearchData.buy_prices_min);
                $.each(mySearchData.buy_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                console.log("max prices:", mySearchData.buy_prices_max);
                $.each(mySearchData.buy_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            }
        },

        bindFormSubmit: function() {
            $('.main-search-form').on('submit.MRFS', function(e) {
                // Remove preventDefault so the form submits normally.
                console.log("Main form submitted. Data being sent:", $(this).serialize());
            });
        },

        bindCountyChange: function() {
            $('#county').on('change.MRFS', function() {
                var county = $(this).val();
                if (county) {
                    MRFS_MainSearch.updateCityDropdown(county);
                } else {
                    $('#city-field-container').slideUp();
                    $('#city').empty().append('<option value="">' + 'Επιλέξτε Πόλη' + '</option>');
                }
            });
        },

        bindPriceAndSqmChanges: function() {
            // Bind changes for price selects on the main form.
            $('#price_min, #price_max').on('change.MRFS', function() {
                var currentDeal = $('#deal_type_input').val(); // "rent" or "buy"
                if (currentDeal === 'buy') {
                    updateSelects(
                        $('#price_min'),
                        $('#price_max'),
                        mySearchData.buy_prices_min,
                        mySearchData.buy_prices_max,
                        mySearchData.priceFrom,
                        mySearchData.priceTo
                    );
                } else {
                    updateSelects(
                        $('#price_min'),
                        $('#price_max'),
                        mySearchData.rent_prices_min,
                        mySearchData.rent_prices_max,
                        mySearchData.priceFrom,
                        mySearchData.priceTo
                    );
                }
            });
            
            // Bind changes for sqm selects (if the same arrays are used regardless of deal type).
            $('#sqm_min, #sqm_max').on('change.MRFS', function() {
                updateSelects(
                    $('#sqm_min'),
                    $('#sqm_max'),
                    mySearchData.sqm_min,
                    mySearchData.sqm_max,
                    'τ.μ. Από',
                    'τ.μ. Έως'
                );
            });
        }
        
    };

    $(document).ready(function(){
        MRFS_MainSearch.init();
    });

    window.MRFS_MainSearch = MRFS_MainSearch;
})(jQuery, window, document);
