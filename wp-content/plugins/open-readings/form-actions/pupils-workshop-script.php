<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

$workshop_arr = [
    'workshop1' => 1,
    'workshop2' => 1,
    'workshop3' => 1,
];

$excursion_arr = [
    'excursion1' => 1,
    'excursion2' => 1,
    'excursion3' => 1,
];

$table_name = 'wp_custom_table_test';

$places_left = [];

?>
<script>
    function appendToLabelByValue(value, appendText) {
        // Find the checkbox by value
        const radio = document.querySelector(`input[type="radio"][value="${value}"]`);
        if (radio) {
            // Find the associated label using the "for" attribute
            const label = document.querySelector(`label[for="${radio.id}"]`);
            if (label) {
                label.textContent += ` (liko vietų: ${appendText})`;
            }
            if (fieldLimit <= 0) {
                radio.disabled = true;
            }
        }
    }
    let fieldValue;
    let fieldLimit;
</script>
<?php

global $wpdb;

   foreach($workshop_arr as $value => $limit){
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name WHERE workshop = %s",
            $value
        ));
        
        ?>
        <script>
            fieldValue = '<?=$value; ?>';
            fieldLimit = '<?=$limit - $result[0]->count; ?>';
            appendToLabelByValue(fieldValue, fieldLimit);
        </script>
        <?php
    }

    foreach($excursion_arr as $value => $limit){
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name WHERE excursion = %s",
            $value
        ));
        
        ?>
        <script>
            fieldValue = '<?=$value; ?>';
            fieldLimit = '<?=$limit - $result[0]->count; ?>';
            appendToLabelByValue(fieldValue, fieldLimit);
        </script>
        <?php
    }


