<?php


use Elementor\Controls_Manager;

class ORSubmissionAction extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    public function get_name()
    {
        return 'or_custom_form_action';
    }

    public function get_label()
    {
        return __('OR Submission Action', 'elementor-pro');
    }

    public function register_settings_section($widget)
    {
        $widget->start_controls_section(
            'section_custom_form_action',
            [
                'label' => __('OR Submission Action', 'elementor-pro'),
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
            'create_table',
            [
                'label' => __('Create table if missing', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Enable to create the submission table if it does not exist.', 'elementor-pro'),
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
            'or_email_from',
            [
                'label' => __('Sender Email', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => 'it@openreadings.eu',
                'options' => [
                    'it@openreadings.eu' => 'it@openreadings.eu',
                    'info@openreadings.eu' => 'info@openreadings.eu',
                ],
                'description' => __('Choose the sender address for the confirmation email.', 'elementor-pro'),
                'condition' => [
                    'send_email' => 'yes',
                ],
            ]
        );
        $widget->add_control(
            'custom_email_subject',
            [
                'label' => __('Email Subject', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Thank you for your submission',
                'description' => __('Enter the subject of the email.', 'elementor-pro'),
                'condition' => [
                    'send_email' => 'yes',
                ],
            ]
        );
        $widget->add_control(
            'email_body',
            [
                'label' => __('Email Body', 'elementor-pro'),
                'type' => Controls_Manager::WYSIWYG,
                'default' => 'Thank you for your submission',
                'description' => __('Write and format the email body. Insert submitted values using ${field_id}, for example ${email} or ${first_name}. Use the field ID from the form, not its label. The format is ${hi}, not {$hi}. The OR header and footer are added automatically. Preview email shows the full layout; placeholders are replaced when the form is submitted.', 'elementor-pro'),
                'condition' => [
                    'send_email' => 'yes',
                ],
            ]
        );
        $widget->add_control(
            'or_email_preview',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => '<button type="button" class="elementor-button elementor-button-default or-email-preview-button">'
                    . esc_html__('Preview email', 'elementor-pro') . '</button>',
                'condition' => [
                    'send_email' => 'yes',
                ],
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
                'condition' => [
                    'limit_submissions' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'or_perform_check',
            [
                'label' => __('Perform check', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Check that a form field value exists in the specified table column.', 'elementor-pro'),
            ]
        );

        $widget->add_control(
            'or_check_table_name',
            [
                'label' => __('Table Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'wp_or_registration',
                'description' => __('Enter the name of the table to check.', 'elementor-pro'),
                'condition' => [
                    'or_perform_check' => 'yes',
                ],
            ]
        );
        
        $widget->add_control(
            'or_check_column',
            [
                'label' => __('Column Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'id',
                'description' => __('Enter the name of the column to check.', 'elementor-pro'),
                'condition' => [
                    'or_perform_check' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'or_check_field',
            [
                'label' => __('Field Name', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => 'id',
                'description' => __('Enter the name of the form field to match.', 'elementor-pro'),
                'condition' => [
                    'or_perform_check' => 'yes',
                ],
            ]
        );

        $widget->add_control(
            'or_check_message',
            [
                'label' => __('Message', 'elementor-pro'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('The supplied value is not recognized.', 'elementor-pro'),
                'description' => __('Enter the message to display if no matching record is found.', 'elementor-pro'),
                'condition' => [
                    'or_perform_check' => 'yes',
                ],
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

        return strtr($template, $vars);

    }

    /**
     * @param ElementorProModulesFormsClassesForm_Record $record
     * @param ElementorProModulesFormsClassesAjax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler)
    {

        global $wpdb, $or_conference_id;

        $form_fields = $record->get('fields');

        if (!$this->validate_existing_record($record, $form_fields, $ajax_handler)) {
            return;
        }

        $table_name = $record->get_form_settings('table_name');

        if (!$this->validate_submission_limit($record, $table_name, $ajax_handler)) {
            return;
        }

        $this->create_submission_table($record, $table_name);

        $columns = $wpdb->get_col("DESC $table_name", 0);

        unset($form_fields['id'], $form_fields['hash_id']);
        $form_fields['conference_id'] = array(
            'value' => $or_conference_id,
        );

        if (!$this->validate_form_fields($form_fields, $ajax_handler)) {
            return;
        }

        $submission_data = [];
        foreach ($form_fields as $field_id => $field_data) {
            // This field is only used to validate the email address.
            if ($field_id === 'repeat_email') {
                continue;
            }

            $sanitized_value = sanitize_text_field($field_data['value']);
            $submission_data[$field_id] = $sanitized_value;

            if (!in_array($field_id, $columns)) {
                $wpdb->query("ALTER TABLE $table_name ADD COLUMN $field_id VARCHAR(255) NOT NULL DEFAULT ''");
            }

        }

        $result = $wpdb->insert(
            $table_name,
            $submission_data,
            array_fill(0, count($submission_data), '%s')
        );
        if ($result === false) {
            $ajax_handler->add_error_message(__('Could not save your submission. Please try again.'));
            return;
        }

        if ($record->get_form_settings('send_email') == 'yes') {
            $this->send_confirmation_email($record, $form_fields, $ajax_handler);
        }
    }

    private function validate_existing_record($record, $form_fields, $ajax_handler)
    {
        global $wpdb;

        $perform_check = $record->get_form_settings('or_perform_check');

        if ($perform_check == "yes") {
            $check_table_name = $record->get_form_settings('or_check_table_name');
            $check_column = $record->get_form_settings('or_check_column');
            $check_field = $record->get_form_settings('or_check_field');
            $check_message = $record->get_form_settings('or_check_message');
            $check_field_value = $form_fields[$check_field]['value'];
            $query = $wpdb->prepare("SELECT * FROM {$check_table_name} WHERE {$check_column} = %s", $check_field_value);
            $result = $wpdb->get_results($query);
            if (empty($result)) {
                $ajax_handler->add_error_message($check_message);
                return false;
            }
        }

        return true;
    }

    private function validate_submission_limit($record, $table_name, $ajax_handler)
    {
        global $wpdb;

        $limit_submissions = $record->get_form_settings('limit_submissions');
        $max_submissions = $record->get_form_settings('max_submissions');

        if ($limit_submissions == "yes") {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

            if ($count >= $max_submissions) {
                $ajax_handler->add_error_message("Registration is closed (maximum number of submissions has been reached)");
                return false;
            }
        }

        return true;
    }

    private function create_submission_table($record, $table_name)
    {
        global $wpdb;

        if ($record->get_form_settings('create_table') === 'yes') {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE $table_name (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    conference_id VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
                ) $charset_collate;";
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
        }
    }

    private function validate_form_fields($form_fields, $ajax_handler)
    {
        if (isset($form_fields['email'], $form_fields['repeat_email'])) {
            if ($form_fields['email']['value'] != $form_fields['repeat_email']['value']) {
                $ajax_handler->add_error_message(__('Emails do not match'));
                return false;
            }
        }
        if (isset($form_fields['privacy'])) {
            if ($form_fields['privacy']['value'] == 'false' || $form_fields['privacy']['value'] == '') {
                $ajax_handler->add_error_message(__('You must agree to the privacy policy'));
                return false;
            }
        }
        if (isset($form_fields['research_area'])) {
            if ($form_fields['research_area']['value'] == 'Null' || $form_fields['research_area']['value'] == 'Select') {
                $ajax_handler->add_error_message(__('You must select a research area'));
                return false;
            }
        }

        return true;
    }

    private function send_confirmation_email($record, $form_fields, $ajax_handler)
    {
        global $or_mailer;

        $from_email = $record->get_form_settings('or_email_from');
        $email_subject = $record->get_form_settings('custom_email_subject');
        $email_body = $record->get_form_settings('email_body');

        $vars = [];

        foreach ($form_fields as $field_id => $field_data) {
            $vars['${' . $field_id . '}'] = $field_data['value'];
        }
        $email_body = $this->replace_template_vars($email_body, $vars);

        $result = $or_mailer->send_OR_mail($form_fields['email']['value'], $email_subject, $email_body, array(), $from_email);
        if ($result) {
            $ajax_handler->add_response_data('message', __('Your submission recorded successfully, please check the email you provided for confirmation', 'elementor-pro'));
        } else {
            $ajax_handler->add_response_data('message', __('Your submission recorded successfully', 'elementor-pro'));
        }
    }
}




?>
