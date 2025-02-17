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

function register_daily_word_endpoint() {
  register_rest_route('dailyword/v1', '/word/', array(
      'methods' => 'GET',
      'callback' => 'get_ordle_daily_word',
  ));
}