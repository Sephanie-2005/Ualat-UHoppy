<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($conn)) {
        require_once 'database-connection.php';
        $conn = new mysqli("localhost", "root", "", "uhoppy_db");
    }

    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $role   = isset($_SESSION['role']) ? $_SESSION['role'] : 'renter'; 

    $modalError = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_settings_action'])) {
        $table = ($role === 'owner') ? 'owners' : 'renters';
        $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';
        
        if ($_POST['modal_settings_action'] === 'save_info') {
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone_number']);

                        $profilePicPath = null;
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Unique file identifier string mapping
                    $newFileName = $role . "_" . $userId . "_" . time() . "." . $fileExtension;
                    
                    // 1. Physical directory target destination relative to this file
                    $physicalUploadDir = '../uploaded-images/';
                    if (!is_dir($physicalUploadDir)) { 
                        mkdir($physicalUploadDir, 0755, true); 
                    }
                    
                    // 2. Save the file cleanly onto the physical server hard drive disk
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $physicalUploadDir . $newFileName)) {
                        // 3. FIX: Save the string path into the database without leading dots
                        // This allows any page on your system to call it universally!
                        $profilePicPath = 'uploaded-images/' . $newFileName;
                    }
                }
            }


            if ($profilePicPath !== null) {
                $stmt = $conn->prepare("UPDATE $table SET email = ?, phone_number = ?, profile_picture = ? WHERE $idCol = ?");
                $stmt->bind_param("sssi", $email, $phone, $profilePicPath, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE $table SET email = ?, phone_number = ? WHERE $idCol = ?");
                $stmt->bind_param("ssi", $email, $phone, $userId);
            }

            if ($stmt->execute()) { 
                echo "<script>alert('Account settings updated.'); window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "';</script>";
                exit();
            } else { $modalError = "Update encountered an operational error."; }
            $stmt->close();
        }

                if ($_POST['modal_settings_action'] === 'change_password') {
            $currPass = $_POST['current_password'];
            $newPass  = $_POST['new_password'];

            $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

            if (!preg_match($passwordRegex, $newPass)) {
                $modalError = "New password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
            } else {
                $stmt = $conn->prepare("SELECT password FROM $table WHERE $idCol = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $pwdRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (password_verify($currPass, $pwdRow['password'])) {
                    $hashedNew = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE $idCol = ?");
                    $stmt->bind_param("si", $hashedNew, $userId);
                    if ($stmt->execute()) {
                        echo "<script>alert('Password updated successfully.'); window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "';</script>";
                        exit();
                    }
                    $stmt->close();
                } else { 
                    $modalError = "Current active password is invalid."; 
                }
            }
        }


            if ($_POST['modal_settings_action'] === 'delete_account') {
            $confirmPass = $_POST['delete_password_confirm'];

            // 1. Fetch both password and profile picture path data fields
            $stmt = $conn->prepare("SELECT password, profile_picture FROM $table WHERE $idCol = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($userRow && password_verify($confirmPass, $userRow['password'])) {
                
                // 2. FILE PURGE CONTROLLER:
                // Check if they have a non-default custom file stored on the local drive disk
                if (!empty($userRow['profile_picture']) && file_exists($userRow['profile_picture'])) {
                    // Make sure it isn't your base default placeholder asset before unlinking
                    if (strpos($userRow['profile_picture'], 'default-avatar.png') === false) {
                        unlink($userRow['profile_picture']); // Physically removes the file from uploaded-images/
                    }
                }

                $stmt = $conn->prepare("DELETE FROM $table WHERE $idCol = ?");
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    
                    session_unset();
                    session_destroy();
                    
                    echo "<script>
                        alert('Your account and uploaded data have been permanently deleted.');
                        window.location.href = 'index.php';
                    </script>";
                    exit();
                }
                $stmt->close();
            } else { 
                $modalError = "Incorrect password confirmation."; 
            }
        }

    }

    $userData = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone_number' => '', 'profile_picture' => ''];
    if ($userId > 0) {
        $table = ($role === 'owner') ? 'owners' : 'renters';
        $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';
        $stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, profile_picture FROM $table WHERE $idCol = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $fetched = $stmt->get_result()->fetch_assoc();
        if ($fetched) { $userData = $fetched; }
        $stmt->close();
    }
     $dbAvatarPath = !empty($userData['profile_picture']) ? htmlspecialchars($userData['profile_picture']) : '';
    
    $dbAvatarPath = str_replace('../', '', $dbAvatarPath);

    if (!empty($dbAvatarPath) && file_exists('../' . $dbAvatarPath)) {
        $modalAvatar = '../' . $dbAvatarPath;
    } else {
        $modalAvatar = '../system-images/Default profile.png';
    }
?>

