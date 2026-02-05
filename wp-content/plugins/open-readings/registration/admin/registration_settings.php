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
<form method="POST">
    <?php
    update_evaluation_table_button();
    ?>
</form>

<form method="POST">
    <button type="submit" name="fix-pdf-url" class="button button-primary">Fix PDF URL</button>
</form>



<?php



function populate_database_button()
{
    echo '<button type="submit" name="populate-database" class="button button-primary">Populate Database</button><br><br>';

}

if (isset($_POST['populate-database'])) {
    populate_database();
}

if (isset($_POST['fix-pdf-url'])) {
    fix_pdf_url();
}

function populate_database()
{
    global $wpdb;
    $table_name = $wpdb->prefix . get_option('or_registration_database_table');
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

    $presentation_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';
    $presentation_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$presentation_table_name'") == $presentation_table_name;

    $temp_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_temp';
    $temp_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$temp_table_name'") == $temp_table_name;

    $registration_save_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_save';
    $registration_save_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$registration_save_table_name'") == $registration_save_table_name;

    $presentation_save_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations_save';
    $presentation_save_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$presentation_save_table_name'") == $presentation_save_table_name;


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
        'research_subarea',
        'presentation_type',
        'submit_time',

    ];

    $presentation_fields = [
        'hash_id',
        'title',
        'authors',
        'affiliations',
        'references',
        'content',
        'images',
        'pdf',
        'session_id',
        'display_title',
        'acknowledgement',
        'keywords'
    ];

    $temp_fields = [
        'hash_id',
        'saved',
        'last_export'
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
        "research_subarea" => "varchar(255) NOT NULL",
        "presentation_type" => "varchar(255) NOT NULL",
        "submit_time" => "GETDATE() NOT NULL",
        "agrees_to_email" => "tinyint(1) NOT NULL"
    ];

    $presentation_data_sql = [
        'person_hash_id' => "varchar(255) NOT NULL, PRIMARY KEY (person_hash_id)",
        'title' => "varchar(255) NOT NULL",
        'authors' => "varchar(1000) NOT NULL",
        'affiliations' => "varchar(1000) NOT NULL",
        'references' => "varchar(1000) NOT NULL",
        'content' => "varchar(4000) NOT NULL",
        'images' => "varchar(1000) NOT NULL",
        'pdf' => "varchar(255) NOT NULL",
        'session_id' => "varchar(255) NOT NULL",
        'display_title' => "varchar(255) NOT NULL",
        'acknowledgement' => "varchar(1000) NOT NULL",
        'keywords' => "varchar(500) NOT NULL"
    ];

    $temp_data_sql = [
        'hash_id' => "varchar(255) NOT NULL, PRIMARY KEY (hash_id)",
        'saved' => "tinyint(1) NOT NULL",
        'last_export' => "DATETIME NOT NULL"
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
            research_subarea varchar(255) NOT NULL,
            presentation_type varchar(255) NOT NULL,
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

    if (!$registration_save_table_exists) {
        $wpdb->query("CREATE TABLE $registration_save_table_name (
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
            research_subarea varchar(255) NOT NULL,
            presentation_type varchar(255) NOT NULL,
            submit_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            agrees_to_email tinyint(1) NOT NULL
            )");


    } else {
        //check if the person table has the correct columns
        $person_columns = $wpdb->get_col("DESC $registration_save_table_name", 0);
        $person_columns = array_map('strtolower', $person_columns);
        $person_fields = array_map('strtolower', $person_fields);
        foreach ($person_fields as $field) {
            if (!in_array($field, $person_columns)) {
                $wpdb->query("ALTER TABLE $registration_save_table_name ADD `$field` $person_data_sql[$field]");
            } else {
                $wpdb->query("ALTER TABLE $registration_save_table_name MODIFY `$field` $person_data_sql[$field]");
            }
        }
    }

    //check if the presentation table has the correct columns
    if (!$presentation_table_exists) {
        $wpdb->query("CREATE TABLE $presentation_table_name (
            person_hash_id varchar(255) NOT NULL, 
            PRIMARY KEY (person_hash_id),
            title varchar(255) NOT NULL,
            authors varchar(1000) NOT NULL,
            affiliations varchar(1000) NOT NULL,
            `references` varchar(1000) NOT NULL,
            content varchar(4000) NOT NULL,
            images varchar(1000) NOT NULL,
            pdf varchar(255) NOT NULL,
            session_id varchar(255) NOT NULL,
            display_title varchar(255) NOT NULL,
            acknowledgement varchar(1000) NOT NULL,
            keywords varchar(500) NOT NULL
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

    if (!$presentation_save_table_exists) {
        $wpdb->query("CREATE TABLE $presentation_save_table_name (
            person_hash_id varchar(255) NOT NULL, 
            PRIMARY KEY (person_hash_id),
            title varchar(255) NOT NULL,
            authors varchar(1000) NOT NULL,
            affiliations varchar(1000) NOT NULL,
            `references` varchar(1000) NOT NULL,
            content varchar(4000) NOT NULL,
            images varchar(1000) NOT NULL,
            pdf varchar(255) NOT NULL,
            session_id varchar(255) NOT NULL,
            display_title varchar(255) NOT NULL,
            acknowledgement varchar(1000) NOT NULL,
            keywords varchar(500) NOT NULL
            )");

    } else {
        $presentation_columns = $wpdb->get_col("DESC $presentation_save_table_name", 0);
        $presentation_columns = array_map('strtolower', $presentation_columns);

        $presentation_fields = array_map('strtolower', $presentation_fields);
        foreach ($presentation_fields as $field) {
            if (!in_array($field, $presentation_columns)) {
                $wpdb->query("ALTER TABLE $presentation_save_table_name ADD `$field` $presentation_data_sql[$field]");
            } else {
                $wpdb->query("ALTER TABLE $presentation_save_table_name MODIFY `$field` $presentation_data_sql[$field]");
            }
        }
    }

    if (!$temp_table_exists) {
        $wpdb->query("CREATE TABLE $temp_table_name (
            hash_id varchar(255) NOT NULL, 
            PRIMARY KEY (hash_id),
            saved tinyint(1) NOT NULL,
            last_export DATETIME NOT NULL
            )");

    } else {
        $temp_columns = $wpdb->get_col("DESC $temp_table_name", 0);
        $temp_columns = array_map('strtolower', $temp_columns);

        $temp_fields = array_map('strtolower', $temp_fields);
        foreach ($temp_fields as $field) {
            if (!in_array($field, $temp_columns)) {
                $wpdb->query("ALTER TABLE $temp_table_name ADD `$field` $temp_data_sql[$field]");
            } else {
                $wpdb->query("ALTER TABLE $temp_table_name MODIFY `$field` $temp_data_sql[$field]");
            }
        }
    }

    //set foreign keys
    $wpdb->query("ALTER TABLE $presentation_table_name ADD FOREIGN KEY (person_hash_id) REFERENCES $table_name (hash_id)");

    echo '<div class="notice notice-success"><p>Database populated</p></div>';



}

function update_evaluation_table_button()
{
    echo '<button type="submit" name="update-evaluation-table" class="button button-primary">Update Evaluation Table</button>';

}

if (isset($_POST['update-evaluation-table'])) {
    update_evaluation_table();
}

function update_evaluation_table()
{
    global $wpdb;
    $evaluation_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_evaluation';
    $registration_table_name = $wpdb->prefix . get_option('or_registration_database_table');

    $evaluation_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$evaluation_table_name'") == $evaluation_table_name;

    $evaluation_fields = [
        'evaluation_hash_id',
        'evaluation_id',
        'status',
        'email_content',
        'checker_name',
        'current_user',
        'evaluation_date',
        'update_date',
        'grade_average',
        'rating',
    ];


    $evaluation_data_sql = [
        "evaluation_hash_id" => "varchar(255) NOT NULL",
        "evaluation_id" => "varchar(255) NOT NULL, PRIMARY KEY (evaluation_hash_id)",
        "status" => "int(11) NOT NULL",
        "email_content" => "varchar(1000)",
        "checker_name" => "varchar(255)",
        "current_user" => "varchar(255)",
        "evaluation_date" => "GETDATE()",
        "update_date" => "GETDATE()",
        "latex_error" => "varchar(255)",
        "grade_average" => "float",
        "rating" => "int(11) NOT NULL",

    ];
    $presentation_table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';
    if (!$evaluation_table_exists) {
        $wpdb->query("CREATE TABLE $evaluation_table_name (
            evaluation_hash_id varchar(255) NOT NULL, 
            evaluation_id varchar(255) NOT NULL, 
            PRIMARY KEY (evaluation_id),
            status int(11) NOT NULL,
            email_content varchar(1000),
            checker_name varchar(255),
            `current_user` varchar(255),
            evaluation_date datetime,
            update_date datetime,
            latex_error varchar(255)
            )");
    }
    //check if the person table has the correct columns
    $evaluation_columns = $wpdb->get_col("DESC $evaluation_table_name", 0);

    $evaluation_columns = array_map('strtolower', $evaluation_columns);
    $evaluation_fields = array_map('strtolower', $evaluation_fields);
    foreach ($evaluation_fields as $field) {
        if (!in_array($field, $evaluation_columns)) {
            $wpdb->query("ALTER TABLE $evaluation_table_name ADD `$field` $evaluation_data_sql[$field]");
        } else {
            $wpdb->query("ALTER TABLE $evaluation_table_name MODIFY `$field` $evaluation_data_sql[$field]");
        }


    }
    $presentation_table_results = $wpdb->get_col("SELECT person_hash_id FROM $presentation_table_name");

    # Get unique checkers
    $checkers = $wpdb->get_col("SELECT DISTINCT checker FROM $evaluation_table_name");
    $checkers = array_filter($checkers, function($checker) {
        return $checker !== null;
    });
    $evaluator_score = [];
    foreach ($checkers as $checker) {
        # get all the grades by the checker
        $grades = $wpdb->get_col($wpdb->prepare("SELECT evaluation FROM $evaluation_table_name WHERE checker = %s AND (decision = 1 OR decision = 2)", $checker));
        $grades = array_filter($grades, function($grade) {
            return $grade !== null;
        });
        # get the average of the grades
        if (!empty($grades)) {
            $average = array_sum($grades) / count($grades);
            $evaluator_score[$checker] = $average;
        }
    }
    $score_sum = array_sum($evaluator_score);
    $score_count = count($evaluator_score);
    $target = $score_count > 0 ? $score_sum / $score_count : 0;
    $coefficient = [];

    # Names of the checkers
    $checker_names = [];
    foreach ($checkers as $checker) {
        $user_info = get_userdata($checker);
        if ($user_info) {
            $checker_names[$checker] = $user_info->first_name . " " . $user_info->last_name;
        } else {
            $checker_names[$checker] = "";
        }
    }

    print("Target: $target <br>");
    foreach ($evaluator_score as $checker => $score) {
        $coefficient[$checker] = $target / $score;
        print("$checker_names[$checker],$evaluator_score[$checker],$coefficient[$checker] <br>");
    }


    foreach ($presentation_table_results as $hash_id) {
        $exists_in_evaluation_table = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $evaluation_table_name WHERE evaluation_id = %s", $hash_id));

        if (!$exists_in_evaluation_table) {
            $evaluation_id = md5($result);
            $query = '
                INSERT INTO ' . $evaluation_table_name . '
                (evaluation_hash_id, evaluation_id, status)
                VALUES (%s, %s, %d)
                ';

            $query = $wpdb->prepare($query, $hash_id, $hash_id, 0);
            $result = $wpdb->query($query);
            if (!$result) {
                echo '<div class="notice notice-error"><p>Error: ' . $wpdb->last_error . '</p></div>';
            }


        } else {
            $checker = $wpdb->get_var($wpdb->prepare("SELECT checker FROM $evaluation_table_name WHERE evaluation_id = %s", $hash_id));
            $grade = $wpdb->get_var($wpdb->prepare("SELECT evaluation FROM $evaluation_table_name WHERE evaluation_id = %s", $hash_id));
            
            if ($checker and $grade) {
                $multiplier = $coefficient[$checker];
                $new_grade = $grade * $multiplier;
                $wpdb->update($evaluation_table_name, array('grade_average' => $new_grade), array('evaluation_id' => $hash_id));
            }
        }
    }

    $wpdb->query("ALTER TABLE $evaluation_table_name ADD FOREIGN KEY (evaluation_hash_id) REFERENCES $registration_table_name (hash_id)");

    echo '<div class="notice notice-success"><p>Evaluation table populated</p></div>';

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
    add_allowed_options(array($allowed_options));


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

function fix_pdf_url()
{
    include_once(OR_PLUGIN_DIR . 'second-evaluation/second-eval-functions.php');
    global $wpdb;
    $hash_ids = $wpdb->get_col("SELECT person_hash_id FROM wp_or_registration_presentations");
    foreach ($hash_ids as $hash_id) {
        $pdf = $wpdb->get_var($wpdb->prepare("SELECT pdf FROM wp_or_registration_presentations WHERE person_hash_id = %s", $hash_id));
        if ($pdf) {
            $url = normalize_url($pdf);
            $wpdb->update('wp_or_registration_presentations', array('pdf' => $url), array('person_hash_id' => $hash_id));
        }
    }
}





?>