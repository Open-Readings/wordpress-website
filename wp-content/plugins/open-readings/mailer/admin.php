<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


class ORmailerAdmin
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('admin_menu', array($this, 'add_admin_pages'));

        add_action('admin_init', array($this, 'add_option_groups'));

    }


    function add_admin_pages()
    {

        add_menu_page('Open Readings Mailer', 'OR mailer', 'manage_or_mailer', 'or_mailer', array($this, 'admin_index'), 'dashicons-email', 2);
        if (current_user_can('manage_options')) {
            add_submenu_page('or_mailer', 'or_mailer_settings', 'OR Mailer settings', 'manage_options', 'or_mailer_settings', array($this, 'admin_settings'), 2);
        }

    }



    function add_option_groups()
    {
        register_setting('or_mailer_options', 'or_mailer_api_key');
    }

    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/mailer-admin.php';
    }

    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/mailer-settings.php';
    }

    public function init()
    {


    }

}

?>