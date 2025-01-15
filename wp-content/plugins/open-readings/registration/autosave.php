<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;
use OpenReadings\Registration\ORReadForm;


if (!$_SERVER['REQUEST_METHOD'] == 'POST')
    return;

global $wpdb;
$read_form = new ORReadForm();
$registration_data = $read_form->get_form();

if (isset($_POST['save-form'])){
    $ORregistration = new OpenReadingsRegistration();
    $person_data = new PersonData();
    $person_data->map_from_class($registration_data, $registration_data->hash_id);
    $presentation_data = new PresentationData();
    $presentation_data->map_from_class($registration_data, $registration_data->hash_id);

    $person_row= $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM wp_or_registration_save WHERE hash_id = %s",
            $registration_data->hash_id
        )
    );

    if (!$person_row){
        $ORregistration->register_person($person_data, $registration_data->hash_id, 'wp_or_registration_save');
    } else {
        $ORregistration->update_person_data($person_data, $registration_data->hash_id, 'wp_or_registration_save');
    }

    $presentation_row= $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM wp_or_registration_presentations_save WHERE person_hash_id = %s",
            $registration_data->hash_id
        )
    );

    if (!$presentation_row){
        $ORregistration->register_presentation($presentation_data, 'wp_or_registration_presentations_save');
    } else {
        $ORregistration->update_presentation_data($presentation_data, $registration_data->hash_id, 'wp_or_registration_presentations_save');
    }
} else {
    $rows_deleted = $wpdb->delete(
        'wp_or_registration_presentations_save', // Table name
        ['person_hash_id' => $registration_data->hash_id], // WHERE condition
        ['%s'] // Data type for 'hash_id'
    );

    $rows_deleted = $wpdb->delete(
        'wp_or_registration_save', // Table name
        ['hash_id' => $registration_data->hash_id], // WHERE condition
        ['%s'] // Data type for 'hash_id'
    );
}