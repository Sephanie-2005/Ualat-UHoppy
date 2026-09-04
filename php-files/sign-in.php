<?php 
    require_once 'database-connection.php'; // ALIGNED: Same folder
?>
<!-- The Modal Overlay Background Container -->
<div id="signinModal" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal-btn" onclick="closeModal()">&times;</span>
        
        <h2 class="modal-title">Sign In to UHoppy</h2>
        
        <!-- Action targets the process runner script file inside the same folder directory -->
        <form action="login-process.php" method="POST" class="modal-form">
            <div class="input-group">
                <label for="modal-email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="modal-email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            
            <div class="input-group">
                <label for="modal-password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="modal-password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <button type="submit" class="modal-submit-btn">Sign In</button>
        </form>
        
        <p class="modal-footer-text">Don't have an account? <a href="../signup.html">Sign up</a></p>
    </div>
</div>

<!-- Links the toggle behaviors script framework straight to the container block -->
<script src="../javascript-files/sign-in.js"></script>