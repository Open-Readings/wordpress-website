<?php
namespace OpenReadings\Registration;

use DateTime;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use WP_Error;



class ORLatexExport {
    public RegistrationData $registration_data;

    public function __construct($registration_data = null){
        if ($registration_data == null || is_wp_error($registration_data)) {
            $or_get_fields = new ORReadForm;
            $this->registration_data = $or_get_fields->get_form();
        } else {
            $this->registration_data = $registration_data;
        }
    }


    public function process_field($field){
        $field = stripslashes($field);
        $replacements = array(
            // Step 1: Temporarily replace `{` and `}` with placeholders
            '{' => '?:OPEN:?',
            '}' => '?:CLOSE:?',
            
            // Step 2: Other special character replacements
            '\\' => '\textbackslash{}',
            '#' => '\#{}',
            '$' => '\${}',
            '%' => '\%{}',
            '^' => '\^{}',
            '&' => '\&{}',
            '_' => '\_{}',
            '~' => '\~{}',
            
            // Step 3: Replace placeholders with safer LaTeX-escaped braces
            '?:OPEN:?' => '\{{}',
            '?:CLOSE:?' => '\}{}',
        );

        $field = str_replace(array_keys($replacements), array_values($replacements), $field);
        return $field;
    }

    public function generate_authors(){
        $authors_tex = '';
        foreach ($this->registration_data->authors as $i => $author) {
            if (count($author) == 3)           
                $authors_tex .= '\underline{' . $this->process_field($author[0]) . '}$^{' . $author[1] . '}$';
            else
                $authors_tex .= $this->process_field($author[0]) . '$^{' . $author[1] . '}$';
    
            if ($i+1 < count($this->registration_data->authors))
                $authors_tex .= ', ';
        }
        return $authors_tex;
    }

    public function generate_affiliations(){
        $affiliations_tex = '';
        foreach ($this->registration_data->affiliations as $i => $affiliation) {
            $affiliations_tex .= '\address{$^{' . $i+1 . '}$' . $this->process_field($affiliation) . '}
        ';
        }
        foreach ($this->registration_data->authors as $author){
            if(count($author) == 3)
                $contact_email = $author[2];
        }
        $affiliations_tex .= '\rightaddress{' . $this->process_field($contact_email) . '}';
        return $affiliations_tex;
    }

    public function generate_references(){
        if(count($this->registration_data->references) > 0){
            $references = '
            \vfill    
            \begin{thebibliography}{}
            ';
            foreach ($this->registration_data->references as $i => $ref) {
                $references .= '\bibitem{' . $i+1 . '} ' . $this->process_field($ref) . '
                ';
            }
            $references .= '\end{thebibliography}
            ';
            } else{
                $references = '';
            return $references;
        }
        return $references;
    }

    public function generate_acknowledgement(){
        if(trim($this->registration_data->acknowledgement) == ''){
            $acknowledgement = '';
        } else {
            $ack_content = $this->process_field($this->registration_data->acknowledgement);
            $acknowledgement = "\leavevmode\\newline
        
            {\\bfseries Acknowledgements} 
            
            {$ack_content}
            ";
        }
        
        return $acknowledgement;
    }

    public function generate_keywords(){
        if(trim($this->registration_data->keywords) == ''){
            $keywords = '';
        } else {
            $keywords_content = $this->process_field($this->registration_data->keywords);
            $keywords = "
            {\\bfseries Keywords:} {$keywords_content}
            ";
        }

        return $keywords;
    }

    private function fixUnclosedTags($text, $tagOpen, $tagClose)
    {
        $countOpen = substr_count($text, $tagOpen);
        $countClose = substr_count($text, $tagClose);

        $tagDiff = $countOpen - $countClose;

        if ($tagDiff > 0) {
            $text .= str_repeat($tagClose, $tagDiff);
        }

        return $text;
    }