<div id="settings-popup-overlay" class="settings-popup-overlay">
    <div class="settings-popup-box">
        <div class="settings-popup-header">
            <h3>Account Settings</h3>
            <button class="settings-close-btn" onclick="closeSettingsModal()">&times;</button>
        </div>

        <div class="settings-popup-nav">
            <button class="popup-nav-tab active" onclick="switchPopupTab('popup-account', this)">Account Info</button>
            <button class="popup-nav-tab" onclick="switchPopupTab('popup-security', this)">Security</button>
            <button class="popup-nav-tab danger-tab" onclick="switchPopupTab('popup-danger', this)">Delete Account</button>
            <button class="popup-nav-tab logout-tab" onclick="window.location.href='index.php?action=logout'">Log Out</button>
        </div>

        <div class="settings-popup-body">
            <?php if(!empty($modalError)): ?>
                <div class="modal-error-banner">
                    <?php echo $modalError; ?>
                </div>
            <?php endif; ?>

            <!-- TAB 1: Account Info -->
            <div id="popup-account" class="popup-section visible">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modal_settings_action" value="save_info">
                    <div class="popup-avatar-row">
                        <img src="<?php echo $modalAvatar; ?>" id="modal-preview-avatar" alt="Avatar">
                        <label for="modal_profile_pic" class="modal-upload-btn">Upload Photo</label>
                        <input type="file" name="profile_pic" id="modal_profile_pic" accept="image/*" style="display: none;">
                    </div>
                    <div class="popup-field">
                        <label>Name (Read-Only)</label>
                        <input type="text" class="modal-readonly" value="<?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>" readonly>
                    </div>
                    <div class="popup-field">
                        <label for="modal_email">Username / Email</label>
                        <input type="email" name="email" id="modal_email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                    <div class="popup-field">
                        <label for="modal_phone">Phone Number</label>
                        <input type="text" name="phone_number" id="modal_phone" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                    </div>
                    <button type="submit" class="popup-submit-btn">Save Changes</button>
                </form>
            </div>

            <!-- TAB 2: Security -->
            <div id="popup-security" class="popup-section">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="modal_settings_action" value="change_password">
                    <div class="popup-field">
                        <label for="m_curr_pass">Current Password</label>
                        <input type="password" name="current_password" id="m_curr_pass" required>
                    </div>
                    <div class="popup-field">
                        <label for="m_new_pass">New Password</label>
                        <input type="password" name="new_password" id="m_new_pass" required>
                    </div>
                    <ul class="password-rules-list" id="password-rules" style="list-style: none; padding: 0px; margin: 8px 0px 15px 0px; text-align: left;">
                        <li id="rule-length" style="font-size: 12px; margin-bottom: 4px; color: #dc3545; font-weight: 500;">❌ Minimum 8 characters</li>
                        <li id="rule-uppercase" style="font-size: 12px; margin-bottom: 4px; color: #dc3545; font-weight: 500;">❌ At least (1) uppercase letter</li>
                        <li id="rule-lowercase" style="font-size: 12px; margin-bottom: 4px; color: #dc3545; font-weight: 500;">❌ At least (1) lowercase letter</li>
                        <li id="rule-number" style="font-size: 12px; margin-bottom: 4px; color: #dc3545; font-weight: 500;">❌ At least (1) number</li>
                        <li id="rule-special" style="font-size: 12px; margin-bottom: 4px; color: #dc3545; font-weight: 500;">❌ At least (1) special character</li>
                    </ul>
                    <button type="submit" class="popup-submit-btn">Update Password</button>
                </form>
            </div>

            <!-- TAB 3: Delete Account -->
            <div id="popup-danger" class="popup-section">
                <div class="popup-danger-warning">
                    <p style="margin: 0px 0px 12px 0px; font-weight: 600;">Warning: Are you sure you want to permanently delete your account in UHoppy? This action is irreversible.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="modal_settings_action" value="delete_account">
                        <div class="popup-field">
                            <label for="delete_password_confirm">Confirm Active Account Password</label>
                            <input type="password" name="delete_password_confirm" id="delete_password_confirm" placeholder="Enter password to confirm account deletion" required class="popup-danger-input" style="width: 95%; padding: 8px; margin-top: 5px; border: 1px solid #cccccc; border-radius: 4px;">
                        </div>
                        <button type="submit" class="popup-delete-btn" style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: pointer; margin-top: 10px;">Permanently Delete Account</button>
                    </form>
                </div>
            </div>

        </div> <!-- Closes settings-popup-body cleanly -->
    </div> <!-- Closes settings-popup-box cleanly -->
</div> <!-- Closes settings-popup-overlay cleanly -->


<script src="../javascript-files/profile-settings-modal.js"></script>
