var textarea = document.getElementById("textArea");
var charCount = document.getElementById("charCount");
const latexButton = document.getElementById("latexButton");

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

function afterWait(){
    latexButton.disabled = false;
    console.log(dirAjax.path + '/latex/' + folderAjax.folder + '/3.log' );
        fetch(dirAjax.path + '/latex/' + folderAjax.folder + '/3.log' + '?timestamp=' + new Date().getTime()) // Replace with the path to your log file
            .then(response => response.text())
            .then(data => {
                document.getElementById('logContent').textContent = data;
                const logContent = document.getElementById('logContent');

                // Get the content of the pre element
                const content = logContent.textContent || logContent.innerText;
        
                // Find the position of the first exclamation mark
                const firstExclamationPosition = content.indexOf('!');
        
                // Scroll to the section containing the first exclamation mark
                if (firstExclamationPosition !== -1) {
                    logContent.scrollTop = logContent.scrollHeight - logContent.clientHeight; // Scroll to the bottom
                    logContent.scrollTop = logContent.scrollHeight * (firstExclamationPosition / content.length) - 1;
                }
            })
            .catch(error => {
                document.getElementById('logContent').textContent = 'Error retrieving log file: ' + error;
            });
    document.getElementById("abstract").contentWindow.location.reload(true);
       
}


latexButton.addEventListener("click", function () {
    const form = this.closest('form');
    console.log(dirAjax.path + "/latex/export.php");
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
        latexButton.disabled = true;
        setTimeout(() => {  afterWait(); }, 4000);
            
        
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

