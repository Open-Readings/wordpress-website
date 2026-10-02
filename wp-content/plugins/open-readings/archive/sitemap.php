<?php
// 1. Register the custom URL endpoint for the sitemap
add_action('init', function() {
    add_rewrite_rule('^abstracts-sitemap\.xml$', 'index.php?abstracts_sitemap=1', 'top');
});

// 2. Add the custom query variable
add_filter('query_vars', function($vars) {
    $vars[] = 'abstracts_sitemap';
    return $vars;
});

// 3. Prevent WordPress canonical redirect from appending a trailing slash to this URL
add_filter('redirect_canonical', function($redirect_url, $requested_url) {
    if (get_query_var('abstracts_sitemap')) {
        return false; // Prevents 301 redirect to /abstracts-sitemap.xml/
    }
    return $redirect_url;
}, 10, 2);

// 4. Intercept the template load and output the XML
add_action('template_redirect', function() {
    if (get_query_var('abstracts_sitemap')) {
        global $wpdb;

        // Clean any output buffers to prevent PHP spaces/notices from corrupting the XML
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Set proper HTTP headers
        status_header(200);
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow', true); // Prevent search engines from indexing the raw XML document itself

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Query database efficiently
        $results = $wpdb->get_results("
            SELECT conference_year, presentation_id, title 
            FROM wp_or_abstracts 
            WHERE is_hidden = 0
        ");

        if (!empty($results)) {
            foreach ($results as $abs) {
                $slug = sanitize_title(wp_trim_words($abs->title, 5, ''));
                $url  = home_url("/archive/{$abs->conference_year}/{$abs->presentation_id}/{$slug}/");

                echo "  <url>\n";
                echo "    <loc>" . esc_url($url) . "</loc>\n";
                echo "    <changefreq>yearly</changefreq>\n";
                echo "  </url>\n";
            }
        }

        echo '</urlset>';
        exit;
    }
});