<?php
/**
 * Template for rendering the random property slider.
 *
 * Expects a WP_Query object in the variable $query.
 *
 * @package My_RealEstateSearch
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Mapping array for property types.
$property_types = array(
    'apartment'     => __( 'Διαμέρισμα', 'my-custom-search' ),
    'house'         => __( 'Μονοκατοικία', 'my-custom-search' ),
    'plot'          => __( 'Οικόπεδο', 'my-custom-search' ),
    'land'          => __( 'Χωράφι', 'my-custom-search' ),
    'office'        => __( 'Επαγγελματικός χώρος', 'my-custom-search' ),
    'service_areas' => __( 'Βοηθητικοί χώροι', 'my-custom-search' ),
);
?>

<?php if ( $query->have_posts() ) : ?>
    <div class="random-suggested-properties swiper-container">
        <div class="swiper-wrapper">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php
                    $permalink   = get_the_permalink();
                    $title       = get_the_title();
                    $kind        = get_post_meta( get_the_ID(), '_property_kind', true );
                    $price       = get_post_meta( get_the_ID(), '_property_price', true );
                    $address     = get_post_meta( get_the_ID(), '_property_address', true );
                    $city        = get_post_meta( get_the_ID(), '_property_city', true );
                    $sqm         = get_post_meta( get_the_ID(), '_property_sqm', true );
                    $bedrooms    = get_post_meta( get_the_ID(), '_property_bedrooms', true );
                    $bathrooms   = get_post_meta( get_the_ID(), '_property_bathrooms', true );
                    $floor       = get_post_meta( get_the_ID(), '_property_floor', true );
                    $description = get_post_meta( get_the_ID(), '_property_desc', true );

                    // Get main image or use placeholder.
                    $main_image_id = get_post_meta( get_the_ID(), '_property_main_image', true );
                    $img_url = $main_image_id ? wp_get_attachment_url( $main_image_id ) : 'https://via.placeholder.com/320x240';
                ?>
                <div class="swiper-slide">
                    <div class="random-property-result-item" data-link="<?php echo esc_url( $permalink ); ?>">
                        <div class="random-property-result-image">
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                        </div>
                        <div class="random-property-result-details">
                            <h3 class="random-prop-kind-sqm">
                                <?php echo esc_html( $property_types[$kind] ) . ', ' . esc_html( $sqm ) . ' τ.μ.'; ?>
                            </h3>
                            <p class="random-prop-address-city">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo esc_html( $address ) . ', ' . esc_html( $city ); ?>
                            </p>
                            <p class="random-prop-stairs-room">
                                <i class="fa-solid fa-stairs"></i> <?php echo esc_html( $floor ); ?>ος &nbsp;&nbsp;
                                <i class="fa-solid fa-bed"></i> <?php echo esc_html( $bedrooms ); ?> υ/δ &nbsp;&nbsp;
                                <i class="fa-solid fa-bed"></i> <?php echo esc_html( $bathrooms ); ?>
                            </p>
                            <p class="random-prop-description"><?php echo esc_html( $description ); ?></p>
                            <p class="random-prop-price"><?php echo esc_html( $price ) . ' €'; ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div><!-- End swiper-wrapper -->
        <div class="swiper-pagination"></div>
    </div><!-- End swiper-container -->
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p><?php esc_html_e( 'No properties found.', 'my-custom-search' ); ?></p>
<?php endif; ?>
