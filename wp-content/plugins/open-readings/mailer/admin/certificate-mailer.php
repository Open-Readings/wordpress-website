<?php


certificate_mail_settings();

?>

<form method="POST" action="options.php">
    <?php
    settings_fields('or_mailer_options');
    do_settings_sections('or_mailer_options');
    submit_button();
    ?>
</form>



<form method="POST" enctype="multipart/form-data">
    <input id="csv" type="file" name="csv" />

    <button type="submit" name="send_certificates"> SEND CERTIFICATES</button>



</form>



<?php



if (isset($_POST['send_certificates'])) {
    if ($_FILES['csv']['error'] === UPLOAD_ERR_OK) {
        $csv_file = $_FILES['csv']['tmp_name']; // Temporary file path
        send_all_certificates($csv_file);
    } else {
        echo "Error uploading file.";
    }

}





function certificate_mail_settings()
{
    add_settings_section('certificate_mail_section', 'Certificate Mail Settings', 'certificate_mail_section_callback', 'or_mailer_options');

    add_settings_field('or_mailer_certificate_subject', 'Certificate Mail Subject', 'certificate_mail_subject_callback', 'or_mailer_options', 'certificate_mail_section');

    add_settings_field('or_mailer_certificate_message', 'Certificate Mail Message', 'certificate_mail_message_callback', 'or_mailer_options', 'certificate_mail_section');

    add_settings_field('or_mailer_certificate_use_template', 'Use Template', 'certificate_mail_use_template_callback', 'or_mailer_options', 'certificate_mail_section');

    $allowed_options = array(
        'or_mailer_certificate_subject',
        'or_mailer_certificate_message',
        'or_mailer_certificate_use_template',
    );
    add_allowed_mailer_options($allowed_options);
}

function certificate_mail_section_callback()
{
    echo 'Set your certificate mail settings.';
}

function certificate_mail_subject_callback()
{
    $options = get_option('or_mailer_certificate_subject');
    $value = $options;
    echo "<input id='or_mailer_certificate_subject' name='or_mailer_certificate_subject' type='text' value='{$value}' />";
}


function certificate_mail_message_callback()
{
    $value = get_option('or_mailer_certificate_message');
    echo "<textarea id='or_mailer_certificate_message' name='or_mailer_certificate_message'>{$value}</textarea>";
}

function certificate_mail_use_template_callback()
{
    $value = get_option('or_mailer_certificate_use_template');
    echo "<input id='or_mailer_certificate_use_template' name='or_mailer_certificate_use_template' type='checkbox' value='Yes' " . checked('Yes', $value, false) . " />";
}

function add_allowed_mailer_options($allowed_options)
{
    add_filter('whitelist_options', function ($whitelist_options) use ($allowed_options) {
        foreach ($whitelist_options as $option => $data) {
            if (!in_array($option, $allowed_options)) {
                unset ($whitelist_options[$option]);
            }
        }
        return $whitelist_options;
    });
}


function send_certficicate_mail($certficate, $to)
{
    $subject = get_option('or_mailer_certificate_subject');
    $message = get_option('or_mailer_certificate_message');
    $use_template = get_option('or_mailer_certificate_use_template');


    global $or_mailer;


    $attachment = array();



    $certificate_path = WP_CONTENT_DIR . '/uploads/' . $certficate;

    $attachment['certificate.pdf'] = $certificate_path;

    return $or_mailer->send_OR_mail($to, $subject, $message, $attachment);



}


function send_all_certificates($csv)
{


    //read csv file
    $file = fopen($csv, 'r');
    if (!$file) {
        echo 'File not found';
        return;
    }
    $header = fgetcsv($file, );
    global $wpdb;
    while ($row = fgetcsv($file)) {


        $data = array_combine($header, $row);
        $to = $data['email'];
        $is_sent = $wpdb->get_results("SELECT is_sent FROM wp_or_mailer WHERE mail = '$to'");
        if ($is_sent != null) {
            if ($is_sent[0]->is_sent == 1) {
                continue;
            }
        }
        $certificate = $data['pdf'];
        $result = send_certficicate_mail($certificate, $to);

        if ($result) {
            $mail_exists = $wpdb->get_results("SELECT * FROM wp_or_mailer WHERE mail = '$to'");
            if ($mail_exists == null) {
                $wpdb->insert('wp_or_mailer', array('mail' => $to, 'is_sent' => 1));
            } else {
                $wpdb->query("UPDATE wp_or_mailer SET is_sent = 1 WHERE mail = '$to'");
            }
        }


    }

    fclose($file);
    echo 'All certificates sent';



}

