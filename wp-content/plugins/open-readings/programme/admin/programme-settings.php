<h2>Sessions Manager</h2>
<?php
if (isset($_POST['session-name'])) {
    echo "<p><strong>" . add_session() . "</strong></p>'";
}
?>
<form method="post">
    <input type="submit" value="CREATE NEW SESSION"><br>
    <label for="session-name"> Session name: </label>
    <input type="text" name="session-name"> <br>
    <label for="session-display-name"> Display name: </label>
    <input type="text" name="session-display-name"> <br>
    <label for="session-type"> Session type: </label> <br>
    <?php

    $session_types = get_field_object('session_type_field');
    if ($session_types) {
        echo '<select name="session-type">';
        foreach ($session_types['choices'] as $key => $value) {
            echo '<option value="' . $key . '">' . $value . '</option>';
        }
        echo '</select>';
    }
    ?><br>
    <label for="session-moderator"> Session moderator: </label> <br>
    <input type="text" name="session-moderator"> <br>
    <label for="session-start"> Session start: </label> <br>
    <input type="datetime-local" name="session-start"> <br>
    <label for="session-end"> Session end: </label> <br>
    <input type="datetime-local" name="session-end"> <br>

</form>


<div>
    <table cellspacing=0 cellpadding=5 border=1 bordercolor=white width=100%>
        <tr>
            <th>ID</th>
            <th>Session</th>
            <th>Display name</th>
            <th>Type</th>
            <th>Moderator</th>
            <th>Start</th>
            <th>End</th>
        </tr>
        <?php
        $args = array(
            'post_type' => 'session',
            'posts_per_page' => -1, // To retrieve all posts, use -1
            'meta_query' => array(
                array(
                    'key' => 'short_title', // Replace 'firstname' with the name of your custom field
                    'compare' => 'EXISTS', // Check if the custom field exists
                ),
                array(
                    'key' => 'display_title', // Replace 'lastname' with the name of your custom field
                    'compare' => 'EXISTS', // Check if the custom field exists
                ),
            ),
        );

        $session_type = array(
            0 => 'Oral',
            1 => 'Poster',
            2 => 'Workshop',
            3 => 'Special event',
            4 => 'Speaker',
            5 => 'Break',
            6 => 'Option 6',
            7 => 'Option 7',
            8 => 'Option 8'
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Retrieve and display the custom field values
                $short = get_post_meta(get_the_ID(), 'short_title', true);
                $display = get_post_meta(get_the_ID(), 'display_title', true);
                $moderator = get_post_meta(get_the_ID(), 'session_moderator', true);
                $type = get_post_meta(get_the_ID(), 'session_type', true);
                $start = get_post_meta(get_the_ID(), 'session_start', true);
                $end = get_post_meta(get_the_ID(), 'session_end', true);
                $current_time_obj = new DateTime('2025-01-01 00:00:00');
                $compare_time_obj = new DateTime($start);
                $color = ($current_time_obj > $compare_time_obj) ? '#fcc' : '';
                if ($type >= 0) {
                    echo '<tr style="background-color:' . $color . '">';
                    echo '<td>' . get_the_ID() . '</td>';
                    echo '<td>' . $short . '</td>';
                    echo '<td>' . $display . '</td>';
                    echo '<td>' . $type . '</td>';
                    echo '<td>' . $moderator . '</td>';
                    echo '<td>' . $start . '</td>';
                    echo '<td>' . $end . '</td>';
                    echo '</tr>';
                }
            }
            wp_reset_postdata();
        } else {
            // No custom posts found
            echo 'No custom posts found.';
        }
        ?>
    </table>

    <?php

    function add_session()
    {
        $my_post = array(
            'post_title' => $_POST['session-name'],
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'session',
            'meta_input' => array(
                'short_title' => $_POST['session-name'],
                'display_title' => $_POST['session-display-name'],
                'session_type' => $_POST['session-type'],
                'session_start' => date('Y-m-d H:i:s', strtotime($_POST['session-start'])),
                'session_end' => date('Y-m-d H:i:s', strtotime($_POST['session-end'])),
                'session_moderator' => $_POST['session-moderator'],
            )
        );

        // Insert the post into the database
        $post_id = wp_insert_post($my_post);

        // Check if the post was successfully inserted
        if ($post_id) {
            return 'Custom post [' . $_POST['session-display-name'] . '] added with ID: ' . $post_id;
        } else {
            return 'Error adding custom post.';
        }
    }

    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Get the element with the name "session-start"
            var sessionStartInput = document.querySelector('input[name="session-start"]');

            // Check if the element is found
            if (sessionStartInput) {
                // Get the current value of the input element
                var currentValue = sessionStartInput.value;

                // Reformat the date string to "Y-m-d H:i:s"
                var formattedDate = new Date(currentValue).toISOString().slice(0, 19).replace('T', ' ');

                // Set the reformatted value back to the input field
                sessionStartInput.value = formattedDate;
            } else {
                console.log("Element with name 'session-start' not found");
            }
        });
    </script>