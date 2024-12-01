<?php
use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;


if ($_SERVER['REQUEST_METHOD'] == 'POST')
    return;
// Get the ID from the URL (you can use your preferred method for getting the ID)
$id = isset($_GET['id']) ? ($_GET['id']) : 0;

$ORregistration = new OpenReadingsRegistration();
$registration_data = $ORregistration->get($id);


?>
<script>
    
    const personTitle = <?= json_encode($registration_data->person_title) ?>;
    const firstName = <?= json_encode($registration_data->first_name) ?>;
    const lastName = <?= json_encode($registration_data->last_name) ?>;
    const email = <?= json_encode($registration_data->email) ?>;
    const country = <?= json_encode($registration_data->country) ?>;
    const institution = <?= json_encode($registration_data->institution) ?>;
    const department = <?= json_encode($registration_data->department) ?>;
    const researchArea = <?= json_encode($registration_data->research_area) ?>;
    const presentationType = <?= json_encode($registration_data->presentation_type) ?>;
    const presentationTitle = <?= json_encode($registration_data->title) ?>;
    const affiliations = <?= json_encode($registration_data->affiliations) ?>;
    const authors = <?= json_encode($registration_data->authors) ?>;
    const references = <?= json_encode($registration_data->references) ?>;
    const acknowledgements = <?= json_encode($registration_data->acknowledgement) ?>;
    const abstractContent = <?= json_encode(stripslashes($registration_data->abstract), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const needsVisa = <?= json_encode($registration_data->needs_visa) ?>;
    const privacy = <?= json_encode($registration_data->privacy) ?>;
    const agreesToEmail = <?= json_encode($registration_data->agrees_to_email) ?>;

    const personFields = [
        ['form-field-person_title', personTitle],
        ['form-field-firstname', firstName],
        ['form-field-lastname', lastName],
        ['form-field-email', email],
        ['form-field-repeat_email', email],
        ['form-field-country', country],
        ['form-field-institution', institution],
        ['form-field-department', department],
        ['form-field-research-area', researchArea],
        ['form-field-abstract-title', presentationTitle],
        ['form-field-acknowledgements', acknowledgements],
        ['textArea', abstractContent],
        ['form-field-needs-visa', needsVisa],
        ['form-field-privacy', privacy],
        ['form-field-agrees-to-email', agreesToEmail]
    ];

    document.addEventListener('DOMContentLoaded', function () {
        console.log('personTitle', personTitle, '\nfirstName', firstName, '\nlastName', lastName, '\nemail', email, '\ncountry', country, '\ninstitution', institution, '\ndepartment', department, '\nresearchArea', researchArea, '\npresentationType', presentationType, '\npresentationTitle', presentationTitle, '\naffiliations', affiliations, '\nauthors', authors, '\nreferences', references, '\nacknowledgements', acknowledgements, '\nabstractContent', abstractContent, '\nneedsVisa', needsVisa, '\nprivacy', privacy, '\nagreesToEmail', agreesToEmail);
    });
    



    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('presentation_title_div').innerHTML = presentationTitle;
        
        personFields.forEach(function(field) {
                element = document.getElementById(field[0]);
                if (element.type === 'checkbox')
                    element.checked = field[1];
                else
                    element.value = field[1];
        });

        var presentation_radio = document.querySelector('input[name="form_fields[presentation_type]"][value="'+presentationType+'"]');
        presentation_radio.checked = true;


        const affiliationList = document.getElementById("affList");
        affiliationList.innerHTML = '';

        <?php
        foreach ($registration_data->affiliations as $affiliation) {
            ?>
            var affiliationField = document.createElement("div");
            var childDivs = affiliationList.querySelectorAll("div");
            var divCount = childDivs.length;
            affiliationField.innerHTML = `<label class="aff-label">` + (divCount + 1) + `.` + `</label>` +
                `<input type="text" class="aff-width form-padding" maxlength="200" name="affiliation[]" value="<?= $affiliation ?>" placeholder="(e.g. Vilnius University)">
                                                                                            `;
            affiliationField.className = "aff-div";
            affiliationList.appendChild(affiliationField);
            <?php
        } ?>


        const peopleList = document.getElementById("authList");
        peopleList.innerHTML = '';

        <?php
        $i = 0;
        foreach ($registration_data->authors as $author) {
            $i++;
            ?>
            var personField = document.createElement("div");
            personField.innerHTML = `
                                                                                            <input type="text" pattern="^[^&%\\$\\\\#^_\\{\\}~]*$" class="author-width form-padding" name="name[]" placeholder="(e.g. John Smith)" value="<?= $author[0] ?>" required>
                                                                                            <input type="text" pattern="[0-9, ]*" class="narrow form-padding" name="aff_ref[]" placeholder="(e.g. 1,2)" value="<?= $author[1] ?>" required>
                                                                                            <label class="text-like-elementor"> Corresponding author </label> <input class="contact-author" style="margin: 5px;" type="radio" name="contact_author" value="<?= $i ?>" <?php echo (isset($author[2])) ? 'checked' : ''; ?>>
                                                                                            <?php echo isset($author[2]) ? ('<input id="email-author" class="form-padding" style="display:inline;" type="email" name="email-author" placeholder="john.smith@example.edu" value="' . $author[2] . '" required>') : (''); ?>

                                                                                            `;
            peopleList.appendChild(personField);
            <?php
        }
        ?>
        function getRadios() {
            var contactRadio = document.querySelectorAll('.contact-author');
            contactRadio.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    console.log(1);
                    if (document.getElementById('email-author') == null) {
                        var emailField = '<input id="email-author" class="form-padding" style="display:none;" type="email" name="email-author" placeholder="john.smith@example.edu" required>'
                        document.getElementById('authList').insertAdjacentHTML('afterend', emailField);
                    }
                    var fieldCopy = document.getElementById('email-author').cloneNode();

                    fieldCopy.style.display = "inline";
                    document.getElementById('email-author').remove();
                    radio.insertAdjacentElement('afterend', fieldCopy);
                });
            });
        }
        getRadios();

        const referenceList = document.getElementById("refList");
        <?php
        if (isset($registration_data->references)) {
            foreach ($registration_data->references as $reference) {
                ?>
                var referenceField = document.createElement("div");
                var childDivs = referenceList.querySelectorAll("div");
                var divCount = childDivs.length;
                referenceField.innerHTML = `<label class="ref-label">` + (divCount + 1) + `.` + `</label>` +
                    `<input type="text" class="ref-width form-padding" maxlength="1000" name="references[]" value="<?= $reference ?>" placeholder="(e.g. M.A.Green, HighEfficiencySiliconSolarCells (Trans. Tech. Publications, Switzerland, 1987).)" required>
                                                                                                                                                                                `;
                referenceField.className = "ref-div";
                referenceList.appendChild(referenceField);
                <?php
            }
        }
        ?>


        


        var imageMessage = document.getElementById('image-names');
        <?php
        foreach ($registration_data->images as $image) {
            ?>
            imageMessage.innerHTML += '<p style="font-weight:bold; display:inline">' + '<?= $image ?>' + '</p> <br>';
            imageMessage.style.display = 'block';
            <?php
        }
        ?>

        var abstract = document.getElementById('abstract');

        abstract.setAttribute("src", '<?= WP_CONTENT_URL ?>' + '/latex/' + '<?= $registration_data->session_id ?>' + '/abstract.pdf' + '?timestamp=' + new Date().getTime() + '#toolbar=0&view=FitH');
        abstract.style.display = 'block';
    });
</script>