<?php
registration_settings();
?>
<h1>
    <?php esc_html_e('Advanced Registration Settings (Devs only)', 'OR'); ?>
</h1>
<form method="POST" action="options.php">
    <?php
    settings_fields('or_registration_advanced');
    do_settings_sections('or_registration_advanced');
    submit_button();
    ?>
</form>
<form method="POST">
    <?php
    populate_database_button();
    ?>
</form>


<?php



function populate_database_button()
{
    echo '<button type="submit" name="populate-database" class="button button-primary">Populate Database</button>';

}

if (isset($_POST['populate-database'])) {
    populate_database();
}

function populate_database()
{
    global $wpdb;
    $table_name = $wpdb->prefix . get_option('or_registration_database_table');

    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

    $presentation_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';

    $presentation_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$presentation_table_name'") == $presentation_table_name;

    $person_fields = [
        'hash_id',
        'first_name',
        'last_name',
        'email',
        'institution',
        'country',
        'department',
        'title',
        'privacy',
        'needs_visa',
        'agrees_to_email',
        'research_area',
        'presentation_type',
        'presentation_id',
        'submit_time',

    ];

    $presentation_fields = [
        'hash_id',
        'presentation_id',
        'title',
        'authors',
        'affiliations',
        'references',
        'content',
        'images',
        'pdf',
        'session_id',
        'display_title'
    ];

    $person_data_sql = [
        "hash_id" => "varchar(255) NOT NULL, PRIMARY KEY (hash_id)",
        "first_name" => "varchar(255) NOT NULL",
        "last_name" => "varchar(255) NOT NULL",
        "email" => "varchar(255) NOT NULL",
        "institution" => "varchar(255) NOT NULL",
        "country" => "varchar(255) NOT NULL",
        "department" => "varchar(255) NOT NULL",
        "title" => "varchar(255)",
        "privacy" => "tinyint(1) NOT NULL",
        "needs_visa" => "tinyint(1) NOT NULL",
        "research_area" => "varchar(255) NOT NULL",
        "presentation_type" => "varchar(255) NOT NULL",
        "presentation_id" => "varchar(255) NOT NULL",
        "submit_time" => "GETDATE() NOT NULL",
        "agrees_to_email" => "tinyint(1) NOT NULL"
    ];

    $presentation_data_sql = [
        'person_hash_id' => "varchar(255) NOT NULL",
        'presentation_id' => "varchar(255) NOT NULL, PRIMARY KEY (presentation_id)",
        'title' => "varchar(255) NOT NULL",
        'authors' => "varchar(1000) NOT NULL",
        'affiliations' => "varchar(1000) NOT NULL",
        'references' => "varchar(1000) NOT NULL",
        'content' => "varchar(4000) NOT NULL",
        'images' => "varchar(1000) NOT NULL",
        'pdf' => "varchar(255) NOT NULL",
        'session_id' => "varchar(255) NOT NULL",
        'display_title' => "varchar(255) NOT NULL"

    ];
    if (!$table_exists) {
        $wpdb->query("CREATE TABLE $table_name (
            hash_id varchar(255) NOT NULL, 
            PRIMARY KEY (hash_id),
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            institution varchar(255) NOT NULL,
            country varchar(255) NOT NULL,
            department varchar(255) NOT NULL,
            title varchar(255),
            privacy varchar(255) NOT NULL,
            needs_visa varchar(255) NOT NULL,
            research_area varchar(255) NOT NULL,
            presentation_type varchar(255) NOT NULL,
            presentation_id varchar(255) NOT NULL, 
            submit_time DATETIME NOT NULL,
            agrees_to_email tinyint(1) NOT NULL
            )");


    } else {
        //check if the person table has the correct columns
        $person_columns = $wpdb->get_col("DESC $table_name", 0);

        $person_columns = array_map('strtolower', $person_columns);
        $person_fields = array_map('strtolower', $person_fields);
        foreach ($person_fields as $field) {
            if (!in_array($field, $person_columns)) {
                $wpdb->query("ALTER TABLE $table_name ADD `$field` $person_data_sql[$field]");
            } else {
                $wpdb->query("ALTER TABLE $table_name MODIFY `$field` $person_data_sql[$field]");
            }


        }
    }

    //check if the presentation table has the correct columns
    if (!$presentation_table_exists) {
        $wpdb->query("CREATE TABLE $presentation_table_name (
            person_hash_id varchar(255) NOT NULL, 
            presentation_id varchar(255) NOT NULL, 
            PRIMARY KEY (presentation_id),
            title varchar(255) NOT NULL,
            authors varchar(1000) NOT NULL,
            affiliations varchar(1000) NOT NULL,
            `references` varchar(1000) NOT NULL,
            content varchar(4000) NOT NULL,
            images varchar(1000) NOT NULL,
            pdf varchar(255) NOT NULL,
            session_id varchar(255) NOT NULL,
            display_title varchar(255) NOT NULL
            )");

    } else {
        $presentation_columns = $wpdb->get_col("DESC $presentation_table_name", 0);
        $presentation_columns = array_map('strtolower', $presentation_columns);

        $presentation_fields = array_map('strtolower', $presentation_fields);
        foreach ($presentation_fields as $field) {
            if (!in_array($field, $presentation_columns)) {
                $wpdb->query("ALTER TABLE $presentation_table_name ADD `$field` $presentation_data_sql[$field]");
            } else {
                $wpdb->query("ALTER TABLE $presentation_table_name MODIFY `$field` $presentation_data_sql[$field]");
            }
        }
    }

    //set foreign keys
    $wpdb->query("ALTER TABLE $presentation_table_name ADD FOREIGN KEY (person_hash_id) REFERENCES $table_name (hash_id)");

    echo '<div class="notice notice-success"><p>Database populated</p></div>';



}

function registration_settings()
{




    add_settings_section('or_registration_database_section', 'Database Settings', 'or_registration_database_section_callback', 'or_registration_advanced');

    add_settings_field('or_registration_database_table', 'Table', 'or_registration_database_table_callback', 'or_registration_advanced', 'or_registration_database_section');
    add_settings_section('or_registration_latex_settings', 'Latex Settings', 'or_registration_latex_settings_callback', 'or_registration_advanced');
    add_settings_field('or_registration_max_images', 'Max Images', 'or_registration_database_max_images_callback', 'or_registration_advanced', 'or_registration_latex_settings');
    $allowed_options = array(
        'or_registration_database_table',
        'or_registration_max_images'
    );
    add_allowed_options($allowed_options);


}

function or_registration_latex_settings_callback()
{
    echo 'Set the latex generation settings';
}

function or_registration_database_section_callback()
{
    echo 'Set the database table name';
}

function or_registration_database_table_callback()
{
    $value = get_option('or_registration_database_table');
    echo '<input type="text" name="or_registration_database_table" value="' . $value . '">';
}


function or_registration_database_max_images_callback()
{
    $value = get_option('or_registration_max_images');
    echo '<input type="number" name="or_registration_max_images" value="' . $value . '">';
}






?>