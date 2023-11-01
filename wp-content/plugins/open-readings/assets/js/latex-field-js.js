var textarea = document.getElementById("textArea");
var charCount = document.getElementById("charCount");

textarea.addEventListener("input", function() {
  var text = this.value;
  var count = text.length;

  // Change the maximum character limit here (e.g., 100)
  var maxLimit = 3500;

  if (count > maxLimit) {
    this.value = text.substring(0, maxLimit);
    count = maxLimit;
  }

  charCount.innerText = count;
});


document.getElementById("latexButton").addEventListener("click", function () {
    const form = this.closest('form');

    // Check if the form is valid
    if (form.checkValidity()) {
        if (1){
        const formData = new FormData(form);

        fetch(dirAjax.path + "/latex/export.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Handle the response data as needed
        })
        .catch(error => {
            console.error("Error exporting file: " + error);
        });
        document.getElementById("abstract").contentWindow.location.reload(true);
    } else {
        // The form is not valid; you can display an error message or take other actions.
        console.error("Form is not valid. Please fill in all required fields.");
    }
}
});

// function addPerson() {
//     const peopleList = document.getElementById("peopleList");
//     const personField = document.createElement("div");
//     personField.innerHTML = `
//         <input type="text" name="name[]" placeholder="Name">
//         <input type="number" name="reference[]" placeholder="Affiliation">
//     `;
//     peopleList.appendChild(personField);
// }

// function removePerson(){
//     const formFields = document.getElementById("peopleList");
//     var childDivs = formFields.querySelectorAll("div");
//     var divCount = childDivs.length;

//         if (formFields.lastChild && divCount > 1) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }

// function addAffiliation() {
//     const affiliationList = document.getElementById("affiliationList");
//     const affiliationField = document.createElement("div");
//     var childDivs = affiliationList.querySelectorAll("div");
//     var divCount = childDivs.length;
//     affiliationField.innerHTML = `<label>` + (divCount+1) + `. ` + `</label>` +
//     `<input type="text" name="affiliation[]" placeholder="Affiliation">
//     `;
//     affiliationList.appendChild(affiliationField);
// }

// function removeAffiliation(){
//     const formFields = document.getElementById("affiliationList");
//     var childDivs = formFields.querySelectorAll("div");
//     var divCount = childDivs.length;

//         if (formFields.lastChild && divCount > 1) {
//             formFields.removeChild(formFields.lastChild);
//         }
// }

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

