<?php

function download_session_zip(){
    if (isset($_GET['page']) && $_GET['page'] === 'or_programme') {
        if (isset($_POST['download'])){
            $session = $_POST['download'];
            $presentations = get_presentation_path_array($session);
            $zip = new ZipArchive();
            $zip_file = $session . '.zip';
    
            if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
                return false;
            }
            $zip->addEmptyDir($session);
    
            foreach($presentations as $presentation){
                $ext = pathinfo(basename($presentation['path']), PATHINFO_EXTENSION);
                $zip->addFile($presentation['path'], $session . '/' . $presentation['name'] . "." . $ext);
            }
            
            $zip->close();
            // Set headers to initiate download
            header('Content-disposition: attachment; filename=' . $zip_file);
            header('Content-Type: application/zip');
            readfile("$zip_file");
            unlink($zip_file);
            // Exit to prevent any further output
            exit;
        }
        
    }
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
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id FROM wp_postmeta WHERE meta_key = 'short_title' AND meta_value = '%s'",
        $session
    ));
    $session_ids = array();
    foreach($results as $result){
        $session_ids[] = $result->post_id;
    }
    $session_id_string = implode(',', $session_ids);

   
    $results = $wpdb->get_results("SELECT post_id FROM wp_postmeta WHERE meta_key = 'presentation_session' AND meta_value IN ($session_id_string) GROUP BY post_id");
    $presentation_ids = array();
    foreach($results as $result){
        $presentation_ids[] = $result->post_id;
    }

    $presentation_id_string = implode(',', $presentation_ids);

    // Get array of presentation post ids
    $results = $wpdb->get_results("SELECT meta_value FROM wp_postmeta WHERE meta_key = 'hash_id' AND post_id IN ($presentation_id_string) GROUP BY meta_value");
    $hash_ids = array();
    foreach($results as $result){
        $hash_ids[] = $result->meta_value;
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