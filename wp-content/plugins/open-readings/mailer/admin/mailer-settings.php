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
    <form method="POST">
        <?php
        clear_mail();
        ?>
    </form>
</div>

<?php


if (isset($_POST['clear-mail'])) {
    clear_mailer_database();
}

function clear_mail()
{
    echo '<button type="submit" name="clear-mail" class="button button-primary">Clear Mailer Database</button>';
}
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


function clear_mailer_database()
{

    global $wpdb;

    $table_name = $wpdb->prefix . 'or_mailer';

    //check if table exists
    $table_exits = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    //echo '<script>alert("' . $table_exits . '")</script>';
    if ($table_exits == null) {
        //table not in database. Create new table
        $sql = "CREATE TABLE $table_name (
            mail VARCHAR(64) PRIMARY KEY,
            is_sent BOOLEAN DEFAULT FALSE
        )";
        $result = $wpdb->query($sql);

        if ($result) {
            echo '<script>alert("Mailer database created")</script>';
        } else {
            echo '<script>alert("Mailer database creation failed: ' . $wpdb->last_error . '")</script>';
            //get the errors
        }

    }

    //delete all rows
    $wpdb->query("TRUNCATE TABLE $table_name");

    //echo '<script>alert("Mailer database cleared")</script>';

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