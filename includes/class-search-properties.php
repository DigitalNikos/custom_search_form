<?php
// File: includes/class-search-properties.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Properties {

    /**
     * Handles the AJAX request for filtering properties.
     */
    public static function filter_properties() {
        // Verify nonce for security.
        check_ajax_referer( 'res_filter_nonce', 'nonce' );

        // Sanitize incoming fields.
        $deal_type     = isset( $_POST['deal_type'] ) ? sanitize_text_field( $_POST['deal_type'] ) : '';
        $property_type = isset( $_POST['property_type'] ) ? sanitize_text_field( $_POST['property_type'] ) : '';
        $price_min_raw = isset( $_POST['price_min'] ) ? sanitize_text_field( $_POST['price_min'] ) : '';
        $price_max_raw = isset( $_POST['price_max'] ) ? sanitize_text_field( $_POST['price_max'] ) : '';
        $sqm_min_raw   = isset( $_POST['sqm_min'] )   ? sanitize_text_field( $_POST['sqm_min'] )   : '';
        $sqm_max_raw   = isset( $_POST['sqm_max'] )   ? sanitize_text_field( $_POST['sqm_max'] )   : '';
        $county        = isset( $_POST['county'] )      ? sanitize_text_field( $_POST['county'] )  : '';
        $city          = isset( $_POST['city'] )        ? sanitize_text_field( $_POST['city'] )    : '';

        // Convert numeric fields.
        $price_min = intval( preg_replace( '/\D/', '', $price_min_raw ) );
        $price_max = intval( preg_replace( '/\D/', '', $price_max_raw ) );
        $sqm_min   = intval( $sqm_min_raw );
        $sqm_max   = intval( $sqm_max_raw );

        // Build meta query based on filters.
        $meta_query = array( 'relation' => 'AND' );
        if ( $deal_type ) {
            $meta_query[] = array(
                'key'     => '_property_deal_type',
                'value'   => $deal_type,
                'compare' => '=',
            );
        }
        if ( $property_type ) {
            $meta_query[] = array(
                'key'     => '_property_kind',
                'value'   => $property_type,
                'compare' => '=',
            );
        }
        if ( $city ) {
            $meta_query[] = array(
                'key'     => '_property_city',
                'value'   => $city,
                'compare' => 'LIKE',
            );
        }
        if ( $county ) {
            $meta_query[] = array(
                'key'     => '_property_county',
                'value'   => $county,
                'compare' => 'LIKE',
            );
        }
        if ( $price_min >= 0 ) {
            $meta_query[] = array(
                'key'     => '_property_price',
                'value'   => $price_min,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            );
        }
        if ( $price_max > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_price',
                'value'   => $price_max,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            );
        }
        if ( $sqm_min >= 0 ) {
            $meta_query[] = array(
                'key'     => '_property_sqm',
                'value'   => $sqm_min,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            );
        }
        if ( $sqm_max > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_sqm',
                'value'   => $sqm_max,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            );
        }

        $args = array(
            'post_type'      => 'property',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
        );

        $query = new WP_Query( $args );
        $properties_data = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                // Define a mapping for property types.
                $property_types = array(
                    'apartment'     => __( 'Διαμέρισμα', 'my-custom-search' ),
                    'house'         => __( 'Μονοκατοικία', 'my-custom-search' ),
                    'plot'          => __( 'Οικόπεδο', 'my-custom-search' ),
                    'land'          => __( 'Χωράφι', 'my-custom-search' ),
                    'office'        => __( 'Επαγγελματικός χώρος', 'my-custom-search' ),
                    'service_areas' => __( 'Βοηθητικοί χώροι', 'my-custom-search' ),
                );

                $kind         = get_post_meta( get_the_ID(), '_property_kind', true );
                $price        = get_post_meta( get_the_ID(), '_property_price', true );
                $address      = get_post_meta( get_the_ID(), '_property_address', true );
                $city_value   = get_post_meta( get_the_ID(), '_property_city', true );
                $county_value = get_post_meta( get_the_ID(), '_property_county', true );
                $sqm          = get_post_meta( get_the_ID(), '_property_sqm', true );
                $full_address = trim( $address . ', ' . $city_value );

                // Get the image URL with a fallback placeholder.
                $main_image_id   = get_post_meta( get_the_ID(), '_property_main_image', true );
                $placeholder_url = MY_CUSTOM_SEARCH_URL . 'assets/images/placeholder.jpg';
                $img_url         = $placeholder_url;
                if ( $main_image_id && is_numeric( $main_image_id ) ) {
                    $img_data = wp_get_attachment_image_src( $main_image_id, array( 320, 240 ) );
                    if ( $img_data && isset( $img_data[0] ) ) {
                        $img_url = $img_data[0];
                    }
                }

                $translated_kind = isset( $property_types[ $kind ] ) ? $property_types[ $kind ] : $kind;

                $properties_data[] = array(
                    'title'       => get_the_title(),
                    'permalink'   => get_the_permalink(),
                    'kind'        => $translated_kind,
                    'price'       => $price,
                    'sqm'         => $sqm,
                    'address'     => $full_address,
                    'city'        => $city_value,
                    'county'      => $county_value,
                    'img_url'     => esc_url( $img_url ),
                );
            }
            wp_reset_postdata();
        }

        error_log( '[My Custom Search] Filtered Properties: ' . print_r( $properties_data, true ) );
        wp_send_json_success( $properties_data );
        wp_die();
    }

    /**
     * Retrieves random suggested properties.
     * Although some developers may separate this into its own module,
     * including it here is acceptable given the plugin’s focused purpose.
     */
    public static function get_random_properties() {
        $args = array(
            'post_type'      => 'property',
            'posts_per_page' => 4,
            'orderby'        => 'rand',
        );
        $query  = new WP_Query( $args );
        $output = '';
        if ( $query->have_posts() ) {
            $output .= '<div class="random-suggested-properties swiper-container">';
            $output .= '<div class="swiper-wrapper">';
            while ( $query->have_posts() ) {
                $query->the_post();

                $property_types = array(
                    'apartment'     => __( 'Διαμέρισμα', 'my-custom-search' ),
                    'house'         => __( 'Μονοκατοικία', 'my-custom-search' ),
                    'plot'          => __( 'Οικόπεδο', 'my-custom-search' ),
                    'land'          => __( 'Χωράφι', 'my-custom-search' ),
                    'office'        => __( 'Επαγγελματικός χώρος', 'my-custom-search' ),
                    'service_areas' => __( 'Βοηθητικοί χώροι', 'my-custom-search' ),
                );

                $permalink   = get_the_permalink();
                $title       = get_the_title();
                $kind        = get_post_meta( get_the_ID(), '_property_kind', true );
                $price       = get_post_meta( get_the_ID(), '_property_price', true );
                $address     = get_post_meta( get_the_ID(), '_property_address', true );
                $city        = get_post_meta( get_the_ID(), '_property_city', true );
                $sqm         = get_post_meta( get_the_ID(), '_property_sqm', true );

                $main_image_id = get_post_meta( get_the_ID(), '_property_main_image', true );
                $img_url       = $main_image_id ? wp_get_attachment_url( $main_image_id ) : 'https://via.placeholder.com/320x240';

                $output .= '<div class="swiper-slide">';
                $output .= '<div class="random-property-result-item" data-link="' . esc_url( $permalink ) . '">';
                $output .= '<div class="random-property-result-image"><img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '" /></div>';
                $output .= '<div class="random-property-result-details">';
                $output .= '<h3 class="random-prop-kind-sqm">' . esc_html( $property_types[ $kind ] ) . ', ' . esc_html( $sqm ) . ' τ.μ.</h3>';
                $output .= '<p class="random-prop-address-city"><i class="fas fa-map-marker-alt"></i> ' . esc_html( $address ) . ', ' . esc_html( $city ) . '</p>';
                $output .= '<p class="random-prop-price">' . esc_html( $price ) . ' €</p>';
                $output .= '</div></div></div>';
            }
            $output .= '</div>'; // End swiper-wrapper.
            $output .= '<div class="swiper-pagination"></div>';
            $output .= '</div>'; // End swiper-container.
            wp_reset_postdata();
        } else {
            $output .= '<p>' . __( 'No properties found.', 'my-custom-search' ) . '</p>';
        }
        return $output;
    }
}
