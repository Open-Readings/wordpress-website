<h1>Open Readings Evaluation System</h1>
<p> Please evaluate each given abstract on scale 1-10 and decide on the acceptance of the participant. </p>
<p> If you decide to accept the participant, please select the presentation type. </p>
<p> If you decide to reject the participant, please provide a comment. </p>
<strong> Please note that you can only evaluate abstracts that have not been evaluated by you before. </strong>




<?php

function send_confirmation_email($email, $comment, $decision, $presentation_title)
{
    global $PRESENTATION_TYPE;
    global $or_mailer;
    $subject = "Open Readings 2023 - Abstract Evaluation";
    $message = "Dear participant, <br><br> Thank you for your abstract submission to Open Readings 2023. 
    <br><br> Your abstract has been evaluated by the conference program committee.
     <br><br> The conference program committee has decided to allow you to present your work '$presentation_title' in the form of: " . array_search($decision, $PRESENTATION_TYPE) . "
         <br><br> $comment <br><br> 
     We hope to see you at Open Readings 2023! <br><br> Best regards, <br><br> Open Readings 2023";
    $sent = $or_mailer->send_mail($email, $subject, $message);
    return $sent;

}

function send_rejection_email($email, $comment, $presentation_title)
{
    global $PRESENTATION_TYPE;
    global $or_mailer;
    $subject = "Open Readings 2023 - Abstract Evaluation";
    $message = "Dear participant, <br><br> Thank you for your abstract submission to Open Readings 2023. 
    <br><br> Your abstract has been evaluated by the conference program committee.
     <br><br> The conference program committee has decided to reject your abstract '$presentation_title'. 
         <br><br> $comment <br><br> 
         Thank you for your interest in our conference and for taking the time to submit your abstract.<br><br> Best regards, <br><br> Open Readings 2023!";
    $sent = $or_mailer->send_mail($email, $subject, $message);
    return $sent;

}

function send_emails($email, $comment, $decision, $presentation_title)
{
    if ($decision == 3) {
        send_rejection_email($email, $comment, $presentation_title);
    } else {
        send_confirmation_email($email, $comment, $decision, $presentation_title);
    }
}



?>



<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=black width=100%>

        <tr>
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
        $presentation_table = 'wp_or_registration_presentations';
        $registration_table = "wp_or_registration";
        $evaluation_table = "wp_or_registration_evaluation";
        $joint_table = "wp_or_registration as r LEFT JOIN wp_or_registration_evaluation as e ON r.hash_id = e.evaluation_hash_id LEFT JOIN wp_or_registration_presentations as p ON p.person_hash_id = e.evaluation_hash_id";
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

        }

        if (isset($_POST['user'])) {
            $user_id = $_POST['user'];
        }

        if (isset($_POST['save_settings'])) {

            $eval_id = $_POST['save_settings'];

            if (!isset($_POST['decision'])) {
                $error = "Please select a decision";
                $save_error = true;
            }


            if (!isset($_POST['grade']) || $_POST['grade'] == 0) {
                $error = 'Please select a grade';
                $save_error = true;
            }
            $decision = $_POST['decision'];
            $grade = $_POST['grade'];
            $comment = $_POST['comment'];

            $decision_before = $wpdb->get_var("SELECT decision FROM $evaluation_table WHERE evaluation_hash_id = '$eval_id'");
            if ($decision_before != 0 && !$is_admin) {
                $error = "You have already evaluated this abstract";
                $save_error = true;

            }

            if (!$save_error) {
                $update = $wpdb->update($evaluation_table, array('decision' => $decision, 'evaluation' => $grade, 'comment' => $comment), array('evaluation_hash_id' => $eval_id));

                if ($update) {

                    $email = $wpdb->get_var("SELECT email FROM $registration_table WHERE hash_id = '$eval_id'");
                    $presentation_title = $wpdb->get_var("SELECT title FROM $presentation_table WHERE person_hash_id = '$eval_id'");
                    //send_emails($email, $comment, $decision, $presentation_title);
                }
            }
            if (!empty($error))
                echo '<p style="background-color:red">' . $error . '</p>';

        }

        $evals = $wpdb->get_results("SELECT * FROM $joint_table WHERE checker = '$user_id'");

        foreach ($evals as $eval) {

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


            echo '<tr style="background-color:' . $color . '">';
            echo '<form method="post">';
            echo '<td>' . $eval->first_name . '</td>';
            echo '<td>' . $eval->last_name . '</td>';
            echo '<td>' . $eval->institution . '</td>';
            echo '<td>' . $eval->title . '</td>';

            echo "<td><a href='" . $eval->pdf . "?" . time() . "'><strong>" . basename($eval->pdf) . "</strong></a></td>";
            echo '<td>' . $ra . '</td>';

            echo '<td> preffered presentation type: <strong>' . $eval->presentation_type . '</strong> <br>';
            echo '<input type="radio" name="decision" value="3" ' . (($decision == 3) ? "checked>" : ">") . 'Reject</input>';
            echo '<br>';
            echo '<input type="radio" name="decision" value="1" ' . (($decision == 1) ? "checked>" : ">") . array_search(1, $PRESENTATION_TYPE) . '</input>';
            echo '<br>';
            echo '<input type="radio" name="decision" value="2" ' . (($decision == 2) ? "checked>" : ">") . array_search(2, $PRESENTATION_TYPE) . '</input>';
            echo ' </td>';

            echo '<td>
                <select name="grade" checked=' . $eval->evaluation . '>
                    <option value="0">unassigned</option>
                    ';
            for ($i = 1; $i <= 10; $i++) {
                echo '<option value="' . $i . '" ' . (($eval->evaluation == $i) ? "selected>" : ">") . $i . '</option>';
            }


            echo '
                </select>
                </td>';
            echo '<td>
                <textarea name="comment" rows="4" cols="50">' . $eval->comment . '</textarea>
                </td>
            
            </td>';
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

//     var buttons = document.getElementsByName('save_settings');
    
//     Array.from(buttons).forEach(function(button) {
//     button.addEventListener('click', function(e) {
//        e.preventDefault();
//        var form1 = button.closest('form');
//        console.log(form1);
//        var userSelectField = document.getElementById('user_select_field');
//        var formData = new FormData(form1);
//        formData.append('user', userSelectField.value);
//        formData.submit();
//     });
// });

</script>