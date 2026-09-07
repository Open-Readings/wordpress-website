<?php

// 2. Fetch the data from your custom SQL table
global $wpdb;
$year = get_query_var('abs_year');
$id   = get_query_var('abs_id');

$abstract = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM wp_or_abstracts WHERE conference_year = %d AND presentation_id = %s AND is_hidden = 0",
    $year, $id
));

// Handle 404 if abstract doesn't exist
if (!$abstract) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit;
}

// 2. Generate the "Smart" Canonical URL
// Take the first 5 words of the title and make it a clean, URL-safe slug
$slug = sanitize_title(wp_trim_words($abstract->title, 5, ''));
$canonical_url = home_url("/archive/$year/$id/$slug/");

// 3. The Seamless 301 Redirect
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$canonical_path = parse_url($canonical_url, PHP_URL_PATH);

// If they visited the short URL, or if the title changed in the database, redirect them.
if (untrailingslashit($current_path) !== untrailingslashit($canonical_path)) {
    wp_safe_redirect($canonical_url, 301);
    exit; // Stop executing immediately
}

add_filter('pre_get_document_title', function() use ($abstract) {
    return $abstract ? $abstract->title . ' | Open Readings' : 'Abstract Not Found | Open Readings';
});

include_once(__DIR__ . '/schema.php'); // Include the schema file for structured data

get_header(); 
?>

<script>
window.MathJax = {
  tex: {
    // Add \\( and \\) for inline math
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    // Add \\[ and \\] for display/block math
    displayMath: [['$$', '$$'], ['\\[', '\\]']],
    processEnvironments: true,
    processEscapes: true
  },
  options: {
    processHtmlClass: 'abstract-body-text',
    ignoreHtmlClass: 'no-mathjax',
    enableMenu: false
  }
};
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>

<style>
/* 1. Stop MathJax's outer wrappers from stretching your page */
    .MathJax {
        font-size: 1em !important;
    }
/* 2. Mobile Responsiveness for the Layout */
    @media (max-width: 992px) {
        .abstract-page-wrapper {
            flex-direction: column !important; /* Stacks content on top of sidebar */
            align-items: center !important;    /* Centers everything horizontally */
            margin: 20px auto !important;      /* Reduces outer margin on mobile */
            padding: 0 15px !important;        /* Adds breathing room on edges */
        }
        
        .abstract-content {
            width: 100% !important;
            max-width: 100% !important;
        }

        .abstract-sidebar {
            width: 100% !important;            /* Overrides the 200px/1200px hardcoded widths */
            max-width: 500px !important;       /* Keeps it from looking stretched out */
            display: flex !important;
            justify-content: center !important;/* Centers the Elementor shortcode content */
            margin-top: 30px !important;       /* Adds space between abstract and sidebar */
        }

        /* Adjust the debug PDF viewer for mobile so it doesn't take up 100vh */
        .abstract-sidebar iframe {
            height: 60vh !important; 
        }
    }
</style>

<?php

if ($abstract) {
    // 1. Get presentation_ids AND titles for this year so we can build direct slugs
    $all_presentations = $wpdb->get_results($wpdb->prepare(
        "SELECT presentation_id, title FROM wp_or_abstracts WHERE conference_year = %d AND is_hidden = 0",
        $year
    ), ARRAY_A);

    // 2. Custom sorting function to group by type (Oral then Poster) and sort numerically
    usort($all_presentations, function($a, $b) {
        $parse_id = function($id) {
            $is_poster = (stripos($id, 'P') === 0); 
            $type_weight = $is_poster ? 1 : 0; 
            $clean_string = preg_replace('/[^A-Za-z0-9.]/', '.', $id);
            return [
                'weight' => $type_weight,
                'clean'  => $clean_string
            ];
        };

        // Access the presentation_id from the associative array
        $parsed_a = $parse_id($a['presentation_id']);
        $parsed_b = $parse_id($b['presentation_id']);

        if ($parsed_a['weight'] !== $parsed_b['weight']) {
            return $parsed_a['weight'] <=> $parsed_b['weight'];
        }

        return strnatcasecmp($parsed_a['clean'], $parsed_b['clean']);
    });

    // 3. Find where we are in our newly grouped and sorted list
    // Extract just the IDs to easily find our current index
    $just_ids = array_column($all_presentations, 'presentation_id');
    $current_key = array_search($abstract->presentation_id, $just_ids);

    // 4. Determine Previous and Next items
    $prev_item = ($current_key > 0) ? $all_presentations[$current_key - 1] : null;
    $next_item = ($current_key < count($all_presentations) - 1) ? $all_presentations[$current_key + 1] : null;

    // 5. Generate the direct canonical URLs to bypass the 301 redirect
    $prev_id  = null;
    $prev_url = null;
    if ($prev_item) {
        $prev_id  = $prev_item['presentation_id'];
        $prev_slug = sanitize_title(wp_trim_words($prev_item['title'], 5, ''));
        $prev_url = home_url("/archive/$year/$prev_id/$prev_slug/");
    }

    $next_id  = null;
    $next_url = null;
    if ($next_item) {
        $next_id  = $next_item['presentation_id'];
        $next_slug = sanitize_title(wp_trim_words($next_item['title'], 5, ''));
        $next_url = home_url("/archive/$year/$next_id/$next_slug/");
    }

    do_shortcode('[latexpage]');
}
?>

