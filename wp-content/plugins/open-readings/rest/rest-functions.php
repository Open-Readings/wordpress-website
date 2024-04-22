<?php

function get_upcoming_sessions(){
    $time = new DateTime();
    global $wpdb;
    $session_post_id = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_type = 'session' GROUP BY ID");
       
    $session_id_array = array();
    foreach ($session_post_id as $print_id) {
        $session_id_array[] = $print_id->ID;
    }

    $session_id_string = implode(',', $session_id_array);
    $results = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN ($session_id_string)");
    // Initialize an array to store the post data
    $session_post_data = array();

    // Organize the data into a 2D array
    foreach ($results as $result) {
        $post_id = $result->post_id;
        $meta_key = $result->meta_key;
        $meta_value = $result->meta_value;
        
        $session_post_data[$post_id][$meta_key] = $meta_value;
    }
    

    foreach ($session_post_data as $id => $data){
        if (!isset($data['session_start']) or $data['session_start'] == '' or DateTime::createFromFormat('Y-m-d H:i:s', $data['session_start']) < $time){
            unset($session_post_data[$id]);
        }
    }
    function sortBySessionStart($a, $b) {
        // Check if 'session_start' key exists in both arrays
        if (isset($a['session_start'], $b['session_start'])) {
            return strtotime($a['session_start']) <=> strtotime($b['session_start']); // Compare session start times
        } elseif (isset($a['session_start'])) {
            return -1; // $a has 'session_start' key, $b doesn't, so $a comes first
        } elseif (isset($b['session_start'])) {
            return 1; // $b has 'session_start' key, $a doesn't, so $b comes first
        } else {
            return 0; // Neither array has 'session_start' key, consider them equal
        }
    }
    
    // Sort the array using the custom comparison function
    usort($session_post_data, 'sortBySessionStart');

    $session_post_data = array_slice($session_post_data, 0, 10);

    return $session_post_data;

}