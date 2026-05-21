<?php

// 1. Register the custom URL pattern
add_action('init', function() {
    add_rewrite_rule('^archive/([0-9]{4})/pdf/?$', 'index.php?archive_year=$matches[1]&serve_pdf=1', 'top');
});

// 2. Register the query variables so WordPress recognizes them
add_filter('query_vars', function($vars) {
    $vars[] = 'archive_year';
    $vars[] = 'serve_pdf';
    return $vars;
});

// 3. Catch the request and stream the PDF securely
add_action('template_redirect', function() {
    // 1. Clean the input variables
    $year = intval(get_query_var('archive_year'));
    $serve_pdf = get_query_var('serve_pdf');

    if ($year && $serve_pdf) {
        // 2. Use the definitive WordPress path constant
        $pdf_path = WP_CONTENT_DIR . "/abstracts/abstract_book_{$year}.pdf";

        if (file_exists($pdf_path)) {
            
            // 3. ULTRA-SAFE BUFFER CLEARING (Prevents the Critical Error)
            // Instead of wiping out buffers blindly, we discard them only if they are active.
            while (ob_get_level() > 0) {
                if (!ob_end_clean()) {
                    break; 
                }
            }

            // 4. Send the headers safely
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Open_Readings_' . $year . '_Abstract_Book.pdf"');
            header('Content-Length: ' . filesize($pdf_path));
            header('Cache-Control: public, max-age=31536000');
            header('Pragma: public');
            
            // 5. Stream the file and terminate immediately
            readfile($pdf_path);
            exit;
        } else {
            // If the file isn't on the server disk, drop cleanly to WP 404
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            get_template_part('404');
            exit;
        }
    }
});