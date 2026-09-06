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
            $firstName  = trim($_POST['first_name']);
            $middleName = trim($_POST['middle_name']);
            $lastName   = trim($_POST['last_name']);
            $username   = trim($_POST['username']);
            $email      = trim($_POST['email']);
            $phone      = trim($_POST['phone_number']);

            $phoneRegex = "/^(?:\+63|0)?9\d{9}$/";

            if (empty($firstName) || empty($lastName) || empty($username) || empty($email)) {
                $modalError = "All core descriptive name fields are required.";
            } elseif (!empty($phone) && !preg_match($phoneRegex, $phone)) {
                $modalError = "Please enter a valid Philippine mobile phone number (e.g., 09123456789).";
            } else {
                $profilePicPath = null;
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $fileExtension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $newFileName = $role . "_" . $userId . "_" . time() . "." . $fileExtension;
                        $uploadFileDir = '../uploaded-images/';
                        if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0755, true); }
                        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFileDir . $newFileName)) {
                            $profilePicPath = 'uploaded-images/' . $newFileName;
                        }
                    }
                }

                if ($profilePicPath !== null) {
                    // Delete old profile picture before overwriting if it exists
                    $oldStmt = $conn->prepare("SELECT profile_picture FROM $table WHERE $idCol = ?");
                    $oldStmt->bind_param("i", $userId);
                    $oldStmt->execute();
                    $oldResult = $oldStmt->get_result()->fetch_assoc();
                    $oldStmt->close();
                    if ($oldResult && !empty($oldResult['profile_picture']) && file_exists('../' . $oldResult['profile_picture'])) {
                        if (strpos($oldResult['profile_picture'], 'default-avatar.png') === false) {
                            unlink('../' . $oldResult['profile_picture']);
                        }
                    }

                    $sql = "UPDATE $table SET first_name = ?, middle_name = ?, last_name = ?, username = ?, email = ?, phone_number = ?, profile_picture = ? WHERE $idCol = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssssi", $firstName, $middleName, $lastName, $username, $email, $phone, $profilePicPath, $userId);
                } else {
                    $sql = "UPDATE $table SET first_name = ?, middle_name = ?, last_name = ?, username = ?, email = ?, phone_number = ? WHERE $idCol = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssi", $firstName, $middleName, $lastName, $username, $email, $phone, $userId);
                }

                if ($stmt->execute()) { 
                    echo "<script>alert('Account profile modifications updated.'); window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "';</script>";
                    exit();
                } else { 
                    $modalError = ($conn->errno === 1062) ? "The Username or Email entered is already taken." : "Update parameters failed.";
                }
                $stmt->close();
            }
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
                } else { $modalError = "Current active password is invalid."; }
            }
        }

        if ($_POST['modal_settings_action'] === 'delete_account') {
            $confirmPass = $_POST['delete_password_confirm'];
            $stmt = $conn->prepare("SELECT password, profile_picture FROM $table WHERE $idCol = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($userRow && password_verify($confirmPass, $userRow['password'])) {
                if (!empty($userRow['profile_picture']) && file_exists('../' . str_replace('../', '', $userRow['profile_picture']))) {
                    if (strpos($userRow['profile_picture'], 'default-avatar.png') === false) {
                        unlink('../' . str_replace('../', '', $userRow['profile_picture']));
                    }
                }

                if ($role === 'owner') {
                    $imgQuery = "SELECT pi.image_url FROM property_images pi 
                                JOIN properties p ON pi.property_id = p.property_id 
                                WHERE p.owner_id = ?";
                    $imgStmt = $conn->prepare($imgQuery);
                    $imgStmt->bind_param("i", $userId);
                    $imgStmt->execute();
                    $imgResult = $imgStmt->get_result();
                    while ($imgRow = $imgResult->fetch_assoc()) {
                        $cleanImgPath = '../' . str_replace('../', '', $imgRow['image_url']);
                        if (!empty($imgRow['image_url']) && file_exists($cleanImgPath)) {
                            unlink($cleanImgPath); // Deletes room asset file completely from disk
                        }
                    }
                    $imgStmt->close();
                }

                $stmt = $conn->prepare("DELETE FROM $table WHERE $idCol = ?");
                $stmt->bind_param("i", $userId);

                if ($stmt->execute()) {
                        $stmt->close();
                        session_unset();
                        session_destroy();
                        echo "<script>window.location.href = '../default-browser/index.php';</script>";
                        exit();
                }
                $stmt->close();
            } else { $modalError = "Incorrect password confirmation."; }
        }
    }

    $userData = ['first_name' => '', 'middle_name' => '', 'last_name' => '', 'username' => '', 'email' => '', 'phone_number' => '', 'profile_picture' => ''];
    if ($userId > 0) {
        $table = ($role === 'owner') ? 'owners' : 'renters';
        $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';
        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, username, email, phone_number, profile_picture FROM $table WHERE $idCol = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $fetched = $stmt->get_result()->fetch_assoc();
        if ($fetched) { $userData = $fetched; }
        $stmt->close();
    }
    $modalAvatar = !empty($userData['profile_picture']) ? '../' . str_replace('../', '', $userData['profile_picture']) : '../../system-images/default-avatar.png';
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
        
            <div id="popup-account" class="popup-section visible">

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modal_settings_action" value="save_info">
                    
                    <div class="popup-avatar-row">
                        <img src="<?php echo $modalAvatar; ?>" id="modal-preview-avatar" alt="Avatar">
                        <label for="modal_profile_pic" class="modal-upload-btn">Upload Photo</label>
                        <input type="file" name="profile_pic" id="modal_profile_pic" accept="image/*" style="display: none;">
                    </div>

                    <div class="popup-field">
                        <label for="m_first_name">First Name</label>
                        <input type="text" name="first_name" id="m_first_name" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_middle_name">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" id="m_middle_name" value="<?php echo htmlspecialchars($userData['middle_name']); ?>">
                    </div>

                    <div class="popup-field">
                        <label for="m_last_name">Last Name</label>
                        <input type="text" name="last_name" id="m_last_name" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_username">Username</label>
                        <input type="text" name="username" id="m_username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_email">Email Address</label>
                     <input type="email" name="email" id="m_email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_phone">Phone Number</label>
                        <input type="text" name="phone_number" id="m_phone" placeholder="e.g. 09123456789" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                    </div>

                    <button type="submit" class="popup-submit-btn">Save Changes</button>
                     </form>
            </div>

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

        </div> 
    </div> 
</div> 


<script src="../../javascript-files/profile-settings-modal.js"></script>
