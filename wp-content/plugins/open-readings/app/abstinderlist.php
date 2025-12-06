<?php
// Make sure this only runs inside WordPress
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REGISTER THE ENDPOINT
 * URL: /wp-json/mypage/v1/abstracttinder
 */
add_action( 'rest_api_init', function () {

    register_rest_route(
        'mypage/v1',                 // <-- namespace
        '/abstracttinder',          // <-- route
        array(
            'methods'             => 'GET',
            'callback'            => 'or_get_abstracttinder_data',
            'permission_callback' => '__return_true', // public endpoint
        )
    );

} );

/**
 * CALLBACK: return APP_Abs_Tin_list rows as JSON
 */
function or_get_abstracttinder_data( WP_REST_Request $request ) {

    global $wpdb;

    // IMPORTANT: your table name exactly as in phpMyAdmin
    $table_name = 'APP_Abs_Tin_list';

    // Optional ?q=Beer filter on "name" column
    $keyword = $request->get_param( 'q' );

    if ( $keyword ) {
        // Safe LIKE search: %Beer%
        $like   = '%' . $wpdb->esc_like( $keyword ) . '%';
        $sql    = $wpdb->prepare(
            "SELECT id, name, shortABS, linkPDF, contact, image, keywords, researchArea, subarea
             FROM {$table_name}
             WHERE name LIKE %s",
            $like
        );
    } else {
        // No filter: return all rows
        $sql = "SELECT id, name, shortABS, linkPDF, contact, image, keywords, researchArea, subarea
                FROM {$table_name}";
    }

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    if ( $rows === null ) {
        // Database error
        return new WP_Error(
            'db_error',
            'Database error when reading APP_Abs_Tin_list',
            array( 'status' => 500, 'mysql_error' => $wpdb->last_error )
        );
    }

    // Return JSON
    return rest_ensure_response( $rows );
}
