<?php
// File: templates/form-main.php

// Ensure that this file is not accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
                <?php 
                if ( defined('MY_CUSTOM_PROPERTY_TYPES') && is_array(MY_CUSTOM_PROPERTY_TYPES) ) {
                    foreach ( MY_CUSTOM_PROPERTY_TYPES as $key => $label ) {
                        echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
                    }
                }
                ?>
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
