<?php
namespace OpenReadings\Registration;

use DateTime;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use WP_Error;

class ORReadForm
{
    public function get_form(){
        $registration_data = new RegistrationData();
        $registration_data->first_name = $_POST['form_fields']['firstname'];
        $registration_data->last_name = $_POST['form_fields']['lastname'];
        $registration_data->email = $_POST['form_fields']['email'];
        $registration_data->institution = $_POST['form_fields']['institution'];
        $registration_data->country = $_POST['form_fields']['country'];
        $registration_data->department = $_POST['form_fields']['department'];
        $registration_data->privacy = isset($_POST['form_fields']['privacy']) ? true : false;
        $registration_data->needs_visa = isset($_POST['form_fields']['visa']) ? true : false;
        $registration_data->research_area = $_POST['form_fields']['research_area'];
        $registration_data->presentation_type = $_POST['form_fields']['presentation_type'];
        $registration_data->presentation_id = $_COOKIE['hash_id'];
        $registration_data->agrees_to_email = isset($_POST['form_fields']['email_agree']) ? true : false;
        $registration_data->hash_id = $_COOKIE['hash_id'];
        $registration_data->person_title = $_POST['form_fields']['person_title'];
        $registration_data->title = $_POST['form_fields']['abstract_title'];
        $registration_data->authors = $this->get_authors_array();
        $registration_data->affiliations = $_POST['affiliation'];
        $registration_data->references = isset($_POST['reference']) ? $_POST['reference'] : [];
        $registration_data->images = $this->get_images();
        $registration_data->abstract = $_POST['textArea'];
        $registration_data->pdf = WP_CONTENT_DIR . '/latex/temp/' . $_POST['session_id'] . '/abstract.pdf';
        $registration_data->session_id = $_POST['session_id'];
        $registration_data->display_title = $_POST['form_fields']['abstract_title'];

        return $registration_data;
    }

    public function get_authors_array(){
        $authors_array = [];
        $author_names = $_POST['name'];
        $author_affiliations = $_POST['aff_ref'];
        for ($i = 0; $i < count($author_names); $i++) {
            if ($_POST['contact_author'] == $i + 1)
                $authors_array[$i] = array($author_names[$i], $author_affiliations[$i], $_POST['email-author']);
            else {
                $authors_array[$i] = array($author_names[$i], $author_affiliations[$i]);
            }
        }
        return $authors_array;
    }

    public function get_images(){
        $directory_path = WP_CONTENT_DIR . '/latex/temp/' . $_POST['session_id'] . '/abstract.pdf';
        $image_directory_path = $directory_path . '/images/';
        $uploaded_images = scandir($image_directory_path);
        $img_array = array();
        foreach ($uploaded_images as $image) {
            if (!is_file($image_directory_path . '/' . $image)) {
                continue;
            }
            if ($image != '.' && $image != '..') {
                $img_array[] = $image;
            }
        }
        return $img_array;
    }
}

class ORCheckForm
{

    public function check_fields($registration_data){
        global $wpdb;
        $country_list = $wpdb->get_col('SELECT name FROM countries');
        global $RESEARCH_AREAS;
        $research_area_list = array_values($RESEARCH_AREAS);

        $fields_exact_match = [
            ['country', 'Country', $country_list],
            ['research_area', 'Research area', $research_area_list],
            ['person_title', 'Person title', ["", "Dr.", "Student (PhD)", "Student (Master's)", "Student (Bachelor)"]],
            ['agrees_to_email', 'ATEfield', [true, false]],
            ['needs_visa', 'NVfield', [true, false]]
        ];

        $field_check_settings = [
            ['first_name', 'First name', 100, '/[^\\p{L}\-. ]/u'],
            ['last_name', 'Last name', 100, '/[^\\p{L}\-. ]/u'],
            ['email', 'Email', 300, ''],
            ['institution', 'Institution', 500, '/[^\\p{L}(),.\- ]/u'],
            ['department', 'Department', 500, '/[^\\p{L}()-,. ]/u'],
            ['title', 'Presentation title', 500, ''],
            ['affiliations', 'Affiliation', 500, ''],
            ['references', 'Abstract references', 1000, ''],
            ['abstract', 'Abstract content', 3000, '']
        ];

        $field_check_authors = [
            [0, 'Author name', 200, '/[^\\p{L} ]/u'],
            [1, 'Author affiliation reference number', 3, '/[^0-9, ]+$/']
        ];

        $check_if_pdf_exists = true;

        $response = $this->check_strings($field_check_settings, $registration_data);
        if ($response !== true)
            return $response;

        $response = $this->check_authors($field_check_authors, $registration_data);
        if ($response !== true)
            return $response;

        $response = $this->check_matches($fields_exact_match, $registration_data);
        if ($response !== true)
            return $response;

        $pdf_path = WP_CONTENT_DIR . '/latex/temp/' . $registration_data->session_id . '/abstract.pdf';
        if ($check_if_pdf_exists and !file_exists($pdf_path))
            return 'Please generate your abstract before submitting';

        return true;

    }

