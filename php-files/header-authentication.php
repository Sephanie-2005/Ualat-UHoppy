<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database-connection.php';

if (!isset($conn)) {
    if (isset($connect)) { $conn = $connect; }
    elseif (isset($con)) { $conn = $con; }
    elseif (isset($db)) { $conn = $db; }
    elseif (isset($link)) { $conn = $link; }
    elseif (isset($uhoppy_db)) { $conn = $uhoppy_db; }
    else {
        $conn = new mysqli("localhost", "root", "", "uhoppy_db");
    }
}

if ($conn->connect_error) {
    die("Database Connection Failure: " . $conn->connect_error);
}

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
$profilePic = '../system-images/default-avatar.png'; 
$userFirstName = ''; 
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'renter';

if ($isLoggedIn) {
    $userId = intval($_SESSION['user_id']);
    $table  = ($role === 'owner') ? 'owners' : 'renters';
    $idCol  = ($role === 'owner') ? 'owner_id' : 'renter_id';

    $stmt = $conn->prepare("SELECT first_name, profile_picture FROM $table WHERE $idCol = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        
        if ($userResult) {
            $userFirstName = htmlspecialchars($userResult['first_name']);
            if (!empty($userResult['profile_picture'])) {
                $cleanPath = str_replace('../', '', $userResult['profile_picture']);
                $profilePic = '../' . htmlspecialchars($cleanPath);
            }
        }
        $stmt->close();
    }
}

// Handle global logout initialization actions
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
