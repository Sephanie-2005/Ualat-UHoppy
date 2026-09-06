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
});

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
    const passwordInput = document.getElementById('reg-password');
    
    // Check elements exist on the current page first
    if (passwordInput) {
        const ruleLength = document.getElementById('rule-length');
        const ruleUpper = document.getElementById('rule-uppercase');
        const ruleLower = document.getElementById('rule-lowercase');
        const ruleNumber = document.getElementById('rule-number');
        const ruleSpecial = document.getElementById('rule-special');

        passwordInput.addEventListener('input', function() {
            const val = this.value;

            // 1. Length validation (>= 8)
            if (val.length >= 8) {
                ruleLength.className = "rule-valid";
                ruleLength.innerHTML = "✅ Minimum 8 characters";
            } else {
                ruleLength.className = "rule-invalid";
                ruleLength.innerHTML = "❌ Minimum 8 characters";
            }

            // 2. Uppercase letter validation
            if (/[A-Z]/.test(val)) {
                ruleUpper.className = "rule-valid";
                ruleUpper.innerHTML = "✅ At least (1) uppercase letter";
            } else {
                ruleUpper.className = "rule-invalid";
                ruleUpper.innerHTML = "❌ At least (1) uppercase letter";
            }

            // 3. Lowercase letter validation
            if (/[a-z]/.test(val)) {
                ruleLower.className = "rule-valid";
                ruleLower.innerHTML = "✅ At least (1) lowercase letter";
            } else {
                ruleLower.className = "rule-invalid";
                ruleLower.innerHTML = "❌ At least (1) lowercase letter";
            }

            // 4. Number validation
            if (/\d/.test(val)) {
                ruleNumber.className = "rule-valid";
                ruleNumber.innerHTML = "✅ At least (1) number";
            } else {
                ruleNumber.className = "rule-invalid";
                ruleNumber.innerHTML = "❌ At least (1) number";
            }

            // 5. Special character validation
            if (/[\W_]/.test(val)) {
                ruleSpecial.className = "rule-valid";
                ruleSpecial.innerHTML = "✅ At least (1) special character";
            } else {
                ruleSpecial.className = "rule-invalid";
                ruleSpecial.innerHTML = "❌ At least (1) special character";
            }
        });
    }
});
