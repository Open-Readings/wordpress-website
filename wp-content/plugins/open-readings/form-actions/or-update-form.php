<?php


use Elementor\Controls_Manager;

class ORUpdateFormAction extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    public function get_name()
    {
        return 'or_update_form_action';
    }

    public function get_label()
    {
        return __('OR Update Form Action', 'elementor-pro');
    }

    public function register_settings_section($widget)
    {

    }


    public function on_export($element)
    {

        return $element;
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
            $fields[$field['id']] = $field['value'];

        }
        $hash_id = $fields['hash_id'];
        $url = 'https://openreadings.eu/registration/?id=' . $hash_id;
        echo '<script>window.location.href = "' . $url . '";</script>';

        exit;
    }
}




?>