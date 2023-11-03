var fileButton1 = document.getElementById('fileButton');
const uploadLoader = document.getElementById("uploadLoader");
var latexButton1 = document.getElementById('latexButton');

function uploadWait(){
    fileButton1.disabled = false;
    latexButton1.disabled = false;
    uploadLoader.style.display = "none";
}

    fileButton1.addEventListener('click', function(event) {
        event.preventDefault();
        fileButton1.disabled = true;
        latexButton1.disabled = true;
        uploadLoader.style.display = "block";
        const form = this.closest('form');
        var fileInput = document.getElementById('fileInput');
        var formFile = new FormData();

        formFile.append('fileToUpload1', fileInput.files[0]);
        formFile.append('fileToUpload2', fileInput.files[1]);

        fetch(dirAjax.path + "/latex/upload.php", {
            method: "POST",
            body: formFile
        })
        .then(response => response.text())
        .then(data => {
            // Handle the response data as needed
        })
        .catch(error => {
            console.error("Error exporting file: " + error);
        });

        fetch(dirAjax.path + "/latex/pause.php")
        .then(response => {
            if (!response.ok) {
            throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            // Handle the response data from the PHP script
            console.log(data);
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
        setTimeout(() => {  uploadWait(); }, 4200);
    });
