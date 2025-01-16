<?php
// Include WordPress
define('WP_USE_THEMES', false);
require_once(WP_CONTENT_DIR . '/../wp-load.php'); // Adjust the path as needed
$registration_functions_url = plugins_url('', __FILE__) . '/registration-functions.php';
wp_enqueue_style('registration-evaluation-style');
wp_enqueue_script('jquery');
wp_enqueue_script('institutions-list-js', '');
wp_enqueue_script('evaluation-js', '');
require_once OR_PLUGIN_DIR . 'registration/registration-session.php';



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
</head>

<body>

    <h1>Open Readings First Evaluation System</h1>

    <?php
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM wp_or_registration AS r LEFT JOIN wp_or_registration_evaluation AS e ON r.hash_id = e.evaluation_hash_id", ARRAY_A);

    //calculate the number of each status
    
    $number_of_not_checked = $wpdb->get_var("SELECT COUNT(*) FROM wp_or_registration_evaluation WHERE status = 0");
    $number_of_accepted = $wpdb->get_var("SELECT COUNT(*) FROM wp_or_registration_evaluation WHERE status = 1");
    $number_of_waiting_for_update = $wpdb->get_var("SELECT COUNT(*) FROM wp_or_registration_evaluation WHERE status = 2");
    $number_of_rejected = $wpdb->get_var("SELECT COUNT(*) FROM wp_or_registration_evaluation WHERE status = 3");
    $number_of_waiting_for_review = $wpdb->get_var("SELECT COUNT(*) FROM wp_or_registration_evaluation WHERE status = 4");
    echo ' Number of not checked: ' . $number_of_not_checked + $number_of_waiting_for_review . ', Accepted: ' . $number_of_accepted . ', Waiting for update: ' . $number_of_waiting_for_update . ', Rejected: ' . $number_of_rejected . '</p>';


    ?>




    <div>
        <h2>Instrukcijos</h2>
        <p>
            Pradėkite vertinimą paspaudę NEXT mygtuką.
            Į ką atkreipti dėmesį:
        <ul>
            <li> Ar pranešimas atitinka temą</li>
            <li> Ar tesingai nurodyta Įstaiga, jeigu laukelis yra <span style="color:red;"> raudonos</span> spalvos,
                vadinasi reikėtų pakeisti.
            </li>
            <li> Ar Tema yra išvis pasirinkta</li>
            <li> Ar tinkamai suformatuotas literatūros sąrašas</li>
            <li> Ar darbas yra 'original research'</li>
            <li> Ar viskas telpa į vieną A4 formato puslapį</li>
            <li> Ar teisingai nurodytos afiliacijos ir autorių sąrašas </li>
        </ul>
        <ul>
            <li>Jeigu norite atmesti pranešimą, paspauskite REJECT mygtuką ir įrašykite atmetimo priežastį (Bus
                išsiųstas
                dalyviui laiškas).</li>
            <li>Jeigu norite priimti pranešimą, paspauskite ACCEPT mygtuką.</li>
            <li>Jeigu norite paprašyti atnaujinimo, paspauskite UPDATE mygtuką, su priežastimi (Bus išsiųstas dalyviui
                laiškas).</li>
            <li>Jeigu norite išsaugoti pakeitimus, paspauskite SAVE mygtuką (Tą darykite pakeitę afiliaciją).</li>
            <li>Prieš išsaugant, jums reikės paspausti 'Generate' mygtuką, kad sugeneruotų PDF failą iš naujo, pagal
                atitinkamą
                šabloną.</li>
            Norėdami tikrinti kitą dalyvį, paspauskite 'NEXT' mygtuką.
        </ul>


        </p>
    </div>
    <button class="button-style r-button" id="evaluationButton">NEXT</button>
    <div id=displayContainer></div>
    <div id="scriptContainer"></div>

    <!-- Include JavaScript -->

    <script>
        jQuery(document).on('submit', '#statusForm', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'evaluation');
            formData.append('function', 'fetch_data');
            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    var parsed = JSON.parse(response);
                    jQuery("#resultContainer").html(parsed.response);
                }

            });
        });
    </script>

    <script>
        jQuery(document).on('click', '#evaluationButton', function (e) {
            e.preventDefault();
            var formData = new FormData();
            formData.append('action', 'evaluation');
            formData.append('function', 'show_evaluation');
            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    var parsed = JSON.parse(response);
                    jQuery("#displayContainer").html(parsed.response)
                }
            });
        });
    </script>

    <script>
        jQuery(document).on('click', '#mainButton', function (e) {
            e.preventDefault();
            var formData = new FormData();
            formData.append('action', 'evaluation');
            formData.append('function', 'show_main');
            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
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

        jQuery(document).on('click', '#generateButton', function (e) {
            e.preventDefault();

            var presentationForm = document.getElementById('presentationForm');
            var formData = new FormData(presentationForm);
            formData.append('action', 'evaluation');
            formData.append('function', 'generate_abstract');
            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
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
        jQuery(document).on('click', '#send-update', function (e) {
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
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
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
        jQuery(document).on('click', '#send-reject', function (e) {
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
                    url: '<?= admin_url('admin-ajax.php') ?>',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
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
        jQuery(document).on('click', '#send-accept', function (e) {
            e.preventDefault();

            var presentationForm = document.getElementById('presentationForm');
            var formData = new FormData(presentationForm);
            formData.append('action', 'evaluation');
            formData.append('function', 'send_accept');

            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    var parsed = JSON.parse(response);
                    // jQuery("#displayContainer").html(parsed.response)
                    var emailResult = document.getElementById('send-email');
                    // Set the innerHTML of the container to your script
                    emailResult.innerHTML = parsed.response;
                    setIframeHeight();
                }
            });

        });
    </script>

    <script>
        jQuery(document).on('click', '#saveButton', function (e) {
            e.preventDefault();

            var presentationForm = document.getElementById('presentationForm');
            var formData = new FormData(presentationForm);
            formData.append('action', 'evaluation');
            formData.append('function', 'save_changes');
            jQuery.ajax({
                method: 'post',
                url: '<?= admin_url('admin-ajax.php') ?>',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
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