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

                // Extract text starting from the first exclamation mark (if found)
                const newTextContent = (firstExclamationPosition !== -1) ? content.substring(firstExclamationPosition) : '';

                // Display content after the first exclamation mark, or empty string if not found
                logContent.textContent = newTextContent;
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

