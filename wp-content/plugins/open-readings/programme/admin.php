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
            'menu_icon' => 'dashicons-calendar-alt',
            'taxonomies' => array('category', 'post_tag'),
        );
        register_post_type('session', $args);
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(
                array(
                    'key' => 'session_group', // Unique key for the field group
                    'title' => 'Session Fields',
                    'fields' => array(
                        array(
                            'key' => 'session_short_title_field',
                            'label' => 'Short Title',
                            'name' => 'short_title',
                            'type' => 'text',
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
                                'special_event' => 'Special Event',
                                'Speaker' => 'Speaker',
                                'Break' => 'Break',
                            ),
                        ),
                        array(
                            'key' => 'session_start_field',
                            'label' => 'Session Start',
                            'name' => 'session_start',
                            'type' => 'date_time_picker',
                        ),
                        array(
                            'key' => 'session_end_field',
                            'label' => 'Session End',
                            'name' => 'session_end',
                            'type' => 'date_time_picker',
                        ),
                        array(
                            'key' => 'session_moderator_field',
                            'label' => 'Session Moderator',
                            'name' => 'session_moderator',
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
        echo 'Adding presentation post type';
        $args = array(
            'public' => true,
            'label' => 'Presentations',
            'supports' => array('title', 'custom-fields'),
            'taxonomies' => array('category', 'post_tag'),
            'menu_icon' => 'dashicons-megaphone',
            'show_in_rest' => true,
            'show_in_menu' => true,
            'in_menu' => true,

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
                        ),
                        array(
                            'key' => 'field_10',
                            'label' => 'Presentation End',
                            'name' => 'presentation_end',
                            'type' => 'date_time_picker',
                        ),
                        array(
                            'key' => 'field_11',
                            'label' => 'Poster Number',
                            'name' => 'poster_number',
                            'type' => 'number',
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
    }

    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/programme-manager.php';
    }

    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/programme-settings.php';
    }

    public function init()
    {
        $this->add_presentation_post_type();
        $this->add_session_post_type();

    }



}




?>