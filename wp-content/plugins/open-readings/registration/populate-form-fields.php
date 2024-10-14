<?php
use OpenReadings\Registration;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

return;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
    return;
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
    ['form-field-abstract_title', 'title'],
];

$fields_group_checkbox = [
    ['form-field-visa', 'needs_visa'],
    ['form-field-privacy', 'privacy'],
    ['form-field-email_agree', 'agrees_to_email']
];


$abstract_content = $registration_data->abstract;
$abstract_content = str_replace('\\\\', '\\', $abstract_content);

$title = $registration_data->title;
$title = str_replace('\\\\', '\\', $title);
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log(1111);

        document.getElementById('presentation_title_div').innerHTML = ' <?= str_replace('"', '', json_encode($title)) ?>';
        document.getElementById('textArea').value = <?= json_encode($abstract_content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
        <?php
        foreach ($person_data_fields as $field) {
            ?>
            document.getElementById('<?= $field[0] ?>').value = '<?= $registration_data->{$field[1]} ?>';
            <?php
        } ?>


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


        var presentation_radio = document.querySelector('input[name="form_fields[presentation_type]"][value="<?= $registration_data->presentation_type ?>"]');
        presentation_radio.checked = true;


        var imageMessage = document.getElementById('image-names');
        <?php
        foreach ($registration_data->images as $image) {
            ?>
            imageMessage.innerHTML += '<p style="font-weight:bold; display:inline">' + '<?= $image ?>' + '</p> <br>';
            imageMessage.style.display = 'block';
            <?php
        }


        foreach ($fields_group_checkbox as $field) {
            ?>
            var checkbox = document.getElementById('<?= $field[0] ?>');
            checkbox.checked = <?= ($registration_data->{$field[1]} == 1) ? 1 : 0 ?>;
            <?php
        }
        ?>

        var abstract = document.getElementById('abstract');

        abstract.setAttribute("src", '<?= WP_CONTENT_URL ?>' + '/latex/' + '<?= $registration_data->session_id ?>' + '/abstract.pdf' + '?timestamp=' + new Date().getTime() + '#toolbar=0&view=FitH');
        abstract.style.display = 'block';
    });
    console.log(9999);
</script>