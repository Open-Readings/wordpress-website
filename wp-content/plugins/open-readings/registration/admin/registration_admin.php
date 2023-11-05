<?php
registration_settings();
?>
<h1>
    <?php esc_html_e('Registration Settings', 'OR'); ?>
</h1>
<form method="POST" action="options.php">
    <?php
    settings_fields('or_registration');
    do_settings_sections('or_registration');
    submit_button();
    ?>
</form>






<?php

function registration_settings()
{




    add_settings_section('or_registration_section', 'Registration Settings', 'or_registration_section_callback', 'or_registration');

    add_settings_field('or_registration_start', 'Start', 'or_registration_start_callback', 'or_registration', 'or_registration_section');
    add_settings_field('or_registration_end', 'End', 'or_registration_end_callback', 'or_registration', 'or_registration_section');
    add_settings_field('or_registration_update_end', 'Update End', 'or_registration_update_end_callback', 'or_registration', 'or_registration_section');

    add_settings_section('or_registration_email_section', 'Email Settings', 'or_registration_email_section_callback', 'or_registration');

    add_settings_field('or_registration_success_email_subject', 'Subject', 'or_registration_email_subject_callback', 'or_registration', 'or_registration_email_section');
    add_settings_field('or_registration_email_success_template', 'Success Template', 'or_registration_email_success_template_callback', 'or_registration', 'or_registration_email_section');
    add_settings_field('or_registration_email_update_template', 'Update Template', 'or_registration_email_update_template_callback', 'or_registration', 'or_registration_email_section');


    $allowed_options = array(
        'or_registration_start',
        'or_registration_end',
        'or_registration_update_end',
        'or_registration_success_email_subject',
        'or_registration_email_success_template',
        'or_registration_email_update_template'
    );
    add_allowed_options($allowed_options);

}



function or_registration_section_callback()
{
    echo 'Set the start and end dates for registration';
}

function or_registration_email_section_callback()
{
    echo 'Set the email subject and templates';
}

function or_registration_start_callback()
{
    $start = get_option('or_registration_start');
    echo '<input type="date" name="or_registration_start" value="' . $start . '">';
}
function or_registration_end_callback()
{
    $end = get_option('or_registration_end');
    echo '<input type="date" name="or_registration_end" value="' . $end . '">';
}
function or_registration_update_end_callback()
{
    $update_end = get_option('or_registration_update_end');
    echo '<input type="date" name="or_registration_update_end" value="' . $update_end . '">';
}

function or_registration_email_subject_callback()
{
    $subject = get_option('or_registration_email_subject');
    echo '<input type="text" name="or_registration_email_subject" value="' . $subject . '">';
}

function or_registration_email_success_template_callback()
{
    $success_template = get_option('or_registration_email_success_template');
    echo '<textarea name="or_registration_email_success_template" rows="10" cols="50">' . $success_template . '</textarea>';
    if (!empty($success_template)) {
        echo '<h3>Preview</h3>';
        echo '<div class="preview"><table>';
        echo $success_template;
        echo '</table></div>';
    }


}

function or_registration_email_update_template_callback()
{
    $update_template = get_option('or_registration_email_update_template');
    echo '<textarea name="or_registration_email_update_template" rows="10" cols="50">' . $update_template . '</textarea>';
    if (!empty($update_template)) {
        echo '<h3>Preview</h3>';
        echo '<div class="preview"><table>';
        echo $update_template;
        echo '</table></div>';

    }


}





?>