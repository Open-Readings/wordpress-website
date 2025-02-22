document.addEventListener("DOMContentLoaded", function () {
    const institutionInputElement = document.getElementById("form-field-" + institution_field_ajax.institution_id);
    institutionInputElement.addEventListener("input", institutionInputChange);
});


function institutionInputChange() {
    removeInstitutionDropdown();
    const value = institutionInputElement.value.toLowerCase();

    if (value.length < 4) return;
    const filteredNames = [];
    Object.values(institutions_list).forEach(name => {
        if (name.toLowerCase().includes(value)) {
            filteredNames.push(name);
        }
    });

    createInstitutionDropdown(filteredNames);
}

function createInstitutionDropdown(list) {
    const listEl = document.createElement("ul");
    listEl.className = 'registration-selection';
    listEl.id = 'registration-li';
    for (let i = 0; i < 100 && i < list.length; i++) {
        const listItem = document.createElement("li");
        const institutionButton = document.createElement("button");
        institutionButton.className = 'registration-dropdown-element';
        institutionButton.innerHTML = list[i];
        institutionButton.addEventListener("click", onInstitutionClick)
        listItem.appendChild(institutionButton);
        listEl.appendChild(listItem);
    }

    document.getElementById("institution-wrapper").appendChild(listEl);
}

function removeInstitutionDropdown() {
    const listEl = document.getElementById('registration-li');
    if (listEl) listEl.remove();
}

function onInstitutionClick(e) {
    e.preventDefault();
    const buttonEl = e.target;
    institutionInputElement.value = buttonEl.innerHTML;
    removeInstitutionDropdown();
}

