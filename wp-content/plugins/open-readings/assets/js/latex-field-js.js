var textarea = document.getElementById("textArea");
var textDisplay = document.getElementById("latexResult");
var charCount = document.getElementById("charCount");
const latexButton = document.getElementById("latexButton");
const loader = document.getElementById('loader');
var fileButton = document.getElementById('fileButton');
const errorMessage = document.getElementById('errorMessage');


textarea.addEventListener("input", function () { countChar(); });
textarea.addEventListener('scroll', function() { textScroll(); });

function countChar() {
    var text = textarea.value;
    var count = text.length;

    // Change the maximum character limit here (e.g., 100)
    var maxLimit = 3000;

    if (count > maxLimit) {
        textarea.value = text.substring(0, maxLimit);
        count = maxLimit;
    }

    charCount.innerText = count;
    textScroll();
}

function textScroll(){
    textDisplay.scrollTop = textarea.scrollTop;
}

function setIframeHeight() {
    const iframe = document.getElementById('abstract');
    const width = iframe.offsetWidth; // Get the current width of the iframe
    const height = width * 1.41; // Calculate the height based on the width and aspect ratio

    iframe.style.height = height + 'px'; // Set the height of the iframe
    countChar();
}

window.addEventListener('load', setIframeHeight);
window.addEventListener('resize', setIframeHeight);


function afterWait($exportReturn) {
    latexButton.disabled = false;
    fileButton.disabled = false;
    loader.style.display = 'none';
    logFilePath = dirAjax.path + '/latex/temp/' + folderAjax.folder + '/abstract.log' + '?timestamp=' + new Date().getTime();
    console.log(logFilePath);
    fetch(logFilePath)
        .then(response => response.text())
        .then(data => {
            document.getElementById('logContent').textContent = data;
            const logContent = document.getElementById('logContent');

            // Get the content of the pre element
            const content = logContent.textContent || logContent.innerText;

            // Find the position of the first exclamation mark
            const firstExclamationPosition = content.indexOf('!');

            // Find the position of the first occurrence of a double line break after the first exclamation mark
            const doubleLineBreakPosition = content.indexOf('\n\n', firstExclamationPosition);

            // Extract text between the first exclamation mark and the first double line break
            const newTextContent = (firstExclamationPosition !== -1 && doubleLineBreakPosition !== -1) ?
                content.substring(firstExclamationPosition, doubleLineBreakPosition) :
                '';

            // Display content between the first exclamation mark and the first double line break
            logContent.textContent = newTextContent;
            if (newTextContent.length == 0)
                logContent.style.display = "none";
            else
                logContent.style.display = "block";
        })
        .catch(error => {
            document.getElementById('logContent').textContent = 'Error retrieving log file: ' + error;
        });
    console.log('afterWait');
    if ($exportReturn == 0) {
        document.getElementById("abstract").style.display = "block";
        document.getElementById("abstract").setAttribute("src", dirAjax.path + '/latex/temp/' + folderAjax.folder + '/abstract.pdf' + '?timestamp=' + new Date().getTime() + '#toolbar=0&view=FitH');
    } else
        document.getElementById("abstract").style.display = "none";

    //document.getElementById("abstract").contentWindow.location.reload(true);
    setIframeHeight();
}



latexButton.addEventListener("click", async function () {
    const form = this.closest('form');
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        if (input.type !== 'submit' && input.type !== 'button' && input.type !== 'file') {
            input.value = input.value.trim();
        }
    });



    // Check if the form is valid
    if (form.checkValidity()) {
        latexButton.disabled = true;
        fileButton.disabled = true;
        loader.style.display = 'block';
        const formData = new FormData(form);
        errorMessage.style.display = 'none';


        try {
            const response = await fetch(dirAjax.path + "/latex/export.php", {
                method: "POST",
                body: formData
            });

            // Wait for the response and check its content
            const data = await response.text();

            // Check if the response indicates the operation has finished
            if (data.includes('Export completed::0')) {
                // Call the function afterWait()
                afterWait(0);
                errorMessage.style.display = 'none';
            } else if (data.includes('Export failed::1')) {
                errorMessage.innerHTML = 'Failed to generate document';
                errorMessage.style.display = 'block';
                afterWait(1);
            } else if (data.includes('Export failed::')) {
                var message = data.match(/Export failed::(.*?)::end/);
                errorMessage.innerHTML = message[1];
                errorMessage.style.display = 'block';
                afterWait(0);
            }
            else {
                console.log(data);
                errorMessage.innerHTML = 'Failed to generate document';
                errorMessage.style.display = 'none';
                afterWait(1);
            }
        } catch (error) {
            console.log(error);
        }
    } else {
        console.log('Form is not valid');
        logFilePath = folderAjax.folder + 'abstract.log' + '?timestamp=' + new Date().getTime();
        console.log(logFilePath);
        errorMessage.style.display = 'block';
        const invalidFields = [];

        // Iterate through the form elements
        for (let i = 0; i < form.elements.length; i++) {
            const field = form.elements[i];

            if (field.type !== 'submit' && !field.validity.valid) {
                // Add the invalid field to the array
                invalidFields.push(field);
            }

        }

        console.log(invalidFields);
        errorMessage.innerHTML = 'Please fill in all the required fields. make sure you have specified the corresponding author email correctly.';
    }
});