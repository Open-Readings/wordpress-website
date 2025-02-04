<div id=displayContainer>




    <?php

    require_once OR_PLUGIN_DIR . 'second-evaluation/second-eval-functions.php';
    global $wpdb;
    $reg_table = "wp_or_registration";
    $eval_table = 'wp_or_registration_evaluation';
    $pres_table = 'wp_or_registration_presentations';
    $joined_table = "$eval_table t1 INNER JOIN $reg_table t2 ON t1.evaluation_hash_id = t2.hash_id INNER JOIN $pres_table t3 ON t3.person_hash_id = t1.evaluation_hash_id";
    $results = $wpdb->get_results("SELECT * FROM $joined_table", ARRAY_A);
    $update = $wpdb->get_var("SELECT COUNT(*) FROM $joined_table WHERE status=2");
    $accepted = $wpdb->get_var("SELECT COUNT(*) FROM $joined_table WHERE status=1");
    $rejected = $wpdb->get_var("SELECT COUNT(*) FROM $joined_table WHERE status=3");
    $not_checked = $wpdb->get_var("SELECT COUNT(*) FROM $joined_table WHERE status=0");

    echo '<h2>Statistics</h2>';
    echo '<p>Accepted: ' . $accepted . ', Not checked: ' . $not_checked . ', Rejected: ' . $rejected . ', Waiting for update: ' . $update . '</p>';
    
    echo '
<div id="resultContainer">
';

    echo '<table border="1">';
    echo '<tr><th>Status</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Research area</th><th>Presentation type</th><th>Hash ID</th></tr>';
    
    
    // Process the fetched data
    foreach ($results as $result) {
        $color = "";
        if ($result['status'] == 1) {
            $color = "#66ff66";
        } else if ($result['status'] == 3) {
            $color = "#ff7777";
        } else if ($result['status'] == 2) {
            $color = "#bbbbff";
        }
        echo '<tr style="background-color:' . $color . '">';
        echo '<td>' . $result['status'] . '</td>';
        echo '<td>' . $result['first_name'] . '</td>';
        echo '<td>' . $result['last_name'] . '</td>';
        echo '<td>' . $result['email'] . '</td>';
        echo '<td>' . $result['research_area'] . '</td>';
        echo '<td>' . $result['presentation_type'] . '</td>';
        echo '<td>' . $result['hash_id'] . '</td>';
        echo '</tr>';
    }
    echo '
</table>
</div>
';

    ?>
</div>