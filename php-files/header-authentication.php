<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn)) {
    require_once 'database-connection.php';
    $conn = new mysqli("localhost", "root", "", "uhoppy_db");
}

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
$profilePic = '../system-images/default-avatar.png'; 
$userFirstName = ''; // Variable to store the first name

if ($isLoggedIn) {
    $userId = intval($_SESSION['user_id']);
    $role   = isset($_SESSION['role']) ? $_SESSION['role'] : 'renter'; 

    if ($role === 'owner') {
        // Fetch first name and profile picture from owners table
        $stmt = $conn->prepare("SELECT first_name, profile_picture FROM owners WHERE owner_id = ?");
    } else {
        // Fetch first name and profile picture from renters table
        $stmt = $conn->prepare("SELECT first_name, profile_picture FROM renters WHERE renter_id = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        
        if ($userResult) {
            $userFirstName = htmlspecialchars($userResult['first_name']);
            if (!empty($userResult['profile_picture'])) {
                $profilePic = htmlspecialchars($userResult['profile_picture']);
            }
        }
        $stmt->close();
    }
}
?>