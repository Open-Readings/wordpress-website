<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

class ORSecondEvaluationAdmin
{
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('admin_init', array($this, 'download_csv'));
        add_action('admin_menu', array($this, 'add_admin_pages'));

    }

    function add_admin_pages()
    {

        add_menu_page('OpenReadings Evaluation System', 'OpenReadings evaluation system', 'or_evaluator', 'or_evaluation_two', array($this, 'admin_index'), 'dashicons-list-view', 4);
        if (current_user_can('manage_options')) {
            add_submenu_page('or_evaluation_two', 'OR Evaluation List', 'Or evaluation admin', 'manage_options', 'or_evaluation_admin', array($this, 'admin_index'), 4);
            add_submenu_page('or_evaluation_two', 'OR Evaluation Settings', 'OR evaluation system settings', 'manage_options', 'or_evaluation_settings_two', array($this, 'admin_settings'), 5);
            add_submenu_page('or_evaluation_two', 'OR Evaluation Emailer', 'OR evaluation emailer', 'manage_options', 'or_evaluation_emailer', array($this, 'admin_emailer'), 6);
            add_submenu_page('or_evaluation_two', 'OR Evaluation List', 'Or evaluation list', 'manage_options', 'or_evaluation_list', array($this, 'admin_list'), 7);
        }

    }

    function admin_index()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/evaluation_admin.php';
    }

    function admin_emailer()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/evaluation_email.php';
    }
    function admin_settings()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/evaluation_settings.php';
    }

    function admin_list()
    {
        require_once plugin_dir_path(__FILE__) . 'admin/evaluation_list.php';

    }


    function download_csv()
    {
        if (isset($_POST['export_csv'])) {
            global $wpdb;
            global $PRESENTATION_TYPE;
            global $RESEARCH_AREAS;
            $registration_table = "23_registration";
            $ra_filter = 'none';


            if (isset($_POST['save_settings'])) {
                foreach ($_POST['decision'] as $id => $decision) {
                    $wpdb->update($registration_table, array('decision' => $decision), array('id' => $id));
                }
            }


            if (isset($_POST['ra_filter'])) {
                $ra_filter = $_POST['ra_filter'];
            }



            global $STATUS_CODES;
            $query = "SELECT * FROM $registration_table WHERE status=" . $STATUS_CODES["Accepted"] . "";


            if ($ra_filter != 'none') {
                $query .= " AND research_area=$ra_filter";
            }
            if (isset($_POST['type_filter'])) {
                $type_filter = $_POST['type_filter'];
                if ($type_filter != 'none') {
                    $query .= " AND decision=$type_filter";
                }
            }


            $results = $wpdb->get_results($query);



            ob_end_clean();
            $fp = fopen('php://output', 'w');

            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            fputcsv($fp, array('First Name', 'Last Name', 'Email', 'Affiliation','Country' , 'Presentation Title', 'Abstract PDF', 'Research Area', 'Decision'));
            foreach ($results as $result) {
                fputcsv($fp, array($result->firstname, $result->lastname, $result->email,  explode(",",$result->affiliation)[0], $result->country, $result->presentation_title, $result->abstract_pdf, $RESEARCH_AREAS[$result->research_area], array_search($result->decision, $PRESENTATION_TYPE)));
            }
            fclose($fp);
            die;
        }
    }
    public function init()
    {
        

    }
}


?>