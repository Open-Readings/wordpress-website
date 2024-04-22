function openSessionModal(sessionId) {
    // Find the session div and read its data
    var sessionData = document.getElementById('modal-data-' + sessionId);
    console.log('modal-data-' + sessionId);
    var title = sessionData.getAttribute('session_title');
    var description = sessionData.getAttribute('session_description');
    var start = sessionData.getAttribute('session_start');
    var end = sessionData.getAttribute('session_end');

    start = start.split(' ')[1];
    end = end.split(' ')[1];



    // Populate the modal with this session's title and description
    if (description == null) {
        description = '';
    }
    document.getElementById('modalSessionTitle').innerText = title;
    document.getElementById('modalSessionTitle').innerHTML += '  <strong class="modal-presentation-title light-blue">' + start + ' - ' + end + '</strong>';
    var session_type = sessionData.getAttribute('type');
    document.getElementById('modalSessionDescription').innerText = description;
    var session_location = sessionData.getAttribute('session_location');
    var session_chair = sessionData.getAttribute('session_moderator');
    if (session_location != null || session_location != '') {
        document.getElementById('modalSessionLocation').innerText = "Location: " + session_location;
    }
    if (session_chair != null || session_chair != '') {
        document.getElementById('modalSessionChair').innerText = "Chair: " + session_chair;
    }



    if (session_type == 'poster') {
        document.getElementById('modalSessionDescription').innerHTML += '<p>(#number refers to the presenters poster location)</p>';
    }


    // Now, find all presenter divs for this session and format them for display
    var presentationsHTML = '';
    var presentations = sessionData.querySelectorAll('.presentation-data');

    presentations.forEach(function (presentation) {
        var presenter = presentation.getAttribute('data-presenter');
        var presentationTitle = presentation.getAttribute('data-title');
        var research_area = presentation.getAttribute('data-research-area');
        var abstract_link = presentation.getAttribute('data-abstract');

        if (session_type == 'poster') {
            var position = presentation.getAttribute('data-poster-number')

            presentationsHTML += '<p><span class="fixed-width">#' + position + '</span> <strong class="modal-presentation-title">' + presenter + '</strong>: ' + presentationTitle + ' <a href="' + abstract_link + '">Read abstract</a> </p></div>';
        } else {
            var position = presentation.getAttribute('data-start');
            //parse from 23/04/2024 12:00:00 to 12:00
            var time = position.split(' ')[1];
            time = time.split(':').slice(0, 2).join(':');

            presentationsHTML += '<p><span class="fixed-width">' + time + '</span> <strong class="modal-presentation-title">' + presenter + '</strong>: ' + presentationTitle + ' <a href="' + abstract_link + '">Read abstract</a> </p></div>';

        }

        // Add more fields as needed
    });

    document.getElementById('modalPresentations').innerHTML = presentationsHTML;

    // Show the modal
    document.getElementById('sessionModal').style.display = 'block';
}

// Close the modal when the user clicks on <span> (x)
document.querySelector('.close').onclick = function () {
    document.getElementById('sessionModal').style.display = 'none';
}

// Close the modal when the user clicks outside of it
window.onclick = function (event) {
    if (event.target == document.getElementById('sessionModal')) {
        document.getElementById('sessionModal').style.display = 'none';
    }
}

function adjustTimeHeaderMargins() {
    document.querySelectorAll('.program-section').forEach(function (section) {
        section.querySelectorAll('.time-header').forEach(function (header) {
            var ending = header.getAttribute('data-end');
            var elements = section.querySelectorAll('[data-end="' + ending + '"]');

            //find the height of the element with the same ending
            var maxHeight = 0;
            var relative_end_of_element = 0;
            //get relative end of the element with respect to the section
            elements.forEach(function (element) {
                if (element != header) {
                    maxHeight = element.clientHeight;
                    relative_end_of_element = element.offsetTop - section.offsetTop + element.clientHeight;

                }
            });





            var headerHeight = header.clientHeight;
            // Convert the vh (viewport height) factor to pixels based on the program-block's height
            // Adjust the calculation below based on your specific needs
            var marginTop = relative_end_of_element - headerHeight + 2;
            header.style.marginTop = marginTop + 'px';
        });
    });
}
// Adjust on load
window.addEventListener('load', adjustTimeHeaderMargins);

// Adjust on resize
window.addEventListener('resize', adjustTimeHeaderMargins);