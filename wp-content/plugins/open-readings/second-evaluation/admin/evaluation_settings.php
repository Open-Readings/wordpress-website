<?php
include_once __DIR__ . '/../second-eval-functions.php';
?>

<h1>Evaluation System Settings</h1>
<h2 style="color:#d00">Per čia vertintojams priskiriami abstraktai</h2>

<!-- <form method="POST">
<input type="submit" name="expand-evaluation-table" style="background-color:red" value="ADD COLUMNS TO EVALUATION TABLE">
</form> -->
<?php
    if (isset($_POST['expand-evaluation-table'])) {
        $message = expand_evaluation_table();
        echo $message;
    }
?>
<div style="display:flex; flex-direction:row; justify-content: left;">

    <?php
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
    <h3>Filters</h3>
    <label>Research area: </label>
    <?php research_area_filter(); ?>
    <?php
        echo "<label for='visa-filter'>Needs visa: </label>";
        if (isset($_POST['visa-filter'])) {
            echo "<input type='checkbox' id='visa-filter' name='visa-filter' checked>";
        } else {
            echo "<input type='checkbox' id='visa-filter' name='visa-filter'>";
        }
        echo "<label for='foreign-filter'>Foreign: </label>";
        if (isset($_POST['foreign-filter'])) {
            echo "<input type='checkbox' id='foreign-filter' name='foreign-filter' checked>";
        } else {
            echo "<input type='checkbox' id='foreign-filter' name='foreign-filter'>";
        }
    ?>

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
        $joint_table = "wp_or_registration as r LEFT JOIN wp_or_registration_evaluation as e ON r.hash_id = e.evaluation_hash_id LEFT JOIN wp_or_registration_presentations as p ON p.person_hash_id = e.evaluation_hash_id";
        $registration_table = "wp_or_registration";
        $evaluation_table = 'wp_or_registration_evaluation';
        $user_id = -1;
        $ra_filter = 'none';
        $visa_filter = false;
        $foreign_filter = false;
        if (isset($_POST['user'])) {
            $user_id = $_POST['user'];
        }

        if (isset($_POST['ra_filter'])) {
            $ra_filter = $_POST['ra_filter'];
        }

        if (isset($_POST['visa-filter'])) {
            $visa_filter = true;
        }
        if (isset($_POST['foreign-filter'])) {
            $foreign_filter = true;
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

        $query = "SELECT * FROM $joint_table WHERE `status`=" . $STATUS_CODES['Accepted'] . " and (checker=0 or checker = $user_id)";


        if ($ra_filter != 'none') {
            $query .= " AND research_area='$RESEARCH_AREAS[$ra_filter]'";
        }
        if ($visa_filter) {
            $query .= " AND needs_visa=1";
        }
        if ($foreign_filter) {
            $query .= " AND country!='Lithuania'";
        }
        $query .= " ORDER BY checker DESC;";
        $results = $wpdb->get_results($query);
        foreach ($results as $result) {
            $first_name = $result->first_name;
            $last_name = $result->last_name;
            $affiliation = $result->institution;
            $presentation_title = $result->title;
            $abstract_pdf = $result->pdf;
            $pdf_url = str_replace(ABSPATH, site_url('/'), subject: $result->pdf);
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
            echo "<td> <a href=\"{$pdf_url}\">" . basename($abstract_pdf) . "</a></td>";
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
        $('#visa-filter').change(function () {
            $('#filter').submit();
        });
        $('#foreign-filter').change(function () {
            $('#filter').submit();
        });
    });

</script>
