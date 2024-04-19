var authAddButtons = document.querySelectorAll('.auth-add');

authAddButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const peopleList = document.getElementById("authList");
        var childDivs = peopleList.querySelectorAll("div");
        var divCount = childDivs.length;
        const personField = document.createElement("div");
        personField.innerHTML = `
        <input type="text" pattern="^[^&%\\$\\\\#^_\\{\\}~]*$" class="author-width form-padding" name="name[]" placeholder="(e.g. John Smith)" required>
        <input type="text" pattern="[0-9, ]*" class="narrow form-padding" name="aff_ref[]" placeholder="(e.g. 1,2)" required>
        <label class="text-like-elementor"> Corresponding author </label> <input class="contact-author form-padding" style="margin: 5px;" type="radio" name="contact_author" value="${divCount + 1}">

    `;
        if (divCount < 15)
            peopleList.appendChild(personField);
        getRadios();
    });
});

var authRemButtons = document.querySelectorAll('.auth-rem');

authRemButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const formFields = document.getElementById("authList");
        var childDivs = formFields.querySelectorAll("div");
        var divCount = childDivs.length;

        if (formFields.lastChild && divCount > 1) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});

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