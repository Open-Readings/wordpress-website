<?php
    include_once __DIR__ . '/../second-eval-functions.php';
?>


<h1>Evaluation System Emailer</h1>
<h2 style="color:#d00">Neveikia kol kas ;)</h2>

<div style="display:flex; flex-direction:row; justify-content: left;">
    <?php
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
            <label for="email_subject">Email Subject</label><br>
            <input id="email_subject" name="email_subject" style="width:500px" value="<?php echo $subject; ?>">
            <?php echo $subject; ?></input><br><br>
            <label for="acceptance_body">Acceptance Body</label>
            <textarea cols="80" rows="20" name="acceptance_body" style="background-color:#cfc"><?php echo $acceptance_body; ?></textarea>
            <label for="rejection_body">Rejection Body</label>
            <textarea cols="80" rows="20" name="rejection_body" style="background-color:#fcc"><?php echo $rejection_body; ?></textarea>

            <button type="submit" name="save_settings"> Save Settings</button>
        </form>
    </div>

    <h3>Filter research areas: </h3>
    <form method="POST" id="filter">
        <?php research_area_filter(); ?>
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

        if (isset($_POST['send_emails']) && isset($_POST['check'])) {

            $check = $_POST['check'];
            if (count($check) > $send_threshold) {
                echo "<p>Too many emails to send at once. Please select less than $send_threshold emails to send at once.</p>";
                return;
            }

            foreach ($check as $id) {

                $query = "SELECT * FROM $joint_table WHERE hash_id='$id'";

                $result = $wpdb->get_results($query);
                $email = $result[0]->email;
                // $args = array(
                //     'post_type' => 'presentation',
                //     'meta_query' => array(
                //         array(
                //             'key' => 'hash_id',
                //             'value' => $id,
                //             'compare' => '='
                //         )
                //     )

                // );
                // $presentation_post = get_posts($args)[0];
                // $presentation_time = get_post_meta($presentation_post->ID, 'presentation_start', true);
                // $presentation_day = date('d/m/Y', strtotime($presentation_time));
                // $presentation_hour = date('H:i', strtotime($presentation_time));

                // $presentation_session = get_post_meta($presentation_post->ID, 'presentation_session', true);
                // $session_post = get_post($presentation_session);
                // $session_title = get_post_meta($session_post->ID, 'short_title', true);

                if ($result[0]->decision == 1) {
                    $duration = "Oral Presentation";
                } else if ($result[0]->decision == 2) {
                    $duration = "Poster Presentation";
                }

                $other_vars = array(
                    // '{$day}' => $presentation_day,
                    // '{$hour}' => $presentation_hour,
                    // '{$session}' => $session_title,
                    '${type}' => $duration,
                    '${first_name}' => $result[0]->first_name,
                    '${last_name}' => $result[0]->last_name,
                    '${research_area}' => $result[0]->research_area,
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
            
            // Replace the base path with the base URL
            $pdf_url = str_replace(ABSPATH, site_url('/'), subject: $result->pdf);

            echo '<tr style="background-color: ' . $color . ';">';
            echo "<td>$first_name</td>";
            echo "<td>$last_name</td>";
            echo "<td>$affiliation</td>";
            echo "<td>$presentation_title</td>";
            echo "<td> <a href='$pdf_url'>" . basename($result->pdf) . "</a></td>";
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