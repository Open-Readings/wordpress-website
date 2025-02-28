<?php



function register_styles(){

    $styles = [
        'faq-widget-style' => 'assets/css/faq-widget-style.css',
        'highlight-style' => 'assets/css/github.css',
        'registration-widget-style' => 'assets/css/registration-widget-style.css',
        'programme-day-style' => 'assets/css/programme-day-style.css',
        'latex-field-style' => 'assets/css/latex-field-style.css',
        'registration-evaluation-style' => 'assets/css/evaluation-style.css',
        'news-widget-style' => 'assets/css/news-section-style.css'
    ];

    foreach ($styles as $handle => $path) {
        $css_path = plugin_dir_path(OR_PLUGIN_FILE) . $path;
        wp_register_style($handle, plugins_url($path, OR_PLUGIN_FILE), array(), filemtime($css_path));
    }
}

add_action('wp_enqueue_scripts', 'register_styles');