<?php
use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

// Get the ID from the URL (you can use your preferred method for getting the ID)
$id = isset($_GET['id']) ? ($_GET['id']) : 0;

$ORregistration = new OpenReadingsRegistration();
$registration_data = $ORregistration->get($id);

$person_data_fields = [
    ['form-field-person_title', 'person_title'],
    ['form-field-firstname', 'first_name'],
    ['form-field-lastname', 'last_name'],
    ['form-field-email', 'email'],
    ['form-field-repeat_email', 'email'],
    ['form-field-country', 'country'],
    ['form-field-institution', 'institution'],
    ['form-field-department', 'department'],
    ['form-field-research_area', 'research_area'],
    ['textArea', 'abstract'],
    ['form-field-abstract_title', 'title'],
    ['form-field-visa', 'needs_visa'],
    ['form-field-privacy', 'privacy']
    
]



?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for the DOM to be fully loaded
    

    document.getElementById('presentation_title_div').innerHTML = '<?=$registration_data->title?>';

    <?php 
    
    foreach($person_data_fields as $field){
        ?>
    document.getElementById('<?=$field[0]?>').value = '<?=$registration_data->{$field[1]}?>';

    // Update the value of the input field
    
    <?php
    } ?>
    const affiliationList = document.getElementById("affList");
    const affiliationField = document.createElement("div");
    affiliationList.innerHTML = '';

    <?php

    foreach($registration_data->affiliations as $affiliation){
        ?>
        var childDivs = affiliationList.querySelectorAll("div");
        var divCount = childDivs.length;
        affiliationField.innerHTML = `<label class="aff-label">` + (divCount + 1) + `.` + `</label>` +
            `<input type="text" class="aff-width" maxlength="200" name="affiliation[]" value="<?=$affiliation?>" placeholder="(e.g. Vilnius University)">
        `;
        affiliationField.className = "aff-div";
        affiliationList.appendChild(affiliationField);
        <?php
    }
    ?>

    const peopleList = document.getElementById("authList");
    const personField = document.createElement("div");
    peopleList.innerHTML = '';

    <?php 
    $i = 0;
    foreach($registration_data->authors as $author){
        $i++;
        ?>
        personField.innerHTML = `
        <input type="text" pattern="^[^&%\\$\\\\#^_\\{\\}~]*$" class="author-width" name="name[]" placeholder="(e.g. John Smith)" value="<?=$author[0]?>" required>
        <input type="text" pattern="[0-9, ]*" class="narrow" name="aff_ref[]" placeholder="(e.g. 1,2)" value="<?=$author[1]?>" required>
        <label class="text-like-elementor"> Corresponding author </label> <input class="contact-author" style="margin: 5px;" type="radio" name="contact_author" value="${divCount + 1}" <?php echo ($registration_data->author_radio == $i) ? 'checked' : ''; ?>>
        <?php echo ($registration_data->author_radio == $i) ? ('<input id="email-author" style="display:inline;" type="email" name="email-author" placeholder="john.smith@example.edu" value="' . $author[2] . '" required>') : (''); ?>

        `;
        peopleList.appendChild(personField);
        <?php

    }
    ?>
    const referenceList = document.getElementById("refList");
    const referenceField = document.createElement("div");
    <?php
    if (isset($registration_data->references)) {
        foreach($registration_data->references as $reference){
        ?>
        var childDivs = referenceList.querySelectorAll("div");
        var divCount = childDivs.length;
        referenceField.innerHTML = `<label class="ref-label">` + (divCount + 1) + `.` + `</label>` +
            `<input type="text" class="ref-width" maxlength="300" name="references[]" value="<?=$reference?>" placeholder="(e.g. M.A.Green, HighEfficiencySiliconSolarCells (Trans. Tech. Publications, Switzerland, 1987).)" required>
        `;
        referenceField.className = "ref-div";
        referenceList.appendChild(referenceField);
        <?php
    }}
    ?>
});

        console.log(111111111);
</script>