document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('reg-password');
    
    if (passwordInput) {
        const ruleLength = document.getElementById('rule-length');
        const ruleUpper = document.getElementById('rule-uppercase');
        const ruleLower = document.getElementById('rule-lowercase');
        const ruleNumber = document.getElementById('rule-number');
        const ruleSpecial = document.getElementById('rule-special');

        passwordInput.addEventListener('input', function() {
            const val = this.value;

            if (val.length >= 8) {
                ruleLength.className = "rule-valid";
                ruleLength.innerHTML = "Minimum 8 characters";
            } else {
                ruleLength.className = "rule-invalid";
                ruleLength.innerHTML = "Minimum 8 characters";
            }

            if (/[A-Z]/.test(val)) {
                ruleUpper.className = "rule-valid";
                ruleUpper.innerHTML = "At least (1) uppercase letter";
            } else {
                ruleUpper.className = "rule-invalid";
                ruleUpper.innerHTML = "At least (1) uppercase letter";
            }

            if (/[a-z]/.test(val)) {
                ruleLower.className = "rule-valid";
                ruleLower.innerHTML = "At least (1) lowercase letter";
            } else {
                ruleLower.className = "rule-invalid";
                ruleLower.innerHTML = "At least (1) lowercase letter";
            }

        
            if (/\d/.test(val)) {
                ruleNumber.className = "rule-valid";
                ruleNumber.innerHTML = "At least (1) number";
            } else {
                ruleNumber.className = "rule-invalid";
                ruleNumber.innerHTML = "At least (1) number";
            }

            if (/[\W_]/.test(val)) {
                ruleSpecial.className = "rule-valid";
                ruleSpecial.innerHTML = "At least (1) special character";
            } else {
                ruleSpecial.className = "rule-invalid";
                ruleSpecial.innerHTML = "At least (1) special character";
            }
        });
    }
});
