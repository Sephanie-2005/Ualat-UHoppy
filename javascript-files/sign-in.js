function openModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.setProperty("display", "flex", "important");
    }
}

function closeModal() {
    const modal = document.getElementById("signinModal");
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

document.addEventListener('click', function(event) {
    const modal = document.getElementById("signinModal");
    if (event.target === modal) {
        modal.style.setProperty("display", "none", "important");
    }
});
