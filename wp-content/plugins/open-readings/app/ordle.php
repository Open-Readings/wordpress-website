<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

function get_ordle_daily_word(WP_REST_Request $request) {
  $api_key = $request->get_header('X-API-Key');
  $valid_key = get_option('ordle_api_key');
  $file_name = get_option('or_ordle_word_file');

  if ($valid_key === false || $file_name === false) {
      return new WP_Error('internal_error', 'API key or word file not set', array('status' => 500));
  }

  if ($api_key !== $valid_key) {
      return new WP_Error('unauthorized', 'Invalid API key', array('status' => 401));
  }

  // Step 1: Remove newlines and extra spaces
  $string = stripslashes(get_option('or_wordle_list'));
  $string = str_replace(["\r", "\n"], '', $string); // Remove newlines
  $string = preg_replace('/\s+/', ' ', $string); // Replace multiple spaces with a single space

  // Step 2: Remove double quotes and trim the string
  $string = str_replace('"', '', $string); // Remove double quotes
  $string = trim($string, ', '); // Remove leading/trailing commas and spaces

  // Step 3: Split the string into an array using comma as the delimiter
  $array = explode(',', $string);

  // Step 4: Trim each element in the array
  $array = array_map('trim', $array);

  $day_of_year = date('z');
  $word_index = $day_of_year % count($array);


  $word = $array[$word_index];

  return rest_ensure_response(array(
      'word' => $word,
  ));
}

function rate_presenter(WP_REST_Request $request) {
  // Get parameters
  $password = $request->get_param('password');
  $hash_id = $request->get_param('hash_id');
  $function = $request->get_param('function');
  
  // Validate password (replace 'your-secret-word' with your actual password)
  if ($password !== get_option('or_rating_pass')) {
      return new WP_Error('unauthorized', 'Invalid password', array('status' => 401));
  }
  
  // Validate user exists
  global $wpdb;
  $resut = $wpdb->get_row("SELECT * FROM wp_or_registration_evaluation WHERE evaluation_hash_id='$hash_id'");
  if (!$resut) {
      return new WP_Error('invalid_user', 'User not found', array('status' => 404));
  }
  
  # Get the rating
  $rating = $wpdb->get_var("SELECT rating FROM wp_or_registration_evaluation WHERE evaluation_hash_id='$hash_id'");

  if ($function == 'add') {
      $rating++;
  } elseif ($function == 'remove') {
      $rating--;
  } elseif ($function == 'get') {
      return new WP_REST_Response(array(
          'success' => true,
          'rating' => $rating,
      ), 200);
  } else {
      // Invalid function
      return new WP_Error('invalid_function', 'Invalid function', array('status' => 400));
  }

  $wpdb->update('wp_or_registration_evaluation', array('rating' => $rating), array('evaluation_hash_id' => $hash_id));

  return new WP_REST_Response(array(
      'success' => true,
  ), 200);
}

function register_daily_word_endpoint() {
  register_rest_route('dailyword/v1', '/word/', array(
      'methods' => 'GET',
      'callback' => 'get_ordle_daily_word',
  ));
}

function register_rating_endpoint() {
  register_rest_route('rating/v1', '/presenter/', array(
      'methods' => 'POST',
      'callback' => 'rate_presenter',
      'permission_callback' => '__return_true' // Public endpoint

  ));
}