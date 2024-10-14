<?php
use OpenReadings\Registration\ORCheckForm;
use OpenReadings\Registration\ORReadForm;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

error_reporting(0);
$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );



class ORLatexExport {
    public string $folder;
    public RegistrationData $registration_data;

    public function __construct($folder){
        $this->folder = $folder;
        $or_get_fields = new ORReadForm;
        $this->registration_data = $or_get_fields->get_form();
    }

    public function generate_authors(){
        $authors_tex = '';
        foreach ($this->registration_data->authors as $i => $author) {
            if (count($author) == 3)           
                $authors_tex .= '\underline{' . $author[0] . '}$^{' . $author[1] . '}$';
            else
                $authors_tex .= $author[0] . '$^{' . $author[1] . '}$';
    
            if ($i+1 < count($this->registration_data->authors))
                $authors_tex .= ', ';
        }
        return $authors_tex;
    }

    public function generate_affiliations(){
        $affiliations_tex = '';
        foreach ($this->registration_data->affiliations as $i => $affiliation) {
            $affiliations_tex .= '\address{$^{' . $i+1 . '}$' . $affiliation . '}
        ';
        }
        foreach ($this->registration_data->authors as $author){
            if(count($author))
                $contact_email = $author[2];
        }
        $affiliations_tex .= '\rightaddress{' . $contact_email . '}';
        return $affiliations_tex;
    }

    public function generate_references(){
        if(count($this->registration_data->references) > 0){
            $references = '
            \vfill    
            \begin{thebibliography}{}
            ';
            foreach ($_POST['references'] as $i => $ref) {
                $references .= '\bibitem{' . $i+1 . '} ' . $ref . '
                ';
            }
            $references .= '\end{thebibliography}
            ';
            } else{
                $references = '';
            return $references;
        }
        return null;
    }

    public function generate_title(){
        $titleField = $this->registration_data->title;
        //$titleField = str_replace('"', '', $title);

        // Add missing </sup> tags
        $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');

        // Add missing </sub> tags
        $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');


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

    public function generate_tex(){
        $templateFilePath = '../plugins/open-readings/evaluation/admin/template.txt';
        $templateContent = file_get_contents($templateFilePath);

        $filename = "temp/" . $this->folder . "/abstract.tex";
        
        $replacements = array(
            '${title}' => $this->generate_title(),
            '${authors}' => $this->generate_authors(),
            '${affiliations}' => $this->generate_affiliations(),
            '${content}' => stripslashes($this->registration_data->abstract),
            '${references}' => $this->generate_references(),
        );

        $templateContent = str_replace(array_keys($replacements), array_values($replacements), $templateContent);
        $_ = file_put_contents($filename, $templateContent);
    }

    public function generate_abstract(){
        $_ = shell_exec('/bin/lualatex -interaction=nonstopmode --output-directory="temp/' . $this->folder . '" "temp/' . $this->folder . '/abstract.tex"');

        if (file_exists(__DIR__ . '/temp/' . $this->folder . '/abstract.pdf'))
            echo 'Export completed::0';
        else
            echo 'Export failed::1';
    }
}


function fixUnclosedTags($text, $tagOpen, $tagClose)
{
    $countOpen = substr_count($text, $tagOpen);
    $countClose = substr_count($text, $tagClose);

    $tagDiff = $countOpen - $countClose;

    if ($tagDiff > 0) {
        $text .= str_repeat($tagClose, $tagDiff);
    }

    return $text;
}

// $field_validity = check_abstract_fields();
// if ($field_validity == 0)

// else if (file_exists(__DIR__ . '/' . $folder . '/abstract.pdf')) {
//     echo 'Export failed::' . $field_validity . '::end';
// } else {
//     echo 'Export failed::' . $field_validity . '::end';
// }

$latex_generator = new ORLatexExport($_COOKIE['folder_hash']);
$person_data = new PersonData();
$person_data->map_from_class($latex_generator->registration_data, $_COOKIE['hash_id']);
$presentation_data = new PresentationData();
$presentation_data->map_from_class($latex_generator->registration_data, $_COOKIE['hash_id'], $_COOKIE['hash_id']);
global $wpdb;
$exists = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM wp_or_temp WHERE hash_id = %s", 
    $_COOKIE['hash_id']
));
if ($exists == 0){
    $or_registration_controller->register_person($person_data, $_COOKIE['hash_id'], 'wp_or_temp');
    $or_registration_controller->register_presentation($presentation_data, $_COOKIE['hash_id'], 'wp_or_temp_presentations');
}
$latex_generator->generate_tex();
$latex_generator->generate_abstract();
