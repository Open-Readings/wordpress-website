<?php

?>


<h2>Evaluation System List</h2>



<div style="display:flex; flex-direction:row; justify-content: left;">
    <?php




    function print_eval_statistics()
    {
        global $wpdb;
        global $STATUS_CODES;
        global $table_name;
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
    ?>
</div>

<div>





    <h3>filter research areas: </h3>
    <form method="POST" id="filter">
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
            <th>Email</th>
            <th>Presentation Title</th>
            <th>Abstract PDF</th>
            <th>Research Area</th>
            <th>Decision</th>
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
            foreach ($_POST['decision'] as $id => $decision) {
                $wpdb->update($evaluation_table, array('decision' => $decision), array('evaluation_hash_id' => $id));
            }
        }


        if (isset($_POST['ra_filter'])) {
            $ra_filter = $_POST['ra_filter'];
        }



        global $STATUS_CODES;
        $query = "SELECT * FROM $joint_table WHERE `status`=" . $STATUS_CODES["Accepted"] . "";


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
            // $affiliation = $result->affiliation;
            $presentation_title = $result->title;
            $abstract_pdf = $result->pdf;
            $research_area = $result->research_area;
            $presentation_type = "";
            $comment = $result->comment;
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
            echo '<tr style="background-color: ' . $color . ';">';
            echo "<td>$first_name</td>";
            echo "<td>$last_name</td>";
            echo "<td><a name=\"{$result->hash_id}\" href=\"mailto:{$result->email}\">{$result->email}</a></td>";
            echo "<td>$presentation_title</td>";
            echo "<td> <a href=\"{$result->pdf}\">" . basename($result->pdf) . "</a></td>";
            echo "<td>" . $research_area . "</td>";
            echo '<td>preffered presentation type: <strong>' . $result->presentation_type . '</strong> <br>';
            echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="3" ' . (($decision == 3) ? "checked>" : ">") . 'Reject</input>';
            echo '<br>';
            echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="1" ' . (($decision == 1) ? "checked>" : ">") . array_search(1, $PRESENTATION_TYPE) . '</input>';
            echo '<br>';
            echo '<input type="radio" name="decision[' . $result->hash_id . ']" value="2" ' . (($decision == 2) ? "checked>" : ">") . array_search(2, $PRESENTATION_TYPE) . '</input>';

            echo "</td>";
            echo "<td>$comment</td>";

            echo "</tr>";

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
        $('#type_filter_select').change(function () {
            $('#filter').submit();
        });
    });



</script>