var textarea = document.getElementById("textArea");
var textDisplay = document.getElementById("latexResult");
var charCount = document.getElementById("charCount");
const loader = document.getElementById('loader');
var fileButton = document.getElementById('fileButton');
const errorMessage = document.getElementById('errorMessage');
let latexButton;


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
    
    countChar();
    iframe.setAttribute("src", dirAjax.path + '/latex/temp/' + folderAjax.folder + '/abstract.pdf' + '?timestamp=' + new Date().getTime() + '#toolbar=0&view=FitH');
    
    iframe.onload = function() {
        const width = iframe.offsetWidth; // Get the current width of the iframe
        const height = width * 1.41; // Calculate the height based on the aspect ratio (A4 standard, 1:1.41)
        iframe.style.height = height + 'px'; // Set the height of the iframe
    };
}

window.addEventListener('load', setIframeHeight);
window.addEventListener('resize', setIframeHeight);

let isFormDirty = false;

// Set to true whenever the user modifies the form
document.querySelectorAll('input, textarea').forEach((input) => {
    input.addEventListener('input', () => {
        isFormDirty = true;
    });
});

window.addEventListener('beforeunload', warnOnExit);

function warnOnExit(){
    
    if (isFormDirty == true){
        event.returnValue = 'Are you sure you want to leave? Changes you made may not be saved.';
    }
}

document.querySelector('.elementor-form').addEventListener('submit', function () {

        window.removeEventListener('beforeunload', warnOnExit); // Remove warning if URL matches
});

const observer1 = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            // Check if the target element has appeared
            const targetDiv = document.querySelector('.elementor-message-danger'); // Change this to your class
            if (targetDiv) {
                handleDivAppearance();
                // Optionally, you can disconnect the observer if you only want to detect it once
                break; // Exit the loop once the element is found
            }
            
        }
    }
});

const config = { childList: true, subtree: true };

// Start observing the document body (or a specific parent element)
observer1.observe(document.body, config);

// If you want to also check if the element already exists when the script runs


function handleDivAppearance(){
    window.addEventListener('beforeunload', warnOnExit);
}


function afterWait($exportReturn) {
    latexButton.disabled = false;
    fileButton.disabled = false;
    loader.style.display = 'none';
    logFilePath = dirAjax.path + '/latex/temp/' + folderAjax.folder + '/abstract.log' + '?timestamp=' + new Date().getTime();
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
            else if($exportReturn == 0)
                logContent.style.display = "block";
            else
                logContent.style.display = "none";
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


function addLatexEventListener(){
    latexButton = document.getElementById("latexButton");
latexButton.addEventListener("click", async function () {
    const form = document.getElementsByClassName('elementor-form')[0];
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

            if (data.includes('Export completed')) {
                errorMessage.style.display = 'none';
            } else if (data.includes('Export failed::')) {
                var message = data.match(/Export failed::(.*?)::end/);
                errorMessage.innerHTML = message[1];
                errorMessage.style.display = 'block';
            }

            if (data.includes('File exists::true')) {
                afterWait(0);
            } else if (data.includes('File exists::false')){
                afterWait(1);
                if (data.includes('Export completed')) {
                    errorMessage.innerHTML = 'Failed to generate document';
                    errorMessage.style.display = 'block';
                }
            }
        } catch (error) {
            console.log(error);
        }
    } else {
        logFilePath = folderAjax.folder + 'abstract.log' + '?timestamp=' + new Date().getTime();
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

        errorMessage.innerHTML = 'Please fill in all the required fields. make sure you have specified the corresponding author email correctly.';
    }
});
}


// Displays latex instruction div only when second registration form page is active
document.addEventListener('DOMContentLoaded', function () {
    const targetElement = document.querySelector('.elementor-field-group-presentation');
    const container = document.getElementById("instructions-container");
    const abstractDiv = document.getElementById("abstract-div");
    const divToMove = document.getElementById("abstract-display");
    const latexDiv = document.getElementById("latex-div");
    const form = document.getElementsByClassName('elementor-form')[0];
    if (form) {
        form.addEventListener('keydown', function (event) {
            // Check if 'Enter' key is pressed and the active element is not a submit button
            if (event.key === 'Enter' && document.activeElement.type !== 'submit' && document.activeElement.type !== 'textarea') {
                event.preventDefault();  // Prevent default form navigation
                event.stopImmediatePropagation(); // Prevent other event listeners from executing
                const focusableElements = Array.from(
                    form.querySelectorAll('input, select')
                ).filter(el => !el.disabled && el.type !== 'hidden' && el.type !== 'submit');

                const currentIndex = focusableElements.indexOf(document.activeElement);
                const nextElement = focusableElements[currentIndex + 1];

                if (nextElement) {
                    nextElement.focus(); // Move to the next form field
                }
            }
        }, true);
    }

    if (divToMove && latexDiv) {
        latexDiv.appendChild(divToMove);
        addLatexEventListener();
    } else {
        console.warn("One or both of the elements do not exist.");
    }

    if (targetElement) {
        // Create a new MutationObserver
        const observer = new MutationObserver((mutationsList) => {
            
            mutationsList.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    if (targetElement.classList.contains('elementor-hidden')) {
                        // Add actions when the element is hidden
                        container.style.display = 'none';
                        // abstractDiv.style.display = 'none';
                        abstractDiv.classList.add('hidden');
                        window.scrollTo(0, window.scrollY - 1000);
                    } else {
                        // Add actions when the element is visible
                        container.style.display = 'block';
                        abstractDiv.classList.remove('hidden');
                        window.dispatchEvent(new Event('resize'));
                        window.scrollTo(0, window.scrollY + 1); // Scroll down by 1 pixel
                        window.scrollTo(0, window.scrollY - 1); // Scroll up by 1 pixel
                        setIframeHeight();

                        // abstractDiv.style.display = 'flex';
                    }
                }
            });
        });
        
        // Start observing the target element for class attribute changes
        observer.observe(targetElement, { attributes: true });
    } else {
        console.warn('Element with class "elementor-field-group-presentation" not found.');
    }
});