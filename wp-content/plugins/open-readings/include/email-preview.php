<?php

add_action('elementor/editor/after_enqueue_scripts', 'or_enqueue_email_preview');
add_action('wp_ajax_or_preview_email', 'or_preview_email');

function or_enqueue_email_preview()
{
    $script = 'assets/js/email-preview.js';
    $style = 'assets/css/email-preview.css';
    wp_enqueue_script('or-email-preview', plugins_url($script, OR_PLUGIN_FILE), ['elementor-editor'], filemtime(OR_PLUGIN_DIR . $script), true);
    wp_enqueue_style('or-email-preview', plugins_url($style, OR_PLUGIN_FILE), [], filemtime(OR_PLUGIN_DIR . $style));
    wp_localize_script('or-email-preview', 'orEmailPreview', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('or_preview_email'),
        'title' => __('Email preview', 'elementor-pro'),
        'close' => __('Close', 'elementor-pro'),
        'loading' => __('Loading preview…', 'elementor-pro'),
        'error' => __('Could not load the email preview. Please try again.', 'elementor-pro'),
        'note' => __('Placeholders show as written; actual values are filled in when the form is submitted. Email apps may display formatting differently.', 'elementor-pro'),
    ]);
}

function or_preview_email()
{
    check_ajax_referer('or_preview_email', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(null, 403);
        return;
    }
    if (!isset($_POST['content']) || !is_string($_POST['content'])) {
        wp_send_json_error(null, 400);
        return;
    }

    global $or_mailer;
    // Return HTML as JSON; the editor displays it only in a sandboxed iframe.
    wp_send_json_success(['html' => $or_mailer->render_email(wp_unslash($_POST['content']))]);
}
