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

  $words = json_decode(file_get_contents(__DIR__ . '/' . $file_name));
  if ($words === null) {
      return new WP_Error('internal_error', 'Failed to read word file', array('status' => 500));
  }
  $day_of_year = date('z');
  $word_index = $day_of_year % count($words);
  $word = $words[$word_index];

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