<!-- 3. Your Custom Layout Structure -->
<div class="abstract-page-wrapper" style="display: flex; max-width: 1140px; gap: 40px; margin: 40px auto; padding: 0 0px;">
    
    <!-- Main Content Area -->
    <main class="abstract-content" style="flex: 1; min-width: 0; padding: 10px; max-width: 900px !important;">
        
        <div class="abstract-nav" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            
            <!-- Previous Button -->
            <div class="nav-prev" style="flex: 1; text-align: left;">
                <?php if ($prev_id && $prev_url): ?>
                    <a href="<?php echo esc_url($prev_url); ?>" 
                    style="text-decoration: none; color: #2387F3; font-weight: bold;">
                        <?php echo 'ᐊ ' . esc_html($prev_id); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Current Abstract Context -->
            <div class="nav-center" style="flex: 2; text-align: center; color: #333;">
                <strong>Open Readings <?php echo esc_html($year); ?></strong> 
                <span style="color: #666; margin: 0 8px;">•</span> 
                <strong><?php echo esc_html($id); ?></strong>
            </div>

            <!-- Next Button -->
            <div class="nav-next" style="flex: 1; text-align: right;">
                <?php if ($next_id && $next_url): ?>
                    <a href="<?php echo esc_url($next_url); ?>" 
                    style="text-decoration: none; color: #2387F3; font-weight: bold;">
                        <?php echo esc_html($next_id) . ' ᐅ'; ?>
                    </a>
                <?php endif; ?>
            </div>    

        </div>

        <!-- Clean divider separating nav from content -->
        <hr style="border: 0; border-top: 1px solid #aaa; margin: 0 0 30px 0;" />

        <?php if ($abstract): ?>
            
            <h1 style="margin-bottom: 10px; font-size: 1.5em; text-align: center;"> <?php echo wp_kses_post($abstract->title); ?></h1>
            
            <?php 
            // Decode and display authors
            $authors_json = json_decode($abstract->authors_json, true) ?: [];
            $author_names = array_column($authors_json, 'author');
            $affiliations = array_column($authors_json, 'affiliations');
            $is_presenting_author = array_column($authors_json, 'is_presenter');
            $affiliation_strings = array_map(function($affil) {
                return $affil ? implode(', ', $affil) : '';
            }, $affiliations);
            
            $authors = array_map(function($name, $affil, $is_presenter) {
                if ($is_presenter) {
                    return '<u>' . esc_html($name) . '</u><sup>' . esc_html($affil) . '</sup>';
                } else {
                    return esc_html($name) . '<sup>' . esc_html($affil) . '</sup>';
                }
            }, $author_names, $affiliation_strings, $is_presenting_author);
            
            $affiliation_array = json_decode($abstract->affiliations_json, true) ?: [];
            
            // Find emails
            $emails = array_column($authors_json, 'email');
            $emails_href = array_map(function($email) {
                return $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '';
            }, $emails);
            ?>
            
            <p style="font-style: italic; width: 100%; text-align: center;">
                <?php echo wp_kses_post(implode(', ', $authors)); ?>
            </p>
            
            <?php foreach ($affiliation_array as $affil): ?>
                <p style="font-size: 0.9em; width: 100%; text-align: center;">
                    <sup><?php echo esc_html($affil['id']); ?></sup> <?php echo esc_html($affil['affiliation']); ?>
                </p>
            <?php endforeach; ?>
            
            <p style="font-size: 0.9em; width: 100%; text-align: center;">
                <?php echo wp_kses_post(implode(', ', array_filter($emails_href))); ?>
            </p>
                        
            <!-- Display the processed HTML -->
            <div class="abstract-body-text" style="text-align: justify;">
                <?php 
                $content = $abstract->content_html;
                
                // ---> THE MATHJAX SAVIOR <---
                // The database contains double backslashes (\\int) which MathJax interprets as a line break. 
                // This converts all double backslashes back to single backslashes (\int) so MathJax renders the symbols!
                $content = str_replace('\\\\', '\\', $content);
                
                $image_dir_url = content_url('/uploads/abstracts/' . $year . '/images/');

                // --- 1. REPLACE FIGURE PLACEHOLDERS ---
                $figures = json_decode($abstract->figures_json, true) ?: [];
                foreach ($figures as $fig) {
                    // Build the caption HTML
                    $caption_html = '';
                    if (!empty($fig['captions'])) {
                        // Split combined IDs like "2 & 3" into an array: ["2", "3"]
                        $individual_ids = array_map('trim', explode('&', $fig['id']));
                        
                        $caption_html = '<figcaption style="margin-top: 10px; font-style: italic;">';
                        
                        foreach ($fig['captions'] as $index => $caption_text) {
                            // Find the matching ID for this caption. 
                            $current_id = isset($individual_ids[$index]) ? $individual_ids[$index] : $fig['id'];
                            
                            $caption_html .= '<div style="margin-top: 5px; text-align: left;">';
                            // Print the bolded "Fig. X." prefix followed by the text
                            $caption_html .= '<strong>Fig. ' . esc_html($current_id) . '. </strong>' . wp_kses_post($caption_text);
                            $caption_html .= '</div>';
                        }
                        
                        $caption_html .= '</figcaption>';
                    }

                    // Build the full Figure HTML block
                    $figure_html = '<figure class="abstract-figure" style="text-align: center; margin: 35px auto; width: 100%;">';
                    $figure_html .= '<img src="' . esc_url($image_dir_url . $fig['filename']) . '" style="max-width: 100%; object-fit: contain; height: 300px; margin: 0 auto;" alt="Figure ' . esc_attr($fig['id']) . '">';
                    $figure_html .= $caption_html;
                    $figure_html .= '</figure>';

                    // Normalize and prepare the ID for Regex matching
                    $safe_id = html_entity_decode($fig['id'], ENT_QUOTES, 'UTF-8'); 
                    $safe_id = preg_quote($safe_id, '/');                           
                    $safe_id = str_replace(' ', '\s*', $safe_id);                   
                    $safe_id = str_replace('&', '(?:\&|&amp;)', $safe_id);          

                    $pattern = '/\{\{\s*FIGURE_' . $safe_id . '\s*\}\}/i';
                    $content = preg_replace_callback($pattern, function($matches) use ($figure_html) {
                        return $figure_html;
                    }, $content);
                }

                // --- 2. REPLACE TABLE PLACEHOLDERS ---
                $tables = json_decode($abstract->tables_json, true) ?: [];
                foreach ($tables as $tbl) {
                    $table_html = '<div class="abstract-table-wrapper" style="margin: 35px 0; overflow-x: auto;">';
                    
                    if (!empty($tbl['caption'])) {
                        $table_html .= '<div style="margin-bottom: 10px; font-weight: bold;">Table ' . esc_html($tbl['id']) . '. ' . wp_kses_post($tbl['caption']) . '</div>';
                    }
                    
                    $table_html .= $tbl['table_content'] ?? ''; 
                    $table_html .= '</div>';

                    // Normalize and prepare the ID for Regex matching
                    $safe_id = html_entity_decode($tbl['id'], ENT_QUOTES, 'UTF-8');
                    $safe_id = preg_quote($safe_id, '/');
                    $safe_id = str_replace(' ', '\s*', $safe_id);
                    $safe_id = str_replace('&', '(?:\&|&amp;)', $safe_id);

                    $pattern = '/\{\{\s*TABLE_' . $safe_id . '\s*\}\}/i';
                    
                    // ---> FIXED: Using preg_replace_callback so math inside tables doesn't get destroyed! <---
                    $content = preg_replace_callback($pattern, function($matches) use ($table_html) {
                        return $table_html;
                    }, $content);
                }

                echo $content; 
                ?>
            </div>

            <?php
            $acknowledgements = $abstract->acknowledgements;
            if (!empty($acknowledgements)){ ?>
                <p style="font-size: 0.9em; width: 100%; text-align: justify;">
                    <strong>Acknowledgments:</strong> <?php echo wp_kses_post($abstract->acknowledgements); ?>
                </p>
            <?php } ?>
                
            <?php 
            $references = json_decode($abstract->references_json, true) ?: [];
            if (!empty($references)){ ?>
                <hr style="border: 0; border-top: 1px solid #aaa; margin: 0 0 30px 0;" />
                <?php for ($i = 0; $i < count($references); $i++){ ?>
                    <p style="font-size: 0.9em; width: 100%; text-align: justify;">
                        <?php echo sprintf('[%d] ', $i + 1) . wp_kses_post($references[$i]); ?>
                    </p>
            <?php }} ?>

        <?php else: ?>
            <h1>404 - Abstract Not Found</h1>
            <p>The requested presentation could not be found.</p>
        <?php endif; ?>
    </main>

    <?php
    // 5. Load the Sidebar (PDF Viewer)
    // DEBUGGING MODE
    $debug_sidebar = current_user_can('manage_options'); // Set to false to hide the sidebar
    $debug_sidebar = false; // Set true to show sidebar or comment to show only for admins
    if ($debug_sidebar) {
    ?>
        <!-- ADD js to make abstract-page-wrapper wider -->
        <style>
            .abstract-page-wrapper {
                max-width: 2140px !important;
            }
        </style>
        <!-- THE DEBUG SIDEBAR -->
        <aside class="abstract-sidebar" style="width: 1200px; flex-shrink: 0;">
            <?php
            $pdf_url = content_url('/uploads/abstracts/' . $year . '/pdf/' . $id . '.pdf');
            ?>
            <iframe src="<?php echo esc_url($pdf_url); ?>#toolbar=0&zoom=125%" style="width: 100%; height: 100vh; border: none;"></iframe>
        </aside>
    <?php } else { ?>
        <!-- SIDEBAR HIDDEN -->
        <aside class="abstract-sidebar" style="width: 200px; flex-shrink: 0; margin: 0; padding: 0;">
            <?php 
            // Check if Elementor is active and the template shortcode engine works
            if (shortcode_exists('elementor-template')) {
                // Replace '1234' with the actual ID of your Elementor Saved Section/Widget Template
                echo do_shortcode('[elementor-template id="17967"]'); 
            }
            ?>
        </aside>
    <?php } ?>

