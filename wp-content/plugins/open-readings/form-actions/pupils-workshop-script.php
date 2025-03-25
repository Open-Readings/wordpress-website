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
$table_name_listener = 'wp_pupils_listener_25';

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
                label.innerHTML += appendText;
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

    function workshopDescriptions(value, description, limit){
        // Append the hoverable symbol and limit to the label
        appendToLabelByValue(value, `<span class='hoverable-symbol-${value}'>❔</span> (liko vietų: ${limit})`);

        // Find the radio input and its parent span
        const radioWorkshop = document.querySelector(`input[type="radio"][value="${value}"]`);
        if (!radioWorkshop) return; // Exit if the radio input doesn't exist

        const workSpan = radioWorkshop.closest('span');

        // Create the textbox element
        const textbox = document.createElement('div');
        textbox.style.display = 'none'; // Initially hidden
        textbox.style.marginTop = '5px'; // Add some spacing
        textbox.style.position = 'fixed'; // Position it absolutely
        textbox.style.backgroundColor = '#fff'; // Style as needed
        textbox.style.border = '1px solid #ccc';
        textbox.style.zIndex = '1000'; // Ensure it appears in front of other elements
        textbox.style.padding = '5px';
        textbox.innerHTML = description; // Set the description text

        // Insert the textbox under the parent span
        workSpan.insertAdjacentElement('afterend', textbox);

        // Add hover event listeners to the hoverable symbol
        const hoverableElement = document.querySelector(`.hoverable-symbol-${value}`);
        if (hoverableElement) {
            hoverableElement.addEventListener('mouseover', () => {
                textbox.style.display = 'block'; // Show the textbox on hover

                 // Position the textbox relative to the mouse cursor
                const mouseX = event.clientX;
                const mouseY = event.clientY;
                const offset = 10; // Distance from the cursor

                // Check if there's enough space below the cursor
                const spaceBelow = window.innerHeight - mouseY;
                if (spaceBelow > textbox.offsetHeight + offset) {
                    // Position below the cursor
                    textbox.style.top = `${mouseY + offset}px`;
                    textbox.style.left = `${mouseX}px`;
                } else {
                    // Position above the cursor
                    textbox.style.top = `${mouseY - textbox.offsetHeight - offset}px`;
                    textbox.style.left = `${mouseX}px`;
                }
            });

            hoverableElement.addEventListener('mouseout', () => {
                textbox.style.display = 'none'; // Hide the textbox on mouseout
            });
        }
    }


    const workshopData = {
        'chem1': 'Veiklos metu mokiniai tirs vandens mėginius, siekdami spektrinės analizės metodu nustatyti geležies jonų koncentraciją. Mokiniai modeliuos scenarijus, kokiais būdais vanduo buvo užterštas ir kokie galimi taršos šaltiniai.',
        'fiz1': 'Mokiniai turės galimybę ištirti žmogaus kūno temperatūrą medicininiu IR termometru, užrašyti ir ištirti žmogaus ir jų grupės kūno paviršiaus temperatūros pasiskirstymą dviejų tipų termovizoriais bei palyginti šiuos matavimus tarpusavyje, atlikti temperatūrinių duomenų analizę; suvokti tokių tyrimų svarbą karščiuojančio asmens aptikimui kolektyve.',
        'bio2': 'Veiklos metu aiškinsimės, kaip šviesa sąveikauja su medžiaga, kaip tai vertiname, ką galime stebėti ir suprasti? Išmoksime vertinti tirpalų koncentracijas pasitelkdami šviesą.',
        'inz2': 'Mokiniai susipažins su kolorimetrija, spalvų teorija, elektronikos komponentais bei kaip veikia šviesos šaltinis ir indikatorius atliekant matavimus kolorimetru. Konstruos kolorimetrą, išmoks paruošti maistinių dažų tirpalų skirtingas koncentracijas bei jas matuos sukonstruotu kolorimetru.'
    }
    

    document.addEventListener('DOMContentLoaded', function() {

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

    let radioWorkshop;
    let workSpan;
    let textbox;
    let hoverableElement;

</script>
<?php

global $wpdb;

   foreach($excursion_arr as $value => $limit){
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name WHERE excursion = %s",
            $value
        ));

        $count = $result[0]->count;
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name_listener WHERE excursion = %s",
            $value
        ));
        if ($result) {
            $count += $result[0]->count;
        }
        
        ?>
        <script>
            fieldValue = '<?=$value; ?>';
            fieldLimit = '<?=$limit - $count; ?>';
            appendToLabelByValue(fieldValue, "(liko vietų: " + fieldLimit + ")");
        </script>
        <?php
    }

    foreach($workshop_arr as $value => $limit){
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name WHERE workshop = %s",
            $value
        ));

        $count = $result[0]->count;
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT COUNT(*) as count FROM $table_name_listener WHERE workshop = %s",
            $value
        ));
        if ($result) {
            $count += $result[0]->count;
        }
        
        ?>
        <script>
            fieldValue = '<?=$value; ?>';
            fieldLimit = '<?=$limit - $count; ?>';
            workshopDescriptions(fieldValue, workshopData[fieldValue], fieldLimit);
            
        </script>
        <?php
    }


