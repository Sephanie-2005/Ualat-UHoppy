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

document.addEventListener('DOMContentLoaded', () => {
    const modalProfilePicInput = document.getElementById('modal_profile_pic');
    const modalPreviewAvatarImage = document.getElementById('modal-preview-avatar');

    if (modalProfilePicInput && modalPreviewAvatarImage) {
        modalProfilePicInput.addEventListener('change', function () {
            const [file] = this.files;
            if (file) {
                modalPreviewAvatarImage.src = URL.createObjectURL(file);
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
                    el.innerHTML = (isValid ? " " : " ") + textContent;
                }
            });
        });
    }
});
