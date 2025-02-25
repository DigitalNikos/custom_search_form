<?php
/**
 * Template for the inline search form.
 *
 * This form appears on the search results page and is pre-filled using GET parameters.
 *
 * @package My_Custom_Search
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Retrieve GET parameters.
$selected_county    = isset( $_GET['county'] ) ? sanitize_text_field( $_GET['county'] ) : '';
$selected_deal      = isset( $_GET['deal_type'] ) ? sanitize_text_field( $_GET['deal_type'] ) : 'rent';
$selected_city      = isset( $_GET['city'] ) ? sanitize_text_field( $_GET['city'] ) : '';
$selected_property  = isset( $_GET['property_type'] ) ? sanitize_text_field( $_GET['property_type'] ) : '';
$selected_price_min = isset( $_GET['price_min'] ) ? sanitize_text_field( $_GET['price_min'] ) : '';
$selected_price_max = isset( $_GET['price_max'] ) ? sanitize_text_field( $_GET['price_max'] ) : '';
$selected_sqm_min   = isset( $_GET['sqm_min'] )   ? sanitize_text_field( $_GET['sqm_min'] )   : '';
$selected_sqm_max   = isset( $_GET['sqm_max'] )   ? sanitize_text_field( $_GET['sqm_max'] )   : '';
?>
   <div class="mobile-controls">
        <button id="mobile-filter-btn" class="mobile-btn">
            <i class="fa fa-sliders" aria-hidden="true"></i>
            Filter
        </button>
        <button id="mobile-view-btn" class="mobile-btn">
        </button>
    </div>
<div class="inline-search-form-container">
    
        <form method="GET" action="<?php echo esc_url( site_url( '/property-search-results/' ) ); ?>" 
              class="inline-search-form"
              data-selected-county="<?php echo esc_attr($selected_county); ?>"
              data-selected-city="<?php echo esc_attr($selected_city); ?>"
              data-selected-price-min="<?php echo esc_attr($selected_price_min); ?>"
              data-selected-price-max="<?php echo esc_attr($selected_price_max); ?>"
              data-selected-sqm-min="<?php echo esc_attr($selected_sqm_min); ?>"
              data-selected-sqm-max="<?php echo esc_attr($selected_sqm_max); ?>">
            
            <!-- Hidden field for deal type -->
            <input type="hidden" name="deal_type" value="<?php echo esc_attr( $selected_deal ); ?>" id="deal_type_input_inline_hidden" />

            <!-- County Dropdown -->
            <div class="inline-form-field">
                <label><i class="fa-solid fa-map-location-dot"></i></label>
                <select name="county" id="inline_county">
                    <option value=""><?php esc_html_e( 'Επιλέξτε Νομό', 'my-custom-search' ); ?></option>
                    <!-- Options will be populated dynamically via JS -->
                </select>
            </div>

            <!-- City Dropdown -->
            <div class="inline-form-field" id="inline_city-field-container" style="display:none;">
                <label><i class="fa-solid fa-city"></i></label>
                <select name="city" id="inline_city">
                    <option value=""><?php esc_html_e( 'Επιλέξτε Πόλη', 'my-custom-search' ); ?></option>
                    <!-- Options will be populated dynamically via JS -->
                </select>
            </div>

            <!-- Deal Type Dropdown -->
            <div class="inline-form-field">
                <label><i class="fa-solid fa-layer-group"></i></label>
                <select name="deal_type_display" id="inline_deal_type" class="deal-type-dropdown">
                    <option value="rent" <?php selected( $selected_deal, 'rent' ); ?>><?php esc_html_e( 'Ενοικίαση', 'my-custom-search' ); ?></option>
                    <option value="buy" <?php selected( $selected_deal, 'buy' ); ?>><?php esc_html_e( 'Πώληση', 'my-custom-search' ); ?></option>
                </select>
            </div>

            <!-- Property Type Dropdown -->
            <div class="inline-form-field">
                <label><i class="fa-solid fa-house"></i></label>
                <select name="property_type" id="inline_property_type">
                    <option value=""><?php esc_html_e( 'Είδος', 'my-custom-search' ); ?></option>
                    <option value="apartment" <?php selected( $selected_property, 'apartment' ); ?>><?php esc_html_e( 'Διαμέρισμα', 'my-custom-search' ); ?></option>
                    <option value="house" <?php selected( $selected_property, 'house' ); ?>><?php esc_html_e( 'Μονοκατοικία', 'my-custom-search' ); ?></option>
                    <option value="plot" <?php selected( $selected_property, 'plot' ); ?>><?php esc_html_e( 'Οικόπεδο', 'my-custom-search' ); ?></option>
                    <option value="land" <?php selected( $selected_property, 'land' ); ?>><?php esc_html_e( 'Χωράφι', 'my-custom-search' ); ?></option>
                    <option value="office" <?php selected( $selected_property, 'office' ); ?>><?php esc_html_e( 'Επαγγελματικός χώρος', 'my-custom-search' ); ?></option>
                    <option value="service_areas" <?php selected( $selected_property, 'service_areas' ); ?>><?php esc_html_e( 'Βοηθητικοί χώροι', 'my-custom-search' ); ?></option>
                </select>
            </div>

             <!-- Price Dropdowns -->
            <div class="inline-price-half-width">
                <div class="inline-form-field">
                    <label class="inline-form-icon"><i class="fa-solid fa-tag"></i></label>
                    <select name="price_min" id="inline-price_min">
                        <option value=""><?php esc_html_e( 'Τιμή από', 'my-custom-search' ); ?></option>
                    </select>
                </div>

                <div class="inline-form-field">
                    <label class="inline-form-icon"><i class="fa-solid fa-tag"></i></label>
                    <select name="price_max" id="inline-price_max">
                        <option value=""><?php esc_html_e( 'Τιμή έως', 'my-custom-search' ); ?></option>
                    </select>
                </div>
            </div>


            <!-- SQM Dropdowns -->
            <div class="inline-price-half-width">
                <div class="inline-form-field">
                    <label class="inline-form-icon"><i class="fa-solid fa-ruler-combined"></i></label>
                    <select name="sqm_min" id="inline_sqm_min">
                        <option value=""><?php esc_html_e( 'τ.μ. Από', 'my-custom-search' ); ?></option>
                    </select>
                </div>

                <div class="inline-form-field">
                    <label class="inline-form-icon"><i class="fa-solid fa-ruler-combined"></i></label>
                    <select name="sqm_max" id="inline_sqm_max">
                            <option value=""><?php esc_html_e( 'τ.μ. Έως', 'my-custom-search' ); ?></option>
                    </select>
                </div>
            </div>
        </form>
    
</div>
