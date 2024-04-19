<h2>Evaluation System Emailer</h2>



<div style="display:flex; flex-direction:row; justify-content: left;">
    <?php

    function send_confirmation_email($email, $comment, $decision, $presentation_title)
    {
        global $PRESENTATION_TYPE;
        global $or_mailer;
        $subject = "Open Readings 2024 - Abstract Evaluation";
        $message = "Dear participant, <br><br> Thank you for your abstract submission to Open Readings 2024. 
    <br><br> Your abstract has been evaluated by the conference program committee.
     <br><br> The conference program committee has decided to allow you to present your work '$presentation_title' in the form of: " . array_search($decision, $PRESENTATION_TYPE) . "
         <br><br> $comment <br><br> 
     We hope to see you at Open Readings 2024! <br><br> Best regards, <br><br> Open Readings 2024";
        $sent = $or_mailer->send_OR_mail($email, $subject, $message);
        return $sent;

    }




    function send_rejection_email($email, $comment, $presentation_title)
    {
        global $PRESENTATION_TYPE;
        global $or_mailer;
        $subject = "Open Readings 2024 - Abstract Evaluation";
        $message = "Dear participant, <br><br> Thank you for your abstract submission to Open Readings 2024. 
    <br><br> Your abstract has been evaluated by the conference program committee.
     <br><br> The conference program committee has decided to reject your abstract '$presentation_title'. 
         <br><br> $comment <br><br> 
         Thank you for your interest in our conference and for taking the time to submit your abstract.<br><br> Best regards, <br><br> Open Readings 2023!";
        $sent = $or_mailer->send_OR_mail($email, $subject, $message);
        return $sent;

    }

    function send_emails($email, $decision, $presentation_title, $other_vars = array())
    {
        global $PRESENTATION_TYPE;
        global $or_mailer;
        $acc_body = get_option('evaluation_accept_body');
        $rej_body = get_option('evaluation_reject_body');
        $subj = get_option('evaluation_subject');



        if ($decision == 3) {
            $vars =
                array(
                    '{$title}' => $presentation_title,
                );
            $template = strtr($rej_body, $vars);
            return $or_mailer->send_or_mail($email, $subj, $template);
        } else {
            $vars =
                array(
                    '{$title}' => $presentation_title,
                    '{$decision}' => array_search($decision, $PRESENTATION_TYPE),
                    '{$link}' => '<a href="https://docs.google.com/forms/d/e/1FAIpQLSfFMjHLhjOXPNJf432hsvh-hxtBA0SvU06jovlNjkIcgYyDTQ/viewform">link</a>'
                );
            $vars = array_merge($vars, $other_vars);
            $template = strtr($acc_body, $vars);
            $template = stripslashes($template);
            return $or_mailer->send_or_mail($email, $subj, $template);
        }
    }





    function print_eval_statistics()
    {
        global $wpdb;
        global $STATUS_CODES;
        global $table_name;
        global $RESEARCH_AREAS;
        global $PRESENTATION_TYPE;
        $table_name = "wp_or_registration_evaluation";

        //$num_confirmed = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS NC FROM $table_name WHERE status='1'"),0); OLD
        $num_accepted = $wpdb->get_var("SELECT COUNT(*) AS NC FROM $table_name WHERE status=" . $STATUS_CODES["Accepted"] . " AND (decision=" . $PRESENTATION_TYPE['Oral'] . " OR decision=" . $PRESENTATION_TYPE['Poster'] . ")");
        $num_notchecked = $wpdb->get_var("SELECT COUNT(*) AS NU FROM $table_name WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=0");
        $num_rejected = $wpdb->get_var("SELECT COUNT(*) AS NR FROM $table_name WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=3");
        $num_emailed = $wpdb->get_var("SELECT COUNT(*) AS NC FROM $table_name WHERE sent_email = 1");

        echo "<h2>Abstracts</h2>";
        echo "<p>Accepted: {$num_accepted}, Not checked: {$num_notchecked}, Rejected: {$num_rejected}, Emailed: {$num_emailed}</p>";


        $num_posters = $wpdb->get_var("SELECT COUNT(*) AS num_posters FROM $table_name WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Poster']);
        $num_orals = $wpdb->get_var("SELECT COUNT(*) AS num_orals FROM $table_name WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Oral']);

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
    <div>
        <h3>Email settings</h3>


        <?php
        if (isset($_POST['save_settings'])) {
            update_option('evaluation_subject', $_POST['email_subject']);
            update_option('evaluation_accept_body', $_POST['acceptance_body']);
            update_option('evaluation_reject_body', $_POST['rejection_body']);
            echo "<p>Settings saved</p>";
        }

        $send_threshold = 30;

        $subject = get_option('evaluation_subject');
        $acceptance_body = get_option('evaluation_accept_body');
        $rejection_body = get_option('evaluation_reject_body');


        ?>

        <form method="POST" id="settings">
            <label for="email_subject">Email Subject</label>
            <input id="email_subject" name="email_subject" value="<?php echo $subject; ?>">
            <?php echo $subject; ?></input>
            <label for="acceptance_body">Acceptance Body</label>
            <textarea name="acceptance_body"><?php echo $acceptance_body; ?></textarea>
            <label for="rejection_body">Rejection Body</label>
            <textarea name="rejection_body"><?php echo $rejection_body; ?></textarea>

            <button type="submit" name="save_settings"> Save Settings</button>
        </form>


    </div>




    <h3>filter research areas: </h3>
    <form method="POST" id="filter">
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
<button name="send_emails" type="submit">Send Emails</button>
<button type="button" id="check_all">Check All
    <?php echo $send_threshold ?>
</button>
<button type="button" id="uncheck_all">Uncheck All</button>
<button type="button" id="check_orals">Check All Orals</button>
<button type="button" id="check_posters">Check All Posters</button>
<div>
    <table cellspacing=0 cellpadding=1 border=1 bordercolor=white width=100%>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Affiliation</th>
            <th>Presentation Title</th>
            <th>Abstract PDF</th>
            <th>Research Area</th>
            <th>Presentation Type</th>
            <th>Comment</th>
            <th>Send</th>
        </tr>
        <?php
        global $wpdb;
        $joint_table = "wp_or_registration as r LEFT JOIN wp_or_registration_evaluation as e ON r.hash_id = e.evaluation_hash_id LEFT JOIN wp_or_registration_presentations as p ON p.person_hash_id = e.evaluation_hash_id";
        $registration_table = "wp_or_registration";
        $evaluation_table = "wp_or_registration_evaluation";
        $ra_filter = 'none';



        if (isset($_POST['ra_filter'])) {
            $ra_filter = $_POST['ra_filter'];
        }

        if (isset($_POST['send_emails'])) {

            $check = $_POST['check'];
            if (count($check) > $send_threshold) {
                echo "<p>Too many emails to send at once. Please select less than $send_threshold emails to send at once.</p>";
                return;
            }

            foreach ($check as $id) {

                $query = "SELECT * FROM $joint_table WHERE hash_id='$id'";

                $result = $wpdb->get_results($query);
                $email = $result[0]->email;
                $args = array(
                    'post_type' => 'presentation',
                    'meta_query' => array(
                        array(
                            'key' => 'hash_id',
                            'value' => $id,
                            'compare' => '='
                        )
                    )

                );
                $presentation_post = get_posts($args)[0];
                $presentation_time = get_post_meta($presentation_post->ID, 'presentation_start', true);
                $presentation_day = date('d/m/Y', strtotime($presentation_time));
                $presentation_hour = date('H:i', strtotime($presentation_time));

                $presentation_session = get_post_meta($presentation_post->ID, 'presentation_session', true);
                $session_post = get_post($presentation_session);
                $session_title = get_post_meta($session_post->ID, 'short_title', true);



                if ($result[0]->decision == 1) {
                    $duration = "15 minutes (account 2-3 minutes for Q&A as part of the duration).";
                } else if ($result[0]->decision == 2) {
                    $duration = "90 minutes (Poster presenter has to be present at their poster at all times during the Poster session; only one presenter per poster)";
                }



                $other_vars = array(
                    '{$day}' => $presentation_day,
                    '{$hour}' => $presentation_hour,
                    '{$session}' => $session_title,
                    '{$duration}' => $duration,
                    '{$first_name}' => $result[0]->first_name,
                    '{$last_name}' => $result[0]->last_name,
                );

                if ($result[0]->sent_email == 0) {
                    $sent = send_emails($email, $result[0]->decision, $result[0]->display_title, $other_vars);
                    if ($sent) {
                        echo "<p>Email sent to $email</p>";

                    } else {
                        echo "<p>Email failed for $email, please try again</p>";

                    }
                } else {
                    echo "<p>Already sent an email to $email</p>";
                    $sent = 1;

                }
                $wpdb->update($evaluation_table, array('sent_email' => $sent), array('evaluation_hash_id' => $id));

            }




        }

        global $STATUS_CODES;
        $query = "SELECT * FROM $joint_table WHERE decision != 0 and sent_email != 1";


        if ($ra_filter != 'none') {
            $query .= " AND research_area='$RESEARCH_AREAS[$ra_filter]'";
        }
        $results = $wpdb->get_results($query);
        foreach ($results as $result) {
            $first_name = $result->first_name;
            $last_name = $result->last_name;
            $affiliation = $result->institution;
            $presentation_title = $result->display_title;
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
            echo '<tr style="background-color: ' . $color . ';">';
            echo "<td>$first_name</td>";
            echo "<td>$last_name</td>";
            echo "<td>$affiliation</td>";
            echo "<td>$presentation_title</td>";
            echo "<td> <a href=\"{$result->pdf}\">" . basename($result->pdf) . "</a></td>";
            echo "<td>" . $research_area . "</td>";
            echo "<td>$presentation_type</td>";
            echo "<td>$comment</td>";

            echo "<td><input type='checkbox' name='check[" . $result->hash_id . "]' value='$result->hash_id'></td>";
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
    });

    jQuery(document).ready(function ($) {
        $('#check_all').click(function () {
            //check first 30 checkboxes
            $('input:checkbox').each(function () {
                if ($('input:checkbox:checked').length < 30) {
                    this.checked = true;
                }


            });
        });

        $('#uncheck_all').click(function () {
            $('input:checkbox').each(function () {
                this.checked = false;
            });
        });

        $('#check_orals').click(function () {
            $('input:checkbox').each(function () {
                if ($('input:checkbox:checked').length < 30) {
                    if ($(this).parent().prev().prev().text() == "Oral") {
                        this.checked = true;
                    }
                }
            });
        });

        $('#check_posters').click(function () {
            $('input:checkbox').each(function () {
                if ($('input:checkbox:checked').length < 30) {
                    if ($(this).parent().prev().prev().text() == "Poster") {
                        this.checked = true;
                    }
                }
            });
        });

    });



</script>