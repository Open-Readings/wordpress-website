<?php

use function Clue\StreamFilter\append;

class Elementor_Programme_25 extends \Elementor\Widget_Base
{

    public function get_style_depends()
    {
        return ['programme-25-style'];
    }

    public function get_script_depends()
    {
        return ['programme-25-js'];
    }

    public function get_name()
    {
        return 'programme_25';
    }

    public function get_title()
    {
        return esc_html__('Programme widget new', 'elementor-addon');
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
        return ['programme', '25', 'new'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'elementor-programme-new-control'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'Date',
            [
                'label' => esc_html__('Date', 'elementor-faq-control'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'input_type' => 'date',
                'placeholder' => esc_html__('Enter your date', 'elementor-faq-control'),
                'default' => date('Y-m-d'),
                'description' => 'Select the date for the programme day',
                //default return only day
                'format' => 'Y-m-d',


            ]

        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $date = $settings['Date'];
        $day = date('Y-m-d', strtotime($date));
        //get all sessions for this day
        $args = array(
            'post_type' => 'session',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'session_start',
                    'value' => $day . ' 23:59',
                    'compare' => '<=',
                    'type' => 'DATETIME'

                ),
                array(
                    'key' => 'session_end',
                    'value' => $day . ' 00:00',
                    'compare' => '>=',
                    'type' => 'DATETIME'

                )

            )

        );
        $sessions = new WP_Query($args);

        // while ($sessions->have_posts()){
        //     $sessions->the_post();
        //     echo '<h2>' . get_the_title() . '</h2>';
        //     echo '<p>' . get_the_content() . '</p>';
        // }

        $posts = [];
        $row_data = [];
        $row_count = 52;

        // Go through all sessions of the day and get data
        while($sessions->have_posts()){
            $sessions->the_post();
            $id = get_the_ID();

            $start = get_post_meta(get_the_ID(), 'session_start', true);
            $posts[$id]['start'] = date('H:i', strtotime($start));
            $end = get_post_meta(get_the_ID(), 'session_end', true);
            $posts[$id]['end'] = date('H:i', strtotime($end));
            $title = get_the_title();
            $posts[$id]['title'] = get_field('display_title');
            $posts[$id]['type'] = get_field('session_type');
            $posts[$id]['rows'] = 0;
            $posts[$id]['printed'] = false;
        }

        // Go through all rows and get the sessions
        for ($i = 0; $i < $row_count; $i++){
            $hour = floor($i/4) + 8;
            $minute = $i % 4 * 15;
            $time = $hour . ':' . $minute;
            $time = date('H:i', strtotime($time));

            $row_posts = [];
            $col_count = 0;
            $occupied = [];
            foreach ($posts as $id => $post){
                if ($post['start'] < $time and $post['end'] >= $time){
                    $row_posts[] = $id;
                    $posts[$id]['rows']++;
                    if (isset($post['col'])) {
                        $col_count = max($col_count, $post['col']);
                        $occupied[] = $post['col'];
                    }
                }
            }

            $col_count = max($col_count, count($row_posts));
            for ($j = 0; $j < $col_count; $j++)
                $row_data[$i][$j] = null;
            $col = 0;
            foreach ($row_posts as $id){
                while (in_array($col, $occupied)){
                    $col++;
                }
                if (!isset($posts[$id]['col'])){
                    $posts[$id]['col'] = $col;
                    $col++;
                }
                $row_data[$i][$posts[$id]['col']] = $id;
            }
        }

        $session_style = [
            'speaker' => 'or-speaker',
            'break' => 'or-break',
            'workshop' => 'or-workshop',
            'oral' => 'or-oral',
            'poster' => 'or-poster',
            'special_event' => 'or-special-event',
            'other' => 'or-other',
        ];


        echo '<table class="programme-table">';
        // Print table one row at a time
        for ($i = 0; $i < $row_count; $i++) {
            $hour = floor($i/4) + 8;
            $minute = $i % 4 * 15;
            $time = $hour . ':' . $minute;
            $time = date('H:i', strtotime($time));
            $border_class = $i % 4 == 0 ? 'or-bottom-border' : '';

            echo '<tr class="row-height">';
            
            // Print time every 4 rows
            if ($i % 4 == 0)
                echo '<td rowspan="1" class="or-bottom or-width or-bottom-border">' . $time . '</td>';
            // Else print empty cell
            else
                echo '<td class="or-width"></td>';

            // Print timetable content
            if (isset($row_data[$i])){
                // One row element at a time
                foreach ($row_data[$i] as $key => $id) {
                    if ($id == null)
                        continue;
                    // Prevent duplicates for multi-row entries
                    if ($posts[$id]['printed'])
                        continue;

                    $cell_style = '';
                    foreach ($session_style as $type => $css){
                        if ($type == $posts[$id]['type']){
                            $cell_style = $css;
                            break;
                        }
                    }

                    // How wide should an entry be
                    $width = 'grid-column: ' . ($posts[$id]['col'] + 1) . ' / span 1;';
                    // How many rows should an entry span (how tall should it be)
                    $rowspan = isset($posts[$id]['rows']) ? $posts[$id]['rows'] : 1;
                    // For the rightmost element, span all the way to the right
                    $colspan = $key == count($row_data[$i]) - 1 ? 1 : 1;
                    // If the timeslot is empty, no need for a border
                    $border_box = (isset($posts[$id]['title']) and $posts[$id]['title'] != null) ? 'or-box' : '';
                    $content = (isset($posts[$id]['title']) and $posts[$id]['title'] != null) ? $posts[$id]['title'] : '';
                 
                    // Print element
                    echo "<td class='or-center or-width $border_box $cell_style' style='$width' colspan='$colspan' rowspan='$rowspan'><div>" . $content . "</div></td>";
                    $posts[$id]['printed'] = true;
                    
                }
            } 
            // Empty line if no content
            else 
            {
                echo "<td class='or-center or-width $border_class' colspan='3'></td>";
            }
            echo '</tr>';
        }
        echo '</table>';

        
    }
}
