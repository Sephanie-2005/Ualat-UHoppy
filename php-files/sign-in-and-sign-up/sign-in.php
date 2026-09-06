<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'database-connection.php'; 

    $role     = $_POST['role'];
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $table  = ($role === 'owner') ? 'owners' : 'renters';
    $id_col = ($role === 'owner') ? 'owner_id' : 'renter_id';

    try {
        if (!isset($pdo) && isset($conn)) {
            $pdo = $conn;
        }

        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user[$id_col];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role']       = $role;

            if ($role === 'owner') {
                header("Location: owner-homepage.php"); 
            } else {
                header("Location: renter-homepage.php");      // Replace with your actual renter filename
            }
            exit();
        } 

    } catch (PDOException $e) {
        die("Login processing block failure error: " . $e->getMessage());
    }
}
?>

<?php if(isset($_SESSION['login_error'])): ?>
            alert("<?php echo addslashes($_SESSION['login_error']); ?>");
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

<div id="signinModal" class="sign-in-overlay">
    <div class="sign-in-content">

        <span class="close-sign-in-btn" onclick="closeModal()">&times;</span>
        
        <h2 class="sign-in-title">Sign In to UHoppy</h2>
    
        <form action="index.php" method="POST" class="sign-in-form">


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