<?php

/**
 * Open Readings
 *
 * @package           PluginPackage
 * @author            Open Readings
 * @copyright         2023 Open Readings
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Open Readings
 * Description:       Plugin for Open Readings website
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Open Readings
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://example.com/my-plugin/
 */

function register_faq_widget($widgets_manager)
{
  require_once(__DIR__ . '/widgets/faq-widget.php');
  $widgets_manager->register(new \Elementor_Faq_Widget());
}

add_action('elementor/widgets/register', 'register_faq_widget');

function register_or_dependencies()
{
  wp_register_style('faq-widget-style', plugins_url('assets/css/faq-widget-style.css', __FILE__));
  wp_register_script('faq-widget-js', plugins_url('assets/js/faq-widget-js.js', __FILE__));
  wp_register_style('registration-widget-style', plugins_url('assets/css/registration-widget-style.css', __FILE__));
  wp_register_style('latex-field-style', plugins_url('assets/css/latex-field-style.css', __FILE__));
  wp_register_script('country-field-js', plugins_url('assets/js/country-field-js.js', __FILE__));
  wp_register_script('institution-field-js', plugins_url('assets/js/institution-field-js.js', __FILE__));
  wp_register_script('institutions-list-js', plugins_url('assets/js/institutions-list-js.js', __FILE__));
  wp_register_script('latex-field-js', plugins_url('assets/js/latex-field-js.js', __FILE__));
  wp_register_script('authors-field-js', plugins_url('assets/js/authors-field-js.js', __FILE__));
  wp_register_script('affiliation-field-js', plugins_url('assets/js/affiliation-field-js.js', __FILE__));
  wp_register_script('reference-field-js', plugins_url('assets/js/reference-field-js.js', __FILE__));
  wp_register_script('image-field-js', plugins_url('assets/js/image-field-js.js', __FILE__));
  wp_register_script('title-field-js', plugins_url('assets/js/title-field-js.js', __FILE__));

  $data_to_pass = array(
    'path' => content_url(),
    // Use admin-ajax.php for AJAX requests
  );
  wp_localize_script('latex-field-js', 'dirAjax', $data_to_pass);
  wp_localize_script('image-field-js', 'dirAjax', $data_to_pass);

}

add_action('wp_enqueue_scripts', 'register_or_dependencies');

function register_faq_controls($controls_manager)
{
  require_once(__DIR__ . '/controls/faq-controls.php');
  $controls_manager->register(new \Elementor_FAQ_Control());
}

add_action('elementor/controls/register', 'register_faq_controls');


function register_or_mailer()
{
  require_once(__DIR__ . '/mailer/mailer.php');
  $mailer = new ORmailer();

  //add mailer as a global variable
  global $or_mailer;
  $or_mailer = $mailer;
}

add_action('init', 'register_or_mailer');


define('OR_PLUGIN_DIR', __DIR__ . '/');




function add_new_form_field($form_fields_registrar)
{

  require_once(__DIR__ . '/form-fields/country-field.php');
  require_once(__DIR__ . '/form-fields/institution-field.php');
  require_once(__DIR__ . '/form-fields/latex-field.php');
  require_once(__DIR__ . '/form-fields/authors-field.php');
  require_once(__DIR__ . '/form-fields/affiliation-field.php');
  require_once(__DIR__ . '/form-fields/reference-field.php');
  require_once(__DIR__ . '/form-fields/image-field.php');
  require_once(__DIR__ . '/form-fields/title-field.php');





  $form_fields_registrar->register(new \Elementor_Country_Field());
  $form_fields_registrar->register(new \Elementor_Institution_Field());
  $form_fields_registrar->register(new \Elementor_Latex_Field());
  $form_fields_registrar->register(new \Elementor_Authors_Field());
  $form_fields_registrar->register(new \Elementor_Affiliation_Field());
  $form_fields_registrar->register(new \Elementor_Reference_Field());
  $form_fields_registrar->register(new \Elementor_Image_Field());
  $form_fields_registrar->register(new \TitleField());



}
add_action('elementor_pro/forms/fields/register', 'add_new_form_field');