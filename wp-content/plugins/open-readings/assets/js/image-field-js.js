var fileButton1 = document.getElementById('fileButton');
const uploadLoader = document.getElementById("uploadLoader");
var latexButton1 = document.getElementById('latexButton');
var imageMessage = document.getElementById('image-names');


function uploadWait() {
    fileButton1.disabled = false;
    latexButton1.disabled = false;
    uploadLoader.style.display = "none";
    var isImage = 1;
    Array.from(fileInput.files).forEach(file => {
        if (!file['name'].endsWith('.png') && !file['name'].endsWith('.jpeg') && !file['name'].endsWith('.jpg'))
            isImage = 0;
    });
    if (!isImage) {
        imageMessage.textContent = "File type not allowed";
    } else if (fileInput.files.length > max_files) {
        imageMessage.textContent = "Maximum number of files allowed is " + max_files;
    }
    else if (fileInput.files.length == 0) {
        imageMessage.textContent = "0 files uploaded";
    } else if (fileInput.files.length == 1) {
        imageMessage.textContent = fileInput.files[0]['name'];
    } else {
        imageMessage.innerHTML = '';
        for (file of fileInput.files) {

            if (file['name'].endsWith('.png') || file['name'].endsWith('.jpeg') || file['name'].endsWith('.jpg'))
                imageMessage.innerHTML += file['name'] + "<br>";
        }
    }
    imageMessage.style.display = 'block';
}

fileButton1.addEventListener('click', function (event) {
    event.preventDefault();
    fileButton1.disabled = true;
    latexButton1.disabled = true;
    uploadLoader.style.display = "block";
    const form = this.closest('form');
    var fileInput = document.getElementById('fileInput');
    var formFile = new FormData();

    for (var i = 0; i < fileInput.files.length; i++) {
        formFile.append('fileToUpload' + (i + 1), fileInput.files[i]);
    }
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
    setTimeout(() => { uploadWait(); }, 4200);
});
