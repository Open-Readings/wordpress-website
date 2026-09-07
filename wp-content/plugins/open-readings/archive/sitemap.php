<?php
// 1. Register the custom URL endpoint for the sitemap
add_action('init', function() {
    add_rewrite_rule('^abstracts-sitemap\.xml$', 'index.php?abstracts_sitemap=1', 'top');
});

// 2. Add the custom query variable so WordPress knows to listen for it
add_filter('query_vars', function($vars) {
    $vars[] = 'abstracts_sitemap';
    return $vars;
});

// 3. Intercept the template load and output the XML instead
add_action('template_redirect', function() {
    // Check if the user/bot is requesting our specific sitemap
    if (get_query_var('abstracts_sitemap')) {
        global $wpdb;
        
        // Set the correct HTTP header for XML
        header('Content-Type: text/xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Fetch only the specific columns needed for the 5000+ rows to save memory
        $abstracts = $wpdb->get_results("
            SELECT conference_year, presentation_id, title 
            FROM wp_or_abstracts 
            WHERE is_hidden = 0
        ");
        
        if ($abstracts) {
            foreach ($abstracts as $abs) {
                // Generate the exact same slug logic used in your canonical URLs
                $slug = sanitize_title(wp_trim_words($abs->title, 5, ''));
                $url = home_url("/archive/{$abs->conference_year}/{$abs->presentation_id}/{$slug}/");
                
                echo "\t<url>\n";
                echo "\t\t<loc>" . esc_url($url) . "</loc>\n";
                // Since conference abstracts rarely change after the event, 'yearly' is perfect
                echo "\t\t<changefreq>yearly</changefreq>\n"; 
                echo "\t</url>\n";
            }
        }
        
        echo '</urlset>';
        exit; // Stop WordPress from loading the rest of the site/theme
    }
});