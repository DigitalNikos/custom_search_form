<?php
// File: includes/class-search-form.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_Form {

    /**
     * Search_Form Class.
     *
     * Handles rendering of the search form templates.
     *
     * @package My_Custom_Search
     */
    public static function render_main_form() {
        ob_start();
        include MY_CUSTOM_SEARCH_PATH . 'templates/form-main.php';
        return ob_get_clean();
    }

    /**
     * Renders the inline search form.
     *
     * @return string HTML output of the inline form.
     */
    public static function render_inline_form() {
        ob_start();
        include MY_CUSTOM_SEARCH_PATH . 'templates/form-inline.php';
        return ob_get_clean();
    }
}
