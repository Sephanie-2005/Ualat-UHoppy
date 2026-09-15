<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database-connection.php'; 

function redirectBack($statusType, $message, $tab = 'popup-account') {
    $_SESSION['modal_message'] = $message;
    $_SESSION['modal_status'] = $statusType;
    $_SESSION['modal_active_tab'] = $tab;
    
    // Fallback path to return back to the profile page
    $referer = $_SERVER['HTTP_REFERER'] ?? '../owner-browser/profile.php'; 
    header("Location: " . $referer);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['modal_settings_action'])) {
    header("Location: ../default-browser/index.php");
    exit();
}

$userId   = $_SESSION['user_id'] ?? null; 
$userRole = $_SESSION['role'] ?? null; 
$action   = $_POST['modal_settings_action'];

if (!$userId || !$userRole) {
    redirectBack('error', 'Session expired. Please log in again.');
}

if ($userRole === 'owner') {
    $table = 'owners';
    $idColumn = 'owner_id';
} elseif ($userRole === 'renter') {
    $table = 'renters';
    $idColumn = 'renter_id';
} else {
    redirectBack('error', 'Invalid account role detected.');
}


if ($action === 'save_info') {
    $firstName   = trim($_POST['first_name']);
    $middleName  = trim($_POST['middle_name'] ?? '');
    $lastName    = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $deleteAvatarFlag = $_POST['delete_avatar_flag'] ?? '0';

    $profilePicPath = null;
    $shouldUpdatePic = false;

    // FIX: Explicitly evaluate the removal flag string value first
    if ($deleteAvatarFlag === '1') {
        $profilePicPath = 'system-images/default_profile.png';
        $shouldUpdatePic = true;
    } 
    // FIX: Only handle file upload if the user actually chose a file (error code 0 means OK)
    elseif (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = '../../uploads/';
            $newFileName = $userRole . '_' . $userId . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profilePicPath = 'uploads/' . $newFileName;
                $shouldUpdatePic = true;
            }
        } else {
            redirectBack('error', 'Invalid file type. Only JPG, PNG, and WEBP are allowed.', 'popup-account');
        }
    }

    // Run database statement execution
    if ($shouldUpdatePic) {
        $query = "UPDATE $table SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone_number = ?, profile_picture = ? WHERE $idColumn = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $firstName, $middleName, $lastName, $email, $phoneNumber, $profilePicPath, $userId);
    } else {
        $query = "UPDATE $table SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone_number = ? WHERE $idColumn = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $firstName, $middleName, $lastName, $email, $phoneNumber, $userId);
    }

    if ($stmt->execute()) {
        if ($shouldUpdatePic) {
            $_SESSION['profile_picture'] = $profilePicPath;
        }
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;

        redirectBack('success', 'Profile information updated successfully!', 'popup-account');
    } else {
        redirectBack('error', 'Database error: Unable to update details.', 'popup-account');
    }
}

if ($action === 'change_password') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    $query = "SELECT password FROM $table WHERE $idColumn = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($currentPassword, $result['password'])) {
        
        if (strlen($newPassword) >= 8 && preg_match('/[A-Z]/', $newPassword) && preg_match('/[a-z]/', $newPassword) && preg_match('/\d/', $newPassword) && preg_match('/[\W_]/', $newPassword)) {
            
            $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $updateQuery = "UPDATE $table SET password = ? WHERE $idColumn = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $newHashedPassword, $userId);
            
            if ($updateStmt->execute()) {
                redirectBack('success', 'Password updated successfully!', 'popup-security');
            } else {
                redirectBack('error', 'Failed to save the new password.', 'popup-security');
            }
        } else {
            redirectBack('error', 'New password does not meet the strong password rules.', 'popup-security');
        }
    } else {
        redirectBack('error', 'Your current password was entered incorrectly.', 'popup-security');
    }
}

if ($action === 'delete_account') {
    $confirmPassword = $_POST['delete_password_confirm'];
    $query = "SELECT password FROM $table WHERE $idColumn = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($confirmPassword, $result['password'])) {
        
        $deleteQuery = "DELETE FROM $table WHERE $idColumn = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $userId);
        
        if ($deleteStmt->execute()) {

            session_unset();
            session_destroy();
            
            header("Location: logout.php");
            exit();
        } else {
            redirectBack('error', 'Database error: Could not complete account deletion.', 'popup-danger');
        }
    } else {
        redirectBack('error', 'Incorrect password. Account deletion aborted.', 'popup-danger');
    }
}
?>