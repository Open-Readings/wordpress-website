var authAddButtons = document.querySelectorAll('.auth-add');

authAddButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        const peopleList = document.getElementById("authList");
        var childDivs = peopleList.querySelectorAll("div");
        var divCount = childDivs.length;
        const personField = document.createElement("div");
        personField.innerHTML = `
        <input type="text" pattern="^[^&%\\$\\\\#^_\\{\\}~]*$" name="name[]" placeholder="Full name" required>
        <input type="text" pattern="[0-9, ]*" class="narrow" name="aff_ref[]" placeholder="Aff. Nr." required>
        <label class="text-like-elementor"> Corresponding author </label> <input class="contact-author" style="margin: 5px;" type="radio" name="contact_author" value="${divCount+1}">

    `;
        peopleList.appendChild(personField);
        getRadios();
    });
});

var authRemButtons = document.querySelectorAll('.auth-rem');

authRemButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        const formFields = document.getElementById("authList");
        var childDivs = formFields.querySelectorAll("div");
        var divCount = childDivs.length;

        if (formFields.lastChild && divCount > 1) {
            formFields.removeChild(formFields.lastChild);
        }
    });
});

function getRadios(){
    var contactRadio = document.querySelectorAll('.contact-author');
    contactRadio.forEach(function(radio) {
    radio.addEventListener('change', function(){
        console.log(1);
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