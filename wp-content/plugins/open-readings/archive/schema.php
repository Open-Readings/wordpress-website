<?php
// schema.php

// Ensure we have the $abstract object from the parent template
if (!isset($abstract) || !$abstract) {
    return;
}

$year = get_query_var('abs_year');
$id   = get_query_var('abs_id');

// Generate the identical text slug used for your canonical redirect
$slug = sanitize_title(wp_trim_words($abstract->title, 5, ''));

// ==========================================
// 1. Build the Breadcrumb Schema
// ==========================================
$breadcrumb_schema = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => home_url('/')
        ],
        [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "Archive",
            "item" => home_url('/archive/')
        ],
        [
            "@type" => "ListItem",
            "position" => 3,
            "name" => "Open Readings " . $year,
            "item" => home_url("/archive/$year/")
        ],
        [
            "@type" => "ListItem",
            "position" => 4,
            "name" => $id . ' | ' . wp_trim_words($abstract->title, 5, '...'), 
            "item" => home_url("/archive/$year/$id/$slug/")
        ]
    ]
];


// ==========================================
// 2. Build the ScholarlyArticle Schema
// ==========================================

// Clean the Description (Strip HTML and {{FIGURE_X}} tags)
$raw_text = wp_strip_all_tags($abstract->content_html);
$clean_description = trim(preg_replace('/\{\{.*?\}\}/', '', $raw_text));

// Decode JSON data
$authors_data = json_decode($abstract->authors_json, true) ?: [];
$affiliations_data = json_decode($abstract->affiliations_json, true) ?: [];

// Create an Affiliations Lookup Map
$affil_lookup = [];
foreach ($affiliations_data as $affil) {
    if (isset($affil['id']) && isset($affil['affiliation'])) {
        $affil_lookup[$affil['id']] = $affil['affiliation'];
    }
}

// Build the Rich Authors Array
$schema_authors = [];
foreach ($authors_data as $author) {
    $person = [
        "@type" => "Person",
        "name"  => $author['author']
    ];

    // Add affiliations if they exist for this author
    if (!empty($author['affiliations']) && is_array($author['affiliations'])) {
        $author_affils = [];
        foreach ($author['affiliations'] as $aff_id) {
            if (isset($affil_lookup[$aff_id])) {
                $author_affils[] = [
                    "@type" => "Organization",
                    "name"  => $affil_lookup[$aff_id]
                ];
            }
        }
        
        // If there's only one affiliation, return the object. If multiple, return the array.
        if (count($author_affils) === 1) {
            $person['affiliation'] = $author_affils[0];
        } elseif (count($author_affils) > 1) {
            $person['affiliation'] = $author_affils;
        }
    }

    $schema_authors[] = $person;
}

// Build the Maximal ScholarlyArticle Schema
$article_schema = [
    "@context" => "https://schema.org",
    "@type" => "ScholarlyArticle",
    "headline" => $abstract->title,
    "datePublished" => $year,
    "url" => $canonical_url, // Inherited from template-abstract.php
    "author" => $schema_authors,
    "description" => $clean_description,
    "publisher" => [
        "@type" => "Organization",
        "name" => "Open Readings Conference"
    ]
];


