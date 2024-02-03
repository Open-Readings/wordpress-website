<?php
// Include WordPress
define('WP_USE_THEMES', false);
require_once(WP_CONTENT_DIR . '/../wp-load.php'); // Adjust the path as needed
$registration_functions_url = plugins_url('', __FILE__) . '/registration-functions.php';
wp_enqueue_style('registration-evaluation-style');
wp_enqueue_script('jquery');
global $wpdb;
$sql = $wpdb->prepare(
    "SELECT name FROM linkedin_universities"
);
$institutions = $wpdb->get_col($sql);

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
<button class="button-style r-button" id="evaluationButton">EVALUATE</button>
<button class="button-style hidden" id="mainButton">MAIN</button>
<div id=displayContainer>

    

    
    <?php
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM wp_or_registration", ARRAY_A);

        // echo '
        // <form class="abstract-half-div" id="statusForm" action="evaluation" method="post">
        // <label for="selectOption">Select an option:</label>
        // <select id="selectOption" name="selectedOption">
        //     <option value="option1">Option 1</option>
        //     <option value="option2">Option 2</option>
        //     <option value="option3">Option 3</option>
        // </select>
        // <input type="hidden" name="action" value="evaluation">
        // <input type="hidden" name="function" value="fetch_data">
        // <input type="submit" value="Submit">
        // </form>
        // <div id="resultContainer">
        // ';

        // echo '<table border="1">';
        // echo '<tr><th>ID</th><th>Name</th></tr>';
        // // Process the fetched data
        // foreach ($results as $result) {
        //     echo '<tr>';
        //     echo '<td>' . $result['first_name'] . '</td>';
        //     echo '<td>' . $result['last_name'] . '</td>';
        //     echo '<td>' . $result['email'] . '</td>';
        //     echo '</tr>';
        // }
        // echo '
        // </table>
        // </div>
        // ';

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

<script>
    const institutions = <?=json_encode($institutions)?>;
    function check_institution(){
        var instField = document.getElementById('institution-field');
        console.log(instField.value);
        if(institutions.includes(instField.value)){
            instField.style.backgroundColor = '#8f8';
        }else{
            instField.style.backgroundColor = '#f88';
        }
    }
</script>

<script>
    function institutionInputChange() {
    removeInstitutionDropdown();
    const value = institutionInputElement.value.toLowerCase();

    if (value.length < 4) return;
    const filteredNames = [];
    Object.values(institutions).forEach(name => {
        if (name.toLowerCase().includes(value)) {
            filteredNames.push(name);
        }
    });

    createInstitutionDropdown(filteredNames);
}

function createInstitutionDropdown(list) {
    const listEl = document.createElement("ul");
    listEl.className = 'registration-selection';
    listEl.id = 'registration-li';
    for (let i = 0; i < 40 && i < list.length; i++) {
        const listItem = document.createElement("li");
        const institutionButton = document.createElement("button");
        institutionButton.className = 'registration-dropdown-element';
        institutionButton.innerHTML = list[i];
        institutionButton.addEventListener("click", onInstitutionClick)
        listItem.appendChild(institutionButton);
        listEl.appendChild(listItem);
    }

    document.getElementById("institution-wrapper").appendChild(listEl);
}

function removeInstitutionDropdown() {
    const listEl = document.getElementById('registration-li');
    if (listEl) listEl.remove();
}

function onInstitutionClick(e) {
    e.preventDefault();
    const buttonEl = e.target;
    institutionInputElement.value = buttonEl.innerHTML;
    removeInstitutionDropdown();
    check_institution();
}
</script>

</body>
</html>