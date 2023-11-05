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
            $fields[$field['name']] = $field['value'];
        }

        $registration = new RegistrationData();

        $registration->first_name = $fields['first_name'];
        $registration->last_name = $fields['last_name'];
        $registration->email = $fields['email'];
        $registration->institution = $fields['institution'];
        $registration->country = $fields['country'];
        $registration->department = $fields['department'];

        $registration->title = $fields['abstract_title'];
        $registration->privacy = $fields['privacy'];
        $registration->needs_visa = $fields['needs_visa'];
        $registration->agrees_to_email = $fields['agrees_to_email'];
        $registration->research_area = $fields['research_area'];
        $registration->presentation_type = $fields['presentation_type'];

        echo print_r($raw_fields);






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