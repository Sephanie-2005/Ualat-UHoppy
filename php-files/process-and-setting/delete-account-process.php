<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Gate: Immediately redirect unauthenticated guests away
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'database-connection.php';

// Direct database link connection configuration instance
$conn = new mysqli("localhost", "root", "", "uhoppy_db");
if ($conn->connect_error) {
    die("Database Connection Failure: " . $conn->connect_error);
}

$userId = intval($_SESSION['user_id']);
$role   = isset($_SESSION['role']) ? $_SESSION['role'] : 'renter'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modal_settings_action']) && $_POST['modal_settings_action'] === 'delete_account') {
    $confirmPass = $_POST['delete_password_confirm'];
    
    // Set table mapping properties dynamically based on the session role
    $table = ($role === 'owner') ? 'owners' : 'renters';
    $idCol = ($role === 'owner') ? 'owner_id' : 'renter_id';

    // 1. Query the database to retrieve the hashed password for identity validation
    $stmt = $conn->prepare("SELECT password FROM $table WHERE $idCol = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $pwdRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pwdRow && password_verify($confirmPass, $pwdRow['password'])) {
        // 2. Erase the account row entry from your tables (Natively wipes properties/rentals due to ON DELETE CASCADE)
        $stmt = $conn->prepare("DELETE FROM $table WHERE $idCol = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // 3. Clear session tracking completely
            session_unset();
            session_destroy();
            
            // 4. Force a clean client-side JavaScript redirect back out to index.php
            echo "<script>
                alert('Your account has been permanently deleted.');
                window.top.location.href = 'index.php?status=deleted';
            </script>";
            exit();
        }
        $stmt->close();
    } else {
        // Fallback: If password validation verification fails, redirect them back to their home panel with a notice alert box
        $fallbackPage = ($role === 'owner') ? 'owner-homepage.php' : 'renter-homepage.php';
        echo "<script>
            alert('Incorrect password. Account deletion aborted.');
            window.location.href = '" . $fallbackPage . "';
        </script>";
        exit();
    }
} else {
    // Restrict direct URL structural manipulation attempts from guests
    header("Location: index.php");
    exit();
}
?>
