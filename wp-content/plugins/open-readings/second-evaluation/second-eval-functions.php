<?php

global $wpdb;
global $STATUS_CODES;
global $RESEARCH_AREAS;
global $PRESENTATION_TYPE;

function print_eval_statistics()
    {
        global $wpdb;
        global $STATUS_CODES;
        global $RESEARCH_AREAS;
        global $PRESENTATION_TYPE;
        $eval_table = "wp_or_registration_evaluation";
        $reg_table = "wp_or_registration";
        $joined_table = "$eval_table t1 INNER JOIN $reg_table t2 ON t1.evaluation_hash_id = t2.hash_id";

        $num_accepted = $wpdb->get_var("SELECT COUNT(*) AS NC FROM $joined_table WHERE status=" . $STATUS_CODES["Accepted"] . " AND (decision=" . $PRESENTATION_TYPE['Oral'] . " OR decision=" . $PRESENTATION_TYPE['Poster'] . ")");
        $num_notchecked = $wpdb->get_var("SELECT COUNT(*) AS NU FROM $joined_table WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=0");
        $num_rejected = $wpdb->get_var("SELECT COUNT(*) AS NR FROM $joined_table WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=3");
        $num_emailed = $wpdb->get_var("SELECT COUNT(*) AS NC FROM $joined_table WHERE sent_email = 1");

        echo "<h2>Abstracts</h2>";
        echo "<p>Accepted: {$num_accepted}, Not checked: {$num_notchecked}, Rejected: {$num_rejected}, Emailed: {$num_emailed}</p>";


        $num_posters = $wpdb->get_var("SELECT COUNT(*) AS num_posters FROM $joined_table WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Poster']);
        $num_orals = $wpdb->get_var("SELECT COUNT(*) AS num_orals FROM $joined_table WHERE status=" . $STATUS_CODES["Accepted"] . " AND decision=" . $PRESENTATION_TYPE['Oral']);

        echo "<p> Accepted presentations:</p>";
        echo "<p>Posters: {$num_posters}, Orals: {$num_orals}</p>";


        $num_areas_total = $wpdb->get_results("SELECT research_area, COUNT(*) AS num_areas_total
        FROM $joined_table WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND 
        (decision=" . $PRESENTATION_TYPE['Poster'] . " OR decision=" . $PRESENTATION_TYPE['Oral'] . ") GROUP BY research_area");

        echo "<h2>Areas (Accepted)</h2>";

        foreach ($num_areas_total as $key => $total_num) {

            $num_area_orals = $wpdb->get_var("SELECT COUNT(*) AS num_areas_orals FROM $joined_table WHERE `status`=" . $STATUS_CODES["Accepted"] . " AND  decision=" . $PRESENTATION_TYPE['Oral'] . " AND research_area='" . $total_num->research_area . "'");
            $num_area_poster = $total_num->num_areas_total - $num_area_orals;
            echo "<strong> " . $total_num->research_area . "  </strong>
            <a>  posters: " . $num_area_poster . ", orals: " . $num_area_orals . "</a>
            </br>";
        }


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
                    '${title}' => $presentation_title,
                );
            $template = strtr($rej_body, $vars);
            return $or_mailer->send_or_mail($email, $subj, $template);
        } else {
            $vars =
                array(
                    '${title}' => $presentation_title,
                    '${decision}' => array_search($decision, $PRESENTATION_TYPE),
                    '${link}' => '<a href="https://docs.google.com/forms/d/e/1FAIpQLSfFMjHLhjOXPNJf432hsvh-hxtBA0SvU06jovlNjkIcgYyDTQ/viewform">link</a>'
                );
            $vars = array_merge($vars, $other_vars);
            $template = strtr($acc_body, $vars);
            $template = stripslashes($template);
            return $or_mailer->send_or_mail($email, $subj, $template);
        }
    }

function research_area_filter(){
    global $RESEARCH_AREAS;
    ?>
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
    <?php
}

function normalize_url($url) {
    // Find the position of 'wp-content' in the URL
    $wp_content_pos = strpos($url, 'wp-content');

    // If 'wp-content' is found, extract the part after it
    if ($wp_content_pos !== false) {
        $relative_path = substr($url, $wp_content_pos + strlen('wp-content'));
        return WP_CONTENT_URL . $relative_path;
    }

    // If 'wp-content' is not found, return the original URL
    return $url;
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