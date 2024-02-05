<?php
// Include WordPress
// define('WP_USE_THEMES', false);
// require_once('../../../../../wp-load.php'); // Adjust the path as needed
$registration_functions_url = plugins_url('', __FILE__) . '/registration-functions.php';

function evaluation()
{

    if (isset($_POST['function']) && !empty($_POST['function'])) {
        $function = $_POST['function'];

        // Perform different actions based on the value of the action parameter
        switch ($function) {
            case 'fetch_data':
                $result = display_status_list();
                break;
            case 'show_evaluation':
                $result = display_evaluation_page();
                break;
            case 'show_main':
                $result = display_main_page();
                break;
            case 'generate_abstract':
                $result = generate_abstract();
                break;
            case 'send_update':
                $result = send_update();
                break;
            case 'send_reject':
                $result = send_reject();
                break;
            case 'send_accept':
                $result = send_accept();
                break;
            case 'save_changes':
                $result = save_changes();
                break;

            default:
                // Handle unknown action
                $result['response'] = 'Unknown action.';
                break;
        }
    } else {
        // Handle case where action parameter is not set
        $result['response'] = 'Action parameter is missing.';
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $result = json_encode($result);
        echo $result;
    } else {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }

    die();
}

function display_status_list()
{
    global $wpdb;

    // Example query to fetch data from a custom table

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration WHERE first_name = %s",
        $_POST['selectedOption'] // replace $yourValue with the actual value you're looking for
    );

    $results = $wpdb->get_results($query, ARRAY_A);
    $result['response'] = '<table border="1">';
    $result['response'] .= '<tr><th>ID</th><th>Name</th></tr>';
    // Process the fetched data
    foreach ($results as $db_result) {
        $result['response'] .= '<tr>';
        $result['response'] .= '<td>' . $db_result['hash_id'] . '</td>';
        $result['response'] .= '<td>' . $db_result['first_name'] . '</td>';
        $result['response'] .= '</tr>';
    }
    $result['response'] .= '</table>';



    return $result;

}

