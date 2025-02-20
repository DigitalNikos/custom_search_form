(function($, window, document, undefined) {
    'use strict';

    var MRFS_MainSearch = {

        init: function() {
            console.log("✅ MRFS_MainSearch: Module initialized.");
            // Populate the county dropdown dynamically.
            this.populateCountyDropdown();
            this.bindToggleButtons();
            this.bindFormSubmit();
            this.bindCountyChange();
        },

        /**
         * Populates the county dropdown by fetching unique county values via AJAX.
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
         * Updates the city dropdown by fetching cities for the selected county via AJAX.
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
                MRFS_MainSearch.updatePriceDropdowns(selectedType);
            });
        },

        updatePriceDropdowns: function(selectedType) {
            var $priceMin = $('#price_min'),
                $priceMax = $('#price_max');
            $priceMin.empty().append('<option value="">' + mySearchData.priceFrom + '</option>');
            $priceMax.empty().append('<option value="">' + mySearchData.priceTo + '</option>');
            if (selectedType === 'rent') {
                $.each(mySearchData.rent_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                $.each(mySearchData.rent_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            } else if (selectedType === 'buy') {
                $.each(mySearchData.buy_prices_min, function(i, val) {
                    $priceMin.append('<option value="' + val + '">' + val + '</option>');
                });
                $.each(mySearchData.buy_prices_max, function(i, val) {
                    $priceMax.append('<option value="' + val + '">' + val + '</option>');
                });
            }
        },

        bindFormSubmit: function() {
            $('.main-search-form').on('submit.MRFS', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                console.log("Data being sent:", formData);
                formData += '&action=filter_properties';
                formData += '&nonce=' + mySearchData.nonce;
                $.ajax({
                    url: mySearchData.ajax_url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            console.log("Properties received:", response.data);
                        } else {
                            console.log("No properties returned.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            });
        },

        /**
         * Binds the change event on the county dropdown to update the city dropdown.
         */
        bindCountyChange: function() {
            $('#county').on('change.MRFS', function() {
                var county = $(this).val();
                if (county) {
                    MRFS_MainSearch.updateCityDropdown(county);
                } else {
                    $('#city-field-container').slideUp();
                    // Optionally, clear the city dropdown.
                    $('#city').empty().append('<option value="">' + 'Επιλέξτε Πόλη' + '</option>');
                }
            });
        }
    };

    $(document).ready(function(){
        MRFS_MainSearch.init();
    });

    window.MRFS_MainSearch = MRFS_MainSearch;
})(jQuery, window, document);
