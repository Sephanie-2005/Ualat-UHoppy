<link rel="stylesheet" href="../../style/default/web-app.css">

<?php if (isset($_SESSION['login_error'])): ?>
    <div class="login-error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['login_error']); 
            unset($_SESSION['login_error']); 
        ?>
    </div>
<?php endif; ?>

<div id="signinModal" class="sign-in-overlay">
    <div class="sign-in-content">

        <span class="close-sign-in-btn" onclick="closeModal()">&times;</span>
        
        <h2 class="sign-in-title">Sign In to UHoppy</h2>
    
        <form action="/uhoppy/php-files/process-and-setting/login-process.php" method="POST" class="sign-in-form">

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
            
       
            <input type="submit" name="login_submit" class="sign-in-submit-btn" value="Sign In">

        </form>
        
        <p class="sign-in-footer-text">Don't have an account? <a href="#" onclick="switchToSignup(event)">Sign up</a></p>
    </div>
</div>

<script src="../javascript-files/sign-in.js"></script>
