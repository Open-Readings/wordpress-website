<h1>Open Readings Evaluation System</h1>
<p> Please evaluate each given abstract on scale 1-10 and decide on the acceptance of the participant. </p>
<p> If you decide to accept the participant, please select the presentation type. </p>
<p> If you decide to reject the participant, please provide a comment. </p>
<strong> Please note that you can only evaluate abstracts that have not been evaluated by you before. </strong>

<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=black width=100%>

        <tr>
            <th style="width: 50 px">Nr</th>
            <th>Study level</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Affiliation</th>
            <th>Presentation Title</th>
            <th>Abstract PDF</th>
            <th>Research Area</th>
            <th>Decision</th>
            <th>Grade</th>
            <th>Comment</th>

        </tr>
        <?php
        global $wpdb;
        global $RESEARCH_AREAS;
        global $PRESENTATION_TYPE;
        $pres_table = 'wp_or_registration_presentations';
        $reg_table = "wp_or_registration";
        $eval_table = "wp_or_registration_evaluation";
        $joined_table = "$eval_table t1 INNER JOIN $reg_table t2 ON t1.evaluation_hash_id = t2.hash_id INNER JOIN $pres_table t3 ON t3.person_hash_id = t1.evaluation_hash_id";
        $user_id = get_current_user_id();
        $user_roles = get_userdata($user_id)->roles;
        $is_admin = in_array('administrator', $user_roles);
        $save_error = false;
        $error = "";

        if ($is_admin) {
            $users = get_users(array('role__in' => array('or_main_evaluator', 'administrator')));

            // selection of users
            echo '<form method="POST" id="filter">';
            echo '<label for="user_select_field">(Admin only feature) Select user: </label>';
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
            echo "</form>";
            echo "<h2 style=\"color:#d00\">Šis puslapis matomas VERTINTOJŲ, esant reikalui or komandai per čia galima pakeisti Grade/Research Area/Decision</h2>";

        }

        if (isset($_POST['user'])) {
            $user_id = $_POST['user'];
        }

        if (isset($_POST['save_settings'])) {
            $comment = $_POST['comment'];
            $research_ar = $_POST['research_area'];
            

            $eval_id = $_POST['save_settings'];
            $update = $wpdb->update($reg_table, array('research_area' => $research_ar), array('hash_id' => $eval_id));

            if (!isset($_POST['decision'])) {
                $error = "Please select a decision";
                $save_error = true;
            } else {
                $decision = $_POST['decision'];
            }

            if (!isset($_POST['grade']) || $_POST['grade'] == 0) {
                $error = 'Please select a grade';
                $save_error = true;
            } else {
                $grade = $_POST['grade'];
            }

            
            $decision_before = $wpdb->get_var("SELECT decision FROM $eval_table WHERE evaluation_hash_id = '$eval_id'");
            if ($decision_before != 0 && !$is_admin) {
                $error = "You have already evaluated this abstract";
                $save_error = true;
            }

            if (!$save_error) {
                $update = $wpdb->update($eval_table, array('decision' => $decision, 'evaluation' => $grade, 'comment' => $comment), array('evaluation_hash_id' => $eval_id));
                if ($update) {
                    $email = $wpdb->get_var("SELECT email FROM $reg_table WHERE hash_id = '$eval_id'");
                    $presentation_title = $wpdb->get_var("SELECT title FROM $pres_table WHERE person_hash_id = '$eval_id'");
                }
            }
            if (!empty($error))
                echo '<p style="background-color:red">' . $error . '</p>';

        }
        $evals = $wpdb->get_results("SELECT t1.* , t2.title as person_title, t2.*, t3.* FROM $joined_table WHERE checker = '$user_id'");
        $person_index = 0;
        foreach ($evals as $eval) {
            $person_index++;

            $ra = $eval->research_area;
            $decision = $eval->decision;
            if ($decision == 0) {
                $color = 'white';
            } else if ($decision == 1) {
                $color = '#55ff55';
            } else if ($decision == 2) {
                $color = '#55ff55';
            } else if ($decision == 3) {
                $color = '#ff6666';
            }

            $pdf_url = str_replace(ABSPATH, site_url('/'), subject: $eval->pdf);

            echo '<tr style="background-color:' . $color . '">';
            echo '<form method="post">';
            echo '<td style="width:30px;padding:7px">' . $person_index . '</td>';
            echo '<td>' . $eval->person_title . '</td>';
            echo '<td>' . $eval->first_name . '</td>';
            echo '<td>' . $eval->last_name . '</td>';
            echo '<td>' . $eval->institution . '</td>';
            echo '<td>' . $eval->title . '</td>';
            echo "<td><a href='" . $pdf_url . "?" . time() . "'><strong>" . basename($eval->pdf) . "</strong></a></td>";
            echo '<td><select name="research_area">';
            foreach ($RESEARCH_AREAS as $key => $value) {
                echo '<option value="' . $value . '" ' . (($ra == $value) ? "selected>" : ">") . $value . '</option>';
            }
            echo '</select></td>';

            echo '<td> preferred presentation type: <strong>' . $eval->presentation_type . '</strong> <br>';
            echo '<input type="radio" name="decision" value="3" ' . (($decision == 3) ? "checked>" : ">") . 'Reject</input>';
            echo '<br>';
            echo '<input type="radio" name="decision" value="1" ' . (($decision == 1) ? "checked>" : ">") . array_search(1, $PRESENTATION_TYPE) . '</input>';
            echo '<br>';
            echo '<input type="radio" name="decision" value="2" ' . (($decision == 2) ? "checked>" : ">") . array_search(2, $PRESENTATION_TYPE) . '</input>';
            echo ' </td>';

            echo '<td>';
            echo '<select name="grade" checked=' . $eval->evaluation . '>';
            echo '<option value="0">unassigned</option>';

            for ($i = 1; $i <= 10; $i++) {
                echo '<option value="' . $i . '" ' . (($eval->evaluation == $i) ? "selected>" : ">") . $i . '</option>';
            }

            echo '</select>';
            echo '</td>';
            echo '<td>';
            echo '<textarea name="comment" rows="4" cols="50">' . $eval->comment . '</textarea></td>';
            echo '</td>';
            if ($eval->decision == 0 || $is_admin)
                echo '<td><button type="submit" name="save_settings"  value=' . $eval->evaluation_hash_id . '>Save</button></td>';
            echo '</form>';
            echo '</tr>';
        }
        ?>

    </table>
</div>

<script>
    jQuery(document).ready(function ($) {
        $('#user_select_field').change(function () {
            $('#filter').submit();
        });
    });
</script>