// var id = affiliation_ajax.id;
var affAddButtons = document.querySelectorAll('.aff-add');

affAddButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const affiliationList = document.getElementById("affList");
        const affiliationField = document.createElement("div");
        var childDivs = affiliationList.querySelectorAll("div");
        var divCount = childDivs.length;
        affiliationField.innerHTML = `<label class="aff-label">` + (divCount + 1) + `.` + `</label>` +
            `<input type="text" class="aff-width form-padding elementor-field elementor-field-textual" name="affiliation[]" placeholder="(e.g. Vilnius University)">
        `;
        affiliationField.className = "aff-div";
        if (divCount < 10)
            affiliationList.appendChild(affiliationField);
    });
});

var affRemButtons = document.querySelectorAll('.aff-rem');

affRemButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        var id = button.getAttribute('data-field-id');
        const formFields = document.getElementById("affList");
        var childDivs = formFields.querySelectorAll("div");
        var divCount = childDivs.length;

        if (formFields.lastChild && divCount > 1) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});

var researchAreaField = document.getElementById('form-field-research_area');
var researchSubareaField = document.getElementById('form-field-research_subarea');

// Create a hidden text input for "Other"
var customSubareaInput = document.createElement('input');
customSubareaInput.type = 'text';
customSubareaInput.id = 'form-field-research_subarea_custom';
customSubareaInput.name = 'form_fields[research_subarea]'; // same name as dropdown so it saves normally
customSubareaInput.placeholder = 'Enter your research area';
customSubareaInput.style.display = 'none';
customSubareaInput.className = researchSubareaField.className; // copy Elementor styling
researchSubareaField.parentNode.insertBefore(customSubareaInput, researchSubareaField.nextSibling);

researchAreaField.addEventListener('change', function () {
    var selectedArea = researchAreaField.value;

    // If "Other" selected → show text input instead of dropdown
    if (selectedArea === 'Other') {
        researchSubareaField.style.display = 'none';
        researchSubareaField.disabled = true;

        customSubareaInput.style.display = 'block';
        customSubareaInput.disabled = false;
    } else {
        researchSubareaField.style.display = 'block';
        researchSubareaField.disabled = false;

        customSubareaInput.style.display = 'none';
        customSubareaInput.disabled = true;
    }

    // Clear previous options
    researchSubareaField.innerHTML = '';

    // Define subareas for each research area
    var subareas = {
        '': [''],
        'Lasers and Optical Technologies': ['', 'Laser Physics', 'Laser Processing', 'Laser Induced Damage', 'Ultrafast Lasers', 'Nonlinear Crystals', 'Photonics and Fibers', 'Lenses and Mirrors', 'Optical Coatings', 'Metrology'],
        'Spectroscopy and Imaging': ['', 'UV/VIS', 'Infrared', 'Raman', 'THZ', 'XRD', 'Optical Microscopy', 'Atomic Force Microscopy', 'Electron Microscopy', 'Nuclear Magnetic Resonance', 'Photoacoustic Imaging'],
        'Astronomy and Theoretical Physics': ['', 'Astrophysics and Astronomy', 'Quantum Physics', 'Theoretical Physics', 'Particle Physics', 'Simulations'],
        'Biology and Medicine': ['', 'Biophysics', 'Biochemistry', 'Genetics', 'DNR/RNA', 'Cancer', 'Diseases', 'Viruses', 'Cells', 'Proteins', 'Microorganisms', 'Lipids', 'Pharmaceuticals', 'Neuroscience', 'CRISPR', 'Biomarkers'],
        'Chemistry': ['', 'Chemical Physics', 'Organic Synthesis', 'Inorganic Synthesis', 'Electrochemistry', 'Catalysis'],
        'Materials Science and Nanotechnology': ['', 'Semiconductor Growth Technologies', 'Carrier Transport', 'LED’s and OLED’s', 'Photovoltaics', 'Fuel Cells', 'Solar Cells', 'Detectors', 'Photoluminescence', 'Micro and Nano Structures', 'Graphene Based Materials', 'Thin Films', 'Perovskites', 'Ceramics', 'Polymers'],
        'Ecology, Geology and Environmental Sciences': ['', 'Ecology', 'Geology', 'Agriculture', 'Aquatic Ecology', 'Pollution', 'Climatology', 'Fungi', 'Forests', 'Allelopathy', 'Microplastics', 'Soil Studies', 'Minerology', 'Hydrology', 'Geochemistry', 'Geochronology', 'Geomorphology', 'Paleontology']
    };

    // Populate subarea dropdown based on selected research area
    if (subareas[selectedArea]) {
        subareas[selectedArea].forEach(function (subarea) {
            var option = document.createElement('option');
            option.value = subarea;
            option.textContent = subarea;
            if (subarea === '') option.textContent = 'Select';
            if (selectedArea === '') option.textContent = 'Select Research Area First';
            researchSubareaField.appendChild(option);
        });
    }

});

// function addAffiliation() {
//     const affiliationList = document.getElementById(id + "-List");
//     const affiliationField = document.createElement("div");
//     var childDivs = affiliationList.querySelectorAll("div");
//     var divCount = childDivs.length;
//     affiliationField.innerHTML = `<label>` + (divCount+1) + `. ` + `</label>` +
//     `<input type="text" name="aff-` + id + `[]" placeholder="Affiliation">
//     `;
//     affiliationList.appendChild(affiliationField);
// }

// function removeAffiliation(){
//     const formFields = document.getElementById(id + "-List");
//     var childDivs = formFields.querySelectorAll("div");
//     var divCount = childDivs.length;

//         if (formFields.lastChild && divCount > 1) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }