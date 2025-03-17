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
            $posts[$id]['link'] = get_field('link');
            $posts[$id]['link']= empty($posts[$id]['link']) ? 'javascript:void(0);' : $posts[$id]['link'];
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
            'sponsor' => 'or-sponsor',
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
                echo '<td rowspan="1" class="or-bottom or-width or-bottom-border or-time-font">' . $time . '&nbsp;</td>';
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
                    $hover = '';
                    $popup = '';
                    // How wide should an entry be
                    $width = 'width: ' . (100 / count($row_data[$i])) . '%;';
                    // How many rows should an entry span (how tall should it be)
                    $rowspan = isset($posts[$id]['rows']) ? $posts[$id]['rows'] : 1;
                    // For the rightmost element, span all the way to the right
                    $colspan = 12 / count($row_data[$i]);
                    // If the timeslot is empty, no need for a border
                    $border_box = (isset($posts[$id]['title']) and $posts[$id]['title'] != null) ? 'or-box' : 'or-white-box';
                    $content = (isset($posts[$id]['title']) and $posts[$id]['title'] != null) ? $posts[$id]['title'] : '';
                    $link = $posts[$id]['link'];
                    if ($posts[$id]['type'] == 'speaker'){
                        $hover = 'or-hover';
                        $speaker_id = get_field('invited_speaker', post_id: $id)->ID;
                        $speaker_image = get_the_post_thumbnail($speaker_id, ['100', '100']);
                        $speaker_affiliation = get_field('affiliation', post_id: $speaker_id);
                        $speaker_url = get_permalink($speaker_id);
                        $link = empty($speaker_url) ? 'javascript:void(0);' : $speaker_url;
                        $content = '
                        <div style="display:flex; align-items:center;">' .
                            '<div style="width: 80%; padding:10px;">' .
                                '<div class="or-font or-p-left">' . get_field('display_title', post_id: $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-normal or-p-left">' . get_field('description', $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-left">' . $speaker_affiliation . '</div>' .
                                '<div class="or-font or-p-small or-p-left">' . get_field('location', $id) . '</div>' .
                            '</div>' .
                            '<div style="" class="or-speaker-image">' . $speaker_image .'</div>'.
                        '</div>';
                    }
                    if ($posts[$id]['type'] == 'oral'){
                        $args = array(
                            'post_type'      => 'presentation', // Your custom post type for presentations
                            'posts_per_page' => -1, // Retrieve all matching posts
                            'meta_query'     => array(
                                array(
                                    'key'   => 'presentation_session', // Custom field key
                                    'value' => $id, // Session ID to match
                                    'compare' => '=', // Exact match
                                ),
                            ),
                            'meta_key'       => 'presentation_start', // Custom field to sort by
                            'orderby'        => 'meta_value', // Sort by the custom field value
                            'order'          => 'ASC', // Sort in ascending order (earliest first)
                        );
                        
                        // Run the query
                        $presentations_query = new WP_Query($args);
                        $presentations = '';
                        $presentations .= '<h1 style="display:inline;">' . $posts[$id]['title'] . ' | </h1>';
                        $time_string = date('H:i', strtotime($posts[$id]['start'])) . ' - ' . date('H:i', strtotime($posts[$id]['end']));
                        $presentations .= '<h1 class="or-blue-font" style="display:inline;">' . $time_string . '</h1>';
                        $presentations .= '<p class="or-dark-font" style="font-size:20px;"><strong>' . get_field('description', $id) . '</strong></p>';
                        
                        // Check if there are any presentations
                        if ($presentations_query->have_posts()) {
                            while ($presentations_query->have_posts()) {
                                $presentations_query->the_post();
                        
                                // Output the presentation title or other details
                                $time = date('H:i', strtotime(get_field('presentation_start')));
                                $presentations .= '<div>' .
                                    '<div style="display:inline-block; width:50px; vertical-align:top;"><p class="or-blue-font or-p-bold">' . 
                                    $time . 
                                    '</p></div>' .
                                '<div style="display:inline-block; overflow:wrap; width:90%;">' . 
                                    '<p class="or-dark-font"><strong>' .
                                    get_the_title() .
                                    '</strong><br>' .
                                    get_field('presentation_title') .
                                    '</p></div></div><hr>';
                                // Add more presentation details as needed
                            }
                        } else {
                            // No presentations found for this session
                            echo 'No presentations found for this session.';
                        }
                        $presentations = json_encode($presentations);
                        $popup = "onclick='showModal($presentations)'";
                        
                        // Reset the post data
                        wp_reset_postdata();
                        $hover = 'or-hover';
                        $content =
                            '<div style="width: 100%; padding:10px;">' .
                                '<div class="or-font">' . get_field('display_title', post_id: $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-normal">' . get_field('description', $id) . '</div>' .
                                '<div class="or-font or-p-small">' . get_field('location', $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-normal">Chair: ' . get_field('session_moderator', $id) . '</div>' .
                            '</div>';
                    }

                    if ($posts[$id]['type'] == 'poster'){
                        $args = array(
                            'post_type'      => 'presentation', // Your custom post type for presentations
                            'posts_per_page' => -1, // Retrieve all matching posts
                            'meta_query'     => array(
                                array(
                                    'key'   => 'presentation_session', // Custom field key
                                    'value' => $id, // Session ID to match
                                    'compare' => '=', // Exact match
                                ),
                            ),
                            'meta_key'       => 'poster_number', // Custom field to sort by
                            'orderby'        => 'meta_value_num', // Sort by the custom field value
                            'order'          => 'ASC', // Sort in ascending order (earliest first)
                        );
                        
                        // Run the query
                        $presentations_query = new WP_Query($args);
                        $presentations = '';
                        $presentations .= '<h1 style="display:inline;">' . $posts[$id]['title'] . '</h1>';
                        $presentations .= '<p class="or-dark-font" style="font-size:20px;"><strong>' . get_field('description', $id) . '</strong></p>';
                        
                        // Check if there are any presentations
                        if ($presentations_query->have_posts()) {
                            while ($presentations_query->have_posts()) {
                                $presentations_query->the_post();
                        
                                // Output the presentation title or other details
                                $nr = get_field('poster_number');
                                $presentations .= '<div>' .
                                    '<div style="display:inline-block; width:50px; vertical-align:top;"><p class="or-blue-font or-p-bold">' . 
                                    $nr . 
                                    '</p></div>' .
                                '<div style="display:inline-block; overflow:wrap; width:90%;">' . 
                                    '<p class="or-dark-font"><strong>' .
                                    get_the_title() .
                                    '</strong><br>' .
                                    get_field('presentation_title') .
                                    '</p></div></div><hr>';
                                // Add more presentation details as needed
                            }
                        } else {
                            // No presentations found for this session
                            echo 'No presentations found for this session.';
                        }
                        $presentations = json_encode($presentations);
                        $popup = "onclick='showModal($presentations)'";
                        $hover = 'or-hover';
                        $content =
                            '<div style="width: 100%; padding:10px;">' .
                                '<div class="or-font">' . get_field('display_title', post_id: $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-normal">' . get_field('description', $id) . '</div>' .
                            '</div>';
                    }

                    if ($posts[$id]['type'] == 'workshop'){
                        $hover = 'or-hover';
                        $content =
                            '<div style="width: 100%; padding:10px;">' .
                                '<div class="or-font">' . get_field('display_title', post_id: $id) . '</div>' .
                                '<div class="or-font or-p-small or-p-normal">' . get_field('description', $id) . '</div>' .
                            '</div>';
                    }

                    if ($posts[$id]['type'] == 'sponsor'){
                        $hover = 'or-hover';
                    }

                    $bottom_border = ($i + $rowspan) % 4 == 1 ? 'or-bottom-border' : '';

                 
                    // Print element
                    echo "<td class='or-center or-width $border_box $hover $cell_style $bottom_border' $popup style='$width' colspan='$colspan' rowspan='$rowspan'><a href='$link'  style='text-decoration: none; color: inherit; display: block; cursor:inherit;'><div class='or-font'>" . $content . "</div></a></td>";
                    $posts[$id]['printed'] = true;
                    
                }
            } 
            // Empty line if no content
            else 
            {
                echo "<td class='or-center or-width $border_class' colspan='12'></td>";
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '<div class="overlay" onclick="hideModal()"></div>
                <div class="or-modal" id="or-modal">
                <div id="modal-content"></div>
            </div>';

        
    }
}

?>

<!-- <script>
    function toggleContent(cell) {
      // Toggle the 'active' class on the clicked cell
      cell.getElementsByClassName('oral-hidden')[0].style.display = 'fixed';
    }
  </script> -->

<script>
    // Function to show the modal
    function showModal(content) {
      const modal = document.getElementById('or-modal');
      const modalContent = document.getElementById('modal-content');
      const overlay = document.querySelector('.overlay');

      // Populate the modal with the cell's content
      modalContent.innerHTML = content;

      // Show the modal and overlay
      modal.classList.add('active');
      overlay.classList.add('active');
    }

    // Function to hide the modal
    function hideModal() {
      const modal = document.getElementById('or-modal');
      const overlay = document.querySelector('.overlay');

      // Hide the modal and overlay
      modal.classList.remove('active');
      overlay.classList.remove('active');
    }
  </script>