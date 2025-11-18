<?php
use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;


if ($_SERVER['REQUEST_METHOD'] == 'POST')
    return;

$ORregistration = new OpenReadingsRegistration();
$update = false;

// If id valid, get registration data
if (isset($_GET['id'])){
    $id = $_GET['id'];
    $registration_data = $ORregistration->get($id);
    if (!is_wp_error($registration_data))
        $update = true;
} else {
    $registration_data = new WP_Error('no_id', 'No ID provided');
}
// Else try to get temporary reistration data
if (isset($_COOKIE['hash_id']) and is_wp_error($registration_data)){
    $registration_data =$ORregistration->get($_COOKIE['hash_id'], true);
}

?>
<script>
    let isSubmittingForm = false;
    const form = document.getElementsByClassName('elementor-form')[0];
    if (form) {
        form.addEventListener('submit', () => {
            isSubmittingForm = true;
        });
    }
    window.onbeforeunload = closingCode;
    function closingCode(event){
        const checkbox = document.getElementById("save-form");
        const formData = new FormData(form);

        fetch("<?=content_url()?>/plugins/open-readings/registration/autosave.php", {
                method: "POST",
                body: formData
        });

        if (checkbox && checkbox.checked){
            return null;
        } else if (!isSubmittingForm){
            const confirmationMessage = "Are you sure you want to leave without saving your progress?";
            //event.returnValue = confirmationMessage; // Standard for most browsers
            //return confirmationMessage;
            return null;
        }    
    }
</script>
<?php
if($update == false){?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let form = document.getElementsByName("or registration")[0];
        let centeredDiv = document.createElement('div');
        centeredDiv.className = 'save-checkbox';

        // Create the label
        let newLabel = document.createElement('label');
        newLabel.setAttribute('for', 'save-form');
        newLabel.textContent = "Autosave form";

        // Create the checkbox
        let newCheckbox = document.createElement('input');
        newCheckbox.setAttribute('type', 'checkbox');
        newCheckbox.setAttribute('id', 'save-form');
        newCheckbox.setAttribute('name', 'save-form');

        // Create a question mark with a tooltip
        let questionMark = document.createElement('span');
        questionMark.textContent = '❔'; // Question mark emoji or use '?' text
        questionMark.className = 'tooltip';

        // Tooltip text
        let tooltipText = document.createElement('span');
        tooltipText.className = 'tooltiptext';
        tooltipText.textContent = 'Automatically save form progress as you type. (Temporalily stores form in our database)';

        // Add the tooltip text inside the question mark span
        questionMark.appendChild(tooltipText);

        // Add label and checkbox to the div
        centeredDiv.appendChild(newCheckbox);
        centeredDiv.appendChild(newLabel);
        centeredDiv.appendChild(questionMark);

        // Insert the div as the first element inside the form
        form.prepend(centeredDiv);
    });
</script>
<?php }

// If no registration data, return
if (is_wp_error($registration_data)){
    return;
}


