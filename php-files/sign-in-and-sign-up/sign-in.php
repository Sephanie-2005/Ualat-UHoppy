 <?php 
if (isset($_SESSION['login_error'])): ?>
    <div class="login-error-msg" style="color: #ff4d4d; background-color: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
        <?php 
            echo $_SESSION['login_error']; 
            unset($_SESSION['login_error']); // Clear message after showing it once
        ?>
    </div>
<?php endif; ?>

 <link rel="stylesheet" href="../../style/default/web-app.css">

<div id="signinModal" class="sign-in-overlay">
        <div class="sign-in-content">

            <span class="close-sign-in-btn" onclick="closeModal()">&times;</span>
            
            <h2 class="sign-in-title">Sign In to UHoppy</h2>
        
            <form action="../process-and-setting/login-process.php" method="POST" class="sign-in-form">

                <div class="input-info">
                    <label for="modal-role">I am a:</label>
                    <div class="input-wrapper">
                        <select id="modal-role" name="role" required>
                            <option value="renter">Renter (Tenant)</option>
                            <option value="owner">Owner (Landlord/Landlady)</option>
                        </select>
                    </div>
                </div>

                <div class="input-info">
                    <label for="modal-email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" id="modal-email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="input-info">
                    <label for="modal-password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="modal-password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" name="login_submit" class="sign-in-submit-btn">Sign In</button>
            </form>
            
            <p class="sign-in-footer-text">Don't have an account? <a href="#" onclick="switchToSignup(event)">Sign up</a></p>
        </div>
    </div>
    <script src="../javascript-files/sign-in.js"></script>
