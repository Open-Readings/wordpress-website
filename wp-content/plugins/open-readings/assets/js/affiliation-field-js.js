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