    public function generate_title(){
        $titleField = $this->process_field($this->registration_data->title);
        //$titleField = str_replace('"', '', $title);


        // Add missing </sup> tags
        $titleField = $this->fixUnclosedTags($titleField, '<sup>', '</sup>');

        // Add missing </sub> tags
        $titleField = $this->fixUnclosedTags($titleField, '<sub>', '</sub>');


        $sup_starting_tag = '<sup>';
        $sub_starting_tag = '<sub>';
        $sub_ending_tag = '</sub>';
        $sup_ending_tag = '</sup>';
        $layers = 0;
        $is_in_math_mode = false;

        for ($i = 0; $i < mb_strlen($titleField); $i++) {
            if (mb_substr($titleField, $i, mb_strlen($sup_starting_tag)) == $sup_starting_tag) {
                $sup_starting_tag_index = $i;
                $layers++;
                if ($layers == 1) {
                    $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '$^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
                } else {
                    //replace <sup> with $^{
                    $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
                }
                $i -= mb_strlen($sup_starting_tag);
            }
            if (mb_substr($titleField, $i, mb_strlen($sub_starting_tag)) == $sub_starting_tag) {
                $sub_starting_tag_index = $i;
                $layers++;
                if ($layers == 1) {
                    $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '$_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
                } else {
                    //replace <sub> with $_{
                    $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
                }
                $i -= mb_strlen($sup_starting_tag);

            }

            if (mb_substr($titleField, $i, mb_strlen($sub_ending_tag)) == $sub_ending_tag) {
                $sub_ending_tag_index = $i;
                $layers--;
                if ($layers == 0) {
                    //replace </sub> with }$
                    $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}$' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
                } else {
                    //replace </sub> with }$
                    $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
                }
                //replace </sub> with }$
                $i -= mb_strlen($sup_starting_tag);
            }
            if (mb_substr($titleField, $i, mb_strlen($sup_ending_tag)) == $sup_ending_tag) {
                $sup_ending_tag_index = $i;
                $layers--;
                if ($layers == 0) {
                    //replace </sup> with }$
                    $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
                } else {
                    //replace </sup> with }$
                    $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
                }
                $i -= mb_strlen($sup_starting_tag);
            }
        }

        $titleField = str_replace('&nbsp;', ' ', $titleField);
        return $titleField;
    }

    public function generate_content(){
        $content = stripslashes($this->registration_data->abstract);

        return $content;
    }

    public function generate_tex(){
        $templateFilePath = '../plugins/open-readings/evaluation/admin/template.txt';
        $templateContent = file_get_contents($templateFilePath);

        $filename = "temp/" . $this->registration_data->session_id . "/abstract.tex";
        
        $replacements = array(
            '${title}' => $this->generate_title(),
            '${authors}' => $this->generate_authors(),
            '${affiliations}' => $this->generate_affiliations(),
            '${content}' => $this->generate_content(),
            '${acknowledgement}' => $this->generate_acknowledgement(),
            '${references}' => $this->generate_references(),
            '${keywords}' => $this->generate_keywords(),
        );

        $templateContent = str_replace(array_keys($replacements), array_values($replacements), $templateContent);
        $_ = file_put_contents($filename, $templateContent);
    }

    public function generate_abstract(){
        $d = chdir("temp/{$this->registration_data->session_id}");
        // $_ = shell_exec('/bin/lualatex -interaction=nonstopmode --output-directory="temp/' . $this->folder . '" "temp/' . $this->folder . '/abstract.tex"');
        $_ = shell_exec(
        'env ' .
        'HOME=/var/www/html/wp-content/latex/.texlive2022 ' .
        'XDG_CACHE_HOME=/var/www/html/wp-content/latex/.texlive2022 ' .
        'TEXMFCACHE=/var/www/html/wp-content/latex/.texlive2022 ' .
        'TEXMFVAR=/var/www/html/wp-content/latex/.texlive2022 ' .
        '/bin/lualatex -interaction=nonstopmode -halt-on-error -file-line-error abstract.tex 2>&1'
        );
    }

}

