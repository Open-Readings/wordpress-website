<?php
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\Registration_Session\ORRegistrationSession;

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

define('OR_PLUGIN_DIR', __DIR__ . '/');
define('OR_PLUGIN_FILE', __FILE__);
function register_faq_widget($widgets_manager)
{
  require_once (__DIR__ . '/widgets/faq-widget.php');
  $widgets_manager->register(new \Elementor_Faq_Widget());
  require_once (__DIR__ . '/widgets/assigned-session-widget.php');
  $widgets_manager->register(new \Elementor_Assigned_Session_Widget());
  require_once (__DIR__ . '/widgets/news-widget.php');
  $widgets_manager->register(new \Elementor_News_Widget());
}

require_once __DIR__ . '/include/register-styles.php';
require_once __DIR__ . '/include/register-scripts.php';

function register_programme_day_widget($widgets_manager)
{
  // require_once (__DIR__ . '/widgets/programme-day.php');
  // $widgets_manager->register(new \ElementorProgrammeDay());
  require_once (__DIR__ . '/widgets/programme-widget-25.php');
  $widgets_manager->register(new \Elementor_Programme_25());
}

add_action('elementor/widgets/register', 'register_programme_day_widget');
add_action('elementor/widgets/register', 'register_faq_widget');

function register_or_dependencies()
{
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
  require_once (__DIR__ . '/controls/faq-controls.php');

  $controls_manager->register(new \Elementor_FAQ_Control());

}

add_action('elementor/controls/register', 'register_faq_controls');


function register_or_mailer()
{
  require_once (__DIR__ . '/mailer/mailer.php');
  $mailer = new ORmailer();

  add_menu_roles();
  //add mailer as a global variable
  global $or_mailer;
  $or_mailer = $mailer;

}

function register_or_registration_controller()
{

  require_once (__DIR__ . '/registration/registration.php');
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
  $role->add_cap('manage_evaluations');
  $role->add_cap('manage_programme');
  //add new capability
  $role = get_role('editor');
  $role->add_cap('manage_or_registration');
  $role->add_cap('manage_or_mailer');
  $role->add_cap('manage_evaluations');
  // create new role
  $role = add_role(
    'or_evaluator',
    'Open Readings Abstract Evaluator',
    array(
      'read' => true, // True allows that capability
      'manage_or_registration' => false,
      'manage_or_mailer' => false,
      'manage_evaluations' => true,
    )
  );
  $role = add_role(
    'or_main_evaluator',
    'Programme Committee',
    array(
      'read' => true, // True allows that capability
      'manage_or_registration' => false,
      'manage_or_mailer' => false,
      'manage_evaluations' => false,
    )
  );
}

function committee_login_redirect($redirect_to, $request, $user) {
  // Check if the user is logged in and is an object
  if (isset($user->roles) && is_array($user->roles)) {
      // Redirect subscribers to a specific page
      if (in_array('or_main_evaluator', $user->roles)) {
          return home_url('/wp-admin/admin.php?page=or_evaluation_two'); // Replace with your desired URL
      }
  }
  // Default redirect for other roles or if no role is matched
  return $redirect_to;
}
add_filter('login_redirect', 'committee_login_redirect', 10, 3);



function register_admin()
{
  require_once (__DIR__ . '/registration/admin.php');
  require_once (__DIR__ . '/second-evaluation/or_evaluation_admin.php');
  require_once (__DIR__ . '/evaluation/admin.php');
  require_once (__DIR__ . '/programme/admin.php');
  require_once (__DIR__ . '/app/admin.php');
  $admin = new OREvaluationAdmin();
  $admin = new ORSecondEvaluationAdmin();
  $admin = new ORregistrationAdmin();
  $admin = new ORApp();
  $programme_admin = new ORProgrammeAdmin();
  $programme_admin->init();

}

add_action('init', 'register_admin');
add_action('init', 'register_or_mailer');

function add_new_form_actions($form_actions_registrar)
{

  require_once (__DIR__ . '/form-actions/or-form-action.php');
  require_once (__DIR__ . '/form-actions/or-presentation-redirect.php');
  require_once (__DIR__ . '/form-actions/custom-form.php');
  require_once (__DIR__ . '/form-actions/or-hash-id-check.php');


  $form_actions_registrar->register(new \Custom_Elementor_Form_Action());
  $form_actions_registrar->register(new \ORPresentationUpload());
  $form_actions_registrar->register(new \ORMainRegistrationSubmit());
  $form_actions_registrar->register(new \Elementor_Form_OR_Hash_Check());

}
add_action('elementor_pro/forms/actions/register', 'add_new_form_actions');

