<?php

class Elementor_Assigned_Session_Widget extends \Elementor\Widget_Base
{

    public function get_style_depends()
    {
        return ['assigned-session-widget-style'];
    }

    public function get_script_depends()
    {
        return ['assigned-session-widget-js'];
    }

    public function get_name()
    {
        return 'assigned_session_widget';
    }

    public function get_title()
    {
        return esc_html__('Assigned Session Table', 'elementor-addon');
    }

    public function get_icon()
    {
        return 'eicon-help-o';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['assigned', 'session', 'table'];
    }

    protected function register_controls()
    {

    }

    protected function render()
    {

        global $wpdb;
        $post_id = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_type = 'presentation' GROUP BY ID");
        echo "<table cellspacing=0 cellpadding=1 border=1 bordercolor=black width=100%>";
        echo "<tr>";
        echo "<th> Name </th>";
        echo "<th> Presentation </th>";
        echo "<th> Session </th>";
        echo "<th> Position (poster) </th>";

        echo "</tr>";
        $id_array = array();
        foreach ($post_id as $print_id) {
            $id_array[] = $print_id->ID;
        }

        $id_string = implode(',', $id_array);
        $results = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN ($id_string)");
        // Initialize an array to store the post data
        $post_data = array();

        // Organize the data into a 2D array
        foreach ($results as $result) {
            $post_id = $result->post_id;
            $meta_key = $result->meta_key;
            $meta_value = $result->meta_value;
            
            $post_data[$post_id][$meta_key] = $meta_value;
        }


        $session_post_id = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_type = 'session' GROUP BY ID");
       
        $session_id_array = array();
        foreach ($session_post_id as $print_id) {
            $session_id_array[] = $print_id->ID;
        }

        $session_id_string = implode(',', $session_id_array);
        $results = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE post_id IN ($session_id_string)");
        // Initialize an array to store the post data
        $session_post_data = array();

        // Organize the data into a 2D array
        foreach ($results as $result) {
            $post_id = $result->post_id;
            $meta_key = $result->meta_key;
            $meta_value = $result->meta_value;
            
            $session_post_data[$post_id][$meta_key] = $meta_value;
        }

        // Define a custom comparison function to sort by firstname
        function sortByFirstname($a, $b) {
            return strcmp($a['first_name'], $b['first_name']);
        }

        // Sort the array by firstname
        usort($post_data, 'sortByFirstname');

        foreach ($post_data as $id => $person) {
            echo "<tr>";
            echo "<td>" . $person['first_name'] . " " . $person['last_name'] . "</td>";
            echo "<td><a href=\"" . $person['abstract_pdf'] . "?timestamp=" . time() . "\">" . $person['presentation_title'] . "</a></td>";
            echo "<td>" . $session_post_data[$person['presentation_session']]['short_title'] . "</td>";
            echo "<td>" . $person['poster_number'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
}
