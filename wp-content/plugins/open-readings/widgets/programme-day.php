<?php
class ElementorProgrammeDay extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'programme-day-widget';
    }

    public function get_title()
    {
        return esc_html__('Programme Day', 'OR plugin');
    }

    public function get_style_depends()
    {
        return ['faq-widget-style', 'programme-day-style'];
    }

    public function get_script_depends()
    {
        return ['faq-widget-js', 'programme-day-js'];
    }
    public function get_icon()
    {
        return 'eicon-help-o';
    }

    function getSessionLengthInMinutes($session_id)
    {
        $start = get_field('session_start', $session_id);
        $end = get_field('session_end', $session_id);
        $startDate = DateTime::createFromFormat('d/m/Y H:i', $start);
        $endDate = DateTime::createFromFormat('d/m/Y H:i', $end);
        $length = $endDate->diff($startDate);
        return ($length->days * 24 * 60) + ($length->h * 60) + $length->i;
    }


    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'elementor-faq-control'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'Title',
            [
                'label' => esc_html__('Title', 'elementor-faq-control'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'text',
                'placeholder' => esc_html__('Enter your title', 'elementor-faq-control'),
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
        echo '
        <div id="sessionModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalSessionTitle"></h2>

                <div id="modalSessionDescription"></div>
                <div id="modalSessionLocation"></div>
                <div id="modalPresentations"></div>
            </div>
        </div>
        ';
        ?>



        <div class="collapsible title"><i class="arrow"></i>&ensp;
            <?php echo $settings['Title'] ?>
        </div>
        <div class="content" style="padding: 0px 0px">
            <?php
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
            $index = 0;
            $format = 'd/m/Y H:i';

            if ($sessions->have_posts()) {
                //check for overlapping sessions
                // Assuming $sessions->posts contains all the session posts...
                $sessions_posts = $sessions->posts;
                $overlaps = array();

                // Initialize the overlaps array with empty arrays for each session
                foreach ($sessions_posts as $session_post) {
                    $overlaps[$session_post->ID] = array(); // Initialize with empty array
                }

                for ($i = 0; $i < count($sessions_posts); $i++) {
                    $session1 = $sessions_posts[$i];
                    $session1_end = get_post_meta($session1->ID, 'session_end', true);

                    // $session1_end_time = DateTime::createFromFormat($format, $session1_end)->getTimestamp();
                    $session1_end_time = strtotime($session1_end);

                    for ($j = 0; $j < count($sessions_posts); $j++) {
                        if ($i == $j)
                            continue; // Skip comparing the session with itself
    
                        $session2 = $sessions_posts[$j];
                        $session2_end = get_post_meta($session2->ID, 'session_end', true);

                        $session2_end_time = strtotime($session2_end);

                        $session_2_start = get_post_meta($session2->ID, 'session_start', true);
                        $session_2_start_time = strtotime($session_2_start);



                        // $session2_start_time = DateTime::createFromFormat($format, $session2_start)->getTimestamp();
    
                        if ($session1_end_time <= $session2_end_time && $session1_end_time > $session_2_start_time) {
                            // There is an overlap. Note that we add the post IDs, but you might want to store more information.
                            $overlaps[$session1->ID][] = $session2->ID;
                            $overlaps[$session2->ID][] = $session1->ID; // Also mark the overlap in the opposite direction
                        }
                    }
                }

                // Remove duplicates from each list of overlapping sessions
    
                foreach ($overlaps as $session_id => &$overlapping_sessions) {
                    $overlapping_sessions = array_unique($overlapping_sessions);
                    if (empty ($overlapping_sessions)) {
                        // Optionally, remove entries for sessions that have no overlaps
                        unset($overlaps[$session_id]);
                    } else {

                    }
                }


                //generate branching dict from the overlaps array
    

                $displayed_time = array();

                $displayed = array();
                while ($sessions->have_posts()) {
                    $sessions->the_post();

                    $session_id = $sessions->post->ID;
                    if (in_array($session_id, $displayed)) {
                        continue;
                    }

                    $session_overlaps = True;

                    if (empty ($overlaps[$session_id])) {
                        $session_overlaps = False;
                    }

                    $overlapping_ids = array();
                    if ($session_overlaps) {

                        $overlapping_ids = $overlaps[$session_id];
                        $total_overlapping_sessions = count($overlapping_ids) + 1;
                        $total_overlapping_sessions = min($total_overlapping_sessions, 3);
                        $width = 80 / $total_overlapping_sessions;
                    } else {
                        $width = 80;
                    }

                    $index++;


                    $session_type = get_field('session_type_field');
                    $title = get_field('display_title');

                    $format = 'd/m/Y H:i';

                    $start = get_field('session_start');
                    // parse from 04/23/2024 09:00 format
                    $startDate = DateTime::createFromFormat($format, $start);




                    if (!in_array($startDate->format('H:i'), $displayed_time)) {
                        if ($index = 1) {
                            $height = 100;
                        } else {
                            $height = 0;
                        }
                        ?>
                        <div class="program-section">
                            <div class="program-block">
                                <div class="time-block" style="margin-top: <?php echo $height - 30; ?>px;">
                                    <div class="time-header" style="position:absolute">
                                        <h4>
                                            <?php echo $startDate->format('H:i'); ?>
                                        </h4>
                                    </div>
                                </div>
                                <div style="width:80%; height: <?php echo $height; ?>px;"></div>
                            </div>
                            <!-- <svg width="100%" height="5" xmlns="http://www.w3.org/2000/svg">
                                <line x1="1%" y1="3" x2="99%" y2="3" stroke="#2386f3" stroke-width="3" stroke-linecap="round" />
                            </svg> -->
                        </div>
                        <?php
                    }


                    $end = get_field('session_end');

                    $endDate = DateTime::createFromFormat($format, $end);
                    $length = $endDate->diff($startDate);
                    $starting_times = array();
                    $starting_times[] = $start;

                    $longest_session = $length;
                    if ($session_overlaps) {
                        $max_endDate = $endDate;
                        foreach ($overlapping_ids as $overlapping_id) {

                            $this_session_start = get_field('session_start', $overlapping_id);
                            $this_session_end = get_field('session_end', $overlapping_id);
                            $this_start = DateTime::createFromFormat($format, $this_session_start);
                            $this_end = DateTime::createFromFormat($format, $this_session_end);
                            $this_length = $this_end->diff($startDate);
                            if ($this_length->h + $this_length->i * 60 > $longest_session->h + $longest_session->i * 60) {
                                $longest_session = $this_length;
                                $max_endDate = $this_end;
                            }

                        }


                    }
                    $starting_times = array_unique($starting_times);




                    $length = $endDate->diff($startDate);
                    $height_per_hour = 20;
                    if ($session_type == 'speaker') {
                        $height_per_hour = 25;
                    }
                    $height_per_minute = $height_per_hour / 60;



                    $height = $length->i * $height_per_minute + $length->h * $height_per_hour;

                    $max_height = $longest_session->i * $height_per_minute + $longest_session->h * $height_per_hour;
                    $max_height = max($max_height, $height);
                    $max_height = min($max_height, 40);
                    $max_height_style = 'height: ' . $max_height . 'vh;';
                    if ($session_type == 'speaker') {
                        $height_style = 'max-height: ' . $height . 'vh;';
                    } else {
                        $height_style = 'height: ' . $height . 'vh;';
                    }




                    $overlapping_ids = array_reverse($overlapping_ids);
                    // Calculate lengths
    


                    //sort overlapping Ids by length, from shortest to longest
    
                    $lengths = [];

                    foreach ($overlapping_ids as $id) {

                        $lengths[$id] = $this->getSessionLengthInMinutes($id);
                    }

                    // Sort based on lengths
                    usort($overlapping_ids, function ($a, $b) use ($lengths) {
                        return $lengths[$a] <=> $lengths[$b];
                    });

                    $overlapping_ids = array_merge(array($session_id), $overlapping_ids);

                    $session_objects = array();
                    $number_of_branches = 1;
                    $ending_times = array();




                    $number_of_branches = 1;
                    foreach ($overlapping_ids as $overlapping_id) {
                        $start = get_field('session_start', $overlapping_id);
                        $end = get_field('session_end', $overlapping_id);
                        $startDate = DateTime::createFromFormat($format, $start);
                        $endDate = DateTime::createFromFormat($format, $end);
                        $length = $endDate->diff($startDate);
                        $ending_times[] = $end;

                        foreach ($session_objects as $existing_session => $existing_session_object) {
                            $existing_start = $existing_session_object['start'];

                            $existing_end = $existing_session_object['end'];
                            if ($existing_start == $start && $existing_session != $overlapping_id) {
                                $number_of_branches += 1;
                            }

                        }

                        $session_objects[$overlapping_id] = array(
                            'start' => $start,
                            'length' => $length->h * 60 + $length->i,
                            'end' => $end,

                        );


                    }
                    $number_of_branches = min(3, $number_of_branches);

                    $ending_times = array_unique($ending_times);
                    asort($ending_times);
                    $width = 80 / $number_of_branches;
                    if ($session_overlaps) {
                        $width_style = 'width: ' . $width . '%;';
                    } else {
                        $width_style = 'width: 80%;';
                    }



                    //check fo non overlapping sessions
    



                    ?>

                    <div class="program-section">
                        <div class="program-block" style=" <?php echo $max_height_style; ?>">
                            <div class="time-block">
                                <?php
                                $compared_date = DateTime::createFromFormat($format, get_field('session_start', $session_id));
                                $totalHeight = 0; // Initialize total height to accumulate block heights
                
                                foreach ($ending_times as $ending_time) {
                                    $endDate = DateTime::createFromFormat($format, $ending_time);
                                    $length = $endDate->diff($compared_date);
                                    $compared_date = $endDate;

                                    // Calculate the height for the current block
                                    $height = $length->i * $height_per_minute + $length->h * $height_per_hour;
                                    $totalHeight += $height; // Accumulate the height
                
                                    if (in_array($endDate->format('H:i'), $displayed_time)) {
                                        continue;
                                    }
                                    $displayed_time[] = $endDate->format('H:i');
                                    ?>

                                    <div class="time-header"
                                        style="position: absolute; margin-top: <?php echo $totalHeight - 4 . 'vh'; ?>;">
                                        <h4>
                                            <?php echo $endDate->format('H:i'); ?>
                                        </h4>
                                    </div>

                                    <?php
                                }

                                echo '</div>';
                                $idx = 0;
                                $overlapped = False;

                                foreach ($overlapping_ids as $overlapping_id) {
                                    $idx++;
                                    if (in_array($overlapping_id, $displayed)) {
                                        continue;
                                    }
                                    $displayed[] = $overlapping_id;

                                    $session_type = get_field('session_type_field', $overlapping_id);
                                    $title = get_field('display_title', $overlapping_id);
                                    $description = get_field('description', $overlapping_id);
                                    $start = get_field('session_start', $overlapping_id);
                                    $end = get_field('session_end', $overlapping_id);
                                    $startDate = DateTime::createFromFormat($format, $start);
                                    $endDate = DateTime::createFromFormat($format, $end);

                                    $length = $endDate->diff($startDate);
                                    $height_per_hour = 20;
                                    if ($session_type == 'speaker') {
                                        $height_per_hour = 25;
                                    }
                                    $height_per_minute = $height_per_hour / 60;


                                    if ($idx == $number_of_branches && count($overlapping_ids) > $number_of_branches && !$overlapped) {
                                        $overlapped = True;
                                        echo '<div style="' . $max_height_style . $width_style . '">';
                                        $width_style = "width: 100%;";

                                    }

                                    $height = $length->i * $height_per_minute + $length->h * $height_per_hour;
                                    $height = min($height, 40);
                                    if ($session_type == 'speaker') {
                                        $height_style = 'max-height: ' . $height . 'vh';
                                    } elseif ($overlapped || !$session_overlaps) {
                                        $height_style = 'min-height: ' . $height . 'vh';
                                    } else {
                                        $height_style = 'height: ' . $height . 'vh';
                                    }

                                    if ($session_type == 'speaker') {
                                        $speaker = get_field('invited_speaker_reference', $overlapping_id);

                                        $thumbnail = get_the_post_thumbnail_url($speaker);
                                        $affiliation = get_field('affiliation', $speaker);
                                        //strip html tags:
                                        $affiliation = strip_tags($affiliation);

                                        $post_link = get_permalink($speaker);

                                        ?>
                                        <a href="<?php echo $post_link; ?>" class="speaker">
                                            <div class="speaker">
                                                <div class="event-title">
                                                    <?php echo $title ?>
                                                </div>
                                                <div class="event-description">
                                                    <?php echo $description ?>
                                                </div>
                                                <div class="event-affiliation">
                                                    <?php echo $affiliation ?>
                                                </div>
                                            </div>
                                            <div class="speaker-image">
                                                <img src="<?php echo $thumbnail ?>" alt="">
                                            </div>

                                        </a>

                                        <?php
                                    } elseif ($session_type == 'oral' || $session_type == 'poster') {
                                        $oral_link = get_field('link', $overlapping_id);

                                        if ($session_type == 'oral') {
                                            $presentation_query = array(
                                                'post_type' => 'presentation',
                                                'posts_per_page' => -1,
                                                'orderby' => 'meta_value',
                                                'order' => 'ASC',
                                                'meta_key' => 'presentation_start',
                                                'meta_type' => 'DATETIME',
                                                'meta_query' => array(
                                                    array(
                                                        'key' => 'presentation_session',
                                                        'value' => $overlapping_id,
                                                        'compare' => '='

                                                    )
                                                )
                                            );

                                        } else {
                                            $presentation_query = array(
                                                'post_type' => 'presentation',
                                                'posts_per_page' => -1,
                                                'orderby' => 'meta_value',
                                                'order' => 'ASC',
                                                'meta_key' => 'poster_number',
                                                'meta_query' => array(
                                                    array(
                                                        'key' => 'presentation_session',
                                                        'value' => $overlapping_id,
                                                        'compare' => '='
                                                    )
                                                )
                                            );

                                        }

                                        $presentations = new WP_Query($presentation_query);

                                        echo '<div id="modal-data-' . esc_attr($overlapping_id) . '" ' .
                                            'type="' . esc_attr($session_type) . '" ' .
                                            'session-id="' . esc_attr($overlapping_id) . '" ' .
                                            'session_title="' . esc_attr($title) . '" ' .
                                            'session_description="' . esc_attr($description) . '" ' .
                                            'session_start="' . esc_attr($start) . '" ' .
                                            'session_end="' . esc_attr($end) . '" ' .

                                            'style="display:none;">';
                                        foreach ($presentations->posts as $presentation) {
                                            $presentation_id = $presentation->ID;
                                            $presenter = get_the_title($presentation_id);
                                            $presentation_title = get_field('presentation_title', $presentation_id);
                                            $presentation_abstract = get_field('abstract_pdf', $presentation_id);
                                            $research_area = get_field('research_area', $presentation_id);
                                            $poster_number = get_field('poster_number', $presentation_id);
                                            $start = get_field('presentation_start', $presentation_id);
                                            echo '<div id="' . $presentation_id . '" class="presentation-data" ' .
                                                'data-presenter="' . esc_attr($presenter) . '" ' .
                                                'data-title="' . esc_attr($presentation_title) . '" ' .
                                                'data-abstract="' . esc_attr($presentation_abstract) . '" ' .
                                                'data-research-area="' . esc_attr($research_area) . '" ' .
                                                'data-poster-number="' . esc_attr($poster_number) . '" ' .
                                                'data-start="' . esc_attr($start) . '" ' .


                                                '></div>';


                                        }

                                        echo '</div>';



                                        ?>
                                        <a href="javascript:void(0);" onclick="openSessionModal(<?php echo $overlapping_id; ?>)" style="<?php echo $width_style;
                                           echo 'height: ' . $height . 'vh;' ?>">
                                            <div class="event-title oral">
                                                <?php echo $title ?>
                                            </div>
                                            <div class="event-description oral">
                                                <?php echo $description ?>
                                            </div>
                                            <!-- <svg width="10000px" height="5" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="0%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3"
                                                    stroke-linecap="round" />
                                            </svg> -->
                                        </a>
                                        <?php

                                    } elseif ($session_type == 'sponsor') {
                                        $sponsor_link = get_field('link', $overlapping_id);

                                        ?>
                                        <a href="<?php echo $sponsor_link; ?>" style="<?php echo $width_style;
                                           echo $max_height_style; ?>">
                                            <div class="event-title other">
                                                <?php echo $title ?>
                                            </div>
                                            <!-- <svg width="10000px" height="5" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="1%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3"
                                                    stroke-linecap="round" />
                                            </svg> -->
                                        </a>
                                        <?php
                                    } elseif ($session_type == "break") {
                                        ?>
                                        <div class="break-wrapper" style="<?php echo $width_style;
                                        echo $height_style; ?>">
                                            <div class="event-container break" style="<?php echo $width_style;
                                            ?>">
                                                <div class="event-title break">
                                                    <?php echo $title ?>
                                                </div>
                                                <!-- <svg width="10000px" height="5" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="1%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3"
                                                    stroke-linecap="round" />
                                            </svg> -->
                                            </div>
                                        </div>


                                        <?php
                                    } elseif ($session_type == 'special_event') {
                                        $post_link = get_field('link', $overlapping_id);
                                        ?>
                                        <a class="event-container special-event" style="<?php echo $width_style;
                                        echo $height_style; ?>" href="<?php echo $post_link; ?>">
                                            <div class="event-title ">
                                                <?php echo $title ?>
                                            </div>
                                            <!-- <svg width="10000px" height="5" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="1%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3"
                                                    stroke-linecap="round" />
                                            </svg> -->
                                        </a>
                                        <?php



                                    } elseif ($session_type == 'workshop') {
                                        $post_link = get_field('link', $overlapping_id);
                                        ?>
                                        <a class="event-container workshop" style="<?php echo $width_style;
                                        echo $height_style; ?>" href="<?php echo $post_link; ?>">

                                            <div class="event-title ">
                                                <?php echo $title ?>
                                            </div>
                                            <div class="event-description">
                                                <?php echo $description ?>
                                            </div>
                                        </a>
                                        <?php



                                    } else {
                                        ?>
                                        <div class="event-container" style="<?php echo $width_style;
                                        echo $height_style; ?>">
                                            <div class="event-title other">
                                                <?php echo $title ?>
                                            </div>
                                            <!-- <svg width="10000px" height="5" xmlns="http://www.w3.org/2000/svg">
                                                <line x1="1%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3"
                                                    stroke-linecap="round" />
                                            </svg> -->
                                        </div>


                                        <?php


                                    }
                                    ?>

                                    <?php
                                }
                                if ($overlapped) {
                                    echo '</div>';
                                }

                                ?>
                            </div>
                            <?php if ($session_type == "speaker") { ?>
                                <!-- <svg width="100%" height="5" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="1%" y1="3" x2="100%" y2="3" stroke="#2386f3" stroke-width="3" stroke-linecap="round" />
                                </svg> -->
                            <?php } ?>
                        </div>

                        <?php

                }
            }





            ?>


                <div class="program-section">
                    <div class="program-block">
                        <div class="time-block">

                        </div>
                        <div style="height: 100px;px;"></div>
                    </div>
                    <!-- <svg width="100%" height="5" xmlns="http://www.w3.org/2000/svg">
                                <line x1="1%" y1="3" x2="99%" y2="3" stroke="#2386f3" stroke-width="3" stroke-linecap="round" />
                            </svg> -->
                </div>
            </div>
            <?php

    }

}



?>