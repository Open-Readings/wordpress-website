var refAddButtons = document.querySelectorAll('.ref-add');

refAddButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const referenceList = document.getElementById("refList");
        const referenceField = document.createElement("div");
        var childDivs = referenceList.querySelectorAll("div");
        var divCount = childDivs.length;
        referenceField.innerHTML = `<label class="ref-label">` + (divCount + 1) + `.` + `</label>` +
            `<input type="text" class="ref-width" maxlength="300" name="references[]" placeholder="(e.g. M.A.Green, HighEfficiencySiliconSolarCells (Trans. Tech. Publications, Switzerland, 1987).)" required>
        `;
        referenceField.className = "ref-div";
        referenceList.appendChild(referenceField);
    });
});

var refRemButtons = document.querySelectorAll('.ref-rem');

refRemButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        const formFields = document.getElementById("refList");

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
