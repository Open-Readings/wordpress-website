<?php
mailer_send_settings();

?>
<div class="wrap">
    <h1>
        <?php esc_html_e('MailerSend Settings (Devs only)', 'MailerSend'); ?>
    </h1>
    <form method="POST" action="options.php">
        <?php
        settings_fields('or_mailer_options');
        do_settings_sections('or_mailer_options');
        submit_button();
        ?>
    </form>
</div>

<?php

function mailer_send_settings()
{
    add_settings_section('mailer_send_section', 'MailerSend Settings', 'mailer_send_section_callback', 'or_mailer_options');

    add_settings_field('or_mailer_api_key', 'MailerSend API Key', 'mailer_send_api_key_callback', 'or_mailer_options', 'mailer_send_section');

    $allowed_options = array(
        'or_mailer_api_key',
    );
    add_allowed_mailer_options($allowed_options);
}

function mailer_send_section_callback()
{
    echo 'Set your MailerSend API key for email sending.';
}

function mailer_send_api_key_callback()
{
    $value = get_option('or_mailer_api_key');
    echo '<input type="text" name="or_mailer_api_key" value="' . $value . '">';
}

function add_allowed_mailer_options($allowed_options)
{
    foreach ($allowed_options as $option) {
        register_setting('or_mailer_options', $option);
    }
}


?>