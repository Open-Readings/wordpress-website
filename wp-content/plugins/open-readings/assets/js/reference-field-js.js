var refAddButtons = document.querySelectorAll('.ref-add');

refAddButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        var id = button.getAttribute('data-field-id');
        const referenceList = document.getElementById(id + "-List");
        const referenceField = document.createElement("div");
        var childDivs = referenceList.querySelectorAll("div");
        var divCount = childDivs.length;
        referenceField.innerHTML = `<label>` + (divCount+1) + `. ` + `</label>` +
        `<input type="text" name="references` + id + `[]" placeholder="Reference">
        `;
        referenceList.appendChild(referenceField);
    });
});

var refRemButtons = document.querySelectorAll('.ref-rem');

refRemButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        var id = button.getAttribute('data-field-id');
        const formFields = document.getElementById(id + "-List");

        if (formFields.lastChild) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});






// function addReference() {
//     const referenceList = document.getElementById("referenceList");
//     const referenceField = document.createElement("div");
//     var childDivs = referenceList.querySelectorAll("div");
//     var divCount = childDivs.length;
//     referenceField.innerHTML = `<label>` + (divCount+1) + `. ` + `</label>` +
//     `<input type="text" name="references[]" placeholder="Reference">
//     `;
//     referenceList.appendChild(referenceField);
// }

// function removeReference(){
//     const formFields = document.getElementById("referenceList");

//         if (formFields.lastChild) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }
