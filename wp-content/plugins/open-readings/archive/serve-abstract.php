<?php

// 1. Register the custom URL patterns
add_action('init', function() {
    // RULE 1: Main conference book (DO NOT CHANGE - LEFT UNTOUCHED)
    add_rewrite_rule('^archive/([0-9]{4})/pdf/?$', 'index.php?archive_year=$matches[1]&serve_pdf=1', 'top');

    // RULE 2: Pupils' session book (Strict clean URL pattern)
    add_rewrite_rule('^archive/([0-9]{4})/moksleiviu-sesija/pdf/?$', 'index.php?archive_year=$matches[1]&serve_pupils_pdf=1', 'top');
});

// 2. Register the query variables so WordPress recognizes them
add_filter('query_vars', function($vars) {
    $vars[] = 'archive_year';
    $vars[] = 'serve_pdf';
    $vars[] = 'serve_pupils_pdf'; // Added for the new rule
    return $vars;
});

// 3. Catch the request and stream the PDF securely
add_action('template_redirect', function() {
    $year = intval(get_query_var('archive_year'));
    $serve_pdf = get_query_var('serve_pdf');
    $serve_pupils_pdf = get_query_var('serve_pupils_pdf');

    if (!$year) {
        return;
    }

    // --- CASE 1: MAIN CONFERENCE BOOK (DO NOT CHANGE - LEFT UNTOUCHED) ---
    if ($serve_pdf) {
        $pdf_path = WP_CONTENT_DIR . "/abstracts/abstract_book_{$year}.pdf";

        if (file_exists($pdf_path)) {
            while (ob_get_level() > 0) {
                if (!ob_end_clean()) { break; }
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Open_Readings_' . $year . '_Abstract_Book.pdf"');
            header('Content-Length: ' . filesize($pdf_path));
            header('Cache-Control: public, max-age=31536000');
            header('Pragma: public');
            readfile($pdf_path);
            exit;
        }
    }

    // --- CASE 2: NEW PUPILS' SESSION BOOK ONLY ---
    if ($serve_pupils_pdf) {
        // Looks directly in: wp-content/archive/YEAR/moksleiviu-sesija/pdf/moksleiviu_sesija_YEAR.pdf
        $pdf_path = WP_CONTENT_DIR . "/archive/{$year}/moksleiviu-sesija/pdf/moksleiviu_sesija_{$year}.pdf";

        if (file_exists($pdf_path)) {
            // Safe buffer clearing
            while (ob_get_level() > 0) {
                if (!ob_end_clean()) { break; }
            }

            // Stream file with clean standard download name: moksleiviu_sesija_YEAR.pdf
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="moksleiviu_sesija_' . $year . '.pdf"');
            header('Content-Length: ' . filesize($pdf_path));
            header('Cache-Control: public, max-age=31536000');
            header('Pragma: public');
            
            readfile($pdf_path);
            exit;
        }
    }

    // If a rule matched but the file wasn't on disk (or they tried to type /pdf/something.pdf), drop cleanly to 404
    if ($serve_pdf || $serve_pupils_pdf) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part('404');
        exit;
    }
});