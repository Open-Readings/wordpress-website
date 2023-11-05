var textarea = document.getElementById("textArea");
var charCount = document.getElementById("charCount");
const latexButton = document.getElementById("latexButton");
const loader = document.getElementById('loader');
var fileButton = document.getElementById('fileButton');
const errorMessage = document.getElementById('errorMessage');


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

function setIframeHeight() {
    const iframe = document.getElementById('abstract');
    const width = iframe.offsetWidth; // Get the current width of the iframe
    const height = width * 1.41; // Calculate the height based on the width and aspect ratio

    iframe.style.height = height + 'px'; // Set the height of the iframe
}

window.addEventListener('load', setIframeHeight);
window.addEventListener('resize', setIframeHeight);

function afterWait(){
    latexButton.disabled = false;
    fileButton.disabled = false;
    loader.style.display = 'none';
    console.log(dirAjax.path + '/latex/' + folderAjax.folder + '/3.log' );
    fetch(dirAjax.path + '/latex/' + folderAjax.folder + '/3.log' + '?timestamp=' + new Date().getTime())
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
            if(newTextContent.length == 0)
                logContent.style.display = "none";
            else
                logContent.style.display = "block";  
        })
        .catch(error => {
            document.getElementById('logContent').textContent = 'Error retrieving log file: ' + error;
    });
    document.getElementById("abstract").contentWindow.location.reload(true);
       
}



latexButton.addEventListener("click", async function () {
    const form = this.closest('form');
    const inputs = form.querySelectorAll('input, textarea');
        latexButton.disabled = true;
        fileButton.disabled = true;
        loader.style.display = 'block';

    inputs.forEach(input => {
        if (input.type !== 'submit' && input.type !== 'button' && input.type !== 'file') {
            input.value = input.value.trim();
        }
    });
    // Check if the form is valid
    if (form.checkValidity()) {
        const formData = new FormData(form);
        errorMessage.style.display = 'none';


    try {
        const response = await fetch(dirAjax.path + "/latex/export.php", {
            method: "POST",
            body: formData
        });

        // Wait for the response and check its content
        const data = await response.text();
        console.log(data);

        // Check if the response indicates the operation has finished
        if (data.includes('Export completed')) {
            // Call the function afterWait()
            afterWait();
        } else {
            // Process not completed yet, display error or handle as needed
        }
    } catch (error) {
        console.error("Error exporting file: " + error);
    }
}
});