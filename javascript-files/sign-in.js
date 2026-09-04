function openModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.display = "flex";
    }
}

function closeModal() {
    const modal = document.getElementById("signinModal");
    if (modal) {
        modal.style.display = "none";
    }
}

window.onclick = function(event) {
    const modal = document.getElementById("signinModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
