<?php
/**
 * Plugin Name: Real Estate Custom Search Form
 * Plugin URI:  https://example.com
 * Description: A plugin to display custom real estate search forms with Rent/Buy toggle and property filtering.
 * Version:     1.0
 * Author:      Lithoxopoulos Nikolaos
 * Author URI:  https://example.com
 * License:     GPL2
 * Text Domain: my-real-estate-search
 */

// Exit if accessed directly – this helps improve security.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants for the plugin's path and URL.
define( 'MY_CUSTOM_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_CUSTOM_SEARCH_URL', plugin_dir_url( __FILE__ ) );

// Include required files that contain our functions and classes.
require_once MY_CUSTOM_SEARCH_PATH . 'includes/config.php';
require_once MY_CUSTOM_SEARCH_PATH . 'includes/functions.php';
require_once MY_CUSTOM_SEARCH_PATH . 'includes/class-search-properties.php';
require_once MY_CUSTOM_SEARCH_PATH . 'includes/class-search-form.php';

/**
 * Plugin initialization function.
 * Loads the textdomain for internationalization.
 */
function my_custom_search_init() {
    load_plugin_textdomain( 'my-custom-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'my_custom_search_init' );

/**
 * Enqueue front-end CSS and JavaScript assets.
 * These files are responsible for the modern design and interactivity of your search forms.
 */
function my_custom_search_enqueue_assets() {

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        array(),
        '6.7.2'
    );

    wp_enqueue_style(
        'my-custom-search-style', 
        MY_CUSTOM_SEARCH_URL . 'assets/css/search-style.css', 
        array(), 
        '1.0', 
        'all'
    );
    
    wp_enqueue_style(
        'inline-search-style',
        MY_CUSTOM_SEARCH_URL . 'assets/css/inline-search-style.css',
        array(),
        '1.0',
        'all'
    );
    
    wp_enqueue_script(
        'my-custom-search-scripts', 
        MY_CUSTOM_SEARCH_URL . 'assets/js/search-scripts.js', 
        array( 'jquery' ), 
        '1.0', 
        true
    );

    wp_enqueue_script(
        'my-inline-search',
        MY_CUSTOM_SEARCH_URL . 'assets/js/inline-search.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script( 'my-custom-search-scripts', 'mySearchData', array(
        'ajax_url'         => admin_url( 'admin-ajax.php' ),
        'nonce'            => wp_create_nonce( 'res_filter_nonce' ),
        'rent_prices_min'  => MRFS_RENT_PRICES_MIN,
        'rent_prices_max'  => MRFS_RENT_PRICES_MAX,
        'buy_prices_min'   => MRFS_BUY_PRICES_MIN,
        'buy_prices_max'   => MRFS_BUY_PRICES_MAX,
        'priceFrom'        => 'Τιμή από',
        'priceTo'          => 'Τιμή έως'
    ));

}
add_action( 'wp_enqueue_scripts', 'my_custom_search_enqueue_assets' );


add_action( 'wp_ajax_nopriv_filter_properties', array( 'Search_Properties', 'filter_properties' ) );
add_action( 'wp_ajax_filter_properties', array( 'Search_Properties', 'filter_properties' ) );
/**
 * Register shortcodes to output the search forms.
 * - [my_custom_search_main]: Renders the main homepage search form.
 * - [my_custom_search_inline]: Renders the inline search form for the results page.
 */
function my_custom_search_register_shortcodes() {
    add_shortcode( 'my_custom_search_main', array( 'Search_Form', 'render_main_form' ) );
    add_shortcode( 'my_custom_search_inline', array( 'Search_Form', 'render_inline_form' ) );
    add_shortcode( 'my_custom_search_results', array( 'Search_Properties', 'render_property_results' ) );
    add_shortcode( 'my_custom_random_results', array( 'Search_Properties', 'get_random_properties' ) );
}
add_action( 'init', 'my_custom_search_register_shortcodes' );