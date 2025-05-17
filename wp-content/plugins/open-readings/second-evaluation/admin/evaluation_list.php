<?php
    include_once __DIR__ . '/../second-eval-functions.php';
    global $wpdb;
    global $PRESENTATION_TYPE;
    global $RESEARCH_AREAS;
    $reg_table = "wp_or_registration";
    $eval_table = 'wp_or_registration_evaluation';
    $pres_table = 'wp_or_registration_presentations';
    $joined_table = "$eval_table t1 INNER JOIN $reg_table t2 ON t1.evaluation_hash_id = t2.hash_id INNER JOIN $pres_table t3 ON t3.person_hash_id = t1.evaluation_hash_id";
    $ra_filter = 'none';

    if (isset($_POST['save_settings'])) {
        foreach ($_POST['decision'] as $id => $decision) {
            $wpdb->update($eval_table, array('decision' => $decision), array('evaluation_hash_id' => $id));
        }
    }
?>

<h1>Evaluation System List</h1>
<h2 style="color:#d00">Dalyvių sąrašas [peržiūra + galima pakeisti Oral/Poster/Reject]</h2>

<div style="display:flex; flex-direction:row; justify-content: left;">
    <?php
        echo "<div style='margin-right: 20px;'>";
        print_eval_statistics();
        echo "</div>";
    ?>
</div>

<form method="POST" id="filter">
<div>
    <h3>Filter research areas: </h3>
        <?php research_area_filter(); ?>

        <select name="type_filter" id="type_filter_select">
            <option value="none">all</option>
            <?php
                global $PRESENTATION_TYPE;
                for ($i = 1; $i < count($PRESENTATION_TYPE) + 1; $i++) {
                    if (isset($_POST['type_filter']) && $_POST['type_filter'] == $i) {
                        echo '<option value="' . $i . '" selected>' . array_search($i, $PRESENTATION_TYPE) . '</option>';
                    } else
                        echo '<option value="' . $i . '">' . array_search($i, $PRESENTATION_TYPE) . '</option>';
                }
            ?>
        </select>
</div>

<button name="export_csv" type="submit">Export as csv</button>
<button name="save_settings" type="submit">Save All decisions</button>

<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=white width=100%>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Study level</th>
            <th>Email</th>
            <th>Presentation Title</th>
            <th>Abstract PDF</th>
            <th>Research Area</th>
            <th>Grade</th>
            <th>Av. Grade</th>
            <th>Decision</th>
            <th>Comment</th>
            <th>Evaluator</th>
        </tr>
        <?php
            if (isset($_POST['ra_filter'])) {
                $ra_filter = $_POST['ra_filter'];
            }

            global $STATUS_CODES;
            $query = "SELECT * FROM $joined_table WHERE `status`=" . $STATUS_CODES["Accepted"] . "";


            if ($ra_filter != 'none') {
                $query .= " AND research_area='$RESEARCH_AREAS[$ra_filter]'";
            }
            if (isset($_POST['type_filter'])) {
                $type_filter = $_POST['type_filter'];
                if ($type_filter != 'none') {
                    $query .= " AND decision=$type_filter";
                }
            }

            $results = $wpdb->get_results($query);

            

            foreach ($results as $result) {
                $first_name = $result->first_name;
                $last_name = $result->last_name;
                $presentation_title = $result->display_title;
                $person_title = $wpdb->get_var("SELECT title FROM wp_or_registration WHERE hash_id='$result->hash_id'"); //get_post_meta($result->hash_id, 'title', true);
                $research_area = $result->research_area;
                $presentation_type = "";
                $comment = $result->comment;
                $user_info = get_userdata($result->checker);
                if ($user_info){
                    $evaluator = $user_info->first_name . " " . $user_info->last_name;
                } else {
                    $evaluator = "";
                }
                
                if ($result->decision == 1) {
                    $presentation_type = "Oral";
                } else if ($result->decision == 2) {
                    $presentation_type = "Poster";
                } else if ($result->decision == 3) {
                    $presentation_type = "Rejected";
                }
                $color = "";
                if ($result->decision == 1 || $result->decision == 2) {
                    $color = "#66ff66";
                } else if ($result->decision == 3) {
                    $color = "#ff7777";
                }
                $decision = $result->decision;
                // $pdf_url = str_replace(ABSPATH, site_url('/'), subject: $result->pdf) . "?" . time();
                $pdf_url = normalize_url($result->pdf) . "?" . time();
                echo '<tr style="background-color: ' . $color . ';">';
                echo "<td>$first_name</td>";
                echo "<td>$last_name</td>";
                echo "<td>$person_title</td>";
                echo "<td><a name=\"{$result->hash_id}\" href=\"mailto:{$result->email}\">{$result->email}</a></td>";
                echo "<td>$presentation_title</td>";
                echo "<td> <a href=\"{$pdf_url}\">" . basename($result->pdf) . "</a></td>";
                echo "<td>" . $research_area . "</td>";
                echo "<td>" . $result->evaluation . "</td>";
                echo "<td>" . $result->grade_average . "</td>";
                echo '<td>Preferred presentation type: <strong>' . $result->presentation_type . '</strong> <br>';
                echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="3" ' . (($decision == 3) ? "checked>" : ">") . 'Reject</input>';
                echo '<br>';
                echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="1" ' . (($decision == 1) ? "checked>" : ">") . array_search(1, $PRESENTATION_TYPE) . '</input>';
                echo '<br>';
                echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="2" ' . (($decision == 2) ? "checked>" : ">") . array_search(2, $PRESENTATION_TYPE) . '</input>';

                echo "</td>";
                echo "<td>$comment</td>";
                echo "<td>$evaluator</td>";

                echo "</tr>";
            }
        ?>
    </table>
</div>
</form>

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
    });
</script>