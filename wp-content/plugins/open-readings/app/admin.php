<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}


class ORApp
{
    public function __construct()
    {

        add_action('admin_menu', array($this, 'add_admin_pages'));

        add_action('admin_init', array($this, 'add_option_groups'));
    }

    function add_admin_pages()
    {

        add_menu_page('Open Readings App', 'OR App', 'manage_options', 'or_app_admin', array($this, 'admin_index'), 'dashicons-smartphone', 1);

    }

    function add_option_groups()
    {
        register_setting('or_app', 'or_wordle_list');
    }

    function admin_index()
    {
        if (isset($_POST['or_wordle_list'])) {
            update_option('or_wordle_list', $_POST['or_wordle_list']);
        }

        echo '<h1>Open Readings App</h1>';
        echo '<form method="post">';
        echo '<h2>Wordle List</h2>';
        echo '<textarea name="or_wordle_list" style="width: 300px; height: 300px;">' . stripslashes(get_option('or_wordle_list')) . '</textarea><br>';
        echo '<input type="submit" value="Save" class="button button-primary">';
        echo '</form>';

        
    }

}