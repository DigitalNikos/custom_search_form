<?php
// File: includes/class-search-form.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Form {

    /**
     * Renders the main search form.
     * This form submits via GET and redirects the user to the results page.
     */
    public static function render_main_form() {
        ob_start();
        ?>
        <div class="real-estate-search-form">
            <div class="search-form-toggle">
                <button type="button" class="toggle-btn active" data-type="rent"><?php esc_html_e( 'Ενοικίαση', 'my-custom-search' ); ?></button>
                <button type="button" class="toggle-btn" data-type="buy"><?php esc_html_e( 'Πώληση', 'my-custom-search' ); ?></button>
            </div>
            <form method="GET" action="<?php echo esc_url( site_url( '/property-search-results/' ) ); ?>" class="main-search-form">
                <input type="hidden" name="deal_type" value="rent" id="deal_type_input" />
                <div class="form-field">
                    <label><i class="fa-solid fa-map-location-dot"></i></label>
                    <select name="county" id="county">
                        <option value=""><?php esc_html_e( 'Επιλέξτε Νομό', 'my-custom-search' ); ?></option>
                        <option value="Ν. Δράμα"><?php esc_html_e( 'Ν. Δράμα', 'my-custom-search' ); ?></option>
                    </select>
                </div>
                <div id="city-field-container" style="display: none;">
                    <div class="form-field">
                        <label><i class="fa-solid fa-city"></i></label>
                        <select name="city" id="city">
                            <option value=""><?php esc_html_e( 'Επιλέξτε Πόλη', 'my-custom-search' ); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-field">
                    <label><i class="fa-solid fa-house"></i></label>
                    <select name="property_type" id="property_type">
                        <option value=""><?php esc_html_e( 'Είδος', 'my-custom-search' ); ?></option>
                        <option value="apartment"><?php esc_html_e( 'Διαμέρισμα', 'my-custom-search' ); ?></option>
                        <option value="house"><?php esc_html_e( 'Μονοκατοικία', 'my-custom-search' ); ?></option>
                        <option value="plot"><?php esc_html_e( 'Οικόπεδο', 'my-custom-search' ); ?></option>
                        <option value="land"><?php esc_html_e( 'Χωράφι', 'my-custom-search' ); ?></option>
                        <option value="office"><?php esc_html_e( 'Επαγγελματικός χώρος', 'my-custom-search' ); ?></option>
                        <option value="service_areas"><?php esc_html_e( 'Βοηθητικοί χώροι', 'my-custom-search' ); ?></option>
                    </select>
                </div>
                <div class="form-row">
                    <label class="form-icon"><i class="fa-solid fa-tag"></i></label>
                    <div class="form-field-group">
                        <div class="form-field half-width">
                            <select name="price_min" id="price_min">
                                <option value=""><?php esc_html_e( 'Τιμή από', 'my-custom-search' ); ?></option>
                                <?php foreach ( MRFS_RENT_PRICES_MIN as $sqm ) : ?>
                                    <option value="<?php echo esc_attr( $sqm ); ?>"><?php echo esc_html( $sqm ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-field half-width">
                        <select name="price_max" id="price_max">
                                <option value=""><?php esc_html_e( 'Τιμή έως', 'my-custom-search' ); ?></option>
                                <?php foreach ( MRFS_RENT_PRICES_MAX as $sqm ) : ?>
                                    <option value="<?php echo esc_attr( $sqm ); ?>"><?php echo esc_html( $sqm ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-icon"><i class="fa-solid fa-ruler-combined"></i></label>
                    <div class="form-field-group">
                        <div class="form-field half-width">
                            <select name="sqm_min" id="sqm_min">
                                <option value=""><?php esc_html_e( 'τ.μ. Από', 'my-custom-search' ); ?></option>
                                <?php
                                $sqm_min = apply_filters( 'my_custom_search_sqm_min', array( 50, 100, 150 ) );
                                foreach ( $sqm_min as $sqm ) {
                                    echo '<option value="' . esc_attr( $sqm ) . '">' . esc_html( $sqm ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-field half-width">
                            <select name="sqm_max" id="sqm_max">
                                <option value=""><?php esc_html_e( 'τ.μ. Έως', 'my-custom-search' ); ?></option>
                                <?php
                                $sqm_max = apply_filters( 'my_custom_search_sqm_max', array( 200, 250, 300 ) );
                                foreach ( $sqm_max as $sqm ) {
                                    echo '<option value="' . esc_attr( $sqm ) . '">' . esc_html( $sqm ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-field">
                    <button type="submit" class="search-btn"><?php esc_html_e( 'Αναζήτηση', 'my-custom-search' ); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders the inline search form.
     * This version replaces the toggle buttons with a dropdown for deal type.
     */
    public static function render_inline_form() {
        // Retrieve any existing GET values for pre-filling the form.
        $selected_county   = isset( $_GET['county'] ) ? sanitize_text_field( $_GET['county'] ) : '';
        $selected_deal     = isset( $_GET['deal_type'] ) ? sanitize_text_field( $_GET['deal_type'] ) : 'rent';
        $selected_city     = isset( $_GET['city'] ) ? sanitize_text_field( $_GET['city'] ) : '';
        $selected_property = isset( $_GET['property_type'] ) ? sanitize_text_field( $_GET['property_type'] ) : '';
        $selected_price_min = isset( $_GET['price_min'] ) ? sanitize_text_field( $_GET['price_min'] ) : '';
        $selected_price_max = isset( $_GET['price_max'] ) ? sanitize_text_field( $_GET['price_max'] ) : '';
        $selected_sqm_min   = isset( $_GET['sqm_min'] )   ? sanitize_text_field( $_GET['sqm_min'] )   : '';
        $selected_sqm_max   = isset( $_GET['sqm_max'] )   ? sanitize_text_field( $_GET['sqm_max'] )   : '';

        ob_start();
        ?>
        <div class="inline-search-form-container">
            <div class="inline-search-form-wrapper">
                <form method="GET" action="<?php echo esc_url( site_url( '/property-search-results/' ) ); ?>" class="inline-search-form">
                    <input type="hidden" name="deal_type" value="<?php echo esc_attr( $selected_deal ); ?>" id="deal_type_input_inline_hidden" />
                    <div class="inline-form-field">
                        <label><i class="fa-solid fa-map-location-dot"></i></label>
                        <select name="county" id="inline_county">
                            <option value=""><?php esc_html_e( 'Επιλέξτε Νομό', 'my-custom-search' ); ?></option>
                            <option value="Ν. Δράμα" <?php selected( $selected_county, 'Ν. Δράμα' ); ?>><?php esc_html_e( 'Ν. Δράμα', 'my-custom-search' ); ?></option>
                        </select>
                    </div>
                    <div class="inline-form-field">
                        <label><i class="fa-solid fa-city"></i></label>
                        <input type="text" name="city" id="inline_city" placeholder="<?php esc_attr_e( 'Πληκτρολογήστε Πόλη', 'my-custom-search' ); ?>" value="<?php echo esc_attr( $selected_city ); ?>">
                    </div>
                    <div class="inline-form-field">
                        <label><i class="fa-solid fa-layer-group"></i></label>
                        <select name="deal_type_display" id="inline_deal_type" class="deal-type-dropdown">
                            <option value="rent" <?php selected( $selected_deal, 'rent' ); ?>><?php esc_html_e( 'Ενοικίαση', 'my-custom-search' ); ?></option>
                            <option value="buy" <?php selected( $selected_deal, 'buy' ); ?>><?php esc_html_e( 'Πώληση', 'my-custom-search' ); ?></option>
                        </select>
                    </div>
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
                    <div class="inline-form-row">
                        <label class="inline-form-icon"><i class="fa-solid fa-tag"></i></label>
                        <div class="inline-form-field half-width">
                            <select name="price_min" id="inline_price_min">
                                <option value=""><?php esc_html_e( 'Τιμή από', 'my-custom-search' ); ?></option>
                                <?php
                                $prices_min = apply_filters( 'my_custom_search_rent_prices_min', array( 500, 1000, 1500 ) );
                                foreach ( $prices_min as $price ) {
                                    echo '<option value="' . esc_attr( $price ) . '" ' . selected( $selected_price_min, $price, false ) . '>' . esc_html( $price ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="inline-form-field half-width">
                            <select name="price_max" id="inline_price_max">
                                <option value=""><?php esc_html_e( 'Τιμή έως', 'my-custom-search' ); ?></option>
                                <?php
                                $prices_max = apply_filters( 'my_custom_search_rent_prices_max', array( 2000, 2500, 3000 ) );
                                foreach ( $prices_max as $price ) {
                                    echo '<option value="' . esc_attr( $price ) . '" ' . selected( $selected_price_max, $price, false ) . '>' . esc_html( $price ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="inline-form-row">
                        <label class="inline-form-icon"><i class="fa-solid fa-ruler-combined"></i></label>
                        <div class="inline-form-field half-width">
                            <select name="sqm_min" id="inline_sqm_min">
                                <option value=""><?php esc_html_e( 'τ.μ. Από', 'my-custom-search' ); ?></option>
                                <?php
                                $sqm_min = apply_filters( 'my_custom_search_sqm_min', array( 50, 100, 150 ) );
                                foreach ( $sqm_min as $sqm ) {
                                    echo '<option value="' . esc_attr( $sqm ) . '" ' . selected( $selected_sqm_min, $sqm, false ) . '>' . esc_html( $sqm ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="inline-form-field half-width">
                            <select name="sqm_max" id="inline_sqm_max">
                                <option value=""><?php esc_html_e( 'τ.μ. Έως', 'my-custom-search' ); ?></option>
                                <?php
                                $sqm_max = apply_filters( 'my_custom_search_sqm_max', array( 200, 250, 300 ) );
                                foreach ( $sqm_max as $sqm ) {
                                    echo '<option value="' . esc_attr( $sqm ) . '" ' . selected( $selected_sqm_max, $sqm, false ) . '>' . esc_html( $sqm ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
