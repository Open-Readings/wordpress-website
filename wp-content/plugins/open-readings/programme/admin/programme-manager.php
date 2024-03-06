<h2>Presentation Manager</h2>


<div>
    <?
    if($_POST['type_filter'] == 2 && $_POST['assigned_filter'] == 2 && $_POST['session_filter'] != 'none'){
        ?>
        <button id="incrementButton"> Increment </button>
        <?
    }

    ?>





    <h3>filter research areas: </h3>
    <form method="POST" id="filter">
        <label>Session: </label>
        <select name="session_filter" id="session_filter_select">
            <option value="none">all</option>
            <?php
            $args = array(
                'post_type' => 'session', 
                'posts_per_page' => -1, // To retrieve all posts, use -1
            );
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    // Retrieve and display the custom field values
                    $display = get_post_meta(get_the_ID(), 'display_title', true);
                    if (get_post_meta(get_the_ID(), 'session_type', true) != -1)
                        echo '<option value="' . get_the_ID() . '"' . ((get_the_ID() == $_POST['session_filter']) ? 'selected' : '').'>' . $display . '</option>';
                }}
            ?>
        </select>

        <label>Reasearch: </label>
        <select name="ra_filter" id="ra_filter_select">
            <option value="none">all</option>
            <?php
            global $RESEARCH_AREAS;
            for ($i = 1; $i < count($RESEARCH_AREAS)+1; $i++) {
                if (isset($_POST['ra_filter']) && $_POST['ra_filter'] == $i) {
                    echo '<option value="' . $i . '" selected>' . $RESEARCH_AREAS[$i] . '</option>';
                } else
                    echo '<option value="' . $i . '">' . $RESEARCH_AREAS[$i] . '</option>';
            }

            ?>
        </select>

        <label>Type: </label>
        <select name="type_filter" id="type_filter_select">
            <option value="none">all</option>
            <?php
            global $PRESENTATION_TYPE;
            for ($i = 1; $i < count($PRESENTATION_TYPE); $i++) {
                if (isset($_POST['type_filter']) && $_POST['type_filter'] == $i) {
                    echo '<option value="' . $i . '" selected>' . array_search($i, $PRESENTATION_TYPE) . '</option>';
                } else
                    echo '<option value="' . $i . '">' . array_search($i, $PRESENTATION_TYPE) . '</option>';
            }

            ?>
        </select>
        <label>Assigned: </label>
        <select name="assigned_filter" id="assigned_filter_select">
            <option value="none">all</option>
            <?php
            $status = [
                1 => 'unassigned',
                2 => 'assigned'
            ];
            for ($i = 1; $i < count($status)+1; $i++) {
                if (isset($_POST['assigned_filter']) && $_POST['assigned_filter'] == $i) {
                    echo '<option value="' . $i . '" selected>' . $status[$i] . '</option>';
                } else
                    echo '<option value="' . $i . '">' . $status[$i] . '</option>';
            }

            ?>
        </select>

