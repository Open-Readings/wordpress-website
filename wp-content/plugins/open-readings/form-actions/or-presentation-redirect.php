<?php

class ORPresentationUpload extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    public function get_name()
    {
        return 'or_presentation_redirect_form_action';
    }

    public function get_label()
    {
        return __('OR Presentation Upload', 'elementor-pro');
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'or_presentation'; // Add your table name here

        // SQL query to create the table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            hash_id VARCHAR(255) NOT NULL,
            presentation VARCHAR(255) NOT NULL,
            PRIMARY KEY (hash_id)
        ) ENGINE=InnoDB;";

        $wpdb->query($sql);
        $raw_fields = $record->get('fields');

        $fields = [];
        foreach ($raw_fields as $field) {
            $fields[$field['id']] = $field['value'];
        }

        $table_name = 'wp_or_registration';

        // Prepare and execute the SQL query
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE hash_id = %s",
            $fields['hash_id']
        );
        $result = $wpdb->get_var($sql);
        // Check if any rows were found
        if ($result > 0) {
            $table_name = 'wp_or_presentation';

            // Prepare and execute the SQL query
            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE hash_id = %s",
                $fields['hash_id']
            );
            $result = $wpdb->get_var($sql);
            // Check if any rows were found
            if ($result > 0) {
                $table_name = 'wp_or_presentation';

                // Define your data to be inserted
                $data = array(
                    'hash_id' => $fields['hash_id'],
                    'presentation' => $fields['presentation'],
                );
                
                // Define the data format (in this case, all string placeholders)
                $where = array(
                    'hash_id' => $fields['hash_id']
                );
                
                // Perform the update
                $result = $wpdb->update( $table_name, $data, $where );
            } else {
                $table_name = 'wp_or_presentation';

                // Define your data to be inserted
                $data = array(
                    'hash_id' => $fields['hash_id'],
                    'presentation' => $fields['presentation'],
                );
                
                // Define the data format (in this case, all string placeholders)
                $data_format = array(
                    '%s', // For string values
                    '%s', // For string values
                );
                
                $result = $wpdb->insert($table_name, $data, $data_format);
            }
            if(is_wp_error($result)){
                $ajax_handler->add_error_message("We encountered a problem while saving your presentation");
            } else {
                $ajax_handler->add_success_message("Your presentation was saved");
            }
        } else {
            // Value does not exist in the column
            $ajax_handler->add_error_message("Hash ID not found");
        }
    }
}
