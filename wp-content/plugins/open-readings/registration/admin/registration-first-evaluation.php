<?php
// Include WordPress
define('WP_USE_THEMES', false);
require_once(WP_CONTENT_DIR . '/../wp-load.php'); // Adjust the path as needed
$registration_functions_url = plugins_url('', __FILE__) . '/registration-functions.php';
wp_enqueue_style('registration-evaluation-style');
wp_enqueue_script('jquery');
wp_enqueue_script('institutions-list-js', '');
wp_enqueue_script('evaluation-js','');



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
</head>
<body>

<h1>First Evaluation System</h1>
<button class="button-style r-button" id="evaluationButton">NEXT</button>
<button class="button-style" id="mainButton">MAIN</button>
<div id=displayContainer>

    

    
    <?php
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM wp_or_registration AS r LEFT JOIN wp_or_registration_evaluation AS e ON r.hash_id = e.evaluation_hash_id", ARRAY_A);
        $status = [0, 0, 0, 0, 0];
        foreach($results as $db_result){
            $status[$db_result['status']]++;
        }
        echo '
        <form class="abstract-half-div" id="statusForm" action="evaluation" method="post">
        <label for="selectOption">Select an option:</label>
        <select id="selectOption" name="selectedOption">
            <option value="all">All</option>
            <option value="option2">Option 2</option>
            <option value="option3">Option 3</option>
        </select>
        <input type="hidden" name="action" value="evaluation">
        <input type="hidden" name="function" value="fetch_data">
        <input type="submit" value="Submit">
        <p> Not checked: ' . $status[0] . ', Accepted: ' . $status[1] . ', Waiting for update: ' . $status[2] + $status[4] . ', Rejected: ' . $status[3] . '</p>
        </form>
        <div id="resultContainer">
        ';

        echo '<table border="1">';
        echo '<tr><th>Status</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Research area</th><th>Presentation type</th><th>Hash ID</th></tr>';
        // Process the fetched data
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . $result['status'] . '</td>';
            echo '<td>' . $result['first_name'] . '</td>';
            echo '<td>' . $result['last_name'] . '</td>';
            echo '<td>' . $result['email'] . '</td>';
            echo '<td>' . $result['research_area'] . '</td>';
            echo '<td>' . $result['presentation_type'] . '</td>';
            echo '<td>' . $result['hash_id'] . '</td>';
            echo '</tr>';
        }
        echo '
        </table>
        </div>
        ';

?>
    </div>
    <div id="scriptContainer"></div>

<!-- Include JavaScript -->

<script>
    jQuery(document).on('submit', '#statusForm', function(e){
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'evaluation');
        formData.append('function', 'fetch_data');
        jQuery.ajax({
            method: 'post',
            url: '<?=admin_url('admin-ajax.php')?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
            var parsed = JSON.parse(response);
            jQuery("#resultContainer").html(parsed.response);
        }

    });
});
</script>

<script>
    jQuery(document).on('click', '#evaluationButton', function(e){
        e.preventDefault();
        var formData = new FormData();
        formData.append('action', 'evaluation');
        formData.append('function', 'show_evaluation');
        jQuery.ajax({
            method: 'post',
            url: '<?=admin_url('admin-ajax.php')?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
            var parsed = JSON.parse(response);
            jQuery("#displayContainer").html(parsed.response)
        }
    });
});
</script>

<script>
    jQuery(document).on('click', '#mainButton', function(e){
        e.preventDefault();
        var formData = new FormData();
        formData.append('action', 'evaluation');
        formData.append('function', 'show_main');
        jQuery.ajax({
            method: 'post',
            url: '<?=admin_url('admin-ajax.php')?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
            var parsed = JSON.parse(response);
            jQuery("#displayContainer").html(parsed.response)
        }
    });
});
</script>

