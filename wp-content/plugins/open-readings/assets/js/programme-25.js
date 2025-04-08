// Function to show the modal
function showModal(content) {
    const modal = document.getElementById('or-modal');
    const modalContent = document.getElementById('modal-content');
    const overlay = document.querySelector('.overlay');

    // Populate the modal with the cell's content
    modalContent.innerHTML = content;

    // Show the modal and overlay
    modal.classList.add('active');
    overlay.classList.add('active');

    modalContent.scrollTop = 0; // Reset scroll position
}

// Function to hide the modal
function hideModal() {
    const modal = document.getElementById('or-modal');
    const overlay = document.querySelector('.overlay');

    // Hide the modal and overlay
    modal.classList.remove('active');
    overlay.classList.remove('active');
}

