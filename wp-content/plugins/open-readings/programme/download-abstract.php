<?php
use OpenReadings\Registration\ORLatexExport;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function download_or_abstracts($hash_ids, $download_figures){
    if (!headers_sent()) {
        header_remove(); // Clear all existing headers
        while (ob_get_level()) ob_end_clean();
    } else {
        die('Headers already sent - check for whitespace or early output');
    }
    $template = stripslashes(get_option('abstract-book-page-template'));
    $hash_ids = explode("\n", $hash_ids);
    $hash_ids = array_map('trim', $hash_ids);
    $include_list = "";

    // Create a temporary directory
    $upload_dir = wp_upload_dir();
    $tempDir = $upload_dir['basedir'] . '/tex_temp_' . uniqid();

    if (!wp_mkdir_p($tempDir)) {
        die("Failed to create temp directory: " . esc_html($tempDir));
    }
    if (!is_writable($upload_dir['basedir'])) {
        die("Uploads directory not writable");
    }

    if ($download_figures) {
        $figures_dir = $upload_dir['basedir'] . '/figures';
        if (!is_dir($figures_dir)) {
            wp_mkdir_p($figures_dir);
        }
    }

    foreach ($hash_ids as $hash_id) {
        $or_registration = new OpenReadingsRegistration();
        $data = $or_registration->get($hash_id);
        if (is_wp_error($data)) {
            error_log("Failed to get data for hash_id: $hash_id");
            continue;
        }
        $or_export = new ORLatexExport($data);

        $args = array(
            'post_type'  => 'presentation', // or specify your post type
            'meta_query' => array(
                array(
                    'key'   => 'hash_id',
                    'value' => $data->hash_id,
                ),
            ),
            'posts_per_page' => 1,
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $query->the_post();
            
            // Get the presentation_session value
            $presentation_session = get_post_meta(get_the_ID(), 'presentation_session', true);
            $session_name = get_post_meta(get_the_ID(), 'session_name', single: true);
            $number = get_post_meta(get_the_ID(), 'poster_number', single: true);
            $type = get_post_meta($presentation_session, 'session_type', single: true);
            $time = get_post_meta(get_the_ID(), 'presentation_start', single: true);
            
            // Reset post data
            wp_reset_postdata();
        } else {
            // No posts found
            continue;
        }

        if ($type == "oral") {
            $args = array(
                'post_type' => 'presentation',
                'meta_key' => 'presentation_start',  // Sort by start time
                'orderby' => 'meta_value',           // Order numerically
                'order' => 'ASC',                    // Earliest first
                'meta_query' => array(
                    array(
                        'key' => 'presentation_session',
                        'value' => $presentation_session,
                    ),
                ),
                'posts_per_page' => -1,
            );

            $presentations = new WP_Query($args);
            $sorted_posts = $presentations->posts;
            
            // Find which presentation starts at $time
            $number = 0;
            foreach ($sorted_posts as $index => $post) {
                $start_time = get_post_meta($post->ID, 'presentation_start', true);
                if ($start_time == $time) {
                    $number = $index + 1;  // +1 because arrays start at 0
                    break;
                }
            }
        }

        $authors_text_list = "";
        for ($i = 0; $i < count($data->authors); $i++) {
            $author = $data->authors[$i];
            $authors_text_list .= $author[0];
            if ($i < count($data->authors) - 1) {
                $authors_text_list .= ", ";
            }
        }

        $replacements = array(
            '${title}' => $or_export->generate_title(),
            '${authors}' => $or_export->generate_authors(),
            '${authors_list}' => $authors_text_list,
            '${affiliations}' => $or_export->generate_affiliations(),
            '${content}' => $or_export->generate_content(),
            '${acknowledgement}' => $or_export->generate_acknowledgement(),
            '${references}' => $or_export->generate_references(),
            '${research_area}' => $data->research_area,
            '${presentation_session}' => $presentation_session,
            '${session_name}' => $session_name,
            '${number}' => $number,
        );

        $figures = $data->images;

        foreach ($figures as $figure) {
            $figure_name = basename($figure);
            $replacements['${content}'] = str_replace("{" . $figure . "}", "{" . "$session_name-$number-$figure_name" . "}", $replacements['${content}']);
        }

        if ($download_figures) {
            $figure_path = WP_CONTENT_DIR . '/latex/perm/' . $data->session_id . '/images/';
            foreach ($figures as $figure) {
                $figure_name = basename($figure);
                $figure_path = WP_CONTENT_DIR . '/latex/perm/' . $data->session_id . '/images/' . $figure_name;
                if (file_exists($figure_path)) {
                    copy($figure_path, "$figures_dir/$session_name-$number-$figure_name");
                } else {
                    error_log("Figure not found: $figure_path");
                }
            }
        }

        $tex_content = $template;
        foreach ($replacements as $key => $value) {
            $tex_content = str_replace($key, $value, $tex_content);
        }
        $include_list .= '\include{abstracts/' . "$session_name-$number-$data->first_name-$data->last_name.tex" . '}' . "\n";
            // Save to .tex file
        $filename = "$session_name-$number-$data->first_name-$data->last_name.tex";
        file_put_contents("$tempDir/$filename", $tex_content);

    }

    file_put_contents("$tempDir/abstracts.tex", $include_list);
    
    $zip = new ZipArchive();
    $zipFilename = $upload_dir['basedir'] . '/presentations_' . time() . '.zip';

    if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
        // Add files to ZIP
        $files = glob("$tempDir/*.tex");
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        if ($download_figures) {
            // Add figures to ZIP
            $figures = glob("$figures_dir/*");
            foreach ($figures as $figure) {
                $zip->addFile($figure, 'figures/' . basename($figure));
            }
        }

        $zip->close();
        
        // Clean up temp files (optional)
        array_map('unlink', array_filter((array)glob("$tempDir/*")));
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
        
        // Send ZIP to browser
        if (file_exists($zipFilename)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
            header('Content-Length: ' . filesize($zipFilename));
            header('Pragma: no-cache');
            
            if (readfile($zipFilename) === FALSE) {
                error_log("Failed to stream file");
            }
            unlink($zipFilename);
            exit;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            die("Generated file not found");
        }
    } else {
        die("Failed to create ZIP file");
    }

}