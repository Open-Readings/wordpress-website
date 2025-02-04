<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

$workshop_arr = [
    'chem1' => 15,
    'fiz1' => 15,
    'bio2' => 15,
    'inz2' => 15,
];

$excursion_arr = [
    'gmc1' => 10,
    'ftmc1' => 10,
    'lightcon1' => 10,
    'gmc2' => 10,
    'ftmc2' => 10,
    'lightcon2' => 10,
];

$table_name = 'wp_pupils_registration_25';

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

    function hide_radios(r1, r2, r3){
        r1.parentNode.style.display = 'none';
        r2.parentNode.style.display = 'none';
        r3.parentNode.style.display = 'none';

        r1.checked = false;
        r2.checked = false;
        r3.checked = false;
    }

    function show_radios(r1, r2, r3){
        r1.parentNode.style.display = 'block';
        r2.parentNode.style.display = 'block';
        r3.parentNode.style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('hi mom');

        const radio1 = document.querySelector('input[type="radio"][value="gmc1"]');
        const radio2 = document.querySelector('input[type="radio"][value="ftmc1"]');
        const radio3 = document.querySelector('input[type="radio"][value="lightcon1"]');
        const radio4 = document.querySelector('input[type="radio"][value="gmc2"]');
        const radio5 = document.querySelector('input[type="radio"][value="ftmc2"]');
        const radio6 = document.querySelector('input[type="radio"][value="lightcon2"]');

        hide_radios(radio1, radio2, radio3);

        document.querySelector('input[type="radio"][value="fiz1"]').addEventListener('change', function(){
            show_radios(radio4, radio5, radio6);
            hide_radios(radio1, radio2, radio3);
        });
        document.querySelector('input[type="radio"][value="chem1"]').addEventListener('change', function(){
            show_radios(radio4, radio5, radio6);
            hide_radios(radio1, radio2, radio3);
        });
        document.querySelector('input[type="radio"][value="bio2"]').addEventListener('change', function(){
            show_radios(radio1, radio2, radio3);
            hide_radios(radio4, radio5, radio6);
        });
        document.querySelector('input[type="radio"][value="inz2"]').addEventListener('change', function(){
            show_radios(radio1, radio2, radio3);
            hide_radios(radio4, radio5, radio6);
        });
    });

    // on form chage i need to run js

</script>
<?php

global $wpdb;

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


