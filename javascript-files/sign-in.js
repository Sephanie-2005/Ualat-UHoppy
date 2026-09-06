/**
 * UHoppy - Sign-In Modal Core View Controller
 */

// 1. Opens the modal window only when explicitly called by button onclick events
function openModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.setProperty("display", "flex", "important");
    }
}

// 2. Closes the modal view box completely
function closeModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.setProperty("display", "none", "important");
    }
}

// 3. Closes modal and opens sign-up form frame seamlessly
function switchToSignup(event) {
    event.preventDefault();
    closeModal();
    if (typeof openSignupModal === "function") {
        openSignupModal();
    }
}

// 4. Closes the modal automatically if a user clicks outside the card onto the dark mask overlay
document.addEventListener('click', function(event) {
    const modal = document.getElementById("signinModal");
    if (event.target === modal) {
        modal.style.setProperty("display", "none", "important");
    }
});