function display_evaluation_page()
{

    global $wpdb;

    $table_name = 'wp_or_registration_evaluation';
    $column_name = `current_user`;

    // Update rows in the specified table where the column has the value 'example' to NULL
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_or_registration_evaluation AS t1
        JOIN wp_or_registration_evaluation AS t2 ON t1.current_user = t2.current_user
        SET t1.current_user = NULL
        WHERE t1.current_user = %s",
            wp_get_current_user()->user_login

        )
    );

    $registration_table = $wpdb->prefix . 'or_registration';
    $presentations_table = $wpdb->prefix . 'or_registration_presentations';
    $evaluation_table = $wpdb->prefix . 'or_registration_evaluation';

    $query = $wpdb->prepare("
select evaluation_hash_id from wp_or_registration as r 
left join wp_or_registration_presentations 
as p on r.hash_id = p.person_hash_id 
left join wp_or_registration_evaluation 
as e on p.person_hash_id = e.evaluation_hash_id
where email in
(SELECT email
FROM wp_or_registration
GROUP BY email
HAVING COUNT(email) = 1) and `current_user` is NULL and `status` = 0 or `status` = 4
ORDER BY RAND()
");


    $evaluation_hash = $wpdb->get_row($query, ARRAY_A)['evaluation_hash_id'];
    if ($evaluation_hash == NULL) {
        $result['response'] = '<h1>No more evaluations are needed for now.</h1>';
        return $result;
    }
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_or_registration_evaluation SET `current_user` = %s WHERE evaluation_hash_id = %s",
            wp_get_current_user()->user_login,
            $evaluation_hash
        )
    );

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration_evaluation WHERE evaluation_hash_id = %s",
        $evaluation_hash
    );
    $evaluation_row = $wpdb->get_row($query, ARRAY_A);

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration_presentations WHERE person_hash_id = %s",
        $evaluation_row['evaluation_hash_id']
    );
    $presentation_row = $wpdb->get_row($query, ARRAY_A);

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration WHERE hash_id = %s",
        $presentation_row['person_hash_id']
    );
    $registration_row = $wpdb->get_row($query, ARRAY_A);

    if (!isset($_SESSION['e_pdf'])) {
        session_start();

    }

    $_SESSION['e_pdf'] = $presentation_row['pdf'];
    $_SESSION['e_file'] = $presentation_row['session_id'];
    $_SESSION['e_email'] = $registration_row['email'];
    $_SESSION['e_hash'] = $registration_row['hash_id'];
    $_SESSION['e_presentation_id'] = $presentation_row['presentation_id'];
    $_SESSION['e_error'] = 0;
    $_SESSION['e_generated'] = 0;
    $_SESSION['e_saved'] = 0;
    $_SESSION['e_sent'] = 0;

    $result['response'] = '<h1>' . $registration_row['first_name'] . ' ' . $registration_row['last_name'] . '</h1>';
    $result['response'] .= '<div class="abstract-flex"><div class="abstract-left-div div-margin">';
    $result['response'] .= '<p>HASH ID:<br> ' . $_SESSION['e_hash'] . '</p>';


    $status_text = [
        0 => 'Not checked',
        1 => 'Accepted',
        2 => 'Waiting for update',
        3 => 'Rejected',
        4 => 'Waiting for review'
    ];
    $result['response'] .= '<p>Status:<br> ' . $status_text[$evaluation_row['status']] . '</p>';



    $print_registration_text_fields = [
        ['Email: ', 'email'],
        ['Country: ', 'country'],
        ['Research area: ', 'research_area'],
        ['Presentation type: ', 'presentation_type']
    ];

    foreach ($print_registration_text_fields as $field) {
        $result['response'] .= '<p>' . $field[0] . '<b>' . $registration_row[$field[1]] . '</b>' . '</p>';
    }

    $result['response'] .= '<form id="presentationForm"><label for="institution">Institution: </label><b><input id="institution-field" class="evaluation-input" autocomplete="off" name="institution" type=text value="' . $registration_row['institution'] . '"></input><div id="institution-wrapper"></div></b><br>';
    $result['response'] .= '<label for="department">Department: </label><b><input class="evaluation-input" name="department" type=text value="' . $registration_row['department'] . '"></input></b><br>';

    $result['response'] .= '<label for="abstract_title">Title: </label><b><input class="evaluation-input" name="abstract_title" type=text value="' . stripslashes($presentation_row['title']) . '"></input></b><br><br>';

    $contact_index = 0;
    foreach (json_decode($presentation_row['authors']) as $item) {
        $contact_index++;
        $result['response'] .= '<label for="name[]">Author name: </label><b><input class="evaluation-input" name="name[]" type=text value="' . $item[0] . '"></input></b>';
        $result['response'] .= '<div><label for="aff_ref[]">Affiliation nr.: </label><b><input class="evaluation-input" name="aff_ref[]" type=text value="' . $item[1] . '"></input></b></div>';
        if (isset($item[2])) {
            $email = $item[2];
            $contact = $contact_index;
        }
    }
    $result['response'] .= '<br><label for="email-author">Contact email: </label><b><input class="evaluation-input" name="email-author" type=text value="' . $email . '"></input></b>';
    $result['response'] .= '<div><label for="contact_author">Contact nr.: </label><b><input class="evaluation-input" name="contact_author" type=text value="' . $contact . '"></input></b></div><br>';

    $affiliation_index = 0;
    foreach (json_decode($presentation_row['affiliations']) as $item) {
        $affiliation_index++;
        $result['response'] .= '<label for="affiliation[]"> Affiliation to display: </label><b><input class="evaluation-input" name="affiliation[]" type=text value="' . $item . '"></input></b><br>';
    }

    $reference_index = 0;
    $result['response'] .= '<p>References [if there are any]<p>';
    if (json_decode($presentation_row['references']) != NULL) {
        foreach (json_decode($presentation_row['references']) as $item) {
            $reference_index++;
            $result['response'] .= '<label for="references[]"> Reference: </label><b><input class="evaluation-input" name="references[]" type=text value="' . $item . '"></input></b><br>';
        }
    }
    $result['response'] .= '<label for="textArea"> Abstract: </label><br><textarea class="evaluation-input" cols=70 rows=20 name="textArea">' . stripslashes($presentation_row['content']) . '</textarea><br>';

    $result['response'] .= '<label for="sendMail"> Email [ONLY USED FOR UPDATE OR REJECT]: </label><br><textarea class="evaluation-input" cols=30 rows=5 id="email-content" name="sendMail"></textarea><br>';


    $result['response'] .= '</form>';

    $result['response'] .= '<button class="button-style g-button" id="send-accept">Accept & Email</button>';
    $result['response'] .= '<button class="button-style b-button" id="send-update">Ask to update</button>';
    $result['response'] .= '<button class="button-style r-button" id="send-reject">Reject & Email</button>';

    $result['response'] .= '<div id="send-email" class="message-div"></div>';



    $result['response'] .= '</div><div class="abstract-right-div div-margin">';
    $result['response'] .= '<button class="button-style r-button" id="generateButton">Generate</button>';
    $result['response'] .= '<button class="button-style r-button" id="saveButton">Save</button><div id="errorContainer"></div><div id="save-message"></div>';



    $result['response'] .= '<iframe id="abstract" class="pdf-frame" id="abstract" src="' . $presentation_row['pdf'] . '#toolbar=0&view=fit' . '"></iframe>';


    $result['response'] .= '</div>
    <script>
    function setIframeHeight() {
        const iframe = document.getElementById(\'abstract\');
        const width = iframe.offsetWidth; // Get the current width of the iframe
        const height = width * 1.41; // Calculate the height based on the width and aspect ratio
    
        iframe.style.height = height + \'px\'; // Set the height of the iframe
    }
    
    window.addEventListener(\'load\', setIframeHeight);
    window.addEventListener(\'resize\', setIframeHeight);
    check_institution();
    var institutionInputElement = document.getElementById(\'institution-field\');

    institutionInputElement.addEventListener(\'input\', function() {
       check_institution();
       institutionInputChange();
    });
    </script>';



    // $field = new Elementor_Institution_Field();
    // $result['response'] = $field->render();

    return $result;
}

function display_main_page()
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM wp_or_registration AS r LEFT JOIN wp_or_registration_evaluation AS e ON r.hash_id = e.evaluation_hash_id", ARRAY_A);
    $status = [0, 0, 0, 0, 0];
    foreach ($results as $db_result) {
        $status[$db_result['status']]++;
    }
    $result['response'] = '
        <form id="statusForm" action="evaluation" method="post">
        <label for="selectOption">Select an option:</label>
        <select id="selectOption" name="selectedOption">
            <option value="Rimantas">Option 1</option>
            <option value="Rimantasnew">Option 2</option>
            <option value="option3">Option 3</option>
        </select>
        <input type="hidden" name="action" value="evaluation">
        <input type="hidden" name="function" value="fetch_data">
        <input type="submit" value="Submit">
        <p> Not checked: ' . $status[0] . ', Accepted: ' . $status[1] . ', Waiting for update: ' . $status[2] + $status[4] . ', Rejected: ' . $status[3] . '</p>
        </form>
        <div id="resultContainer">
        ';

    $result['response'] .= '<table border="1">';
    $result['response'] .= '<tr><th>Status</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Research area</th><th>Presentation type</th><th>Hash ID</th></tr>';
    // Process the fetched data
    foreach ($results as $db_result) {
        $result['response'] .= '<tr>';
        $result['response'] .= '<td>' . $db_result['status'] . '</td>';
        $result['response'] .= '<td>' . $db_result['first_name'] . '</td>';
        $result['response'] .= '<td>' . $db_result['last_name'] . '</td>';
        $result['response'] .= '<td>' . $db_result['email'] . '</td>';
        $result['response'] .= '<td>' . $db_result['research_area'] . '</td>';
        $result['response'] .= '<td>' . $db_result['presentation_type'] . '</td>';
        $result['response'] .= '<td>' . $db_result['hash_id'] . '</td>';
        $result['response'] .= '</tr>';
    }
    $result['response'] .= '
        </table>
        </div>
        ';
    return $result;
}

function fixUnclosedTags($text, $tagOpen, $tagClose)
{
    $countOpen = substr_count($text, $tagOpen);
    $countClose = substr_count($text, $tagClose);

    $tagDiff = $countOpen - $countClose;

    if ($tagDiff > 0) {
        $text .= str_repeat($tagClose, $tagDiff);
    }

    return $text;
}

function generate_abstract()
{
    $i = 1;
    $authors = '';
    foreach ($_POST['name'] as $name) {
        $name = trim($name);
        $name = preg_replace('/[^\p{L}\-\s.,;]/u', '', $name);
        $aff_ref = $_POST['aff_ref'][$i - 1];
        $aff_ref = trim($aff_ref);
        //replace everything that is not a digit or ,
        $aff_ref = preg_replace('/[^\d,]/', '', $aff_ref);

        if ($_POST['contact_author'] == $i)
            $authors = $authors . '\underline{' . $name . '}$^{' . $aff_ref . '}$';
        else
            $authors = $authors . $name . '$^{' . $aff_ref . '}$';

        if ($i < count($_POST['name']))
            $authors = $authors . ', ';
        $i++;
    }

    $affiliations = '';
    $i = 1;
    foreach ($_POST['affiliation'] as $aff) {
        $affiliations = $affiliations . '$^{' . $i . '}$' . $aff . '
    
    ';
        $i++;
    }
    $affiliations = $affiliations . '\underline{' . $_POST['email-author'] . '}';
    if (isset($_POST['references'])) {
        $references = '
    \vfill
    \hrule
    \begingroup
    \renewcommand{\section}[2]{}%
    \begin{thebibliography}{}
    
    
    ';
        $i = 1;
        foreach ($_POST['references'] as $ref) {
            $references .= '\bibitem{' . $i . '} ' . $ref . '
       
       ';
            $i++;
        }
        $references .= '\end{thebibliography}
    \endgroup
    ';
    } else {
        $references = '';
    }

    $titleField = stripslashes($_POST['abstract_title']);

    $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');
    $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');
    $titleField = preg_replace('/[^\p{L}\p{N}\s&-+()=.:,<>;\/]/', '', $titleField);

    $sup_starting_tag = '<sup>';
    $sub_starting_tag = '<sub>';
    $sub_ending_tag = '</sub>';
    $sup_ending_tag = '</sup>';
    $layers = 0;
    $is_in_math_mode = false;
    for ($i = 0; $i < mb_strlen($titleField); $i++) {
        if (mb_substr($titleField, $i, mb_strlen($sup_starting_tag)) == $sup_starting_tag) {
            $sup_starting_tag_index = $i;
            $layers++;
            if ($layers == 1) {
                $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '$^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
            } else {
                //replace <sup> with $^{
                $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
            }
            $i -= mb_strlen($sup_starting_tag);
        }
        if (mb_substr($titleField, $i, mb_strlen($sub_starting_tag)) == $sub_starting_tag) {
            $sub_starting_tag_index = $i;
            $layers++;
            if ($layers == 1) {
                $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '$_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
            } else {
                //replace <sub> with $_{
                $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
            }
            $i -= mb_strlen($sup_starting_tag);

        }

        if (mb_substr($titleField, $i, mb_strlen($sub_ending_tag)) == $sub_ending_tag) {
            $sub_ending_tag_index = $i;
            $layers--;
            if ($layers == 0) {
                //replace </sub> with }$
                $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}$' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
            } else {
                //replace </sub> with }$
                $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
            }
            //replace </sub> with }$
            $i -= mb_strlen($sup_starting_tag);
        }
        if (mb_substr($titleField, $i, mb_strlen($sup_ending_tag)) == $sup_ending_tag) {
            $sup_ending_tag_index = $i;
            $layers--;
            if ($layers == 0) {
                //replace </sup> with }$
                $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
            } else {
                //replace </sup> with }$
                $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
            }
            $i -= mb_strlen($sup_starting_tag);
        }

    }

    $titleField = str_replace('&nbsp;', '', $titleField);

    $abstractContent = stripslashes($_POST["textArea"]);

    if (!isset($_SESSION['e_pdf'])) {
        session_start();
    }
    if ($_SESSION['e_sent'] == 1) {
        $result['response'] = '<p class="e-red">Already sent</p>';
        return $result;
    }

    $result['pdf'] = $_SESSION['e_pdf'];

    $result['response'] = '
    <script>
    document.getElementById("abstract").setAttribute("src", \'http://localhost:10009/wp-content/latex/17061177252d0f36db/abstract.pdf\' + \'?timestamp=\' + new Date().getTime() + \'#toolbar=0&view=FitH\');
    setIframeHeight();
    </script>';

    $templateFilePath = plugin_dir_path(__FILE__) . 'template.txt';
    $templateContent = file_get_contents($templateFilePath);

    // Define your variables
    // Add more variables as needed

    // Create an associative array of placeholders and their corresponding values
    $replacements = array(
        '${title}' => $titleField,
        '${authors}' => $authors,
        '${affiliations}' => $affiliations,
        '${content}' => $abstractContent,
        '${references}' => $references

        // Add more placeholders and values as needed
    );

    // Replace placeholders in the template content
    $templateContent = str_replace(array_keys($replacements), array_values($replacements), $templateContent);


    // Write the modified content to the abstract file
    $folder = WP_CONTENT_DIR . '/latex/' . $_SESSION['e_file'];
    $abstractFilePath = $folder . '/abstract.tex';
    file_put_contents($abstractFilePath, $templateContent);
    // $folder = '../wp-content/latex/' . $_SESSION['file'];

    chdir($folder);
    $abcd = shell_exec('/bin/pdflatex -interaction=nonstopmode abstract.tex');
    $_SESSION['e_generated'] = 1;

    $logContent = file_get_contents($folder . '/abstract.log');

    // Check if '!' exists in the log content
    if (strpos($logContent, '!') !== false) {
        $position = mb_strpos($logContent, '!', 0, 'UTF-8');

        $cutString = mb_substr($logContent, $position, null, 'UTF-8');
        $result['error'] = '<pre id="pre-container" class="error-pre">' . htmlspecialchars($cutString) . '</pre>';
        $_SESSION['e_error'] = 1;
    } else {
        $_SESSION['e_error'] = 0;
    }

    return $result;

}

function send_update()
{

    global $or_mailer;
    if (!isset($_SESSION['e_email'])) {
        session_start();
    }
    if ($_SESSION['e_sent'] == 1) {
        $result['response'] = '<p class="e-red">Already sent</p>';
        return $result;
    }
    if ($_SESSION['e_generated'] == 0) {
        $result['response'] = '<p class="e-red">Please generate abstract</p>';
        return $result;
    } else if ($_SESSION['e_saved'] == 0) {
        $result['response'] = '<p class="e-red">Please save the generated abstract</p>';
        return $result;
    }

    $update_text = '
    <tr>
    <td align="justify" style="padding:25px;">
        <p>
            Dear participant,<br><br>
            You must make the following adjustments to your submission before we can send it to our programme committee for further evaluation: <br><br>
            ' . $_POST['sendMail'] . '<br><br>

            The reference ID of your registration:<br>
            ' . $_SESSION['e_hash'] . '<br>
            To update your submission please click <strong><a href="https://openreadings.eu/registration?id=' . $_SESSION['e_hash'] . '">HERE</a></strong><br><br>

            Best regards, <br>
            Open Readings team
            <br>
            <br>
        </p>
    </td>
    </tr>
    ';

    global $wpdb;
    $query = 'UPDATE wp_or_registration_evaluation SET `status` = %s, email_content = %s, checker_name = %s, evaluation_date = %s, latex_error = %s WHERE evaluation_hash_id = %s';

    $query = $wpdb->prepare($query, '2', $_POST['sendMail'], wp_get_current_user()->user_login, current_time('mysql', 1), $_SESSION['e_error'], $_SESSION['e_hash']);
    $db_result = $wpdb->query($query);

    if ($db_result === false) {
        $result['response'] = '<p class="e-red">database fail</p>';
        return $result;
    }
    $_SESSION['e_sent'] = 1;

    $sent = $or_mailer->send_OR_mail($_SESSION['e_email'], 'Please Update Your Registration Details', $update_text);

    if ($sent) {
        $result['response'] = '<p class="e-green">Update email sent</p>';
    } else {
        $result['response'] = '<p class="e-green">Failed to send update email, database ok</p>';
    }
    return $result;
}

function send_reject()
{
    global $or_mailer;
    if (!isset($_SESSION['e_email'])) {
        session_start();
    }
    if ($_SESSION['e_sent'] == 1) {
        $result['response'] = '<p class="e-red">Already sent</p>';
        return $result;
    }
    if ($_SESSION['e_generated'] == 0) {
        $result['response'] = '<p class="e-red">Please generate abstract</p>';
        return $result;
    } else if ($_SESSION['e_saved'] == 0) {
        $result['response'] = '<p class="e-red">Please save the generated abstract</p>';
        return $result;
    }

    $rejected_text = '
    <tr>
    <td align="justify" style="padding:25px;">
        <p>
            Dear participant,<br><br>
            We regret to inform you that your submission has not been accepted for the following reason:<br><br>' . $_POST['sendMail'] . '<br><br>

            Best regards, <br>
            Open Readings team
            <br>
            <br>
        </p>
    </td>
    </tr>
    ';

    global $wpdb;
    $query = 'UPDATE wp_or_registration_evaluation SET `status` = %s, email_content = %s, checker_name = %s, evaluation_date = %s, latex_error = %s WHERE evaluation_hash_id = %s';

    $query = $wpdb->prepare($query, '3', $_POST['sendMail'], wp_get_current_user()->user_login, current_time('mysql', 1), $_SESSION['e_error'], $_SESSION['e_hash']);
    $db_result = $wpdb->query($query);

    if ($db_result === false) {
        $result['response'] = '<p class="e-red">database fail</p>';
        return $result;
    }
    $_SESSION['e_sent'] = 1;

    $sent = $or_mailer->send_OR_mail($_SESSION['e_email'], 'Registration Update', $rejected_text);


    if ($sent) {
        $result['response'] = '<p class="e-green">Reject email sent</p>';
    } else {
        $result['response'] = '<p class="e-green">Failed to send reject email, database ok</p>';
    }
    return $result;
}

function send_accept()
{
    global $or_mailer;
    if (!isset($_SESSION['e_email'])) {
        session_start();
    }
    if ($_SESSION['e_sent'] == 1) {
        $result['response'] = '<p class="e-red">Already sent</p>';
        return $result;
    }
    if ($_SESSION['e_generated'] == 0) {
        $result['response'] = '<p class="e-red">Please generate abstract</p>';
        return $result;
    } else if ($_SESSION['e_saved'] == 0) {
        $result['response'] = '<p class="e-red">Please save the generated abstract</p>';
        return $result;
    }

    $accepted_text = '
    <tr>
    <td align="justify" style="padding:25px;">
        <p>
            Dear participant,<br><br>
            We would like to inform you that your submission has successfully passed our initial inspection and is now scheduled for review by our programme committee. We kindly request you to refrain from making any further modifications to your submission unless absolutely necessary.
            Thank you for your contribution to our event.<br><br>

            Best regards, <br>
            Open Readings team
            <br>
            <br>
        </p>
    </td>
    </tr>
    ';

    // $accepted_text = '
    // <h1>Registration update</h1>
    // <p>Dear participant,</p><br>
    // <p>We would like to inform you that your submission has successfully passed our initial inspection and is now scheduled for review by our program committee. We kindly request you to refrain from making any further modifications to your submission unless absolutely necessary.</p>
    // <p>Thank you for your contribution to our event.</p><br>
    // <p>Best regards,</p>
    // <p>Open Readings team</p>';

    global $wpdb;
    $query = 'UPDATE wp_or_registration_evaluation SET `status` = %s, email_content = %s, checker_name = %s, evaluation_date = %s, latex_error = %s WHERE evaluation_hash_id = %s';


    $query = $wpdb->prepare($query, '1', '', wp_get_current_user()->user_login, current_time('mysql', 1), $_SESSION['e_error'], $_SESSION['e_hash']);
    $db_result = $wpdb->query($query);

    if ($db_result === false) {
        $result['response'] = '<p class="e-red">database fail</p>';
        return $result;
    }
    $_SESSION['e_sent'] = 1;
    $sent = $or_mailer->send_OR_mail($_SESSION['e_email'], 'Registration Update', $accepted_text);

    if ($sent) {
        $result['response'] = '<p class="e-green">Accept email sent</p>';
    } else {
        $result['response'] = '<p class="e-green">Failed to send accept email, database ok</p>';
    }

    return $result;
}


function save_changes()
{
    if (!isset($_SESSION['e_hash'])) {
        session_start();
    }
    if ($_SESSION['e_generated'] == 0) {
        $result['response'] = '<p class="e-red">Please generate first</p>';
        return $result;
    }
    if ($_SESSION['e_sent'] == 1) {
        $result['response'] = '<p class="e-red">Already sent</p>';
        return $result;
    }

    $authors_array = array();
    for ($i = 0; $i < count($_POST['name']); $i++) {
        if ($_POST['contact_author'] == $i + 1)
            $authors_array[$i] = array($_POST['name'][$i], $_POST['aff_ref'][$i], $_POST['email-author']);
        else {
            $authors_array[$i] = array($_POST['name'][$i], $_POST['aff_ref'][$i]);
        }
    }

    $title = $_POST['abstract_title'];
    $authors = json_encode($authors_array);
    $affiliations = json_encode($_POST['affiliation']);
    $content = $_POST['textArea'];
    if (isset($_POST['references'])) {
        $references = json_encode($_POST['references']);
    } else {
        $references = [];
    }

    global $wpdb;

    $presentation_table_name = 'wp_or_registration_presentations';

    $query = 'UPDATE ' . $presentation_table_name . ' SET title = %s, authors = %s, affiliations = %s, content = %s, `references` = %s WHERE person_hash_id = %s';

    $query = $wpdb->prepare($query, $title, $authors, $affiliations, $content, $references, $_SESSION['e_hash']);

    $db_result = $wpdb->query($query);
    if ($db_result === false) {
        $result['response'] = '<p class="e-red">error</p>';
        return $result;
    }
    $registration_table_name = 'wp_or_registration';

    $query = 'UPDATE ' . $registration_table_name . ' SET institution = %s, department = %s WHERE hash_id = %s';

    $query = $wpdb->prepare($query, $_POST['institution'], $_POST['department'], $_SESSION['e_hash']);

    $db_result = $wpdb->query($query);
    if ($db_result === false) {
        $result['response'] = '<p class="e-red">error</p>';
        return $result;
    }

    $result['response'] = '<p class="e-green">Success</p>';
    $_SESSION['e_saved'] = 1;
    return $result;

}

?>