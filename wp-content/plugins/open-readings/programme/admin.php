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


    }



}

?>