<?php
namespace OpenReadings\Registration;

use DateTime;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use WP_Error;


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

    public string $person_title;
    public string $title;
    public $authors = array();
    public $affiliations = array();
    public $references = array();
    public $images = array();
    public string $abstract;
    public string $pdf;
    public string $session_id;


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

    }

}


class OpenReadingsRegistration
{


    function register_person(PersonData $person_data, $hash_id)
    {

        //check if all important fields exist and are not empty
        foreach ($person_data as $key => $value) {
            if (empty($value)) {
                if ($key == 'agrees_to_email') {
                    continue;
                }
                if ($key == 'needs_visa') {
                    continue;
                }
                return new WP_Error('missing_field', 'Missing required field: ' . $key);
            }
        }
        //insert person data into database
        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table');
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

    function register_presentation(PresentationData $presentation_data, $presentation_id)
    {
        //check if all important fields exist


        foreach ($presentation_data as $key => $value) {
            if (empty($value)) {
                if ($key == 'agrees_to_email') {
                    continue;
                }
                if ($key == 'needs_visa') {
                    continue;
                }
                return new WP_Error('missing_field', 'Missing required field: ' . $key);
            }
        }

        //insert person data into database
        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';



        $query = '
        INSERT INTO ' . $table_name . '
        (person_hash_id, presentation_id, title, authors, affiliations, content, `references`, images, pdf, session_id)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ';


        $query = $wpdb->prepare($query, $presentation_data->person_hash_id, $presentation_id, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_data->session_id);
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

    function update_person_data(PersonData $person_data, $hash_id)
    {

        //check if all important fields exist
        foreach ($person_data as $key => $value) {
            if (empty($value)) {
                if ($key == 'agrees_to_email') {
                    continue;
                }
                if ($key == 'needs_visa') {
                    continue;
                }
                return new WP_Error('missing_field', 'Missing required field: ' . $key);
            }
        }

        //update person data into database

        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table');

        $query = 'UPDATE ' . $table_name . ' SET first_name = %s, last_name = %s, email = %s, institution = %s, country = %s, department = %s, privacy = %d, needs_visa = %d, research_area = %s, presentation_type = %s, presentation_id = %s, agrees_to_email = %d WHERE hash_id = %s';

        $query = $wpdb->prepare($query, $person_data->first_name, $person_data->last_name, $person_data->email, $person_data->institution, $person_data->country, $person_data->department, $person_data->privacy, $person_data->needs_visa, $person_data->research_area, $person_data->presentation_type, $person_data->presentation_id, $person_data->agrees_to_email, $hash_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $person_data = $this->get_person_data($hash_id);
        return $person_data;
    }


    function update_presentation_data(PresentationData $presentation_data, $presentation_id)
    {
        //check if all important fields exist
        foreach ($presentation_data as $key => $value) {
            if (empty($value)) {
                return new WP_Error('missing_field', 'Missing required field: ' . $key);
            }
        }

        //update person data into database

        global $wpdb;
        $table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';

        $query = 'UPDATE ' . $table_name . ' SET title = %s, authors = %s, affiliations = %s, content = %s, `references` = %s, images = %s, pdf = %s WHERE presentation_id = %s';

        $query = $wpdb->prepare($query, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $presentation_data = $this->get_presentation_data($presentation_id);
        return $presentation_data;
    }


    public function check_form_fields(RegistrationData $registration_data)
    {

        $field_group_one = [
            ['first_name', 'First name', 100, '/[^\\p{L} ]/u'],
            ['last_name', 'Last name', 100, '/[^\\p{L} ]/u'],
            ['email', 'Email', 100, ''],
            ['institution', 'Institution', 200, '/[^\\p{L} ]/u'],
            ['country', 'Country', 100, '/[^\\p{L} ]/u'],
            ['department', 'Department', 200, '/[^\\p{L} ]/u'],
            ['research_area', 'Research area', 200, '/[^\\p{L} ]/u'],
            ['person_title', 'Person title', 200, ''],
            ['title', 'Presentation title', 300, '/[^\p{L}\p{N} .<>()\-&:^;!$]/u'],
            ['affiliations', 'Affiliation', 200, '/[^\\p{L}0-9 .<>()\\-&:;!$]/u'],
            ['references', 'Abstract references', 200, '/[^\\p{L}0-9 <>()\-&:;!$]/u'],
            ['abstract', 'Abstract content', 3000, '']
        ];
        foreach ($field_group_one as $item) {
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
        }

        $contact_exists = false;
        foreach ($registration_data->authors as $author) {
            if (mb_strlen($author[0]) > 100) {
                return "Abstract author name: input too long";
            }
            if (preg_match('/[^\\p{L} ]/u', $author[0])) {
                return "Abstract author field: special characters not allowed in field.";
            }
            if (trim($author[0]) == '') {
                return "Abstract author: detected empty field.";
            }

            if (mb_strlen($author[1]) > 100) {
                return "Author affiliation: input too long";
            }
            if (!preg_match('/^[0-9, ]+$/', $author[1])) {
                return "Author affiliation field: special characters not allowed in field.";
            }
            if (trim($author[1]) == '') {
                return "Author affiliation: detected empty field.";
            }

            if (isset($author[2])) {
                $contact_exists = true;
                if (filter_var($author[2], FILTER_VALIDATE_EMAIL) == false)
                    return "Corresponding author email not valid";
            }
        }
        if ($contact_exists == false)
            return "Corresponding author email not set";

        return true;
    }


    public function register(RegistrationData $registration_data)
    {

        $start_date = get_option('or_registration_start');
        $end_date = get_option('or_registration_end');

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

        $field_check = $this->check_form_fields($registration_data);
        if ($field_check !== true) {
            return new WP_Error('field_check_error', $field_check);
        }



        $hash_id = md5($registration_data->email . $registration_data->first_name . $registration_data->last_name . rand(0, 10000));
        $presentaion_id = md5($registration_data->title . $registration_data->email);


        $person_data = new PersonData();
        $registration_data->presentation_id = $presentaion_id;
        $person_data->map_from_class($registration_data, $hash_id);


        $result = $this->register_person($person_data, $hash_id);

        if (is_wp_error($result)) {
            return $result;
        }

        $presentaion_data = new PresentationData();
        $presentaion_data->map_from_class($registration_data, $presentaion_id, $hash_id);


        $result = $this->register_presentation($presentaion_data, $presentaion_id);
        if (is_wp_error($result)) {
            return $result;
        }

        global $or_mailer;

        $vars = $this->email_vars_map($registration_data, $hash_id);


        $sent = $or_mailer->send_registration_success_email($vars, $registration_data->email);
        if ($sent) {
            return true;
        } else {
            return new WP_Error('email_error', 'Your submission, was saved, but we experienced an error sending you a confirmation email. Please contact us at info@openreadings.eu');
        }


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
            '${abstract_pdf}' => $registration_data->pdf,
            '${hash}' => $hash_id,
            '${authors_list}' => implode(', ', $authors_list),
            '${title}' => $registration_data->title,

        );
        return $vars;

    }

    public function update(RegistrationData $registration_data, $hash_id)
    {

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

        $field_check = $this->check_form_fields($registration_data);
        if ($field_check !== true) {
            return new WP_Error('field_check_error', $field_check);
        }

        $person_data = new PersonData();
        $person_data->map_from_class($registration_data, $hash_id);
        $presentation_id = $this->get_person_data($hash_id)->presentation_id;
        $update = $this->update_person_data($person_data, $hash_id);

        if (is_wp_error($update)) {
            return $update;
        }

        $presentaion_data = new PresentationData();
        $presentaion_data->map_from_class($registration_data, $presentation_id, $hash_id);
        $update = $this->update_presentation_data($presentaion_data, $presentation_id);
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





?>