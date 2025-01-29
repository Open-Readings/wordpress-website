<?php


use Elementor\Controls_Manager;

class Custom_Elementor_Form_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    public function get_name()
    {
        return 'or_custom_form_action';
    }

    public function get_label()
    {
        return __('Custom Form Action', 'elementor-pro');
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section(
            'section_custom_form_action',
            [
                'label' => __('Custom Form Action', 'elementor-pro'),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'table_name',
            [
                'label' => __('Table Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'custom_table',
                'description' => __('Enter the name of the table to store the form data.', 'elementor-pro'),
            ]
        );
        $widget->add_control(
            'send_email',
            [
                'label' => __('Should we send confirmation email?', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Should we send confirmation email', 'elementor-pro'),
            ]
        );
        $widget->add_control(
            'custom_email_subject',
            [
                'label' => __('Email Subject', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Thank you for your submission',
                'description' => __('Enter the subject of the email.', 'elementor-pro'),
            ]
        );
        $widget->add_control(
            'email_body',
            [
                'label' => __('Email Body', 'elementor-pro'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Thank you for your submission',
                'description' => __('Enter the body of the email.', 'elementor-pro'),
            ]
        );
        $widget->add_control(
            'limit_submissions',
            [
                'label' => __('Limit number of submissions', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Should we limit the maximum number of submissions', 'elementor-pro'),
            ]
        );
        $widget->add_control(
            'max_submissions',
            [
                'label' => __('Maximum submissions', 'elementor-pro'),
                'type' => Controls_Manager::NUMBER,
                'description' => __('Set the submission limit', 'elementor-pro'),
            ]
        );

        $widget->add_control(
            'limit_fields',
            [
                'label' => __('Field entries limit:', 'elementor-pro'),
                'type' => Controls_Manager::TEXTAREA,
                'description' => __('Enter limits for fields in format: field_name|value=max_entries', 'elementor-pro'),
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
        $table_name = $record->get_form_settings('table_name');
        
        
        $table_name = $wpdb->prefix . $table_name;

        $limit_submissions = $record->get_form_settings('limit_submissions'); //bool
        $max_submissions = $record->get_form_settings('max_submissions');

        if($limit_submissions == "yes"){
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

            if ($count >= $max_submissions){
                $ajax_handler->add_error_message("Registration is closed (maximum number of submissions has been reached)");
                return;
            }
        }

        // Generate a hash_id
        $hash_id = md5(uniqid(rand(), true));

        // Check if the table exists, create it if not
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name (
                hash_id VARCHAR(32) PRIMARY KEY,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }



        // Get form data from Elementor form
        $form_fields = $record->get('fields');

        // Check if the table has the required columns
        $columns = $wpdb->get_col("DESC $table_name", 0);

        // Insert hash_id into the data array
        $form_fields['hash_id'] = array(
            'value' => $hash_id,
        );

        

        if (in_array('email', $form_fields) && in_array('repeat_email', $form_fields)) {
            if ($form_fields['email']['value'] != $form_fields['repeat_email']['value']) {
                $ajax_handler->add_error_message(_e('Emails do not match'));
                return;
            }
        }
        if (in_array('privacy', $form_fields)) {
            if ($form_fields['privacy']['value'] == 'false' || $form_fields['privacy']['value'] == '') {
                $ajax_handler->add_error_message(_e('You must agree to the privacy policy'));
                return;
            }
        }
        if (in_array('research_area', $form_fields)) {
            if ($form_fields['research_area']['value'] == 'Null' || $form_fields['research_area']['value'] == 'Select') {
                $ajax_handler->add_error_message(_e('You must select a research area'));
                return;
            }
        }

        // Get the field limit data
        $pattern = '/(\w+)\|(\w+)=(\d+)/';
        preg_match_all($pattern, $record->get_form_settings('limit_fields'), $matches, PREG_SET_ORDER);

        $field_limit_arr = [];
        foreach ($matches as $match) {
            $field_name = $match[1]; // Captured field name
            $value = $match[2];  // Captured value name
            $number = (int) $match[3]; // Captured number

            $field_limit_arr[] = [
                'field_name' => $field_name,
                'value' => $value,
                'number' => $number
            ];
        }

        // Check if the field limit is reached
        foreach ($field_limit_arr as $field_limit) {
            $field_name = $field_limit['field_name'];
            $value = $field_limit['value'];
            $number = $field_limit['number'];

            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $field_name = '$value'");


            if ($count >= $number) {
                if ($form_fields[$field_name]['value'] == $value) {
                    $ajax_handler->add_error_message("(Maximum number of submissions for $field_name: $value has been reached)");
                    return;
                }
            }
        }

        // Iterate through form fields and update the database
        foreach ($form_fields as $field_id => $field_data) {
            $sanitized_value = sanitize_text_field($field_data['value']);

            if (!in_array($field_id, $columns)) {
                // Alter the table to add a new column
                $wpdb->query("ALTER TABLE $table_name ADD COLUMN $field_id VARCHAR(255) NOT NULL DEFAULT ''");
            }

            //check if the row exists
            $row = $wpdb->get_row("SELECT * FROM $table_name WHERE hash_id = '$hash_id'");
            if ($row == null) {
                // Insert data into the table
                $wpdb->insert(
                    $table_name,
                    array($field_id => $sanitized_value, 'hash_id' => $hash_id),
                    array('%s')
                );
            } else {
                // Update or insert data into the table
                $wpdb->update(
                    $table_name,
                    array($field_id => $sanitized_value),
                    array('hash_id' => $hash_id), // Use hash_id as the primary identifier
                    array('%s'),
                    array('%s')
                );
            }
        }

        if ($record->get_form_settings('send_email') == 'yes') {
            global $or_mailer;
            $email_subject = $record->get_form_settings('custom_email_subject');
            $email_body = $record->get_form_settings('email_body');

            $vars = array('${hash_id}' => $hash_id);

            foreach ($form_fields as $field_id => $field_data) {
                $vars['${' . $field_id . '}'] = $field_data['value'];
            }
            $email_body = $this->replace_template_vars($email_body, $vars);


            $result = $or_mailer->send_OR_mail($form_fields['email']['value'], $email_subject, $email_body);
            if ($result) {
                $ajax_handler->add_response_data('message', __('Your submission recorded successfully, please check the email you provided for confirmation', 'elementor-pro'));
            } else {
                $ajax_handler->add_response_data('message', __('Your submission recorded successfully', 'elementor-pro'));
            }
        }


    }
}




?>