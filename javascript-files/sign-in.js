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

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === 'failed') {
        openModal(); 
    }
});


const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === 'failed') {
        document.getElementById('signinModal').style.display = 'flex'; 
        
    }