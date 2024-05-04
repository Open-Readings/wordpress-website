<?php

function get_upcoming_sessions()
{
    $time = new DateTime();
    $args = array(
        'post_type' => 'session',
        'posts_per_page' => 10,
        'meta_key' => 'session_start',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'session_start',
                'value' => $time->format('Y-m-d H:i:s'),
                'compare' => '>=',
                'type' => 'DATETIME'
            )
        )
    );

    $session_posts = get_posts($args);
    // Include ACF data for each session post
    foreach ($session_posts as $post) {
        setup_postdata($post);
        $post->acf = get_fields($post->ID); // Retrieves all ACF fields for the post
    }
    wp_reset_postdata();

    return $session_posts;




}