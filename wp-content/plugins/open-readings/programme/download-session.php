<?php

function download_session_zip() {
    // Basic check for your admin page
    if (!isset($_GET['page']) || $_GET['page'] !== 'or_programme') return;
    
    // Check if download was requested
    if (!isset($_POST['download'])) return;
    
    $session = sanitize_text_field($_POST['download']);
    $presentations = get_presentation_path_array($session);
    
    // Create ZIP in system temp directory
    $zip_file = sys_get_temp_dir() . '/' . $session . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die("Failed to create ZIP file");
    }
    
    $zip->addEmptyDir($session);
    
    foreach ($presentations as $presentation) {
        if (file_exists($presentation['path'])) {
            $ext = pathinfo($presentation['path'], PATHINFO_EXTENSION);
            $zip->addFile(
                $presentation['path'],
                $session . '/' . sanitize_file_name($presentation['name']) . '.' . $ext
            );
        }
    }
    
    $zip->close();
    
    // Clear any previous output
    if (ob_get_level()) ob_end_clean();
    
    // Send headers and file
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $session . '.zip"');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    
    // Clean up
    unlink($zip_file);
    exit;
}

function wp_url_to_path($url) {
    // Get the base URL and path for uploads
    $base_url = get_site_url() . '/';
    $base_path = ABSPATH;
    // Check if the URL belongs to the uploads directory
    if (strpos($url, $base_url) === false) {
        // URL doesn't belong to uploads directory
        return false;
    }

    // Replace the base URL with the base path to get the file path
    $file_path = str_replace($base_url, $base_path, $url);

    // Check if the file exists
    if (file_exists($file_path)) {
        return $file_path;
    } else {
        return false;
    }
}

function get_presentation_path_array(string $session){
    global $wpdb;

    $presentation_args = array(
        'post_type' => 'presentation',
        'posts_per_page' => -1,  // Get all matching posts
        'meta_query' => array(
            array(
                'key' => 'presentation_session',  // Meta key that stores session reference
                'value' => $session,  // The session ID you're filtering by
                'compare' => '='
            )
        ),
        'fields' => 'ids'  // Only get post IDs for better performance
    );
    
    $presentation_posts = get_posts($presentation_args);
    
    // Now collect all hash_ids from these presentations
    $hash_ids = array();
    
    foreach ($presentation_posts as $post_id) {
        $hash_id = get_post_meta($post_id, 'hash_id', true);
        if ($hash_id) {
            $hash_ids[] = $hash_id;
        }
    }

    foreach($hash_ids as $hash_id){
        $result = $wpdb->get_row("SELECT presentation FROM wp_or_presentation WHERE hash_id = '$hash_id'");
        if ($result->presentation != NULL){
            $path = wp_url_to_path($result->presentation);
            $result = $wpdb->get_row("SELECT first_name, last_name FROM wp_or_registration WHERE hash_id = '$hash_id'");
            $name = $result->first_name . ' ' . $result->last_name;
            $path_array[] = array('path' => $path, 'name' => $name);
        }
    }
    
    return $path_array;
}