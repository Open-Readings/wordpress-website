<h2>Evaluation System Settings</h2>


<form method="POST">
<input type="submit" name="expand-evaluation-table" style="background-color:red" value="ADD COLUMNS TO EVALUATION TABLE">
</form>
<?php
if (isset($_POST['expand-evaluation-table'])) {
    $message = expand_evaluation_table();
    echo $message;
}
?>
<div style="display:flex; flex-direction:row; justify-content: left;">

    <?php
    function print_eval_statistics()
    {
        global $wpdb;
        global $STATUS_CODES;
        global $RESEARCH_AREAS;
        global $PRESENTATION_TYPE;
        $table_name = "wp_or_registration_evaluation";

        //$num_confirmed = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS NC FROM $table_name WHERE `status`='1'"),0); OLD
        $num_accepted = $wpdb->get_var("SELECT COUNT(*) AS NC FROM $table_name WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND (decision=" . $PRESENTATION_TYPE['Oral'] . " OR decision=" . $PRESENTATION_TYPE['Poster'] . ")");
        $num_notchecked = $wpdb->get_var("SELECT COUNT(*) AS NU FROM $table_name WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND decision=0");
        $num_rejected = $wpdb->get_var("SELECT COUNT(*) AS NR FROM $table_name WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND decision=3");


        echo "<h2>Abstracts</h2>";
        echo "<p>Accepted: {$num_accepted}, Not checked: {$num_notchecked}, Rejected: {$num_rejected}</p>";


        $num_posters = $wpdb->get_var("SELECT COUNT(*) AS num_posters FROM $table_name WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Poster']);
        $num_orals = $wpdb->get_var("SELECT COUNT(*) AS num_orals FROM $table_name WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Oral']);
        echo "<p> Accepted presentations:</p>";
        echo "<p>Posters: {$num_posters}, Orals: {$num_orals}</p>";


        $num_areas_total = $wpdb->get_results("SELECT research_area, COUNT(*) AS num_areas_total
        FROM wp_or_registration_evaluation AS e LEFT JOIN wp_or_registration as r ON e.evaluation_hash_id = r.hash_id WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND 
        (decision=" . $PRESENTATION_TYPE['Poster'] . " OR decision=" . $PRESENTATION_TYPE['Oral'] . ") GROUP BY research_area");

        echo "<h2>Areas (Accepted)</h2>";

        foreach ($num_areas_total as $key => $total_num) {

            $num_area_orals = $wpdb->get_var("SELECT COUNT(*) AS num_areas_orals FROM wp_or_registration_evaluation AS e LEFT JOIN wp_or_registration as r ON e.evaluation_hash_id = r.hash_id WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND  decision=" . $PRESENTATION_TYPE['Oral'] . " AND research_area='" . $total_num->research_area . "'");
            $num_area_poster = $total_num->num_areas_total - $num_area_orals;
            echo "<strong> " . $total_num->research_area . "  </strong>
            <a>  posters: " . $num_area_poster . ", orals: " . $num_area_orals . "</a>
            </br>";
        }


    }
    
    echo "<div style='margin-right: 20px;'>";
    print_eval_statistics();
    echo "</div>";
    // get all users with the or_evalutaion_member role
    $users = get_users(array('role__in' => array('or_main_evaluator', 'administrator')));

    // selection of users
    echo '<form method="POST" id="filter">';
    echo '<label for="user_select_field">Select user: </label>';
    echo '<select id="user_select_field" name="user">';
    echo '<option value="-1">Select user</option>';
    foreach ($users as $user) {
        if (isset($_POST['user']) && $_POST['user'] == $user->ID) {
            echo '<option value="' . $user->ID . '" selected>' . $user->display_name . '</option>';
        } else {
            echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
        }
    }
    echo "</select>";

    


    ?>
</div>

<div>
    <h3>filter research areas: </h3>
    <select name="ra_filter" id="ra_filter_select">
        <option value="none">all</option>
        <?php
        global $RESEARCH_AREAS;
        for ($i = 1; $i <= count($RESEARCH_AREAS); $i++) {
            if (isset($_POST['ra_filter']) && $_POST['ra_filter'] == $i) {
                echo '<option value="' . $i . '" selected>' . $RESEARCH_AREAS[$i] . '</option>';
            } else
                echo '<option value="' . $i . '">' . $RESEARCH_AREAS[$i] . '</option>';
        }

        ?>
    </select>

</div>
<button name="save_settings" type="submit">Save Settings</button>
<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=white width=100%>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Affiliation</th>
            <th>Presentation Title</th>
            <th>Abstract PDF</th>
            <th>Research Area</th>
            <th>Check</th>
        </tr>
        <?php
        global $wpdb;
        $joint_table = "wp_or_registration as r LEFT JOIN wp_or_registration_evaluation as e ON r.hash_id = e.evaluation_hash_id LEFT JOIN wp_or_registration_presentations as p ON p.person_hash_id = e.evaluation_hash_id";
        $registration_table = "wp_or_registration";
        $evaluation_table = 'wp_or_registration_evaluation';
        $user_id = -1;
        $ra_filter = 'none';
        if (isset($_POST['user'])) {
            $user_id = $_POST['user'];


        }


        if (isset($_POST['ra_filter'])) {
            $ra_filter = $_POST['ra_filter'];
        }

        if (isset($_POST['save_settings'])) {
            if ($user_id != -1) {
                
                $checked_before = $wpdb->get_results("SELECT * FROM $joint_table WHERE checker=$user_id");
                foreach ($checked_before as $cb) {
                    if($cb->research_area != $ra_filter && $ra_filter!='none')
                        continue;
                    if (empty($_POST['check']) || !in_array($cb->hash_id, $_POST['check'])) {
                        $wpdb->update($evaluation_table, array('checker' => 0), array('evaluation_hash_id' => $cb->evaluation_hash_id));
                    }
                }
                if(isset($_POST['check']))
                {
                    $check = $_POST['check'];
                    foreach ($check as $id) {
                        $wpdb->update($evaluation_table, array('checker' => $user_id), array('evaluation_hash_id' => $id));
                }
            }
            } else {
                echo "Please select a user";
            }

        }

        global $STATUS_CODES;
        $query = "SELECT * FROM $joint_table WHERE `status`=" . $STATUS_CODES['Accepted'] . " and (checker=0 or checker = $user_id)";


        if ($ra_filter != 'none') {
            $query .= " AND research_area='$RESEARCH_AREAS[$ra_filter]'";
        }
        $query .= " ORDER BY checker DESC;";
        $results = $wpdb->get_results($query);
        foreach ($results as $result) {
            $first_name = $result->first_name;
            $last_name = $result->last_name;
            $affiliation = $result->institution;
            $presentation_title = $result->title;
            $abstract_pdf = $result->pdf;
            $research_area = $result->research_area;
            $color = "";
            if ($result->decision == 1 || $result->decision == 2) {
                $color = "#66ff66";
            } else if ($result->decision == 3) {
                $color = "#ff7777";
            }
            echo '<tr style="background-color: ' . $color . ';">';
            echo "<td>$first_name</td>";
            echo "<td>$last_name</td>";
            echo "<td>$affiliation</td>";
            echo "<td>$presentation_title</td>";
            echo "<td> <a href=\"{$abstract_pdf}\">" . basename($abstract_pdf) . "</a></td>";
            echo "<td>" . $research_area . "</td>";
            if ($result->checker == $user_id || (isset($_POST['save_settings']) && !empty($_POST['check']) && in_array($result->hash_id, $_POST['check']))) {
                echo "<td><input type='checkbox' name='check[" . $result->hash_id . "]' value='$result->hash_id' checked></td>";
            } else if ((!empty($checked_before) and !in_array($result->hash_id, $checked_before)) || $result->checker == 0)
                echo "<td><input type='checkbox' name='check[" . $result->hash_id . "]' value='$result->hash_id'></td>";
            echo "</tr>";

        }
        if (isset($_POST['save_settings']) and $user_id != -1) {
            echo "Settings saved";
        }

        function expand_evaluation_table(){
            global $wpdb;

            $table_name = 'wp_or_registration_evaluation';

            $columns_exist = $wpdb->get_row("SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
                                 WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                                 AND TABLE_NAME = '$table_name' 
                                 AND (COLUMN_NAME = 'sent_email' OR COLUMN_NAME = 'evaluation' OR COLUMN_NAME = 'checker' OR COLUMN_NAME = 'comment' OR COLUMN_NAME = 'decision')");

            if (!$columns_exist) {
                // Define the SQL query to add columns
                $sql = "ALTER TABLE $table_name 
                        ADD COLUMN sent_email INT(11) NOT NULL DEFAULT 0,
                        ADD COLUMN evaluation INT(11),
                        ADD COLUMN checker INT(11) DEFAULT 0,
                        ADD COLUMN comment VARCHAR(1000) DEFAULT '',
                        ADD COLUMN decision INT(11) DEFAULT 0";

                // Execute the SQL query
                $result = $wpdb->query($sql);
            }
            if(isset($result)){
                if($result){
                    return '<p>SUCCESS</p>';
                } else {
                    return '<p>FAIL</p>';
                }} else {
                    return '<p>FAIL</p>';
                }
        }

        ?>

    </table>


</div>

<script>
    jQuery(document).ready(function ($) {
        $('#user_select_field').change(function () {
            $('#filter').submit();
        });
        $('#ra_filter_select').change(function () {
            $('#filter').submit();
        });
    });

</script>