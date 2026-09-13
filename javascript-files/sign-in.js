const modal = document.getElementById("signinModal");
const myButton = document.getElementById('myButton');

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

// CRUCIAL: Bind functions to the window object so inline HTML 'onclick' can find them
window.openModal = openModal;
window.closeModal = closeModal;
window.switchToSignup = switchToSignup;

// CONSOLIDATED SINGLE LISTENER: Prevents double-firing anomalies
document.addEventListener("DOMContentLoaded", function() {
    // Standard button fallback hook
    if (myButton) {
        myButton.addEventListener('click', openModal);
    }

    // Force check for both URL string parameter and internal PHP session error containers
    const urlParams = new URLSearchParams(window.location.search);
    const hasErrorParam = urlParams.get('error') === 'failed';
    const hasErrorDiv = document.querySelector('.login-error-msg') !== null;

    if (hasErrorParam || hasErrorDiv) {
        setTimeout(openModal, 50); // Small 50ms delay gives CSS time to paint before opening
    }
});
