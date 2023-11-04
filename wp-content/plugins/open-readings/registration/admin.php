<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


class ORregistrationAdmin
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('admin_menu', array($this, 'add_admin_pages'));

        add_action('admin_init', array($this, 'add_option_groups'));
    }


    function add_admin_pages()
    {

        add_menu_page('Open Readings Registration', 'OR registration', 'manage_or_registration', 'or_registration', array($this, 'admin_index'), 'dashicons-index-card', 1);
        if (current_user_can('manage_options')) {
            add_submenu_page('or_registration', 'or_registration_settings', 'OR registration settings', 'manage_options', 'or_registration_settings', array($this, 'admin_settings'), 2);
        }

    }



    function add_option_groups()
    {
        register_setting('or_registration', 'or_registration_start');
        register_setting('or_registration', 'or_registration_end');
        register_setting('or_registration', 'or_registration_update_end');
        register_setting('or_registration', 'or_registration_email_subject');
        register_setting('or_registration', 'or_registration_email_success_template');
        register_setting('or_registration', 'or_registration_email_update_template');

        register_setting('or_registration_advanced', 'or_registration_database_table');
        register_setting('or_registration_advanced', 'or_registration_max_images');
    }

    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/registration_admin.php';
    }

    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/registration_settings.php';
    }

    public function init()
    {


    }

}





?>