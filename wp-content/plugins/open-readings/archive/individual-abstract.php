<?php
// 1. Register URL variables
function register_abstract_query_vars($vars) {
    $vars[] = 'abs_year';
    $vars[] = 'abs_id';
    return $vars;
}
add_filter('query_vars', 'register_abstract_query_vars');

// 2. Catch the URL (Note: we removed the pagename redirect)
function custom_abstract_rewrite_rules() {
    add_rewrite_rule(
        '^archive/([0-9]{4})/([^/]+)/?$',
        'index.php?abs_year=$matches[1]&abs_id=$matches[2]',
        'top'
    );
}
add_action('init', 'custom_abstract_rewrite_rules');

add_action('init', function() {
    // Matches /archive/2012/P2-20/ AND /archive/2012/P2-20/any-text-slug-here/
    add_rewrite_rule(
        '^archive/([0-9]{4})/([^/]+)(?:/([^/]+))?/?$', 
        'index.php?abs_year=$matches[1]&abs_id=$matches[2]', 
        'top'
    );
});

// 3. CRITICAL: Prevent WordPress from failing or throwing a 404 before template loading
function bypass_wordpress_404_on_abstract_page($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (get_query_var('abs_year') && get_query_var('abs_id')) {
            // Force WordPress to think this is a valid page request
            $query->is_home = false;
            $query->is_404  = false;
        }
    }
}
add_action('parse_query', 'bypass_wordpress_404_on_abstract_page');

// 4. The Template Intercept
function load_custom_abstract_template($template) {
    // If our custom URL variables are present, take over the page load
    if (get_query_var('abs_year') && get_query_var('abs_id')) {
        global $wpdb;
        $year = get_query_var('abs_year');
        $id   = get_query_var('abs_id');

        // Check if the abstract actually exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_or_abstracts WHERE conference_year = %d AND presentation_id = %s AND is_hidden = 0",
            $year, $id
        ));

        if ($exists) {
            // Tell WordPress this is NOT a 404
            global $wp_query;
            $wp_query->is_404 = false;
            status_header(200);
        }
        
        $custom_template = dirname(__FILE__) . '/template-abstract.php';
        if (file_exists($custom_template)) {
            return $custom_template; // Force WordPress to load this file
        }
    }
    return $template;
}
add_filter('template_include', 'load_custom_abstract_template');