<?php

use OpenReadings\Registration;
use OpenReadings\Registration\RegistrationData;

class ORMainRegistrationSubmit extends ElementorPro\Modules\Forms\Classes\Action_Base
{
    public function get_name()
    {
        return 'or_registration_submit';
    }


    public function get_label()
    {
        return __('OR Registration Submit', 'or');
    }
    /**
     * @param ElementorProModulesFormsClassesForm_Record $record
     * @param ElementorProModulesFormsClassesAjax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler)
    {
        $form_name = $record->get_form_settings('form_name');
        $form_id = $record->get_form_settings('form_id');
        $raw_fields = $record->get('fields');

        $fields = [];
        foreach ($raw_fields as $field) {
            $fields[$field['id']] = $field['raw_value'];



        }


        foreach ($fields as $key => $value) {



        }

        $registration = new RegistrationData();

        if ($fields['email'] != $fields['repeat_email']) {
            $ajax_handler->add_error_message('Emails do not match');
            return;
        }

        if ($fields['privacy'] == 'false' || $fields['privacy'] == '') {
            $ajax_handler->add_error_message('You must agree to the privacy policy');
            return;
        }

        if ($fields['research_area'] == 'Null' || $fields['research_area'] == 'Select') {
            $ajax_handler->add_error_message('You must select a research area');
            return;
        }




        $registration->first_name = preg_replace('/[^\\p{L} ]/u', '', $fields['firstname']);
        $registration->last_name = preg_replace('/[^\\p{L} ]/u', '', $fields['lastname']);
        $registration->email = $fields['email'];
        $registration->institution = preg_replace('/[^\\p{L} ]/u', '', $fields['institution']);
        $registration->country = $fields['country'];
        $registration->department = preg_replace('/[^\\p{L} ]/u', '', $fields['department']);

        $registration->title = $fields['abstract_title'];
        $registration->person_title = $fields['person_title'];
        $registration->privacy = $fields['privacy'];
        $registration->needs_visa = $fields['visa'];
        $registration->agrees_to_email = $fields['email_agree'];
        $registration->research_area = $fields['research_area'];
        $registration->presentation_type = $fields['presentation_type'];

        $author_name_array = $_POST['name']; //array su vardais is eiles
        $author_affiliation_reference_array = $_POST['aff_ref']; //array su nuorodomis i affiliacijas is eiles
        $author_radio = $_POST['contact_author']; //grazina skaiciu (autoriaus eiles nr)
        $author_contact_email = $_POST['email-author'];
        $affiliation_array = $_POST['affiliation'];
        $abstract_content = $_POST['textArea'];
        if (!isset($_SESSION['id'])) {
            session_start();
            $_SESSION['id'] = 1;
        }
        $session_id = $_SESSION['file'];



        $registration->session_id = $session_id;
        $generated_files_dir = WP_CONTENT_DIR . '/latex/' . $session_id . '';
        $generated_images_dir = $generated_files_dir . '/images/';
        $uploaded_images = scandir($generated_images_dir);
        $img_array = array();
        foreach ($uploaded_images as $image) {
            if (!is_file($generated_images_dir . '/' . $image)) {
                continue;
            }
            if ($image != '.' && $image != '..') {
                $img_array[] = $image;

            }
        }
        $registration->images = $img_array;
        $pdf = $generated_files_dir . '/abstract.pdf';
        if (!file_exists($pdf)) {
            $ajax_handler->add_error_message('Please generate your abstract before submitting');
            return;
        }
        $pdf = str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $pdf);
        $registration->pdf = $pdf;

        if(isset($_POST['references'])){
            $reference_array = $_POST['references']; //array su reference'ais is eiles
            $references = [];
            foreach ($reference_array as $reference) {
                $references[] = $reference;
        }
        }
        else{
            $reference_array = [];
            $references = [];
        }

        
        $registration->references = $references;

        $aggregated_authors_array = array();
        for ($i = 0; $i < count($author_name_array); $i++) {
            if ($author_radio == $i + 1)
                $aggregated_authors_array[$i] = array(preg_replace('/[^\\p{L} ]/u', '', $author_name_array[$i]), $author_affiliation_reference_array[$i], $author_contact_email);
            else {
                $aggregated_authors_array[$i] = array(preg_replace('/[^\\p{L} ]/u', '', $author_name_array[$i]), $author_affiliation_reference_array[$i]);
            }
        }

        $registration->authors = $aggregated_authors_array;
        $registration->affiliations = $affiliation_array;
        $registration->references = $reference_array;
        $registration->abstract = $abstract_content;

        $registration->session_id = $session_id;

        global $or_registration_controller;
        if(isset($_SESSION['update'])){
            $registration->presentation_id = $_SESSION['presentation_id'];
            $result = $or_registration_controller->update($registration, $_SESSION['hash']);
        }
        else{
            $result = $or_registration_controller->register($registration);
        }
        if (is_wp_error($result)) {
            $ajax_handler->add_error_message($result->get_error_message());
            return;
        }
        session_unset();
        session_destroy();
        $ajax_handler->add_success_message('Registration was successful, please check your email for confirmation.');

    }


    public function on_export($element)
    {
        return [];
    }

    public function on_import($element)
    {
    }

    public function register_settings_section($widget)
    {

    }


}




?>