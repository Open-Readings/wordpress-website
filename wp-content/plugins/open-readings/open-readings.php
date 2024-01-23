<?php
use OpenReadings\Registration\OpenReadingsRegistration;

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
  wp_register_style('highlight-style', plugins_url('assets/css/github.css', __FILE__));
  wp_register_style('registration-widget-style', plugins_url('assets/css/registration-widget-style.css', __FILE__));
  wp_register_style('latex-field-style', plugins_url('assets/css/latex-field-style.css', __FILE__));
  wp_register_script('highlight-js', plugins_url('assets/js/highlight.js', __FILE__));
  wp_register_script('latex-min-js', plugins_url('assets/js/latex.min.js', __FILE__));
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

  add_menu_roles();
  //add mailer as a global variable
  global $or_mailer;
  $or_mailer = $mailer;

}

function register_or_registration_controller()
{

  require_once(__DIR__ . '/registration/registration.php');
  global $or_registration_controller;
  $or_registration_controller = new OpenReadingsRegistration();


}

add_action('init', 'register_or_registration_controller');
function load_custom_wp_admin_style($hook)
{
  // Load only on ?page=mypluginname
  if ($hook != 'toplevel_page_or_registration') {
    return;
  }
  wp_enqueue_style('or-registration-options.css', plugins_url('assets/css/registration-options.css', __FILE__));
}


add_action('admin_enqueue_scripts', 'load_custom_wp_admin_style');

function add_menu_roles()
{


  //add new capability
  $role = get_role('administrator');
  $role->add_cap('manage_or_registration');
  $role->add_cap('manage_or_mailer');
  //add new capability
  $role = get_role('editor');
  $role->add_cap('manage_or_registration');
  $role->add_cap('manage_or_mailer');
}


function register_admin()
{
  require_once(__DIR__ . '/registration/admin.php');
  $admin = new ORregistrationAdmin();



}

add_action('init', 'register_admin');
add_action('init', 'register_or_mailer');



define('OR_PLUGIN_DIR', __DIR__ . '/');


function add_new_form_actions($form_actions_registrar)
{

  require_once(__DIR__ . '/form-actions/or-form-action.php');
  require_once(__DIR__ . '/form-actions/custom-form.php');
  $form_actions_registrar->register(new \Custom_Elementor_Form_Action());
  $form_actions_registrar->register(new \ORMainRegistrationSubmit());


}
add_action('elementor_pro/forms/actions/register', 'add_new_form_actions');

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
  require_once(__DIR__ . '/form-fields/simple-check-field.php');






  $form_fields_registrar->register(new \Elementor_Country_Field());
  $form_fields_registrar->register(new \Elementor_Institution_Field());
  $form_fields_registrar->register(new \Elementor_Latex_Field());
  $form_fields_registrar->register(new \Elementor_Authors_Field());
  $form_fields_registrar->register(new \Elementor_Affiliation_Field());
  $form_fields_registrar->register(new \Elementor_Reference_Field());
  $form_fields_registrar->register(new \Elementor_Image_Field());
  $form_fields_registrar->register(new \TitleField());
  $form_fields_registrar->register(new \Elementor_Simple_Check_Field());



}
add_action('elementor_pro/forms/fields/register', 'add_new_form_field');



// function populate_registration_form()
// {
//   require_once(__DIR__ . '/registration/populate-form-fields.php');

// }



// add_action('init', 'populate_registration_form');

function enqueue_form_fill_script() {
  if (did_action('wp_loaded') > 1){
    return;
  }
  if (strpos($_SERVER['REQUEST_URI'], 'registration') !== false) {
    require_once(__DIR__ . '/registration/begin-session.php');
  }
}
  
add_action('wp_loaded', 'enqueue_form_fill_script');

function my_custom_function() {
  if (did_action('wp_footer') > 1)
    return;
  require_once(__DIR__ . '/registration/populate-form-fields.php');
}
add_action('wp_footer', 'my_custom_function');