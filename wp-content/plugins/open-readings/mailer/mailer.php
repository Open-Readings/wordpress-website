<?php
class ORmailer
{
    var $mail;
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        $this->init();

    }
    public static function get_instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    public function init()
    {

        // stuff to init
    }

    public function send_OR_mail($to, $subject, $content)
    {
        //fetch template from wp settings

        $template = get_option('or_email_template');
        if (!$template) {
            $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_email_template.html');
        }
        $message = str_replace('[content]', $content, $template);
        $headers = array('
        Content-Type: text/html; charset=UTF-8
        From: Open Readings 2024 <info@openreadings.eu>
        '
        );
        $this->mail = wp_mail($to, $subject, $message, $headers);
        return $this->mail;
    }


    public function send_registration_success_email($vars, $to)
    {
        $subject = get_option('or_registration_success_email_subject');
        if ($subject == '') {
            $subject = 'Open Readings 2024 registration success';
        }

        $template = get_option('or_registration_success_email_content');
        if ($template == '') {
            $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_registration_success_content.html');
        }
        $message = strtr($template, $vars);
        $this->mail = wp_mail($to, $subject, $message);


    }




}


?>