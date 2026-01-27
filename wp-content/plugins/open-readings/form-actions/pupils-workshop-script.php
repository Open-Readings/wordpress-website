<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

$table = $wpdb->prefix . 'pupils_registration_26';
$column = 'workshop';

$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT $column AS value, COUNT(*) AS total
         FROM $table
         GROUP BY $column"
    ),
    ARRAY_A
);

$counts = [];

foreach ($results as $row) {
    $counts[$row['value']] = (int) $row['total'];
}

echo '<script>
    const workshops = ' . json_encode($counts) . ';
</script>';

$column = 'excursion';

$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT $column AS value, COUNT(*) AS total
         FROM $table
         GROUP BY $column"
    ),
    ARRAY_A
);

$counts = [];

foreach ($results as $row) {
    $counts[$row['value']] = (int) $row['total'];
}

echo '<script>
    const excursions = ' . json_encode($counts) . ';
</script>';