    public function check_strings($field_check_settings, $registration_data){
        foreach ($field_check_settings as $item) {
            if (is_array($registration_data->{$item[0]})) {
                foreach ($registration_data->{$item[0]} as $field) {
                    if (mb_strlen($field) > $item[2]) {
                        return $item[1] . ": field input too long";
                    }
                    if ($item[3] != '') if (preg_match($item[3], $field)) {
                        return $item[1] . " field: special characters not allowed in field.";
                    }
                    if (trim($field) == '' && $item[0] != 'references') {
                        return $item[1] . ": detected empty field.";
                    }
                }
            } else {
                $field = $registration_data->{$item[0]};
                if (mb_strlen($field) - substr_count($field, "\n") > $item[2]) {
                    return $item[1] . ": field input too long";
                }
                if ($item[3] != '') if (preg_match($item[3], $field)) {
                    return $item[1] . " field: special characters not allowed in field.";
                }
                if (trim($field) == '' && $item[0] != 'references') {
                    return $item[1] . ": detected empty field.";
                }
            }
        }
        return true;
    }

    public function check_authors($field_check_authors, $registration_data){
        $contact_exists = false;
        foreach ($registration_data->authors as $author){
            if(count($author) == 3){
                $contact_exists = true;
                if (filter_var($author[2], FILTER_VALIDATE_EMAIL) == false)
                    return "Corresponding author email not valid";
            }
            foreach ($field_check_authors as $item){
                $field_id = $item[0];
                $field_value = $author[$field_id];
                if (mb_strlen($field_value) > $item[2]) {
                    return $item[1] . ": field input too long";
                }
                if ($item[3] != '') if (preg_match($item[3], $field_value)) {
                    return $item[1] . " field: special characters not allowed in field.";
                }
                if (trim($field_value) == '' && $item[0]) {
                    return $item[1] . ": detected empty field.";
                }
            }
        }

        if ($contact_exists == false)
            return "Corresponding author email not set";

        return true;
    }

    public function check_matches($fields_exact_match, $registration_data){
        foreach ($fields_exact_match as $item){
            $field_value = $registration_data->{$item[0]};
            $allowed_values = $item[2];
            $field_name = $item[1];
            if (!in_array($field_value, $allowed_values))
                return $field_name . " field value is incorrect";
        }

        return true;
    }
}


class RegistrationData
{
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $institution;
    public string $country;
    public string $department;
    public bool $privacy;
    public bool $needs_visa;
    public string $research_area;
    public string $presentation_type;
    public string $presentation_id;
    public bool $agrees_to_email;
    public string $hash_id;
    public string $person_title;
    public string $title;
    public $authors = array();
    public $affiliations = array();
    public $references = array();
    public $images = array();
    public string $abstract;
    public string $pdf;
    public string $session_id;

    public string $display_title;


    function map_from_person_data(PersonData $personData)
    {
        $this->person_title = $personData->title;
        $this->first_name = $personData->first_name;
        $this->last_name = $personData->last_name;
        $this->email = $personData->email;
        $this->institution = $personData->institution;
        $this->country = $personData->country;
        $this->department = $personData->department;
        $this->privacy = $personData->privacy;
        $this->needs_visa = $personData->needs_visa;
        $this->research_area = $personData->research_area;
        $this->presentation_type = $personData->presentation_type;
        $this->presentation_id = $personData->presentation_id;
        $this->agrees_to_email = $personData->agrees_to_email;



    }

