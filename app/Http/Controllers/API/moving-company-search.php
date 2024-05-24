<?php
/*

Plugin Name: Moving Company Serch 
Description: A Custom serch form for finding moving company
version: 1.0
Author: KATHAN PATEL
*/


// Enqueue scripts and styles
function mcs_enqueue_scripts() {
    wp_enqueue_style('mcs-styles', plugin_dir_url(__FILE__) . 'css/styles.css');
    wp_enqueue_script('mcs-scripts', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), null, true);
    wp_localize_script('mcs-scripts', 'mcs_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mcs_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'mcs_enqueue_scripts');

// Shortcode for search form
function mcs_search_form() {
    ob_start();
    ?>
    <form id="mcs-search-form">
        <div class="mcs-field">
            <label for="mcs-country">Country:</label>
            <select id="mcs-country" name="country" class="mcs-country-dropdown">
                <option value="" data-icon="">Select a country</option>
                <option value="us" data-icon="https://countryflagsapi.com/png/us">United States</option>
                <option value="ca" data-icon="https://countryflagsapi.com/png/ca">Canada</option>
                <option value="gb" data-icon="https://countryflagsapi.com/png/gb">United Kingdom</option>
                <option value="fr" data-icon="https://countryflagsapi.com/png/fr">France</option>
                <option value="de" data-icon="https://countryflagsapi.com/png/de">Germany</option>
                <!-- Add more countries as needed -->
            </select>
        </div>
        <div class="mcs-field">
            <label for="mcs-keyword">Search:</label>
            <input type="text" id="mcs-keyword" name="keyword" placeholder="Enter keyword">
        </div>
        <button type="submit">Search</button>
    </form>
    <div id="mcs-results"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('mcs_search', 'mcs_search_form');


// AJAX handler for search
function mcs_search_ajax_handler() {
    check_ajax_referer('mcs_nonce', 'nonce');

    $country = sanitize_text_field($_POST['country']);
    $keyword = sanitize_text_field($_POST['keyword']);

    $args = array(
        'post_type' => 'moving_company',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'country',
                'value' => $country,
                'compare' => 'LIKE'
            ),
            array(
                'key' => 'keywords',
                'value' => $keyword,
                'compare' => 'LIKE'
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="mcs-result-item">';
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '</div>';
        }
        wp_reset_postdata();
    } else {
        echo '<p>No results found.</p>';
    }

    wp_die();
}
add_action('wp_ajax_mcs_search', 'mcs_search_ajax_handler');
add_action('wp_ajax_nopriv_mcs_search', 'mcs_search_ajax_handler');

// Register custom post type for moving companies
function mcs_register_post_type() {
    register_post_type('moving_company', array(
        'labels' => array(
            'name' => __('Moving Companies'),
            'singular_name' => __('Moving Company')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'moving-companies'),
        'supports' => array('title', 'editor', 'excerpt', 'custom-fields')
    ));
}
add_action('init', 'mcs_register_post_type');