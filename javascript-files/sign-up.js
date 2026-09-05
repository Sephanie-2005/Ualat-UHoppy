function openSignupModal() {
    const modal = document.getElementById("signupModal");
    if (modal) {
        modal.style.setProperty("display", "flex", "important");
    }
}

function closeSignupModal() {
    const modal = document.getElementById("signupModal");
    if (modal) {
        modal.style.setProperty("display", "none", "important");
    }
}

function switchToSignin(event) {
    event.preventDefault();
    closeSignupModal();
    if (typeof openModal === "function") {
        openModal();
    }
}

// FIX: Target click listeners safely so they don't fight your toggle triggers
document.addEventListener('click', function(event) {
    const modal = document.getElementById("signupModal");
    if (event.target === modal) {
        modal.style.setProperty("display", "none", "important");
    }
});