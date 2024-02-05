<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


class OREvaluationAdmin
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('admin_menu', array($this, 'add_admin_pages'));

        include_once plugin_dir_path(__FILE__) . 'admin/registration-functions.php';


        add_action('wp_ajax_evaluation', 'evaluation');
    }








    // Add more action registrations as needed


    function add_admin_pages()
    {

        add_menu_page('Open Readings Evaluation', 'OR evaluation', 'manage_options', 'or_evaluation', array($this, 'admin_index'), 'dashicons-trash', 6);
        if (current_user_can('manage_options')) {
            add_submenu_page('or_evaluation', 'or_evaluation_settings', 'OR evaluation settings', 'manage_options', 'or_evaluation_settings', array($this, 'admin_settings'), 2);
        }


    }



    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/evaluation.php';
    }

    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/list.php';
    }

    public function init()
    {


    }

}





?>