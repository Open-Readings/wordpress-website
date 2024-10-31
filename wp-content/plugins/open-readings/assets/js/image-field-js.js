var fileButton1 = document.getElementById('fileButton');
const uploadLoader = document.getElementById("uploadLoader");
var latexButton1 = document.getElementById('latexButton');
var imageMessage = document.getElementById('image-names');
var fileInputField = document.getElementById('fileInput');
let imageCodeElements;

function uploadWait() {
    fileButton1.disabled = false;
    latexButton1.disabled = false;
    fileInputField.disabled = false;
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
    } else {
        imageMessage.innerHTML = '';
        for (file of fileInput.files) {

            if (file['name'].endsWith('.png') || file['name'].endsWith('.jpeg') || file['name'].endsWith('.jpg'))
                imageMessage.innerHTML += '<p style="font-weight:bold; display:inline">' + file['name'] + '</p> <p class="image-code"> Copy image LaTeX code to clipboard' + "</p><br>";
        }
    }
    imageCodeElements = document.querySelectorAll(".image-code");
    imageMessage.style.display = 'block';
    imageCodeElements.forEach((imageCode) => {
        imageCode.addEventListener('click', async function() {
            const precedingName = this.previousElementSibling;
            const name = precedingName.textContent;
            const textToCopy = `\\begin{figure}[H] \n\\center \n\\includegraphics[height=6cm]{${name}} \n\\caption{Add a caption} \n\\end{figure}`;
    
            try {
                await navigator.clipboard.writeText(textToCopy);
            } catch (err) {
            }
        });
    });
}



// fileButton1.addEventListener('click', function (event) {
//     event.preventDefault();
//     fileButton1.disabled = true;
//     latexButton1.disabled = true;
//     uploadLoader.style.display = "block";
//     const form = this.closest('form');
//     var fileInput = document.getElementById('fileInput');
//     var formFile = new FormData();

//     for (var i = 0; i < fileInput.files.length; i++) {
//         formFile.append('fileToUpload' + (i + 1), fileInput.files[i]);
//     }
//     fetch(dirAjax.path + "/latex/upload.php", {
//         method: "POST",
//         body: formFile
//     })
//         .then(response => response.text())
//         .then(data => {
//             // Handle the response data as needed
//         })
//         .catch(error => {
//         });

    
//     setTimeout(() => { uploadWait(); }, 4200);
// });

fileInputField.addEventListener('change', function(event){
    event.preventDefault();
    fileButton1.disabled = true;
    latexButton1.disabled = true;
    fileInputField.disabled = true;
    uploadLoader.style.display = "block";
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
            uploadWait();
        })
        .catch(error => {
            uploadWait();
        });

    
    // setTimeout(() => { uploadWait(); }, 4200);

});