class ORReadForm {
    public function get_form(){
        $registration_data = new RegistrationData();
        $registration_data->first_name = $_POST['form_fields']['firstname'] ?? '';
        $registration_data->last_name = $_POST['form_fields']['lastname'] ?? '';
        $registration_data->email = $_POST['form_fields']['email'] ?? '';
        $registration_data->institution = $_POST['form_fields']['institution'] ?? '';
        $registration_data->country = $_POST['form_fields']['country'] ?? '';
        $registration_data->department = $_POST['form_fields']['department'] ?? '';
        $registration_data->privacy = isset($_POST['form_fields']['privacy']) ? true : false;
        $registration_data->needs_visa = isset($_POST['form_fields']['visa']) ? true : false;
        $registration_data->research_area = $_POST['form_fields']['research_area'] ?? '';
        $registration_data->research_subarea = $_POST['form_fields']['research_subarea'] ?? '';
        $registration_data->presentation_type = $_POST['form_fields']['presentation_type'] ?? '';
        $registration_data->agrees_to_email = isset($_POST['form_fields']['email_agree']) ? true : false;
        $registration_data->hash_id = $_POST['hash_id'];
        $registration_data->person_title = $_POST['form_fields']['person_title'] ;
        $registration_data->title = $_POST['form_fields']['abstract_title'] ?? '';
        $registration_data->authors = $this->get_authors_array();
        $registration_data->affiliations = $_POST['affiliation'] ?? [""];
        $registration_data->references = isset($_POST['references']) ? $_POST['references'] : [];
        $registration_data->images = $this->get_images();
        $registration_data->abstract = $_POST['textArea'] ?? '';
        $registration_data->acknowledgement = isset($_POST['form_fields']['acknowledgement']) ? $_POST['form_fields']['acknowledgement'] : '';
        $registration_data->keywords = isset($_POST['form_fields']['keywords']) ? $_POST['form_fields']['keywords'] : '';
        $registration_data->pdf = content_url() . '/latex/perm/' . $_POST['session_id'] . '/abstract.pdf';
        $registration_data->session_id = $_POST['session_id'];
        $registration_data->display_title = $_POST['form_fields']['abstract_title'] ?? '';

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

class ORCheckForm {
    public array $export_exact_field_settings;
    public array $export_regex_settings;
    public array $registration_exact_field_settings;
    public array $author_field_settings;

    public function __construct(){
        global $wpdb;
        $country_list = $wpdb->get_col('SELECT name FROM countries');
        global $RESEARCH_AREAS;
        $research_area_list = array_values($RESEARCH_AREAS);

        $this->export_exact_field_settings = [
            ['country', 'Country', $country_list],
            ['research_area', 'Research Area', $research_area_list],
            ['person_title', 'Study Level', ["Dr.", "Student (PhD)", "Student (Masters)", "Student (Bachelor)", "Other"]],
            ['presentation_type', 'Preferred Presentation Type', ["Oral", "Poster"]],
        ];

        $this->registration_exact_field_settings = [
            ['agrees_to_email', 'ATEfield', [true, false]],
            ['needs_visa', 'NVfield', [true, false]]
        ];

        $this->export_regex_settings = [
            ['first_name', 'First Name', 100, '/[^\p{L}\p{M}\s\'\-.]/u'],
            ['last_name', 'Last Name', 100, '/[^\p{L}\p{M}\s\'\-.]/u'],
            ['email', 'Email', 300, ''],
            ['institution', 'Academic Institution', 500, ''],
            ['department', 'Department', 500, ''],
            ['title', 'Presentation Title', 500, '/[&;]/u'],
            ['affiliations', 'Affiliations List', 500, ''],
            ['references', 'References (optional)', 1000, ''],
            ['abstract', 'Abstract content', 3000, ''],
            ['acknowledgement', 'Acknowledgement (optional)', 1000, ''],
            ['research_subarea', 'Research Areas (multiple)', 500, ''],
            ['keywords', 'Keywords (optional)', 500, ''],
        ];

        $this->author_field_settings = [
            [0, 'Authors List: author name', 200, '/[^\p{L}\p{M}\s\'\-.]/u'],
            [1, 'Authors List: affiliation reference number ', 10, '/[^0-9, ]+$/']
        ];

    }

    public function check_strings($field_check_settings, $registration_data){
        foreach ($field_check_settings as $item) {
            if (is_array($registration_data->{$item[0]})) {
                foreach ($registration_data->{$item[0]} as $field) {
                    $field = stripslashes($field);
                    if (mb_strlen($field) > $item[2]) {
                        return $item[1] . " field input too long. Please try to shorten it.";
                    }
                    if ($item[3] != '') if (preg_match($item[3], $field)) {
                        return $item[1] . " field. Special characters are not allowed in this field."; # NEEDS FIXING
                    }
                    if (trim($field) == '' && !in_array($item[0], ['references', 'research_subarea'])) {
                        return $item[1] . " field can't be left empty.";
                    }
                }
            } else {
                $field = stripslashes($registration_data->{$item[0]});
                if (mb_strlen($field) - substr_count($field, "\n") > $item[2]) {
                    return $item[1] . " field input too long. Please try to shorten it.";
                }
                if ($item[3] != '') if (preg_match($item[3], $field)) {
                    return $item[1] . " field. Special characters are not allowed in this field.";
                }
                if (trim($field) == '' && !in_array($item[0], ['references', 'acknowledgement', 'keywords'])) {
                    return $item[1] . " field can't be left empty.";
                }
            }
        }
        if (strpos($registration_data->abstract, '\begin{document}') !== false) {
            return "The \\begin{document} in the abstract content field is unnecessary. Please remove it.";
        }

        if (strpos($registration_data->abstract, '\end{document}') !== false) {
            return "The \\end{document} in the abstract content field is unnecessary. Please remove it.";
        }

        return true;
    }

    public function check_authors($field_check_authors, $registration_data){
        $contact_exists = false;
        foreach ($registration_data->authors as $author){
            if(count($author) == 3){
                $contact_exists = true;
                if (filter_var(stripslashes($author[2]), FILTER_VALIDATE_EMAIL) == false)
                    return "Corresponding author email is not valid. Check if you entered it correctly.";
            }
            foreach ($field_check_authors as $item){
                $field_id = $item[0];
                $field_value = stripslashes($author[$field_id]);
                if (mb_strlen($field_value) > $item[2]) {
                    return $item[1] . " field input too long. Please try to shorten it.";
                }
                if ($item[3] != '') if (preg_match($item[3], $field_value)) {
                    return $item[1] . " field. Special characters are not allowed in this field.";
                }
                if (trim($field_value) == '') {
                    return $item[1] . " field can't be left empty.";
                }
            }
        }

        if ($contact_exists == false)
            return "Please select the corresponding author and specify their email.";

        return true;
    }

    public function check_matches($fields_exact_match, $registration_data){
        foreach ($fields_exact_match as $item){
            $field_value = $registration_data->{$item[0]};
            $allowed_values = $item[2];
            $field_name = $item[1];
            if (!in_array($field_value, $allowed_values))
                return $field_name . " field. Check if the field is filled correctly.";
        }

        return true;
    }

    public function export_check($registration_data){

        # Get registration status (saved/not saved) and time of last latex export
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wp_or_registration_temp WHERE hash_id = %s",
            $_POST['hash_id']
        ), ARRAY_A);

        if ($row == null)
            return 'Database temp ID error';

        if ($row['saved'] == 1)
            return 'Your submition has already been saved';


        # Prevent people from spamming the generate abstract button
        $last_save_time = strtotime($row['last_export']);
        $current_time = strtotime(current_time('mysql'));

        if ($current_time - $last_save_time < 1) # If more than a second has passed continue
            return;
        else{
            $updated = $wpdb->update(
                'wp_or_registration_temp',
                array('last_export' => current_time('mysql')),
                array('hash_id' => $_POST['hash_id'])
            );
        }

        # Check if hash_id and session_id (folder hash) match
        if (hash('sha256', $_POST['hash_id']) != $_POST['session_id'])
            return 'Folder ID error. Please reload the page and try again.';

        # Check if folder exists
        if (!is_dir(WP_CONTENT_DIR . "/latex/temp/{$_POST['session_id']}"))
            return 'Folder error. Please reload the page and try again.';

        # Perform field checks before generating latex
        $response = $this->check_strings($this->export_regex_settings, $registration_data);
        if ($response !== true)
            return $response;

        $response = $this->check_authors($this->author_field_settings, $registration_data);
        if ($response !== true)
            return $response;

        $response = $this->check_matches($this->export_exact_field_settings, $registration_data);
        if ($response !== true)
            return $response;
        
        return true;
    }

