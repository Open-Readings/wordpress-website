const inputElement = document.getElementById("form-field-" + registration_ajax.custom_id);
const items = registration_ajax.items;

inputElement.addEventListener("input", onInputChange);
inputElement.addEventListener("blur", function () {
    // Clear the input field when it loses focus
    const value = inputElement.value;
    var inList = 0;
    Object.values(items).forEach(name => {
        if (name == value) {
            inList = 1;
        }
    });
    if (inList == 0) inputElement.value = "";
});

function onInputChange() {
    removeCountryDropdown();
    const value = inputElement.value.toLowerCase();

    if (value.length < 1) return;

    const filteredNames = [];
    Object.values(items).forEach(name => {
        if (name.toLowerCase().includes(value)) {
            filteredNames.push(name);
        }
    });

    createCountryDropdown(filteredNames);
}

function createCountryDropdown(list) {
    const listEl = document.createElement("ul");
    listEl.className = 'registration-selection';
    listEl.id = 'registration-li';
    for (let i = 0; i < 100 && i < list.length; i++) {
        const listItem = document.createElement("li");
        const countryButton = document.createElement("button");
        countryButton.className = 'registration-dropdown-element';
        countryButton.innerHTML = list[i];
        countryButton.addEventListener("click", onCountryClick)
        listItem.appendChild(countryButton);
        listEl.appendChild(listItem);
    }

    document.getElementById("country-wrapper").appendChild(listEl);
}

function removeCountryDropdown() {
    const listEl = document.getElementById('registration-li');
    if (listEl) listEl.remove();
}

function onCountryClick(e) {
    e.preventDefault();
    const buttonEl = e.target;
    inputElement.value = buttonEl.innerHTML;
    console.log(inputElement.value);
    removeCountryDropdown();
}

