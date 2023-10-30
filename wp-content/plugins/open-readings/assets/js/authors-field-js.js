var authAddButtons = document.querySelectorAll('.auth-add');

authAddButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        var id = button.getAttribute('data-field-id');
        const peopleList = document.getElementById(id + "-List");
        const personField = document.createElement("div");
        personField.innerHTML = `
        <input type="text" name="name-` + id + `[]" placeholder="Name">
        <input type="number" name="reference-` + id + `[]" placeholder="Affiliation">
    `;
        peopleList.appendChild(personField);
    });
});

var authRemButtons = document.querySelectorAll('.auth-rem');

authRemButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        var id = button.getAttribute('data-field-id');
        const formFields = document.getElementById(id + "-List");
        var childDivs = formFields.querySelectorAll("div");
        var divCount = childDivs.length;

        if (formFields.lastChild && divCount > 1) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});



// function addPerson() {
//     const peopleList = document.getElementById(id + "-List");
//     const personField = document.createElement("div");
//     personField.innerHTML = `
//         <input type="text" name="name-` + id + `[]" placeholder="Name">
//         <input type="number" name="reference-` + id + `[]" placeholder="Affiliation">
//     `;
//     peopleList.appendChild(personField);
// }

// function removePerson(){
//     const formFields = document.getElementById(id + "-List");
//     var childDivs = formFields.querySelectorAll("div");
//     var divCount = childDivs.length;

//         if (formFields.lastChild && divCount > 1) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }