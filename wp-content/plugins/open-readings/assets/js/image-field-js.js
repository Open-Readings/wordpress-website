var fileAddButtons = document.querySelectorAll('.file-add');

fileAddButtons.forEach(function(button) {
    button.addEventListener('click', function() {
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
    });
});