function add_new_form_field($form_fields_registrar)
{

  require_once (__DIR__ . '/form-fields/country-field.php');
  require_once (__DIR__ . '/form-fields/institution-field.php');
  require_once (__DIR__ . '/form-fields/latex-field.php');
  require_once (__DIR__ . '/form-fields/authors-field.php');
  require_once (__DIR__ . '/form-fields/affiliation-field.php');
  require_once (__DIR__ . '/form-fields/reference-field.php');
  require_once (__DIR__ . '/form-fields/image-field.php');
  require_once (__DIR__ . '/form-fields/title-field.php');
  require_once (__DIR__ . '/form-fields/simple-check-field.php');

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

// function register_or_mailer_admin()
// {
//   require_once (__DIR__ . '/mailer/admin.php');
//   $admin = new ORmailerAdmin();
// }

// add_action('init', 'register_or_mailer_admin');

function populate_registration()
{
  if (did_action('wp_footer') > 1)
    return;
  if (is_page('registration'))
    require_once (__DIR__ . '/registration/populate-form-fields.php');
}
add_action('wp_footer', 'populate_registration');

global $PRESENTATION_TYPE;
$PRESENTATION_TYPE = [
  'Oral' => 1,
  'Poster' => 2,
  'Rejected' => 3
];

global $RESEARCH_AREAS;
$RESEARCH_AREAS = [
  1 => 'Astrophysics and Astronomy',
  2 => 'Chemistry and Chemical Physics',
  3 => 'Material Science and Nanotechnology',
  4 => 'Laser Physics and Optical Technologies',
  5 => 'Theoretical Physics',
  6 => 'Spectroscopy and Imaging',
  7 => 'Biochemistry, Biophysics, and Biotechnology',
  8 => 'Biology, Genetics and Biomedical Sciences'
];
global $STATUS_CODES;
$STATUS_CODES = [
  'Not Checked' => 0,
  'Accepted' => 1,
  'Update' => 2,
  'Rejected' => 3,
  'Duplicate' => 99,

];
//
//
require_once __DIR__ . '/programme/download-session.php';

add_action('admin_init', 'download_session_zip');

function searchfilter($query)
{

  if ($query->is_search && !is_admin()) {
    $query->set('post_type', array('presentation'));
  }

  return $query;
}

add_filter('rest_presentation_query', function ($args, $request) {
  $session = $request->get_param('session');

  if ($session) {
    $args['meta_query'] = array (
      array (
        'key' => 'presentation_session', // This is assuming the ACF field name is 'status'.
        'value' => $session,
      ),
    );
    $args['per_page'] = 200; // Set your desired maximum limit
    // remove page limit
  }



  return $args;
}, 10, 2);


add_filter("rest_presentation_collection_params", function ($params) {
  $params['per_page']['maximum'] = 500;
  return $params;
});

require_once __DIR__ . '/programme/generate-abstract.php';

add_action('admin_init', 'download_abstract');

function or_registration_cookies() {
  // Check if the page slug or ID matches the specific page you want
  if (is_page('registration')) {
      // Only set the cookie if it isn't already set
      global $or_session;
      require_once __DIR__ . '/registration/registration-session.php';
      $or_session = new ORRegistrationSession();
  }
}
add_action('template_redirect', 'or_registration_cookies');

// Handle the AJAX request when logged-in user
add_action('wp_ajax_evaluation', 'evaluation');
add_action('wp_ajax_nopriv_evaluation', 'evaluation');

require_once __DIR__ . '/evaluation/admin/registration-functions.php';

function custom_admin_styles() {
  // Check if we're on the 'or_evaluation' page in the admin panel
  if ( isset( $_GET['page'] ) && $_GET['page'] == 'or_evaluation' ) {
    // Enqueue the custom admin CS
    $path = 'assets/css/evaluation-style.css';
    $css_path = plugin_dir_path(file: OR_PLUGIN_FILE) . $path;
    wp_register_style('first-evaluation-style', plugins_url($path, OR_PLUGIN_FILE), array(), filemtime($css_path));
    wp_enqueue_style('first-evaluation-style');
    $path = 'assets/js/evaluation-js.js';
    $js_path = plugin_dir_path(file: OR_PLUGIN_FILE) . $path;
    wp_register_script('first-evaluation-js', plugins_url($path, OR_PLUGIN_FILE), array(), filemtime($js_path));
    wp_enqueue_script('first-evaluation-js');
  }
}
add_action( 'admin_enqueue_scripts', 'custom_admin_styles' );


require_once __DIR__ . '/app/ordle.php';
add_action('rest_api_init', 'register_daily_word_endpoint');

function register_pupils_workshop_script()
{
  if (is_page(page: 'moksleiviu-sesijos-registracija'))
    require_once (__DIR__ . '/form-actions/pupils-workshop-script.php');
  else if (is_page(page: 'mokiniu-sesijos-klausytojo-registracija'))
    require_once (__DIR__ . '/form-actions/pupils-workshop-script.php');
}
add_action('wp_footer', 'register_pupils_workshop_script');