    function map_from_presentation_data(PresentationData $presentationData)
    {
        $this->title = $presentationData->title;
        $this->authors = json_decode($presentationData->authors);
        $this->affiliations = json_decode($presentationData->affiliations);
        $this->references = json_decode($presentationData->references);
        $this->images = json_decode($presentationData->images);
        $this->abstract = $presentationData->abstract;
        $this->pdf = $presentationData->pdf;
        $this->hash_id = $presentationData->person_hash_id;
        $this->session_id = $presentationData->session_id;
        $this->display_title = $presentationData->title;

    }


}

class PersonData
{
    public string $title;
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $institution;
    public string $country;
    public string $department;
    public bool $privacy;
    public bool $needs_visa;
    public string $research_area;
    public string $presentation_type;
    public string $presentation_id;
    public bool $agrees_to_email;
    public string $hash_id;
    public string $session_id;

    function map_from_query($result)
    {
        $this->first_name = $result['first_name'];
        $this->last_name = $result['last_name'];
        $this->email = $result['email'];
        $this->institution = $result['institution'];
        $this->country = $result['country'];
        $this->department = $result['department'];
        $this->privacy = $result['privacy'];
        $this->needs_visa = $result['needs_visa'];
        $this->research_area = $result['research_area'];
        $this->presentation_type = $result['presentation_type'];
        $this->presentation_id = $result['presentation_id'];
        $this->agrees_to_email = $result['agrees_to_email'];
        $this->title = $result['title'];


    }

    function map_from_class(RegistrationData $data, $hash_id)
    {
        $this->first_name = $data->first_name;
        $this->last_name = $data->last_name;
        $this->email = $data->email;
        $this->institution = $data->institution;
        $this->country = $data->country;
        $this->department = $data->department;
        $this->privacy = $data->privacy;
        $this->needs_visa = $data->needs_visa;
        $this->research_area = $data->research_area;
        $this->presentation_type = $data->presentation_type;
        $this->presentation_id = $data->presentation_id;
        $this->agrees_to_email = $data->agrees_to_email;
        $this->hash_id = $hash_id;
        $this->title = $data->person_title;


    }
}


class PresentationData
{
    public string $title;
    public string $authors;
    public string $affiliations;
    public string $abstract;
    public string $references;
    public string $images;
    public string $pdf;
    public string $person_hash_id;
    public string $presentation_id;
    public string $display_title;

    public string $session_id;
    function map_from_query($result)
    {
        $this->title = $result['title'];
        $this->authors = $result['authors'];
        $this->affiliations = $result['affiliations'];
        $this->abstract = $result['content'];
        $this->references = $result['references'];
        $this->images = $result['images'];
        $this->pdf = $result['pdf'];
        $this->person_hash_id = $result['person_hash_id'];
        $this->session_id = $result['session_id'];
        $this->display_title = $result['display_title'];

    }

    function map_from_class(RegistrationData $data, $presentation_id, $hash_id)
    {
        $this->title = $data->title;
        $this->authors = json_encode($data->authors, JSON_UNESCAPED_UNICODE);
        $this->affiliations = json_encode($data->affiliations, JSON_UNESCAPED_UNICODE);
        $this->abstract = $data->abstract;
        $this->references = json_encode($data->references, JSON_UNESCAPED_UNICODE);
        $this->images = json_encode($data->images, JSON_UNESCAPED_UNICODE);
        $this->pdf = $data->pdf;
        $this->session_id = $data->session_id;
        $this->presentation_id = $presentation_id;
        $this->person_hash_id = $hash_id;
        $this->display_title = $data->display_title;

    }

}


class OpenReadingsRegistration
{
    function register_person(PersonData $person_data, $hash_id, $table_name)
    {

        //insert person data into database
        global $wpdb;
        $query = '
        INSERT INTO ' . $table_name . '
        (hash_id, first_name, last_name, email, institution, country, department, privacy, needs_visa, research_area, presentation_type, presentation_id, agrees_to_email, title) 
        VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %s, %s, %s, %d, %s)
        ';

        $query = $wpdb->prepare($query, $hash_id, $person_data->first_name, $person_data->last_name, $person_data->email, $person_data->institution, $person_data->country, $person_data->department, $person_data->privacy, $person_data->needs_visa, $person_data->research_area, $person_data->presentation_type, $person_data->presentation_id, $person_data->agrees_to_email, $person_data->title);
        $result = $wpdb->query($query);
        if ($result == false) {
            return new WP_Error('database_error', 'Database error');
        }

