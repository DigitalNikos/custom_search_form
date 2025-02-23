<?php
/**
 * Class Search_Properties
 *
 * Handles property search functionality including AJAX filtering,
 * rendering of property results (for the results page), and random property suggestions.
 *
 * @package My_Custom_Search
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Properties {

    /**
     * Retrieves and sanitizes filter parameters from either GET or POST.
     *
     * @param string $source 'GET' or 'POST'.
     * @return array Associative array of filters.
     */
    private static function get_filters( $source = 'GET' ) {
        $data = ( $source === 'GET' ) ? $_GET : $_POST;
        $filters = array();
        $filters['deal_type']     = isset( $data['deal_type'] ) ? sanitize_text_field( $data['deal_type'] ) : '';
        $filters['property_type'] = isset( $data['property_type'] ) ? sanitize_text_field( $data['property_type'] ) : '';
        $filters['county']        = isset( $data['county'] ) ? sanitize_text_field( $data['county'] ) : '';
        $filters['city']          = isset( $data['city'] ) ? sanitize_text_field( $data['city'] ) : '';
        $price_min_raw            = isset( $data['price_min'] ) ? sanitize_text_field( $data['price_min'] ) : '';
        $price_max_raw            = isset( $data['price_max'] ) ? sanitize_text_field( $data['price_max'] ) : '';
        $sqm_min_raw              = isset( $data['sqm_min'] )   ? sanitize_text_field( $data['sqm_min'] )   : '';
        $sqm_max_raw              = isset( $data['sqm_max'] )   ? sanitize_text_field( $data['sqm_max'] )   : '';

        // Convert to numeric values.
        $filters['price_min'] = intval( preg_replace( '/\D/', '', $price_min_raw ) );
        $filters['price_max'] = intval( preg_replace( '/\D/', '', $price_max_raw ) );
        $filters['sqm_min']   = intval( $sqm_min_raw );
        $filters['sqm_max']   = intval( $sqm_max_raw );
        return $filters;
    }

    /**
     * Builds a meta query array for WP_Query based on filters.
     *
     * @param array $filters Array of filter values.
     * @return array Meta query array.
     */
    private static function build_meta_query( $filters ) {
        $meta_query = array( 'relation' => 'AND' );
        if ( ! empty( $filters['deal_type'] ) ) {
            $meta_query[] = array(
                'key'     => '_property_deal_type',
                'value'   => $filters['deal_type'],
                'compare' => '=',
            );
        }
        if ( ! empty( $filters['property_type'] ) ) {
            $meta_query[] = array(
                'key'     => '_property_kind',
                'value'   => $filters['property_type'],
                'compare' => '=',
            );
        }
        if ( ! empty( $filters['county'] ) ) {
            $meta_query[] = array(
                'key'     => '_property_county',
                'value'   => $filters['county'],
                'compare' => 'LIKE',
            );
        }
        if ( ! empty( $filters['city'] ) ) {
            $meta_query[] = array(
                'key'     => '_property_city',
                'value'   => $filters['city'],
                'compare' => 'LIKE',
            );
        }
        if ( $filters['price_min'] > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_price',
                'value'   => $filters['price_min'],
                'type'    => 'NUMERIC',
                'compare' => '>=',
            );
        }
        if ( $filters['price_max'] > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_price',
                'value'   => $filters['price_max'],
                'type'    => 'NUMERIC',
                'compare' => '<=',
            );
        }
        if ( $filters['sqm_min'] > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_sqm',
                'value'   => $filters['sqm_min'],
                'type'    => 'NUMERIC',
                'compare' => '>=',
            );
        }
        if ( $filters['sqm_max'] > 0 ) {
            $meta_query[] = array(
                'key'     => '_property_sqm',
                'value'   => $filters['sqm_max'],
                'type'    => 'NUMERIC',
                'compare' => '<=',
            );
        }
        return $meta_query;
    }

    /**
     * Retrieves filtered properties using WP_Query.
     *
     * @param array $filters Array of filters.
     * @return WP_Query Query object.
     */
    private static function get_filtered_properties( $filters ) {
        $args = array(
            'post_type'      => 'property',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => self::build_meta_query( $filters ),
        );
        return new WP_Query( $args );
    }

    /**
     * Handles the AJAX request for filtering properties.
     *
     * Expects filter parameters via POST.
     *
     * @return void Outputs JSON response with matching properties.
     */
    public static function filter_properties() {
        check_ajax_referer( 'res_filter_nonce', 'nonce' );
        $filters = self::get_filters( 'POST' );
        $query = self::get_filtered_properties( $filters );
        $properties_data = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                // Map property types to translated labels.
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

                // Get image URL with fallback.
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
                    'title'     => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'kind'      => $translated_kind,
                    'price'     => $price,
                    'sqm'       => $sqm,
                    'address'   => $full_address,
                    'city'      => $city_value,
                    'county'    => $county_value,
                    'img_url'   => esc_url( $img_url ),
                );
            }
            wp_reset_postdata();
        }

        error_log( '[My Custom Search] Filtered Properties: ' . print_r( $properties_data, true ) );
        wp_send_json_success( $properties_data );
        wp_die();
    }

    /**
     * Renders property results based on GET query parameters.
     *
     * Reads filters from the URL, performs the query, and returns HTML output.
     *
     * @return string HTML output containing the list of filtered properties.
     */
    public static function render_property_results() {
        $filters = self::get_filters( 'GET' );
        $query = self::get_filtered_properties( $filters );
        ob_start();

        if ( $query->have_posts() ) {
            echo '<div class="property-results-list">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $county      = get_post_meta( get_the_ID(), '_property_county', true );
                $kind        = get_post_meta( get_the_ID(), '_property_kind', true );
                $price       = get_post_meta( get_the_ID(), '_property_price', true );
                $address     = get_post_meta( get_the_ID(), '_property_address', true );
                $city        = get_post_meta( get_the_ID(), '_property_city', true );
                $sqm         = get_post_meta( get_the_ID(), '_property_sqm', true );
                $description = get_post_meta( get_the_ID(), '_property_desc', true );
                $bathrooms  = get_post_meta( get_the_ID(), '_property_bathrooms', true );

                $full_address = trim( $address . ', ' . $city );

                $property_types = array(
                    'apartment'     => __( 'Διαμέρισμα', 'my-custom-search' ),
                    'house'         => __( 'Μονοκατοικία', 'my-custom-search' ),
                    'plot'          => __( 'Οικόπεδο', 'my-custom-search' ),
                    'land'          => __( 'Χωράφι', 'my-custom-search' ),
                    'office'        => __( 'Επαγγελματικός χώρος', 'my-custom-search' ),
                    'service_areas' => __( 'Βοηθητικοί χώροι', 'my-custom-search' ),
                );
                ?>
                <div class="property-result-item" data-link="<?php echo esc_url( get_the_permalink() ); ?>">
                <?php
                    // Retrieve the main image ID from the post meta.
                    $main_image_id = get_post_meta( get_the_ID(), '_property_main_image', true );
                    // Define a fallback placeholder image URL.
                    error_log( 'MRFS_PLUGIN_URL: ' . MRFS_PLUGIN_URL );
                    $placeholder_url = MRFS_PLUGIN_URL . 'assets/images/placeholder.jpg';
                    // Start with the placeholder as the default.
                    $img_url = $placeholder_url;
                    // If there is a valid image ID, try to get the image URL.
                    if ( $main_image_id && is_numeric( $main_image_id ) ) {
                        error_log( 'MALAKIA');
                        $img_data = wp_get_attachment_image_src( $main_image_id, array(320, 240) );
                        if ( $img_data && isset( $img_data[0] ) ) {
                            error_log( 'MALAKIA2');
                            $img_url = $img_data[0];
                        }
                    }
                    ?>
                    <div class="property-result-image">
                        <?php
                        if ( $img_url ) {
                            echo '<img src="' . esc_url( $img_url ) . '" />';
                        } else {
                            echo '<div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; background:#eee;">No image</div>';
                        }
                        ?>
                    </div>
                    <div class="property-result-details">
                        <h3 class="prop-kind-sqm">
                            <?php echo esc_html( $property_types[ $kind ] ) . ', ' . esc_html( $sqm ) . ' τ.μ.'; ?>
                        </h3>
                        <p class="prop-address-city">
                          <?php 
                             echo esc_html( $address );
                             if ( $address && $city ) {
                                 echo ', ';
                             }
                             echo esc_html( $city );
                             echo ' (' . esc_html( $county ) . ')';
                          ?>
                        </p>
                        
                        <p class="prop-description">
                            <?php echo esc_html( $description ); ?>
                        </p>
                        <p class="prop-price">
                          <?php echo esc_html( $price ) . ' €'; ?>
                        </p>
                    </div>
                    
                    
                </div>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__( 'No properties found matching your criteria.', 'my-custom-search' ) . '</p>';
        }
        return ob_get_clean();
    }

    /**
     * Retrieves random suggested properties and renders them as a Swiper slider.
     *
     * @return string HTML markup of the slider with random properties.
     */
    public static function get_random_properties() {
        $args = array(
            'post_type'      => 'property',
            'posts_per_page' => 4,
            'orderby'        => 'rand',
        );
        $query = new WP_Query( $args );
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

                $permalink = get_the_permalink();
                $title = get_the_title();
                $kind = get_post_meta( get_the_ID(), '_property_kind', true );
                $price = get_post_meta( get_the_ID(), '_property_price', true );
                $address = get_post_meta( get_the_ID(), '_property_address', true );
                $city = get_post_meta( get_the_ID(), '_property_city', true );
                $sqm = get_post_meta( get_the_ID(), '_property_sqm', true );
                $bedrooms    = get_post_meta( get_the_ID(), '_property_bedrooms', true );
                $bathrooms   = get_post_meta( get_the_ID(), '_property_bathrooms', true );
                $floor       = get_post_meta( get_the_ID(), '_property_floor', true );
                $description = get_post_meta( get_the_ID(), '_property_desc', true );

                $main_image_id = get_post_meta( get_the_ID(), '_property_main_image', true );
                $img_url = $main_image_id ? wp_get_attachment_url( $main_image_id ) : 'https://via.placeholder.com/320x240';

                $output .= '
                <div class="swiper-slide">
                    <div class="random-property-result-item" data-link="' . esc_url( $permalink ) . '">
                        <div class="random-property-result-image">
                            <img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $title ) . '" />
                        </div>
                        <div class="random-property-result-details">
                            <h3 class="random-prop-kind-sqm">' . esc_html( $property_types[ $kind ] ) . ', ' . esc_html( $sqm ) . ' τ.μ.</h3>
                            <p class="random-prop-address-city">
                                <i class="fas fa-map-marker-alt"></i> ' . esc_html( $address ) . ', ' . esc_html( $city ) . '
                            </p>
                            <p class="random-prop-stairs-room">
                                <i class="fa-solid fa-stairs"></i> ' . esc_html( $floor ) . 'ος &nbsp;&nbsp;
                                <i class="fa-solid fa-bed"></i> ' . esc_html( $bedrooms ) . ' υ/δ &nbsp;&nbsp;
                                <i class="fa-solid fa-bed"></i> ' . esc_html( $bathrooms ) . '
                            </p>
                            <p class="random-prop-description">' . esc_html( $description ) . '</p>
                            <p class="random-prop-price">' . esc_html( $price ) . ' €</p>
                        </div>
                    </div>
                </div>';
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