// ==========================================
// 3. Inject into the <head>
// ==========================================
add_action('wp_head', function() use ($breadcrumb_schema, $article_schema) {
    echo "\n<!-- Open Readings Breadcrumb Schema -->\n";
    echo '<script type="application/ld+json">' . wp_json_encode($breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    
    echo "\n<!-- Open Readings Abstract Schema -->\n";
    echo '<script type="application/ld+json">' . wp_json_encode($article_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
});

// 4. DEBUG OUTPUT: Print directly to the footer so it's visible on the page
// add_action('wp_footer', function() use ($breadcrumb_schema, $article_schema) {
//     // Wrap it in a brightly colored block so you can't miss it
//     echo '<div style="background: #1e1e1e; color: #36cc22; padding: 20px; font-family: monospace; border: 3px solid #ff00ff; margin: 20px; border-radius: 8px;">';
//     echo '<h2 style="color: #ff00ff; margin-top: 0;">UwU Debug Mode: Schema Generator</h2>';
    
//     echo '<h3 style="color: #fff;">1. Breadcrumbs</h3>';
//     echo '<pre style="white-space: pre-wrap; overflow-wrap: break-word;">' . esc_html(wp_json_encode($breadcrumb_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
    
//     echo '<hr style="border-color: #555;">';
    
//     echo '<h3 style="color: #fff;">2. ScholarlyArticle</h3>';
//     echo '<pre style="white-space: pre-wrap; overflow-wrap: break-word;">' . esc_html(wp_json_encode($article_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>';
    
//     echo '<p style="color: #ff5555; font-weight: bold;">(Remember to delete or comment out step 5 in schema.php before going live!)</p>';
//     echo '</div>';
// });

// ==========================================
// 5. Inject Standard Meta, Open Graph, and Scholar Tags
// ==========================================
add_action('wp_head', function() use ($abstract, $clean_description, $canonical_url, $authors_data, $year, $id) {
    
    // Fallback image for Open Graph (Use your conference logo/banner URL here)
    $og_image = home_url('/wp-content/uploads/open-readings-social-share.jpg');
    
    // Clean Title for attributes
    $safe_title = esc_attr(wp_strip_all_tags($abstract->title));
    $safe_desc = esc_attr(wp_trim_words($clean_description, 40, '...'));

    echo "\n<!-- Open Readings Core SEO & Social Tags -->\n";
    
    // Standard Meta
    echo '<meta name="description" content="' . $safe_desc . '">' . "\n";
    
    // Canonical URL (Crucial for your custom rewrite rules)
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";

    // Open Graph (Facebook, LinkedIn, Discord)
    echo '<meta property="og:title" content="' . $safe_title . '">' . "\n";
    echo '<meta property="og:description" content="' . $safe_desc . '">' . "\n";
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    echo '<meta property="og:site_name" content="Open Readings Conference">' . "\n";

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . $safe_title . '">' . "\n";
    echo '<meta name="twitter:description" content="' . $safe_desc . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";

    // ==========================================
    // Google Scholar Specific Tags (Highwire Press)
    // ==========================================
    echo "\n<!-- Open Readings Google Scholar Tags -->\n";
    echo '<meta name="citation_title" content="' . $safe_title . '">' . "\n";
    echo '<meta name="citation_publication_date" content="' . esc_attr($year) . '">' . "\n";
    echo '<meta name="citation_conference_title" content="Open Readings ' . esc_attr($year) . '">' . "\n";
    echo '<meta name="citation_publisher" content="Open Readings">' . "\n";
    echo '<meta name="citation_language" content="en">' . "\n";
    echo '<meta name="citation_abstract_html_url" content="' . esc_url($canonical_url) . '">' . "\n";

    // Loop through authors for Google Scholar
    if (!empty($authors_data) && is_array($authors_data)) {
        foreach ($authors_data as $author) {
            echo '<meta name="citation_author" content="' . esc_attr($author['author']) . '">' . "\n";
            // Note: Scholar supports citation_author_institution, but it's optional.
        }
    }
    
    // Dynamic Sliced PDF Endpoint
    $pdf_url = home_url("/archive/{$year}/{$id}/pdf/");
    echo '<meta name="citation_pdf_url" content="' . esc_url($pdf_url) . '">' . "\n";

}, 1); // Priority 1 to ensure these load high up in the <head>

// Force The SEO Framework to allow indexing on abstract pages
add_filter('the_seo_framework_robots_meta_array', function($meta) {
    // Explicitly set index and follow to true
    $meta['noindex'] = false;
    $meta['nofollow'] = false;
    
    // Clean up indexing directives
    unset($meta['noarchive']);
    
    return $meta;
}, 999);

// Disable TSF's automated canonical URL generator so it doesn't conflict with your custom smart slug canonical
add_filter('the_seo_framework_rel_canonical_output', '__return_false');