    public function registration_check($registration_data){
        $response = $this->export_check($registration_data);
        if ($response !== true)
            return $response;

        $response = $this->check_matches($this->registration_exact_field_settings, $registration_data);
        if ($response !== true)
            return $response;

        $pdf_path = WP_CONTENT_DIR . '/latex/temp/' . $registration_data->session_id . '/abstract.pdf';
        if (!file_exists($pdf_path))
            return 'Please generate your abstract before submitting';

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

    public $research_subarea = array();
    public string $presentation_type;
    public bool $agrees_to_email;
    public string $hash_id;
    public string $person_title;
    public string $title;
    public $authors = array();
    public $affiliations = array();
    public $references = array();
    public $images = array();
    public string $abstract;
    public string $acknowledgement;

    public string $keywords;
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
        $this->research_subarea = json_decode($personData->research_subarea);
        $this->presentation_type = $personData->presentation_type;
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
        $this->acknowledgement = $presentationData->acknowledgement;
        $this->keywords = $presentationData->keywords;
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
    public string $research_subarea;
    public string $presentation_type;
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
        $this->research_subarea = $result['research_subarea'];
        $this->presentation_type = $result['presentation_type'];
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
        $this->research_subarea = json_encode($data->research_subarea, JSON_UNESCAPED_UNICODE);
        $this->presentation_type = $data->presentation_type;
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
    public string $acknowledgement;
    public string $keywords;
    public string $references;
    public string $images;
    public string $pdf;
    public string $person_hash_id;
    public string $display_title;

    public string $session_id;
    function map_from_query($result)
    {
        $this->title = $result['title'];
        $this->authors = $result['authors'];
        $this->affiliations = $result['affiliations'];
        $this->abstract = $result['content'];
        $this->acknowledgement = $result['acknowledgement'];
        $this->keywords = $result['keywords'];
        $this->references = $result['references'];
        $this->images = $result['images'];
        $this->pdf = $result['pdf'];
        $this->person_hash_id = $result['person_hash_id'];
        $this->session_id = $result['session_id'];
        $this->display_title = $result['display_title'];

    }

    function map_from_class(RegistrationData $data, $hash_id)
    {
        $this->title = $data->title;
        $this->authors = json_encode($data->authors, JSON_UNESCAPED_UNICODE);
        $this->affiliations = json_encode($data->affiliations, JSON_UNESCAPED_UNICODE);
        $this->abstract = $data->abstract;
        $this->acknowledgement = $data->acknowledgement;
        $this->keywords = $data->keywords;
        $this->references = json_encode($data->references, JSON_UNESCAPED_UNICODE);
        $this->images = json_encode($data->images, JSON_UNESCAPED_UNICODE);
        $this->pdf = $data->pdf;
        $this->session_id = $data->session_id;
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
        (hash_id, first_name, last_name, email, institution, country, department, privacy, needs_visa, research_area, research_subarea, presentation_type, agrees_to_email, title) 
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %d, %s)
        ';

        $query = $wpdb->prepare($query, $hash_id, $person_data->first_name, $person_data->last_name, $person_data->email, $person_data->institution, $person_data->country, $person_data->department, $person_data->privacy, $person_data->needs_visa, $person_data->research_area, $person_data->research_subarea, $person_data->presentation_type, $person_data->agrees_to_email, $person_data->title);
        $result = $wpdb->query($query);
        if ($result == false) {
            return new WP_Error('database_error', 'Database error');
        }

        return true;

    }

