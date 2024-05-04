<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


class ORProgrammeAdmin
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('admin_menu', array($this, 'add_admin_pages'));

        add_filter('manage_session_posts_columns', array($this, 'set_custom_edit_session_columns'));
        add_action('manage_session_posts_custom_column', array($this, 'custom_session_column'), 10, 2);

    }

    function set_custom_edit_session_columns($columns)
    {
        $columns['session_start'] = 'Session Start - End';
        return $columns;
    }

    function custom_session_column($column, $post_id)
    {
        switch ($column) {
            case 'session_start':
                $start = get_post_meta($post_id, 'session_start', true);
                $end = get_post_meta($post_id, 'session_end', true);
                $day = date('d/m/Y', strtotime($start));
                $start = date('H:i', strtotime($start));
                $end = date('H:i', strtotime($end));
                echo $day . ' ' . $start . ' - ' . $end;
                break;

        }
    }



    function add_session_post_type()
    {

        $args = array(
            'label' => __('Session', 'your_text_domain'),
            'description' => __('Session Description', 'your_text_domain'),
            'supports' => array('title', 'custom-fields'),
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'in_menu' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'taxonomies' => array('category', 'post_tag'),
        );
        register_post_type('session', $args);
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(
                array(
                    'key' => 'session_group', // Unique key for the field group
                    'title' => 'Session Fields',
                    'show_in_rest' => true,
                    'fields' => array(
                        array(
                            'key' => 'session_short_title_field',
                            'label' => 'Short Title',
                            'name' => 'short_title',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'session_description_field',
                            'label' => 'Description',
                            'name' => 'description',
                            'type' => 'text',

                        ),
                        array(
                            'key' => 'invited_speaker_reference',
                            'label' => 'Invited Speaker',
                            'name' => 'invited_speaker',
                            'type' => 'post_object',
                            'post_type' => 'invited_speaker',
                        ),
                        array(
                            'key' => 'session_link_field',
                            'label' => 'Link',
                            'name' => 'link',
                            'type' => 'url',
                        ),
                        array(
                            'key' => 'session_display_title_field',
                            'label' => 'Display Title',
                            'name' => 'display_title',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'session_type_field',
                            'label' => 'Session Type',
                            'name' => 'session_type',
                            'type' => 'select',
                            'choices' => array(
                                'oral' => 'Oral',
                                'poster' => 'Poster',
                                'workshop' => 'Workshop',
                                'sponsor' => 'Sponsor',
                                'special_event' => 'Special Event',
                                'speaker' => 'Speaker',
                                'break' => 'Break',
                                'other' => 'Other'
                            ),
                        ),
                        array(
                            'key' => 'session_start_field',
                            'label' => 'Session Start',
                            'name' => 'session_start',
                            'type' => 'date_time_picker',
                            'display_format' => 'd/m/Y H:i', // Date in 'd/m/Y' format and time in 'H:i:s' format
                            'return_format' => 'd/m/Y H:i',
                        ),
                        array(
                            'key' => 'session_end_field',
                            'label' => 'Session End',
                            'name' => 'session_end',
                            'type' => 'date_time_picker',
                            'display_format' => 'd/m/Y H:i', // Date in 'd/m/Y' format and time in 'H:i:s' format
                            'return_format' => 'd/m/Y H:i',
                        ),
                        array(
                            'key' => 'session_moderator_field',
                            'label' => 'Session Moderator',
                            'name' => 'session_moderator',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'location_field',
                            'label' => 'Location',
                            'name' => 'location',
                            'type' => 'text',
                        ),


                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'session',
                            ),
                        ),
                    ),
                )
            );

        }
    }

    function add_presentation_post_type()
    {
        $args = array(
            'public' => true,
            'label' => 'Presentations',
            'supports' => array('title', 'custom-fields'),
            'taxonomies' => array('category', 'post_tag'),
            'menu_icon' => 'dashicons-megaphone',
            'show_in_rest' => true,
            'show_in_menu' => true,
            'in_menu' => true,
            'query_var' => true

            // Add other arguments as needed
        );
        $result = register_post_type('presentation', $args);
        if (is_wp_error($result)) {
            echo $result->get_error_message();
        }

        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(
                array(
                    'key' => 'presentation_details',
                    'title' => 'Presentation Details',
                    'show_in_rest' => true,
                    'fields' => array(
                        array(
                            'key' => 'field_1',
                            'label' => 'First Name',
                            'name' => 'first_name',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_2',
                            'label' => 'Last Name',
                            'name' => 'last_name',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_3',
                            'label' => 'Research Area',
                            'name' => 'research_area',
                            'type' => 'textarea',
                        ),
                        array(
                            'key' => 'field_4',
                            'label' => 'Presentation Title',
                            'name' => 'presentation_title',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_5',
                            'label' => 'Abstract PDF',
                            'name' => 'abstract_pdf',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_6',
                            'label' => 'Presentation Type',
                            'name' => 'presentation_type',
                            'type' => 'select',
                            'choices' => array(
                                'oral' => 'Oral',
                                'poster' => 'Poster',
                                // Add more types as needed
                            ),
                        ),
                        array(
                            'key' => 'field_7',
                            'label' => 'Hash ID',
                            'name' => 'hash_id',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_8',
                            'label' => 'Presentation Session',
                            'name' => 'presentation_session',
                            'type' => 'text',
                        ),
                        array(
                            'key' => 'field_9',
                            'label' => 'Presentation Start',
                            'name' => 'presentation_start',
                            'type' => 'date_time_picker',
                            'display_format' => 'Y-m-d H:i', // Date in 'd/m/Y' format and time in 'H:i:s' format
                            'return_format' => 'Y-m-d H:i',

                        ),
                        array(
                            'key' => 'field_10',
                            'label' => 'Presentation End',
                            'name' => 'presentation_end',
                            'type' => 'date_time_picker',
                            'display_format' => 'Y-m-d H:i', // Date in 'd/m/Y' format and time in 'H:i:s' format
                            'return_format' => 'Y-m-d H:i',

                        ),
                        array(
                            'key' => 'field_11',
                            'label' => 'Poster Number',
                            'name' => 'poster_number',
                            'type' => 'number',
                        ),
                        array(
                            'key' => 'field_12',
                            'label' => 'Session name',
                            'name' => 'session_name',
                            'type' => 'text',
                        ),
                    ),
                    'location' => array(
                        array(
                            array(
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'presentation',
                            ),
                        ),
                    ),
                )
            );
        }


    }

    function add_admin_pages()
    {
        add_menu_page('Open Readings Programme', 'OR programme', 'manage_programme', 'or_programme', array($this, 'admin_index'), 'dashicons-excerpt-view', 6);
        add_submenu_page('or_programme', 'or_programme_settings', 'OR programme settings', 'manage_programme', 'or_programme_settings', array($this, 'admin_settings'), 2);
        add_submenu_page('or_programme', 'or_programme_abstract', 'OR programme abstract', 'manage_programme', 'or_programme_abstract', array($this, 'admin_abstract'), 3);

    }

    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/programme-manager.php';
    }

    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/programme-settings.php';
    }

    function admin_abstract()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/programme-abstract.php';
    }

    public function init()
    {
        $this->add_presentation_post_type();
        $this->add_session_post_type();

    }



}




?>