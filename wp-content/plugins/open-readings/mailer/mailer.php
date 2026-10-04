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

    public function render_email($content)
    {
        //fetch template from wp settings

        $template = get_option('or_email_template');
        if (!$template) {
            $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_email_template.html');
        }
        // Table-based templates need a cell around ordinary editor HTML or text.
        // Keep existing row-based messages (such as registration emails) intact.
        if (preg_match('/<table\b[^>]*>\s*\[content\]/i', $template)
            && !preg_match('/^\s*(?:<!--.*?-->\s*)*<(?:tr|tbody|thead|tfoot)\b/is', $content)) {
            $content = '<tr><td align="left" style="text-align:left;">' . $content . '</td></tr>';
        }

        return str_replace('[content]', $content, $template);
    }

    public function send_OR_mail($to, $subject, $content, $attachments = array(), $from_email = 'it@openreadings.eu')
    {
        if (!in_array($from_email, ['it@openreadings.eu', 'info@openreadings.eu'], true)) {
            $from_email = 'it@openreadings.eu';
        }

        $message = $this->render_email($content);
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Open Readings 2027 <' . $from_email . '>'
        );


        if (!empty($attachments)) {
            return wp_mail($to, $subject, $message, $headers, $attachments);
        }


        return wp_mail($to, $subject, $message, $headers);

    }


    public function send_registration_success_email($vars, $to)
    {
        $subject = get_option('or_registration_success_email_subject');
        if ($subject == '') {
            $subject = 'Open Readings 2027 registration success';
        }

        $template = get_option('or_registration_email_success_template');
        if ($template == '') {
            $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_registration_success_content.html');
        }
        $message = strtr($template, $vars);
        return $this->send_OR_mail($to, $subject, $message);


    }


    public function send_registration_update_success_email($vars, $to)
    {
        $subject = '';
        if ($subject == '') {
            $subject = 'Open Readings 2027 registration update';
        }

        $template = get_option('or_registration_email_update_template');
        if ($template == '') {
            $template = file_get_contents(OR_PLUGIN_DIR . 'mailer/OR_registration_update_content.html');
        }
        $message = strtr($template, $vars);
        return $this->send_OR_mail($to, $subject, $message);
    }


}


?>