    function register_presentation(PresentationData $presentation_data, $table_name)
    {

        //insert person data into database
        global $wpdb;

        $query = '
        INSERT INTO ' . $table_name . '
        (person_hash_id, title, authors, affiliations, content, `references`, images, pdf, session_id, display_title, acknowledgement, keywords)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ';

        $presentation_data->pdf = WP_CONTENT_DIR . "/latex/perm/{$presentation_data->session_id}/abstract.pdf";
        $query = $wpdb->prepare($query, $presentation_data->person_hash_id, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_data->session_id, $presentation_data->display_title, $presentation_data->acknowledgement, $presentation_data->keywords);
        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }

        return true;


    }

    function get_person_data($hash_id, $temp = false)
    {
        if ($temp){
            $table_name = 'wp_or_registration_save';
        } else {
            $table_name = 'wp_or_registration';
        }
        global $wpdb;
        // $table_name = $wpdb->prefix . get_option('or_registration_database_table');
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

    function get_presentation_data($hash_id, $temp = false)
    {
        if ($temp){
            $table_name = 'wp_or_registration_presentations_save';
        } else {
            $table_name = 'wp_or_registration_presentations';
        }
        global $wpdb;
        // $table_name = $wpdb->prefix . get_option('or_registration_database_table') . '_presentations';
        $query = '
        SELECT * FROM ' . $table_name . '
        WHERE person_hash_id = %s
        ';
        $query = $wpdb->prepare($query, $hash_id);
        $result = $wpdb->get_row($query, ARRAY_A);
        //check if result is empty:
        if ($result == null) {
            return new WP_Error('database_error', 'Database error');
        }

        //map result into a PresentationData object
        $presentation_data = new PresentationData();
        $presentation_data->map_from_query($result);

        if ($temp){
            $image_path = WP_CONTENT_DIR . '/latex/temp/' . $presentation_data->session_id . '/images/';
        } else {
            $image_path = WP_CONTENT_DIR . '/latex/perm/' . $presentation_data->session_id . '/images/';
        }
        $images = scandir($image_path);
        if ($images === false) {
            $images = [];
        } else {
            $images = array_values(array_diff($images, array('.', '..')));
        }


        $presentation_data->images = json_encode($images, JSON_UNESCAPED_UNICODE);

        return $presentation_data;
    }

    function update_person_data(PersonData $person_data, $hash_id, $table_name)
    {

        //update person data into database

        global $wpdb;

        $query = 'UPDATE ' . $table_name . ' SET first_name = %s, last_name = %s, email = %s, institution = %s, country = %s, department = %s, privacy = %d, needs_visa = %d, research_area = %s, research_subarea = %s, presentation_type = %s, agrees_to_email = %d, title = %s WHERE hash_id = %s';

        $query = $wpdb->prepare($query, $person_data->first_name, $person_data->last_name, $person_data->email, $person_data->institution, $person_data->country, $person_data->department, $person_data->privacy, $person_data->needs_visa, $person_data->research_area, $person_data->research_subarea, $person_data->presentation_type, $person_data->agrees_to_email, $person_data->title, $hash_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $person_data = $this->get_person_data($hash_id);
        return $person_data;
    }

    function update_presentation_data(PresentationData $presentation_data, $hash_id, $table_name)
    {
        //update person data into database

        global $wpdb;

        $query = 'UPDATE ' . $table_name . ' SET title = %s, authors = %s, affiliations = %s, content = %s, `references` = %s, images = %s, pdf = %s, display_title= %s, acknowledgement=%s, keywords=%s WHERE person_hash_id = %s';

        $query = $wpdb->prepare($query, $presentation_data->title, $presentation_data->authors, $presentation_data->affiliations, $presentation_data->abstract, $presentation_data->references, $presentation_data->images, $presentation_data->pdf, $presentation_data->display_title, $presentation_data->acknowledgement, $presentation_data->keywords, $hash_id);

        $result = $wpdb->query($query);
        if ($result === false) {
            return new WP_Error('database_error', 'Database error');
        }
        $presentation_data = $this->get_presentation_data($hash_id);
        return $presentation_data;
    }

    public function register(RegistrationData $registration_data)
    {
        global $wpdb;
        $start_date = get_option('or_registration_start');
        $end_date = get_option('or_registration_end');
        $late_end_date = get_option(('or_registration_late_end'));
        $is_late = $wpdb->get_var($wpdb->prepare('SELECT used FROM wp_or_registration_late WHERE late_hash_id = %s', $registration_data->hash_id));
        $is_late = $is_late === "0" ? true : false;

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
            if ($now > $endDate && !$is_late) {
                return new WP_Error('registration_closed', 'Registration is closed');
            }
        }

        if (!empty($late_end_date)){
            $lateEndDate = new DateTime($late_end_date);
            if ($now > $lateEndDate){
                return new WP_Error('registration_closed', 'Registration is closed');
            }
        }



        $person_data = new PersonData();
        $person_data->map_from_class($registration_data, $registration_data->hash_id);
        
        $result = $this->register_person($person_data, $registration_data->hash_id, $wpdb->prefix . get_option('or_registration_database_table'));
        if (is_wp_error($result)) {
            return $result;
        }

        $presentation_data = new PresentationData();
        $presentation_data->map_from_class($registration_data, $registration_data->hash_id);
        $result = $this->register_presentation($presentation_data, $wpdb->prefix . get_option('or_registration_database_table') . '_presentations');
        if (is_wp_error($result)) {
            return $result;
        }

        $result = rename(WP_CONTENT_DIR . "/latex/temp/{$presentation_data->session_id}/", WP_CONTENT_DIR . "/latex/perm/{$presentation_data->session_id}/");
        
        if ($result === false) {
            return new WP_Error('folder_error', 'Failed to rename folder');
        }

        $result = $this->register_evaluation($registration_data->hash_id);
        if (is_wp_error($result)) {
            return $result;
        }

        $update = $wpdb->update(
            'wp_or_registration_temp',
            array('saved' => 1),
            array('hash_id' => $registration_data->hash_id)
        );

        if ($is_late) {
            $wpdb->update(
                'wp_or_registration_late',
                array('used' => 1),
                array('late_hash_id' => $registration_data->hash_id)
            );
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
                'evaluation_id' => $hash_id,
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
            '${research_area}' => $registration_data->research_area,
            '${research_subarea}' => $registration_data->research_subarea,
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
        $result = $wpdb->get_row($wpdb->prepare('SELECT * FROM wp_or_registration_evaluation WHERE evaluation_hash_id = %s', $hash_id));
        if ($result) {
            if ($result->status == 3) {
                return new WP_Error('evaluation_error', 'Your submission has been rejected. You cannot update it.');
            }
        }

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


        $person_data = new PersonData();
        $person_data->map_from_class($registration_data, $hash_id);
        $update = $this->update_person_data($person_data, $hash_id, $wpdb->prefix . get_option('or_registration_database_table'));

        if (is_wp_error($update)) {
            return $update;
        }

        $presentation_data = new PresentationData();
        $presentation_data->map_from_class($registration_data, $hash_id);
        $update = $this->update_presentation_data($presentation_data, $hash_id, $wpdb->prefix . get_option('or_registration_database_table') . '_presentations');
        if (is_wp_error($update)) {
            return $update;
        }

        function deleteFolder($folder) {
            if (!is_dir($folder)) {
                return false;
            }
        
            $files = array_diff(scandir($folder), ['.', '..']);
            foreach ($files as $file) {
                $path = $folder . DIRECTORY_SEPARATOR . $file;
                is_dir($path) ? deleteFolder($path) : unlink($path);
            }
            return rmdir($folder);
        }

        $temp_folder = WP_CONTENT_DIR . "/latex/temp/{$presentation_data->session_id}";
        $perm_folder = WP_CONTENT_DIR . "/latex/perm/{$presentation_data->session_id}";

        deleteFolder($perm_folder . '_old');
        $result = rename($perm_folder, $perm_folder . '_old');
        if ($result === false) {
            return new WP_Error('folder_error', 'Failed to rename folder');
        }

        $result = rename($temp_folder, $perm_folder);
        if ($result === false) {
            rename($perm_folder . '_old', $perm_folder);
            return new WP_Error('folder_error', 'Failed to rename folder');
        }

        $wpdb->query($wpdb->prepare('UPDATE wp_or_registration_evaluation SET status = 0 WHERE evaluation_hash_id = %s', $hash_id));


        $vars = $this->email_vars_map($registration_data, $hash_id);

        global $or_mailer;

        $sent = $or_mailer->send_registration_update_success_email($vars, $registration_data->email);

        if ($sent) {
            return true;
        } else {
            return new WP_Error('email_error', 'Your submission, was saved, but we experienced an error sending you a confirmation email. Please contact us at info@openreadings.eu');
        }

    }

    public function get($hash_id, $temp = false)
    {
        $person_data = $this->get_person_data($hash_id, $temp);
        
        if (is_wp_error($person_data))
            return new WP_Error('database_error', 'Database error');

        $presentation_data = $this->get_presentation_data($hash_id, $temp);
        
        if (is_wp_error($presentation_data))
            return new WP_Error('database_error', 'Database error');

        $registration_data = new RegistrationData();
        $registration_data->map_from_person_data($person_data);
        $registration_data->map_from_presentation_data($presentation_data);

        return $registration_data;
    }

}

