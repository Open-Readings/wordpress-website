const universityInputElement = document.getElementById("form-field-" + registration_ajax.uni_id);

const universities = registration_ajax.uni_items;


universityInputElement.addEventListener("input", universityInputChange);


function universityInputChange() {
    removeUniversityDropdown();
    const value = universityInputElement.value.toLowerCase();

    if (value.length < 4) return;

    const filteredNames = [];
    Object.values(universities).forEach(name => {
        if (name.toLowerCase().includes(value)) {
            filteredNames.push(name);
        }
    });

    createUniversityDropdown(filteredNames);
}

function createUniversityDropdown(list) {
    const listEl = document.createElement("ul");
    listEl.className = 'registration-selection';
    listEl.id = 'registration-li';
    for (let i = 0; i < 100 && i < list.length; i++) {
        const listItem = document.createElement("li");
        const universityButton = document.createElement("button");
        universityButton.className = 'registration-dropdown-element';
        universityButton.innerHTML = list[i];
        universityButton.addEventListener("click", onUniversityClick)
        listItem.appendChild(universityButton);
        listEl.appendChild(listItem);
    }

    document.getElementById("university-wrapper").appendChild(listEl);
}

function removeUniversityDropdown() {
    const listEl = document.getElementById('registration-li');
    if (listEl) listEl.remove();
}

function onUniversityClick(e) {
    e.preventDefault();
    const buttonEl = e.target;
    universityInputElement.value = buttonEl.innerHTML;
    removeUniversityDropdown();
}