        return true;

    }

    function register_presentation(PresentationData $presentation_data, $presentation_id, $table_name)
    {

        //insert person data into database
        global $wpdb;

        $query = '
        INSERT INTO ' . $table_name . '
        (person_hash_id, presentation_id, title, authors, affiliations, content, `references`, images, pdf, session_id, display_title)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ';


        $query = $wpdb->prepare($query, $presentation_data->person_hash_id, $presentation_id, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_data->session_id, $presentation_data->display_title);
        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }

        return true;


    }

    function get_person_data($hash_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table');
        $query = '
        SELECT * FROM ' . $table_name . '
        WHERE hash_id = %s
        ';
        $query = $wpdb->prepare($query, $hash_id);
        $result = $wpdb->get_row($query, ARRAY_A);
        if ($result == null) {
            return new WP_Error('database_error', 'Database error');
        }

        //map result into a PersonData object
        $person_data = new PersonData();
        $person_data->map_from_query($result);


        return $person_data;


    }

    function get_presentation_data($presentation_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';
        $query = '
        SELECT * FROM ' . $table_name . '
        WHERE presentation_id = %s
        ';
        $query = $wpdb->prepare($query, $presentation_id);
        $result = $wpdb->get_row($query, ARRAY_A);
        //check if result is empty:
        if ($result == null) {
            return new WP_Error('database_error', 'Database error');
        }

        //map result into a PresentationData object
        $presentation_data = new PresentationData();
        $presentation_data->map_from_query($result);

        return $presentation_data;
    }

    function update_person_data(PersonData $person_data, $hash_id, $table_name)
    {

        //update person data into database

        global $wpdb;

        $query = 'UPDATE ' . $table_name . ' SET first_name = %s, last_name = %s, email = %s, institution = %s, country = %s, department = %s, privacy = %d, needs_visa = %d, research_area = %s, presentation_type = %s, presentation_id = %s, agrees_to_email = %d, title = %s WHERE hash_id = %s';

        $query = $wpdb->prepare($query, $person_data->first_name, $person_data->last_name, $person_data->email, $person_data->institution, $person_data->country, $person_data->department, $person_data->privacy, $person_data->needs_visa, $person_data->research_area, $person_data->presentation_type, $person_data->presentation_id, $person_data->agrees_to_email, $person_data->title, $hash_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $person_data = $this->get_person_data($hash_id);
        return $person_data;
    }

    function update_presentation_data(PresentationData $presentation_data, $presentation_id, $table_name)
    {
        //update person data into database

        global $wpdb;

        $query = 'UPDATE ' . $table_name . ' SET title = %s, authors = %s, affiliations = %s, content = %s, `references` = %s, images = %s, pdf = %s, display_title= %s WHERE presentation_id = %s';

        $query = $wpdb->prepare($query, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_data->display_title, $presentation_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $presentation_data = $this->get_presentation_data($presentation_id);
        return $presentation_data;
    }

    public function register(RegistrationData $registration_data)
    {
        global $wpdb;
        $start_date = get_option('or_registration_start');
        $end_date = get_option('or_registration_end');
        $late_end_date = get_option(('or_registration_late_end'));

        $now = new DateTime();

        if (empty($start_date)) {
            return new WP_Error('registration_not_open', 'Registration is not yet open');
        } else {
            $startDate = new DateTime($start_date);

            if ($now < $startDate) {
                return new WP_Error('registration_not_open', 'Registration is not yet open');
            }
        }

        if (!empty($late_end_date)){
            $lateEndDate = new DateTime($late_end_date);
            if ($now > $lateEndDate){
                return new WP_Error('registration_closed', 'Registration is closed');
            }
        }

        if (!empty($end_date)) {
            $endDate = new DateTime($end_date);
            if ($now > $endDate) {
                return new WP_Error('registration_closed', 'Registration is closed');
            }
        }

        $form_checker = new ORCheckForm();
        $field_check = $form_checker->check_fields($registration_data);
        if ($field_check !== true) {
            return new WP_Error('field_check_error', $field_check);
        }

        $presentation_id = md5($registration_data->title . $registration_data->email);


        $person_data = new PersonData();
        $person_data->map_from_class($registration_data, $registration_data->hash_id);
        
        $result = $this->register_person($person_data, $registration_data->hash_id, $wpdb->prefix . get_option('or_registration_database_table'));
        if (is_wp_error($result)) {
            return $result;
        }

        $presentation_data = new PresentationData();
        $presentation_data->map_from_class($registration_data, $registration_data->presentation_id, $registration_data->hash_id);
        $result = $this->register_presentation($presentation_data, $presentation_id, $wpdb->prefix . get_option('or_registration_database_table') . '_presentations');
        if (is_wp_error($result)) {
            return $result;
        }

        $result = $this->register_evaluation($registration_data->hash_id);
        if (is_wp_error($result)) {
            return $result;
        }

        global $or_mailer;

        $vars = $this->email_vars_map($registration_data, $registration_data->hash_id);

        $sent = $or_mailer->send_registration_success_email($vars, $registration_data->email);
        if ($sent) {
            return true;
        } else {
            return new WP_Error('email_error', 'Your submission, was saved, but we experienced an error sending you a confirmation email. Please contact us at info@openreadings.eu');
        }
        

    }

    function register_evaluation($hash_id){
        global $wpdb;

        $table_name = 'wp_or_registration_evaluation';
        $result = $wpdb->insert(
            $table_name,
            array(
                'evaluation_hash_id' => $hash_id,
                'evaluation_id' => md5($hash_id)
            )
        );
        return $result;
    }

    function email_vars_map($registration_data, $hash_id)
    {

        $authors_list_string = '';
        //seperate authors by comma
        $authors_list = [];
        foreach ($registration_data->authors as $author) {
            $authors_list[] = $author[0];
        }
        $vars = array(
            '${firstname}' => $registration_data->first_name,
            '${lastname}' => $registration_data->last_name,
            '${email}' => $registration_data->email,
            '${institution}' => $registration_data->institution,
            '${country}' => $registration_data->country,
            '${department}' => $registration_data->department,
            '${presentation_title}' => $registration_data->title,
            '${abstract_pdf}' => $registration_data->pdf . '?' . time(),
            '${hash}' => $hash_id,
            '${authors_list}' => implode(', ', $authors_list),
            '${title}' => $registration_data->title,
            '${display_title}' => $registration_data->display_title
        );
        return $vars;

    }

    public function update(RegistrationData $registration_data, $hash_id)
    {
        global $wpdb;
        $start_date = get_option('or_registration_start');
        $end_date = get_option('or_registration_update_end');

        $now = new DateTime();

        if (empty($start_date)) {
            return new WP_Error('registration_not_open', 'Registration is not yet open');
        } else {
            $startDate = new DateTime($start_date);

            if ($now < $startDate) {
                return new WP_Error('registration_not_open', 'Registration is not yet open');
            }
        }

        if (!empty($end_date)) {
            $endDate = new DateTime($end_date);
            if ($now > $endDate) {
                return new WP_Error('registration_closed', 'Registration is closed');
            }
        }

        $form_checker = new ORCheckForm();
        $field_check = $form_checker->check_fields($registration_data);
        if ($field_check !== true) {
            return new WP_Error('field_check_error', $field_check);
        }

        $person_data = new PersonData();
        $person_data->map_from_class($registration_data, $hash_id);
        $presentation_id = $this->get_person_data($hash_id)->presentation_id;
        $update = $this->update_person_data($person_data, $hash_id, $wpdb->prefix . get_option('or_registration_database_table'));

        if (is_wp_error($update)) {
            return $update;
        }

        $presentation_data = new PresentationData();
        $presentation_data->map_from_class($registration_data, $presentation_id, $hash_id);
        $update = $this->update_presentation_data($presentation_data, $presentation_id, $wpdb->prefix . get_option('or_registration_database_table') . '_presentations');
        if (is_wp_error($update)) {
            return $update;
        }


        $vars = $this->email_vars_map($registration_data, $hash_id);
        global $or_mailer;

        $sent = $or_mailer->send_registration_update_success_email($vars, $registration_data->email);

        if ($sent) {
            return true;
        } else {
            return new WP_Error('email_error', 'Your submission, was saved, but we experienced an error sending you a confirmation email. Please contact us at info@openreadings.eu');
        }

    }

    public function get($hash_id)
    {
        $person_data = $this->get_person_data($hash_id);
        if (is_wp_error($person_data))
            return new WP_Error('database_error', 'Database error');
        $presentation_data = $this->get_presentation_data($person_data->presentation_id);
        if (is_wp_error($presentation_data))
            return new WP_Error('database_error', 'Database error');
        $registration_data = new RegistrationData();
        $registration_data->map_from_person_data($person_data);
        $registration_data->map_from_presentation_data($presentation_data);
        return $registration_data;
    }


}

