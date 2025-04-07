<?php


use Elementor\Controls_Manager;

class Elementor_Form_OR_Hash_Check extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    public function get_name()
    {
        return 'or_hash_id_check';
    }

    public function get_label()
    {
        return __('Hash ID Check', 'elementor-pro');
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section(
            'check_hash_id_section',
            [
                'label' => __('Hash ID Check', 'elementor-pro'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'check_table_name',
            [
                'label' => __('Table Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'wp_or_registration',
                'description' => __('Enter the name of the table to check.', 'elementor-pro'),
            ]
        );
        
        $widget->add_control(
            'check_column',
            [
                'label' => __('Table Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'hash_id',
                'description' => __('Enter the name of the column to check.', 'elementor-pro'),
            ]
        );

        $widget->add_control(
            'check_field',
            [
                'label' => __('Field Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'hash_id',
                'description' => __('Enter the name of the name of the form field to match.', 'elementor-pro'),
            ]
        );

        $widget->add_control(
            'check_message',
            [
                'label' => __('Message', 'elementor-pro'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('The hash ID is not recognized.', 'elementor-pro'),
                'description' => __('Enter the message to display if the hash ID is not recognized.', 'elementor-pro'),
            ]
        );

        $widget->end_controls_section();
    }


    public function on_export($element)
    {
        unset($element['settings']['table_name']);

        return $element;
    }


    private function replace_template_vars($template, $vars)
    {

        $message = strtr($template, $vars);
        return $message;

    }


    /**
     * @param ElementorProModulesFormsClassesForm_Record $record
     * @param ElementorProModulesFormsClassesAjax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler)
    {
        global $wpdb;

        // Get the table name from the control
        $table_name = $record->get_form_settings('check_table_name');
        $column = $record->get_form_settings('check_column');
        $field = $record->get_form_settings('check_field');
        $message = $record->get_form_settings('check_message');
        // Get the value of the field from the form submission
        $fields = $record->get('fields');
        $field_value = $fields[$field]['value'];
        // Check if the field value exists in the database
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE {$column} = %s", $field_value);
        $result = $wpdb->get_results($query);
        // If the field value exists, do something
        if (empty($result)) {
            // The field value does not exist, so you can proceed with the form submission
            $ajax_handler->add_error_message($message);
        } 
       
    }
}
