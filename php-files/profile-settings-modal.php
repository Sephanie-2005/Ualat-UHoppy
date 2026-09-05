<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if unauthenticated
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'database-connection.php';
$conn = new mysqli("localhost", "root", "", "uhoppy_db");
if ($conn->connect_error) {
    die("Database Connection Failure: " . $conn->connect_error);
}

$userId = intval($_SESSION['user_id']);
$role   = isset($_SESSION['role']) ? $_SESSION['role'] : 'renter'; 

$successMsg = "";
$errorMsg = "";

// --- 1. ACTION: PROCESS LOGOUT ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// --- FORM POST PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- 2. ACTION: UPDATE PROFILE / USERNAME (EMAIL) ---
    if (isset($_POST['update_profile'])) {
        $email       = trim($_POST['email']);
        $phoneNumber = trim($_POST['phone_number']);
        
        if (empty($email)) {
            $errorMsg = "Email (Username) cannot be left blank.";
        } else {
            $profilePicPath = null;
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                $fileName    = $_FILES['profile_pic']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = $role . "_" . $userId . "_" . time() . "." . $fileExtension;
                    $uploadFileDir = '../uploaded-images/';
                    if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0755, true); }
                    $dest_path = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $profilePicPath = $dest_path;
                    }
                }
            }

            $table = ($role === 'owner') ? 'owners' : 'renters';
            $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';

            if ($profilePicPath !== null) {
                $stmt = $conn->prepare("UPDATE $table SET email = ?, phone_number = ?, profile_picture = ? WHERE $idCol = ?");
                $stmt->bind_param("sssi", $email, $phoneNumber, $profilePicPath, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE $table SET email = ?, phone_number = ? WHERE $idCol = ?");
                $stmt->bind_param("ssi", $email, $phoneNumber, $userId);
            }

            if ($stmt->execute()) { $successMsg = "Account configurations updated successfully."; }
            else { $errorMsg = ($conn->errno === 1062) ? "Username / Email already exists." : "Update execution error."; }
            $stmt->close();
        }
    }

    // --- 3. ACTION: SECURITY PASSWORD UPDATE ---
    if (isset($_POST['update_password'])) {
        $currPass = $_POST['current_password'];
        $newPass  = $_POST['new_password'];
        $confPass = $_POST['confirm_password'];

        if ($newPass !== $confPass) {
            $errorMsg = "New passwords do not match match criteria.";
        } else {
            $table = ($role === 'owner') ? 'owners' : 'renters';
            $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';

            $stmt = $conn->prepare("SELECT password FROM $table WHERE $idCol = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $pwdRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (password_verify($currPass, $pwdRow['password'])) {
                $hashedNew = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE $idCol = ?");
                $stmt->bind_param("si", $hashedNew, $userId);
                if ($stmt->execute()) { $successMsg = "Password updated securely."; }
                $stmt->close();
            } else {
                $errorMsg = "Current tracking password confirmation verification invalid.";
            }
        }
    }

    // --- 4. ACTION: ACCOUNT DELETION TERMINATION ---
    if (isset($_POST['delete_account'])) {
        $confirmPass = $_POST['delete_password_confirm'];
        $table = ($role === 'owner') ? 'owners' : 'renters';
        $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';

        $stmt = $conn->prepare("SELECT password FROM $table WHERE $idCol = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $pwdRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (password_verify($confirmPass, $pwdRow['password'])) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE $idCol = ?");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                header("Location: index.php?status=account_deleted");
                exit();
            }
            $stmt->close();
        } else {
            $errorMsg = "Incorrect credentials. Account deletion aborted.";
        }
    }
}

// FETCH CURRENT PARAMS
$table = ($role === 'owner') ? 'owners' : 'renters';
$idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone_number, profile_picture FROM $table WHERE $idCol = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$currentAvatar = !empty($userData['profile_picture']) ? htmlspecialchars($userData['profile_picture']) : '../system-images/default-avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Engine - UHoppy</title>
    <link rel="stylesheet" href="../style/web-app.css">
    <link rel="stylesheet" href="../style/header-style.css">
    <link rel="stylesheet" href="../style/profile-settings.css">
</head>
<body>
    <div class="web-app">
        <header>
            <img src="../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
            <button class="home_button" onclick="window.location.href='index.php'">HOME</button>
            <button class="listings_button" onclick="window.location.href='listings.php'">LISTINGS</button>
            <div class="profile-nav-wrapper">
                <span class="header-user-name"><?php echo htmlspecialchars($userData['first_name']); ?></span>
                <img src="<?php echo $currentAvatar; ?>" alt="Avatar" class="header-profile-pic">
            </div>
        </header>

        <main class="settings-layout-container">
            <!-- Sidebar Panel Options Navigation Links -->
            <div class="settings-sidebar">
                <div class="sidebar-user-card">
                    <img src="<?php echo $currentAvatar; ?>" class="sidebar-avatar" alt="Avatar">
                    <h3><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h3>
                    <span class="role-badge"><?php echo strtoupper($role); ?></span>
                </div>
                <button class="sidebar-tab active" onclick="switchSection('account-sec', this)">⚙️ Account Information</button>
                <button class="sidebar-tab" onclick="switchSection('security-sec', this)">🔒 Password Security</button>
                <button class="sidebar-tab danger-tab" onclick="switchSection('danger-sec', this)">⚠️ Danger Zone</button>
                <a href="profile-settings.php?action=logout" class="sidebar-logout-link">🚪 Log Out Account</a>
            </div>

            <!-- Configuration Options Forms Body Container Panels -->
            <div class="settings-main-panel">
                <h2>System Settings Panel</h2>
                
                <?php if (!empty($successMsg)): ?><div class="alert alert-success"><?php echo $successMsg; ?></div><?php endif; ?>
                <?php if (!empty($errorMsg)): ?><div class="alert alert-danger"><?php echo $errorMsg; ?></div><?php endif; ?>

                <!-- SECTION 1: ACCOUNT DETAIL INFO -->
                <div id="account-sec" class="settings-section visible">
                    <h3>Account Parameters</h3>
                    <form action="profile-settings.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="avatar-row">
                            <img src="<?php echo $currentAvatar; ?>" id="preview-box-node" class="form-avatar-circle" alt="Avatar">
                            <label for="profile_pic" class="upload-trigger-btn">Change Profile Photo</label>
                            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" style="display: none;">
                        </div>
                        <div class="field-item">
                            <label>Full Structural Name (Read-Only)</label>
                            <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>" readonly>
                        </div>
                        <div class="field-item">
                            <label for="email">Username / Linked Email (Editable)</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                        </div>
                        <div class="field-item">
                            <label for="phone_number">Contact Phone Number</label>