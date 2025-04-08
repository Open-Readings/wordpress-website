<?php

function or_register_scripts()
{
    $scripts = [
        'faq-widget-js' => 'assets/js/faq-widget-js.js',
        'programme-day-js' => 'assets/js/program-day-js.js',
        'highlight-js' => 'assets/js/highlight.js',
        'latex-min-js' => 'assets/js/latex.min.js',
        'country-field-js' => 'assets/js/country-field-js.js',
        'institution-field-js' => 'assets/js/institution-field-js.js',
        'institutions-list-js' => 'assets/js/institutions-list-js.js',
        'latex-field-js' => 'assets/js/latex-field-js.js',
        'authors-field-js' => 'assets/js/authors-field-js.js',
        'affiliation-field-js' => 'assets/js/affiliation-field-js.js',
        'reference-field-js' => 'assets/js/reference-field-js.js',
        'image-field-js' => 'assets/js/image-field-js.js',
        'title-field-js' => 'assets/js/title-field-js.js',
        'jquery-js' => 'assets/js/jquery-3.6.4.min.js',
        'evaluation-js' => 'assets/js/evaluation-js.js',
        'news-section' => 'assets/js/news-section.js',
        'programme-25-js' => 'assets/js/programme-25.js',
    ];

    foreach ($scripts as $handle => $path) {
        $js_path = OR_PLUGIN_DIR . $path;
        wp_register_script($handle, plugins_url($path, OR_PLUGIN_FILE), array(), filemtime($js_path));
    }

}

add_action('wp_enqueue_scripts', 'or_register_scripts');
