// Open the modal popup when the user clicks the nav button
function openModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.display = "flex";
    }
}

// Close the modal popup when the user clicks the "X" button
function closeModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.display = "none";
    }
}

// Close the modal popup if the user clicks anywhere on the dark background mask area
window.onclick = function(event) {
    const modal = document.getElementById("signinModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
