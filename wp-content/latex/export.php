<?php
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\ORCheckForm;
use OpenReadings\Registration\ORReadForm;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;
use OpenReadings\Registration\ORLatexExport;

error_reporting(0);
$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

function main(){
    global $wpdb;
    $latex_generator = new ORLatexExport();
    $field_checker = new ORCheckForm();
    $response = $field_checker->export_check($latex_generator->registration_data);
    if ($response !== true){
        echo 'Export failed::' . $response . '::end';
    } else {
        $latex_generator->generate_tex();
        $latex_generator->generate_abstract();

        echo 'Export completed';
    }
    if (file_exists(__DIR__ . '/temp/' . $latex_generator->registration_data->session_id . '/abstract.pdf'))
        echo 'File exists::true';
    else
        echo 'File exists::false';
        
}







// $field_validity = check_abstract_fields();
// if ($field_validity == 0)

// else if (file_exists(__DIR__ . '/' . $folder . '/abstract.pdf')) {
//     echo 'Export failed::' . $field_validity . '::end';
// } else {
//     echo 'Export failed::' . $field_validity . '::end';
// }

main();
