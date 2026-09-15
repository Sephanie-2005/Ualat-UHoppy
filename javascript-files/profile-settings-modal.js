function openSettingsModal() {
    const overlay = document.getElementById('settings-popup-overlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

function closeSettingsModal() {
    const overlay = document.getElementById('settings-popup-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

function switchPopupTab(tabId, btnNode) {
    document.querySelectorAll('.popup-section').forEach(section => {
        section.classList.remove('visible');
    });

    document.querySelectorAll('.popup-nav-tab').forEach(tabButton => {
        tabButton.classList.remove('active');
    });

    const targetSection = document.getElementById(tabId);
    if (targetSection) {
        targetSection.classList.add('visible');
    }

    if (btnNode) {
        btnNode.classList.add('active');
    }
}

// Global function so the HTML button onclick action can always call it
function removeProfilePicture() {
    const avatarPreview = document.getElementById('modal-preview-avatar');
    const flagInput = document.getElementById('delete_avatar_flag');
    const fileInput = document.getElementById('modal_profile_pic');

    if (avatarPreview) {
        // Steps up out of owner-browser to find your system-images folder
        avatarPreview.src = '../../system-images/default_profile.png';
    }
    if (flagInput) {
        flagInput.value = '1'; // Forces flag trigger to 1
    }
    if (fileInput) {
        fileInput.value = ''; // Clears selected local disk file
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const modalProfilePicInput = document.getElementById('modal_profile_pic');
    const modalPreviewAvatarImage = document.getElementById('modal-preview-avatar');
    const flagInput = document.getElementById('delete_avatar_flag');

    if (modalProfilePicInput && modalPreviewAvatarImage) {
        modalProfilePicInput.addEventListener('change', function () {
            const [file] = this.files;
            if (file) {
                modalPreviewAvatarImage.src = URL.createObjectURL(file);
                if (flagInput) {
                    flagInput.value = '0'; // Reset deletion flag because a new file is chosen instead
                }
            }
        });
    }

    const passwordInput = document.getElementById('m_new_pass');
    if (passwordInput) {
        const rules = {
            length: [document.getElementById('rule-length'), (v) => v.length >= 8, "Minimum 8 characters"],
            upper: [document.getElementById('rule-uppercase'), (v) => /[A-Z]/.test(v), "At least (1) uppercase letter"],
            lower: [document.getElementById('rule-lowercase'), (v) => /[a-z]/.test(v), "At least (1) lowercase letter"],
            num: [document.getElementById('rule-number'), (v) => /\d/.test(v), "At least (1) number"],
            special: [document.getElementById('rule-special'), (v) => /[\W_]/.test(v), "At least (1) special character"]
        };

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            Object.values(rules).forEach(([el, validationCheck, textContent]) => {
                if (el) {
                    const isValid = validationCheck(val);
                    el.className = isValid ? "rule-valid" : "rule-invalid";
                    el.innerHTML = textContent;
                }
            });
        });
    }
});