<script>
     

        // Call the function to scroll to the first warning
    function checkFileExists(url) {
    return fetch(url, { method: 'HEAD' })
        .then(response => response.ok)
        .catch(() => false);
    }

    jQuery(document).on('click', '#generateButton', function(e){
        e.preventDefault();
        
        var presentationForm = document.getElementById('presentationForm');
        var formData = new FormData(presentationForm);
        formData.append('action', 'evaluation');
        formData.append('function', 'generate_abstract');
        jQuery.ajax({
            method: 'post',
            url: '<?=admin_url('admin-ajax.php')?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
            var parsed = JSON.parse(response);
            // jQuery("#displayContainer").html(parsed.response)
            var scriptContainer = document.getElementById('scriptContainer');
            // Set the innerHTML of the container to your script
            checkFileExists(parsed.pdf)
            .then(exists => {
                if (exists) {
                    document.getElementById("abstract").setAttribute("src", parsed.pdf + '?timestamp=' + new Date().getTime() + '#toolbar=0&view=FitH');
                    scriptContainer.innerHTML = parsed.response;
                    setIframeHeight();  
                }
            });
            var errorContainer = document.getElementById('errorContainer');
            if (parsed.error !== undefined && parsed.error !== null) {
                errorContainer.innerHTML = parsed.error;
            } else {
                errorContainer.innerHTML = '';
            }
            var saveMessage = document.getElementById('save-message');
            // Set the innerHTML of the container to your script
            saveMessage.innerHTML = '';


        }
    });
});
</script>

<script>
    jQuery(document).on('click', '#send-update', function(e){
        e.preventDefault();
        
        var presentationForm = document.getElementById('presentationForm');
        var formData = new FormData(presentationForm);
        formData.append('action', 'evaluation');
        formData.append('function', 'send_update');

        var textareaValue = document.getElementById('email-content').value;

        var trimmedValue = textareaValue.trim();

        if (trimmedValue !== '') {
            jQuery.ajax({
                method: 'post',
                url: '<?=admin_url('admin-ajax.php')?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                var parsed = JSON.parse(response);
                // jQuery("#displayContainer").html(parsed.response)
                var emailResult = document.getElementById('send-email');
                // Set the innerHTML of the container to your script
                emailResult.innerHTML = parsed.response;
                setIframeHeight();
            }
        });
        } else {
            var emailResult = document.getElementById('send-email');
            emailResult.innerHTML = '<p class="e-red">Please enter email text</p>';
        }
    });
</script>

<script>
    jQuery(document).on('click', '#send-reject', function(e){
        e.preventDefault();
        
        var presentationForm = document.getElementById('presentationForm');
        var formData = new FormData(presentationForm);
        formData.append('action', 'evaluation');
        formData.append('function', 'send_reject');

        var textareaValue = document.getElementById('email-content').value;

        var trimmedValue = textareaValue.trim();

        if (trimmedValue !== '') {
            jQuery.ajax({
                method: 'post',
                url: '<?=admin_url('admin-ajax.php')?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                var parsed = JSON.parse(response);
                // jQuery("#displayContainer").html(parsed.response)
                var emailResult = document.getElementById('send-email');
                // Set the innerHTML of the container to your script
                emailResult.innerHTML = parsed.response;
                setIframeHeight();
            }
        });
        } else {
            var emailResult = document.getElementById('send-email');
            emailResult.innerHTML = '<p class="e-red">Please enter email text</p>';
        }
    });
</script>

<script>
    jQuery(document).on('click', '#send-accept', function(e){
        e.preventDefault();
        
        var presentationForm = document.getElementById('presentationForm');
        var formData = new FormData(presentationForm);
        formData.append('action', 'evaluation');
        formData.append('function', 'send_accept');

            jQuery.ajax({
                method: 'post',
                url: '<?=admin_url('admin-ajax.php')?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                var parsed = JSON.parse(response);
                // jQuery("#displayContainer").html(parsed.response)
                var emailResult = document.getElementById('send-email');
                // Set the innerHTML of the container to your script
                emailResult.innerHTML = parsed.response;
                setIframeHeight();
    }});
           
    });
</script>

<script>
    jQuery(document).on('click', '#saveButton', function(e){
        e.preventDefault();
        
        var presentationForm = document.getElementById('presentationForm');
        var formData = new FormData(presentationForm);
        formData.append('action', 'evaluation');
        formData.append('function', 'save_changes');
        jQuery.ajax({
            method: 'post',
            url: '<?=admin_url('admin-ajax.php')?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
            var parsed = JSON.parse(response);
            // jQuery("#displayContainer").html(parsed.response)
            var saveMessage = document.getElementById('save-message');
            // Set the innerHTML of the container to your script
            saveMessage.innerHTML = parsed.response;
        }
    });
});
</script>




</body>
</html>
