// 1. Get references to your elements at the top
const modal = document.getElementById("signinModal");
const myButton = document.getElementById('myButton');

// 2. Define the modal functions
function openModal() {
    if (modal) {
        modal.style.setProperty("display", "flex", "important");
    }
}

function closeModal() {
    if (modal) {
        modal.style.setProperty("display", "none", "important");
    }
}

function switchToSignup(event) {
    event.preventDefault();
    closeModal();
    if (typeof openSignupModal === "function") {
        openSignupModal();
    }
}

// 3. Handle page load and event setup when the DOM is ready
document.addEventListener("DOMContentLoaded", function() {
    // Attach the click event to your button safely
    if (myButton) {
        myButton.addEventListener('click', openModal);
    }

    // Check URL parameters (Only opens automatically IF 'error=failed' is in the URL)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === 'failed') {
        openModal(); 
    }
});