</div>
<form method=post>
<button name="save_settings" type="submit">Save All</button>
<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=white width=100%>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Presentation Title</th>
            <th>Research Area</th>
            <th>PDF</th>
            <th>Decision</th>
            <th>Session</th>
            <th>Time</th>
            <th>Poster nr.</th>
            <th>Clear</th>
        </tr>
        <?php
        global $wpdb;
        global $PRESENTATION_TYPE;
        global $RESEARCH_AREAS;
        $joint_table = "wp_or_registration as r LEFT JOIN wp_or_registration_evaluation as e ON r.hash_id = e.evaluation_hash_id LEFT JOIN wp_or_registration_presentations as p ON p.person_hash_id = e.evaluation_hash_id";
        $registration_table = "wp_or_registration";
        $evaluation_table = 'wp_or_registration_evaluation';
        $ra_filter = 'none';


        if (isset($_POST['save_settings'])) {
            foreach ($_POST['session-name'] as $id => $session_name) {
                if($session_name != 'none'){
                    $sql = $wpdb->prepare("SELECT * FROM $joint_table WHERE hash_id = %s", $id);
                    $result = $wpdb->get_row($sql);

                    if(!isset($_POST['field-id'][$id])){

                        $presentation_data = array(
                            'post_title'    => $result->first_name . ' ' . $result->last_name,
                            'post_content'  => '',
                            'post_status'   => 'publish',
                            'post_type'     => 'presentation',
                            'meta_input'    => array(
                                'first_name' => $result->first_name,
                                'last_name' => $result->last_name,
                                'research_area' => $result->research_area,
                                'presentation_title' => $result->display_title,
                                'abstract_pdf' => $result->pdf,
                                'presentation_type' => $result->decision,
                                'hash_id' => $id,
                                'presentation_session' => $_POST['session-name'][$id],
                                'presentation_start' => $_POST['session-start'][$id],
                                'presentation_end' => $_POST['session-end'][$id],
                                'poster_number' => $_POST['session-poster'][$id],

                            )
                        );
                        
                        // Insert the post into the database
                        $post_id = wp_insert_post($presentation_data);
                    } else {
                        $update_presentation_data = array(
                            'ID'            => $_POST['field-id'][$id],
                            'post_title'    => $result->first_name . ' ' . $result->last_name,
                            'post_content'  => '',
                            'post_status'   => 'publish',
                            'post_type'     => 'presentation',
                            'meta_input'    => array(
                                'first_name' => $result->first_name,
                                'last_name' => $result->last_name,
                                'research_area' => $result->research_area,
                                'presentation_title' => $result->display_title,
                                'abstract_pdf' => $result->pdf,
                                'presentation_type' => $result->decision,
                                'hash_id' => $id,
                                'presentation_session' => $_POST['session-name'][$id],
                                'presentation_start' => $_POST['session-start'][$id],
                                'presentation_end' => $_POST['session-end'][$id],
                                'poster_number' => $_POST['session-poster'][$id],

                            )
                        );
                        
                        $post_id = wp_update_post($update_presentation_data);
                    }

                    if(isset($_POST['delete'][$id])){
                        $delete = wp_delete_post($_POST['field-id'][$id], true);
                    }
                }
            }
        }


        if (isset($_POST['ra_filter'])) {
            $ra_filter = $_POST['ra_filter'];
        }



        global $STATUS_CODES;
        $query = "SELECT * FROM $joint_table WHERE (decision = 1 OR decision = 2)";
        


        if ($ra_filter != 'none') {
            $query .= " AND research_area='$RESEARCH_AREAS[$ra_filter]'";
            $query_array[] = array(
                'key' => 'research_area',
                'value' => $RESEARCH_AREAS[$ra_filter],
                'compare' => '=',
            );
        }
        if (isset($_POST['type_filter'])) {
            $type_filter = $_POST['type_filter'];
            if ($type_filter != 'none') {
                $query .= " AND decision=$type_filter";
                $query_array[] = array(
                    'key' => 'presentation_type',
                    'value' => $type_filter,
                    'compare' => '=',
                );
            }
        }

        if (isset($_POST['session_filter'])) {
            $session_filter = $_POST['session_filter'];
            if ($session_filter != 'none') {
                // $query .= " AND decision=$type_filter";
                $query_array[] = array(
                    'key' => 'presentation_session',
                    'value' => $session_filter,
                    'compare' => '=',
                );
            }
        }

        $presentation_posts = new WP_Query(array(
            'post_type' => 'presentation', // Replace with your custom post type
            'posts_per_page' => -1,
        ));
        
        $presentation_post_ids = array();
        while ($presentation_posts->have_posts()) {
            $presentation_posts->the_post();
            $presentation_post_ids[] = get_post_meta(get_the_ID(), 'hash_id', true); // Replace with the meta key for your custom person ID field
        }
        wp_reset_postdata();


        $results = $wpdb->get_results($query);



        $presentation_posts = new WP_Query(array(
            'post_type' => 'presentation', // Replace with your custom post type
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                $query_array
            )
        ));
        
        if($_POST['assigned_filter'] != '1') while ($presentation_posts->have_posts()) {
            $presentation_posts->the_post();
            $post_id = get_the_ID();
            $id = get_post_meta($post_id, 'hash_id', true);
            $presentation_session = get_post_meta($post_id, 'presentation_session', true);
            echo '<tr style="background-color:#ffa;">';
            echo "<td>" . get_post_meta($post_id, 'first_name', true) . "</td>";
            echo "<td>" . get_post_meta($post_id, 'last_name', true) . "</td>";
            echo "<td>" . get_post_meta($post_id, 'presentation_title', true) . "</td>";
            echo "<td>" . get_post_meta($post_id, 'research_area', true) . "</td>";
            echo "<td> <a href=\"". get_post_meta($post_id, 'abstract_pdf', true) . "?timestamp=" . time() . "\">" . basename(get_post_meta($post_id, 'abstract_pdf', true)) . "</a></td>";
            echo '<td><strong>' . array_search(get_post_meta($post_id, 'presentation_type', true), $PRESENTATION_TYPE) . '</strong> <br>';
            echo "</td>";
            echo '<td><select name="session-name[' . $id . ']">';
            echo '<option value="none">Select session</option>';
            $session_args = array(
                'post_type' => 'session', 
                'posts_per_page' => -1, // To retrieve all posts, use -1
            );
            $session_query = new WP_Query($session_args);
            if ($session_query->have_posts()) {
                while ($session_query->have_posts()) {
                    $session_query->the_post();
                    // Retrieve and display the custom field values
                    $display = get_post_meta(get_the_ID(), 'display_title', true);
                    if (get_post_meta(get_the_ID(), 'session_type', true) != -1){
                        if (get_the_ID() == $presentation_session){
                            echo '<option value="' . get_the_ID() . '" selected>' . $display . '</option>';
                        } else {
                            echo '<option value="' . get_the_ID() . '">' . $display . '</option>';
                        }
                    }
                }
            }
            echo '</select></td>';
            echo '<td><label>Start: </label><input type="time" name="session-start[' . $id . ']" value="' . get_post_meta($post_id, 'presentation_start', true) . '"><br>
            <label>End: </label><input type="time" name="session-end[' . $id . ']" value="' . get_post_meta($post_id, 'presentation_end', true) . '"></td>';
            echo '<td><input type="number" name="session-poster[' . $id . ']" value="' . get_post_meta($post_id, 'poster_number', true) . '"></td>';
            echo '<td><input type="checkbox" name="delete[' . $id . ']"> <input type="text" name="field-id[' . $id . ']" value="'. $post_id .'" hidden></td>';
            echo "</tr>";
        }
        wp_reset_postdata();



        if($_POST['assigned_filter'] != '2') foreach ($results as $result) {
            $id = $result->hash_id;
            if (!in_array($id, $presentation_post_ids)) {
                $first_name = $result->first_name;
                $last_name = $result->last_name;
                // $affiliation = $result->affiliation;
                $presentation_title = $result->display_title;
                $abstract_pdf = $result->pdf;
                $research_area = $result->research_area;
                $presentation_type = "";
                
                $decision = $result->decision;
                $args = array(
                    'post_type' => 'session', 
                    'posts_per_page' => -1, // To retrieve all posts, use -1
                );
                $query = new WP_Query($args);
                echo '<tr style="background-color:;">';
                echo "<td>$first_name</td>";
                echo "<td>$last_name</td>";
                echo "<td>$presentation_title</td>";
                echo "<td>" . $research_area . "</td>";
                echo "<td> <a href=\"". $result->pdf . "?timestamp=" . time() . "\">" . basename($result->pdf) . "</a></td>";
                echo '<td><strong>' . array_search($decision, $PRESENTATION_TYPE) . '</strong> <br>';
                echo "</td>";
                echo '<td><select name="session-name[' . $id . ']">';
                echo '<option value="none">Select session</option>';
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        // Retrieve and display the custom field values
                        $display = get_post_meta(get_the_ID(), 'display_title', true);
                        if (get_post_meta(get_the_ID(), 'session_type', true) != -1)
                            echo '<option value="' . get_the_ID() . '">' . $display . '</option>';
                    }
                }
                echo '</select></td>';
                echo '<td><label>Start: </label><input type="time" name="session-start[' . $id . ']"><br>
                <label>End: </label><input type="time" name="session-end[' . $id . ']"></td>';
                echo '<td><input type="number" name="session-poster[' . $id . ']"></td>';
                echo '<td><input type="checkbox"></td>';
                echo "</tr>";

        }
    }


        ?>

</table></form>


</div>

<script>
    jQuery(document).ready(function ($) {
        $('#user_select_field').change(function () {
            $('#filter').submit();
        });
        $('#ra_filter_select').change(function () {
            $('#filter').submit();
        });
        $('#type_filter_select').change(function () {
            $('#filter').submit();
        });
        $('#session_filter_select').change(function () {
            $('#filter').submit();
        });
        $('#assigned_filter_select').change(function () {
            $('#filter').submit();
        });
    });



</script>
<script>
// Add event listener when the DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
    // Select the button element
    var incrementButton = document.getElementById("incrementButton");

    // Add click event listener to the button
    incrementButton.addEventListener("click", function() {
        // Select all input fields with names like "session-poster[wild]"
        var posterInputs = document.querySelectorAll('input[name^="session-poster["]');

        // Counter for incrementing
        var count = 1;

        // Loop through each input field
        posterInputs.forEach(function(input) {
            // Assign incremented value to the input field
            input.value = count++;
        });
    });
});
</script>