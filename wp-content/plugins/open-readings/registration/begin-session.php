<?php

use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
    return;


$id = isset($_GET['id']) ? ($_GET['id']) : 0;
?>
<script>console.log('<?=$id?>'+'abc');</script>
<?php
$ORregistration = new OpenReadingsRegistration();
$registration_data = $ORregistration->get($id);

global $wpdb;
$query = $wpdb->prepare("SELECT * FROM wp_or_registration_late WHERE late_hash_id = %s", $id);
$late_registration_row = $wpdb->get_row($query, ARRAY_A);


if (!isset($_SESSION['id'])) {
    ini_set('session.gc_maxlifetime', 14400);
    session_start();
    $_SESSION['id'] = 1;
}
   


if(is_wp_error($registration_data)){
    if (isset($_SESSION['update'])){
        session_unset();
        $_SESSION['id'] = 1;
        $_SESSION['exists'] = 0;
    }
    if (!isset($_SESSION['file'])) {
        $timestamp = time();
        $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
    }
    
    if (!is_dir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file'])) {
        mkdir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file'], 0777, true);
        mkdir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '/images', 0777, true);
        copy(WP_CONTENT_DIR . '/latex/abstract.pdf', WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '/abstract.pdf');
    }
    unset($_SESSION['late_hash']);
    if (isset($late_registration_row['used'])){
        if ($late_registration_row['used'] == 0){
            $_SESSION['late_hash'] = $id;
        }
    }
} else {
    session_unset();
    $_SESSION['id'] = 1;
    $_SESSION['file'] = $registration_data->session_id;
    $_SESSION['update'] = 1;
    $_SESSION['hash'] = $id;
    $_SESSION['presentation_id'] = $registration_data->presentation_id;
    $_SESSION['exists'] = 1;
}

?>
