<?php
// Include WordPress
// define('WP_USE_THEMES', false);
// require_once('../../../../../wp-load.php'); // Adjust the path as needed
$registration_functions_url = plugins_url('', __FILE__) . '/registration-functions.php';


function evaluation(){
    
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
        case 'send_email':
            $result = send_email();
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

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

function display_status_list(){
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

function display_evaluation_page(){

    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration_evaluation WHERE checker_name = %s",
        wp_get_current_user()->user_login
    );
    $evaluation_row = $wpdb->get_row($query, ARRAY_A);

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration_presentations WHERE presentation_id = %s",
        $evaluation_row['evaluation_hash_id']
    );
    $presentation_row = $wpdb->get_row($query, ARRAY_A);

    $query = $wpdb->prepare(
        "SELECT * FROM wp_or_registration WHERE hash_id = %s",
        $presentation_row['person_hash_id']
    );
    $registration_row = $wpdb->get_row($query, ARRAY_A);

    if(!isset($_SESSION['pdf'])){
        session_start();
        
    }

    $_SESSION['pdf'] = $presentation_row['pdf'];
    $_SESSION['file'] = $presentation_row['session_id'];
    $_SESSION['email'] = $registration_row['email'];
    $_SESSION['e_hash'] = $registration_row['hash_id'];

    $result['response'] = '<h1 class="red">' . $registration_row['first_name'] . ' ' . $registration_row['last_name'] . '</h1>';
    
    $result['response'] .= '<div class="abstract-flex"><div class="abstract-left-div">';

   

    $print_registration_text_fields = [
        ['Email: ', 'email'],
        ['Country: ', 'country'],
        ['Research area: ', 'research_area'],
        ['Presentation type: ', 'presentation_type']
    ];

    foreach($print_registration_text_fields as $field){
        $result['response'] .= '<p>' . $field[0] . '<b>' . $registration_row[$field[1]] . '</b>' . '</p>';
    }

    $result['response'] .= '<form id="presentationForm"><label for="institution">Institution: </label><b><input class="evaluation-input" name="institution" type=text value="'. $registration_row['institution'] . '"></input></b><br>';
    $result['response'] .= '<label for="department">Department: </label><b><input class="evaluation-input" name="department" type=text value="'. $registration_row['department'] . '"></input></b><br>';

    $result['response'] .= '<label for="abstract_title">Title: </label><b><input class="evaluation-input" name="abstract_title" type=text value="'. $presentation_row['title'] . '"></input></b><br><br>';

    $contact_index = 0;
    foreach(json_decode($presentation_row['authors']) as $item){
        $contact_index++;
        $result['response'] .= '<label for="name[]">Author name: </label><b><input class="evaluation-input" name="name[]" type=text value="'. $item[0] . '"></input></b>';
        $result['response'] .= '<div><label for="aff_ref[]">Affiliation nr.: </label><b><input class="evaluation-input" name="aff_ref[]" type=text value="'. $item[1] . '"></input></b></div>';
        if(isset($item[2])){
            $email = $item[2];
            $contact = $contact_index;
        }
    }
    $result['response'] .= '<br><label for="email-author">Contact email: </label><b><input class="evaluation-input" name="email-author" type=text value="'. $email . '"></input></b>';
    $result['response'] .= '<div><label for="contact_author">Contact nr.: </label><b><input class="evaluation-input" name="contact_author" type=text value="'. $contact . '"></input></b></div><br>';

    $affiliation_index = 0;
    foreach(json_decode($presentation_row['affiliations']) as $item){
        $affiliation_index++;
        $result['response'] .= '<label for="affiliation[]"> Affiliation: </label><b><input class="evaluation-input" name="affiliation[]" type=text value="'. $item . '"></input></b><br>';
    }

    $reference_index = 0;
    $result['response'] .= '<p>References<p>';
    if($presentation_row['references'] != NULL) foreach(json_decode($presentation_row['references']) as $item){
        $reference_index++;
        $result['response'] .= '<label for="references[]"> Reference: </label><b><input class="evaluation-input" name="references[]" type=text value="'. $item . '"></input></b><br>';
    }

    $result['response'] .= '<label for="textArea"> Abstract: </label><br><textarea class="evaluation-input" cols=70 rows=20 name="textArea">'. $presentation_row['content'] . '</textarea><br>';

    $result['response'] .= '<label for="sendMail"> Email: </label><br><textarea class="evaluation-input" cols=30 rows=5 name="sendMail"></textarea><br>';


    $result['response'] .= '</form>';

    $result['response'] .= '<button id="send-email">Send Email</button>';
    $result['response'] .= '<div id="send-email"></div>';



    $result['response'] .= '</div><div class="abstract-right-div">';

    $result['response'] .= '<button id="generateButton">Generate</button>';
    $result['response'] .= '<button id="saveButton">Save</button><div id="save-message"></div>';

    $result['response'] .= '<iframe id="abstract" class="pdf-frame" id="abstract" src="' . $presentation_row['pdf'] . '#toolbar=0&view=fit' . '"></iframe>';


    $result['response'] .= '</div>
    <script>
    function setIframeHeight() {
        const iframe = document.getElementById(\'abstract\');
        const width = iframe.offsetWidth; // Get the current width of the iframe
        const height = width * 1.41; // Calculate the height based on the width and aspect ratio
    
        iframe.style.height = height + \'px\'; // Set the height of the iframe
        countChar();
    }
    
    window.addEventListener(\'load\', setIframeHeight);
    window.addEventListener(\'resize\', setIframeHeight);
    </script>';



    // $field = new Elementor_Institution_Field();
    // $result['response'] = $field->render();

    return $result;
}

function display_main_page(){
    global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM wp_or_registration", ARRAY_A);

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
        </form>
        <div id="resultContainer">
        ';

        $result['response'] .= '<table border="1">';
        $result['response'] .= '<tr><th>ID</th><th>Name</th></tr>';
        // Process the fetched data
        foreach ($results as $db_result) {
            $result['response'] .= '<tr>';
            $result['response'] .= '<td>' . $db_result['first_name'] . '</td>';
            $result['response'] .= '<td>' . $db_result['last_name'] . '</td>';
            $result['response'] .= '<td>' . $db_result['email'] . '</td>';
            $result['response'] .= '</tr>';
        }
        $result['response'] .= '
        </table>
        </div>
        ';
    return $result;
}

function fixUnclosedTags($text, $tagOpen, $tagClose){
    $countOpen = substr_count($text, $tagOpen);
    $countClose = substr_count($text, $tagClose);

    $tagDiff = $countOpen - $countClose;

    if ($tagDiff > 0) {
        $text .= str_repeat($tagClose, $tagDiff);
    }

    return $text;
}

function generate_abstract(){
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

    $references = '';
    $i = 1;
    foreach ($_POST['references'] as $ref) {
        $references = $references . '\setcounter{footnote}{' . $i . '} ' . '\footnotetext{' . $ref . '}
    ';
        $i++;
    }

    $titleField = $_POST['abstract_title'];

            $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');
            $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');
            $titleField = preg_replace('/[^\p{L}\p{N}\s&\-+()=.:,<>;\/]/', '', $titleField);

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

    $abstractContent = $_POST["textArea"];

    if(!isset($_SESSION['pdf'])){
        session_start();
    }

    $result['pdf'] = $_SESSION['pdf'];

    $result['response'] = '
    <script>
    console.log(4320987498321410934809321);
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
    $folder = WP_CONTENT_DIR . '/latex/' . $_SESSION['file'];
    $abstractFilePath = $folder . '/abstract.tex';
    file_put_contents($abstractFilePath, $templateContent);

    // $folder = '../wp-content/latex/' . $_SESSION['file'];


    // Optionally, you can echo a success message
    $aaaa = '/bin/pdflatex -interaction=nonstopmode --output-directory="' . $folder . '" "' . $folder . '/abstract.tex"';

    chdir($folder);
    $abcd = shell_exec('/bin/pdflatex -interaction=nonstopmode abstract.tex');
    // $result['dir'] = shell_exec('/bin/pwd');

        return $result;

}

function send_email(){
    global $or_mailer;
    if(!isset($_SESSION['email'])){
        session_start();
    }
    $sent = $or_mailer->send_OR_mail($_SESSION['email'], 'Waiting for update', $_POST['sendMail']);

    if ($sent) {
        $result['response'] = '<p>Email sent</p>';
    } else {
        $result['response'] = '<p>Your submission, was saved, but we experienced an error sending you a confirmation email. Please contact us at info@openreadings.eu</p>';
    }
    return $result;
}

function save_changes(){
    if(!isset($_SESSION['e_hash'])){
        session_start();
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
    if(isset($_POST['references'])){
        $references = json_encode($_POST['reference']);
    }else{
        $references = [];
    }   

    global $wpdb;
    
    $presentation_table_name = 'wp_or_registration_presentations';

    $query = 'UPDATE ' . $presentation_table_name . ' SET title = %s, authors = %s, affiliations = %s, content = %s, `references` = %s WHERE person_hash_id = %s';

    $query = $wpdb->prepare($query, $title, $authors, $affiliations, $content, $references, $_SESSION['e_hash']);

    $db_result = $wpdb->query($query);
    if ($db_result === false) {
        $result['response'] = '<p>error</p>';
        return $result;
    }
    $result['response'] = '<p>success</p>';
    return $result;

}

?>