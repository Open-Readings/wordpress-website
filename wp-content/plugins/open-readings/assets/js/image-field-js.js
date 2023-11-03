var fileAddButtons = document.querySelectorAll('.file-add');

fileAddButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        console.log(1);
        // const form = this.closest('form');
        var id = button.getAttribute('data-field-id');
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

        
    });
});




// $('#uploadButton').on('click', function() {
    // var fileInput = document.getElementById('fileInput').files[0];
    // var formData = new FormData();
    // formData.append('fileToUpload', fileInput);

//     $.ajax({
//         url: '/latex/upload.php',
//         type: 'POST',
//         data: formData,
//         contentType: false,
//         processData: false,
//         success: function(response) {
//             console.log(response);
//         },
//         error: function() {
//             console.error('Error uploading file.');
//         }
//     });
// });