array_walk_recursive($registration_data->affiliations, function (&$value) {
    $value = stripslashes($value);
});
array_walk_recursive($registration_data->authors, function (&$value) {
    $value = stripslashes($value);
});
array_walk_recursive($registration_data->references, function (&$value) {
    $value = stripslashes($value);
});
?>
<script>
    
    // Set the values of the form fields
    const personTitle = <?= json_encode($registration_data->person_title) ?>;
    const firstName = <?= json_encode(stripslashes($registration_data->first_name)) ?>;
    const lastName = <?= json_encode(stripslashes($registration_data->last_name)) ?>;
    const email = <?= json_encode($registration_data->email) ?>;
    const country = <?= json_encode($registration_data->country) ?>;
    const institution = <?= json_encode(stripslashes($registration_data->institution)) ?>;
    const department = <?= json_encode(stripslashes($registration_data->department)) ?>;
    const researchArea = <?= json_encode($registration_data->research_area) ?>;
    const researchSubarea = <?= json_encode($registration_data->research_subarea) ?>;
    const presentationType = <?= json_encode($registration_data->presentation_type) ?>;
    const presentationTitle = <?= json_encode(stripslashes($registration_data->title)) ?>;
    const affiliations = <?= json_encode($registration_data->affiliations) ?>;
    const authors = <?= json_encode($registration_data->authors) ?>;
    const references = <?= json_encode($registration_data->references) ?>;
    const acknowledgements = <?= json_encode(stripslashes($registration_data->acknowledgement)) ?>;
    const keywords = <?= json_encode(stripslashes($registration_data->keywords)) ?>;
    const abstractContent = <?= json_encode(stripslashes($registration_data->abstract), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const needsVisa = <?= json_encode($registration_data->needs_visa) ?>;
    const privacy = <?= json_encode($registration_data->privacy) ?>;
    const agreesToEmail = <?= json_encode($registration_data->agrees_to_email) ?>;
    const images = <?= json_encode($registration_data->images) ?>;
    const pdfURL = <?= json_encode(content_url() . '/latex/temp/' . $registration_data->session_id . '/abstract.pdf#toolbar=0') ?>;
    const saveCheckbox = <?= json_encode(!$update) ?>;
    const abstractAgree = <?= json_encode($update) ?>;

    // Array of form field names and their corresponding values
    const personFields = [
        ['form-field-person_title', personTitle],
        ['form-field-firstname', firstName],
        ['form-field-lastname', lastName],
        ['form-field-email', email],
        ['form-field-repeat_email', email],
        ['form-field-country', country],
        ['form-field-institution', institution],
        ['form-field-department', department],
        ['form-field-research_area', researchArea],
        ['form-field-abstract_title', presentationTitle],
        ['form-field-acknowledgement', acknowledgements],
        ['form-field-keywords', keywords],
        ['textArea', abstractContent],
        ['form-field-visa', needsVisa],
        ['form-field-privacy', privacy],
        ['form-field-abstract_agree', abstractAgree],
        ['form-field-email_agree', agreesToEmail],
        ['save-form', saveCheckbox]
    ];

    console.log(researchArea);
    console.log(researchSubarea);

    // document.addEventListener('DOMContentLoaded', function () {
    //     console.log('personTitle', personTitle, '\nfirstName', firstName, '\nlastName', lastName, '\nemail', email, '\ncountry', country, '\ninstitution', institution, '\ndepartment', department, '\nresearchArea', researchArea, '\npresentationType', presentationType, '\npresentationTitle', presentationTitle, '\naffiliations', affiliations, '\nauthors', authors, '\nreferences', references, '\nacknowledgements', acknowledgements, '\nabstractContent', abstractContent, '\nneedsVisa', needsVisa, '\nprivacy', privacy, '\nagreesToEmail', agreesToEmail);
    // });
    

    document.addEventListener('DOMContentLoaded', function () {

        // Set value of presentation title div
        document.getElementById('presentation_title_div').innerHTML = presentationTitle;

        // Set values of form fields
        personFields.forEach(function(field) {
                element = document.getElementById(field[0]);
                if (element){
                    if (element.type === 'checkbox')
                        element.checked = field[1];
                    else
                        element.value = field[1];
                        if(field[0] === 'form-field-research_area'){
                            const event = new Event('change');
                            element.dispatchEvent(event);
                        }
                }
        });

        for (let i = 0; i < researchSubarea.length; i++) {
            const subarea = researchSubarea[i];
            const checkbox = document.querySelector(`input[name="form_fields[research_subarea][]"][value="${subarea}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        }

        if (researchSubarea.includes("Other")) {
            const otherCheckbox = document.querySelector(`input[name="form_fields[research_subarea][]"][value="Other"]`);
            const otherField = document.querySelector('input[name="form_fields[research_subarea][]"][type="text"]');
            otherField.value = researchSubarea.at(-1);
            otherCheckbox.checked = true;
            otherField.style.display = "block";
            otherField.disabled = false;
        }

        // Set presentation type (oral/poster)
        var presentation_radio = document.querySelector('input[name="form_fields[presentation_type]"][value="'+presentationType+'"]');
        presentation_radio.checked = true;

        // Set affiliations
        const addAffiliation = document.querySelector('.aff-add');
        for (let i = 1; i < affiliations.length; i++) {
            addAffiliation.click();
        }
        const affiliationFields = document.getElementsByName('affiliation[]');
        for (let i = 0; i < affiliations.length; i++) {
            affiliationFields[i].value = affiliations[i];
        }

        // Set references
        const addReference = document.querySelector('.ref-add');
        for (let i = 0; i < references.length; i++) {
            addReference.click();
        }
        const refFields = document.getElementsByName('references[]');
        for (let i = 0; i < references.length; i++) {
            refFields[i].value = references[i];
        }

        // Set authors
        const addAuthor = document.querySelector('.auth-add');
        for (let i = 1; i < authors.length; i++) {
            addAuthor.click();
        }
        const authName = document.getElementsByName('name[]');
        const authRef = document.getElementsByName('aff_ref[]');
        const authEmail = document.getElementById('email-author');
        const authRadio = document.getElementsByName('contact_author');
        for (let i = 0; i < authors.length; i++) {
            authName[i].value = authors[i][0];
            authRef[i].value = authors[i][1];
            if (authors[i].length == 3) {
                authEmail.value = authors[i][2];
                authRadio[i].click();
            }
        }

        // Set pdf
        const pdfFrame = document.getElementById('abstract');
        pdfFrame.src = pdfURL;

        // Set the copy/paste code for images
        let imageMessage = document.getElementById('image-names');

        images.forEach((file) => {
            imageMessage.innerHTML += '<p style="font-weight:bold; font-family:sans-serif; display:inline">' + file + '</p> <p class="image-code"> Copy image LaTeX code to clipboard' + "</p><br>";
        });
        
        let imageCodeElements = document.querySelectorAll(".image-code");
        imageMessage.style.display = 'block';
        imageCodeElements.forEach((imageCode) => {
            imageCode.addEventListener('click', async function() {
                const precedingName = this.previousElementSibling;
                const name = precedingName.textContent;
                const textToCopy = `\\begin{figure}[H] \n\\center \n\\includegraphics[height=6cm]{${name}} \n\\caption{Add a caption} \n\\end{figure}`;
        
                try {
                    await navigator.clipboard.writeText(textToCopy);
                } catch (err) {
                }
            });
        });
        
    });
</script>