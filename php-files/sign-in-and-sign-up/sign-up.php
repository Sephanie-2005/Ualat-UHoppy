<link rel="stylesheet" href="../../style/default/web-app.css">
<div id="signupModal" class="sign-up-overlay">
    <div class="sign-up-content">
        <span class="close-sign-up-btn" onclick="closeSignupModal()">&times;</span>
        
        <h2 class="sign-up-title">Create an Account</h2>
        
        <!-- Change from signup-process.php to sign-up-process.php -->
<form action="../process-and-setting/sign-up-process.php" method="POST" class="sign-up-form">

             <input type="hidden" name="signup_submit" value="1"> 
    
             <div class="input-info">
                <label for="reg-role">I want to register as a:</label>
                <div class="input-wrapper">
                    <select id="reg-role" name="role" required>
                        <option value="renter">Renter (Searching for space)</option>
                        <option value="owner">Owner (Listing a space)</option>
                    </select>
                </div>
            </div>

            <div class="input-info">
                <label for="reg-firstname">First Name</label>
                <div class="input-wrapper"><input type="text" id="reg-firstname" name="first_name" required></div>
            </div>

            <div class="input-info">
                <label for="reg-middlename">Middle Name</label>
                <div class="input-wrapper"><input type="text" id="reg-middlename" name="middle_name" required></div>
            </div>

            <div class="input-info">
                <label for="reg-lastname">Last Name</label>
                <div class="input-wrapper"><input type="text" id="reg-lastname" name="last_name" required></div>
            </div>

            <div class="input-info">
                <label for="reg-email">Email Address</label>
                <div class="input-wrapper"><input type="email" id="reg-email" name="email" placeholder="Enter your email" required></div>
            </div>
            
            <div class="input-info">
                <label for="reg-password">Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="reg-password" 
                        name="password" 
                        placeholder="Create a password" 
                        required
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$"
                        title="Please follow the required password instructions!"
                    >
                </div>
                <ul class="password-rules-list" id="password-rules">
                    <li id="rule-length" class="rule-invalid"> Minimum 8 characters</li>
                    <li id="rule-uppercase" class="rule-invalid"> At least (1) uppercase letter</li>
                    <li id="rule-lowercase" class="rule-invalid"> At least (1) lowercase letter</li>
                    <li id="rule-number" class="rule-invalid"> At least (1) number</li>
                    <li id="rule-special" class="rule-invalid"> At least (1) special character</li>
                </ul>
            </div>

            <button type="submit" name="signup_submit" class="sign-up-submit-btn signup-theme-btn">Sign Up</button>
        </form>
        
        <p class="modal-footer-text">Already have an account? <a href="#" onclick="switchToSignin(event)">Sign In</a></p>
    </div>
</div>

<script src="../javascript-files/sign-up.js"></script>
<script src="../javascript-files/pass-required-input.js"></script>