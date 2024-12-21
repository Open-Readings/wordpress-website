<?php

use OpenReadings\Registration;
use OpenReadings\Registration\ORCheckForm;
use OpenReadings\Registration\ORReadForm;
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
            if ($field['id'] != 'abstract_title')
                $fields[$field['id']] = $field['raw_value'];
            else {
                // $fields['display_title'] = $field['value'];
                $fields['display_title'] = $field['raw_value'];
                $fields['abstract_title'] = $field['raw_value'];

            }
        }


###########################################################################

        $registration = new RegistrationData();
        $or_get_form = new ORReadForm();
        $registration = $or_get_form->get_form();

        $form_checker = new ORCheckForm();

        $result = $form_checker->registration_check($registration);
        if ($result !== true){
            $ajax_handler->add_error_message($result);
            return;
        }

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

###########################################################################


        global $wpdb;
        
        global $or_registration_controller;

        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_or_registration WHERE hash_id = %s",
            $registration->hash_id
        ));

        if (count($result) > 0){
            $result = $or_registration_controller->update($registration, $registration->hash_id);
        } else {
            $result = $or_registration_controller->register($registration);
        }
       
        if (is_wp_error($result)) {
            $ajax_handler->add_error_message($result->get_error_message());
            return;
        }
        
        $ajax_handler->add_success_message('Registration was successful, please check your email for confirmation.');
        $ajax_handler->add_response_data('registration_success', 'Successful');

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