</div>

<?php 
// 4. Load the Elementor/Theme Footer
get_footer(); 
?>

<script>
document.addEventListener('keydown', function(e) {
    // Check for Ctrl + Q (or Cmd + Q on Mac)
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'q') {
        e.preventDefault(); 

        // 1. Instant Visual Feedback
        document.body.style.opacity = '0.5';
        document.body.style.pointerEvents = 'none';

        // 2. Prepare the AJAX payload (No security nonces needed)
        const data = new FormData();
        data.append('action', 'hide_broken_abstract');
        data.append('year', '<?php echo esc_js($year); ?>');
        data.append('id', '<?php echo esc_js($id); ?>');

        // 3. Send to WordPress
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                // 1. Flash success UI - Mega thick border with a pulsating neon red glow
                document.body.style.border = '20px solid #ef4444'; 
                document.body.style.boxShadow = 'inset 0 0 100px rgba(239, 68, 68, 0.8)';
                document.body.style.transition = 'all 0.1s ease-in-out';
                document.body.style.opacity = '1';

                // 2. Add a giant, animated watermark
                const watermark = document.createElement('div');
                watermark.innerText = 'ABSTRACT HIDDEN';
                
                // THE FIX: Combined translate and rotate into one string so it stays perfectly centered!
                // Added a dramatic scale-in animation so it slams down onto the screen.
                watermark.style.cssText = `
                    position: fixed; 
                    top: 50%; 
                    left: 50%; 
                    transform: translate(-50%, -50%) rotate(-35deg) scale(0.5); 
                    font-size: 12rem; 
                    color: #ef4444; 
                    font-weight: 900; 
                    font-family: 'Impact', 'Arial Black', sans-serif;
                    text-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
                    z-index: 9999; 
                    opacity: 0.9; 
                    pointer-events: none;
                    white-space: nowrap;
                    letter-spacing: 4px;
                    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                `;
                
                document.body.appendChild(watermark);
                
                // Force a reflow to trigger the slam-down animation
                requestAnimationFrame(() => {
                    watermark.style.transform = 'translate(-50%, -50%) rotate(-35deg) scale(1)';
                });

                // 3. Re-enable pointer events so you can click "Next"
                document.body.style.pointerEvents = 'auto';
            } else {
                // Keep your fallback error state intact
                alert('Error: Could not hide abstract.');
                document.body.style.opacity = '1';
                document.body.style.pointerEvents = 'auto';
            }
        });
    }
});
</script>