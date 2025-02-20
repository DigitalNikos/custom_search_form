<?php
/**
 * Functions for My Custom Search plugin.
 *
 * This file contains helper functions and AJAX endpoints for:
 *  - Fetching unique counties from property meta data.
 *  - Fetching unique cities for a given county.
 *
 * @package My_Custom_Search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Retrieves a list of unique counties from published property posts.
 *
 * @return void Outputs a JSON response with the unique counties.
 */
function my_custom_search_get_counties() {
    global $wpdb;
    $meta_key = '_property_county';

    // Prepare and execute the query for distinct county values.
    $query = $wpdb->prepare(
         "SELECT DISTINCT meta_value 
          FROM $wpdb->postmeta 
          WHERE meta_key = %s 
            AND post_id IN (
                SELECT ID FROM $wpdb->posts 
                WHERE post_type = 'property' 
                  AND post_status = 'publish'
            )",
         $meta_key
    );
    $counties = $wpdb->get_col($query);

    wp_send_json_success($counties);
}
add_action('wp_ajax_nopriv_get_counties', 'my_custom_search_get_counties');
add_action('wp_ajax_get_counties', 'my_custom_search_get_counties');

/**
 * Retrieves a list of unique cities for a specified county from published property posts.
 *
 * Expects a POST parameter 'county'. If not provided, returns an error.
 *
 * @return void Outputs a JSON response with the unique cities.
 */
function my_custom_search_get_cities() {
    global $wpdb;
    $county = isset($_POST['county']) ? sanitize_text_field($_POST['county']) : '';

    if ( empty($county) ) {
        wp_send_json_error('No county provided');
    }

    $meta_key = '_property_city';

    // Prepare and execute the query for distinct city values for the given county.
    $query = $wpdb->prepare(
         "SELECT DISTINCT meta_value 
          FROM $wpdb->postmeta 
          WHERE meta_key = %s 
            AND post_id IN (
                 SELECT post_id 
                 FROM $wpdb->postmeta 
                 WHERE meta_key = %s 
                   AND meta_value = %s
            )",
         $meta_key, '_property_county', $county
    );
    $cities = $wpdb->get_col($query);

    wp_send_json_success($cities);
}
add_action('wp_ajax_nopriv_get_cities', 'my_custom_search_get_cities');
add_action('wp_ajax_get_cities', 'my_custom_search_get_cities');
