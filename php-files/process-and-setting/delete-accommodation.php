<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../default-browser/index.php?error=unauthorized");
    exit();
}

require_once 'database-connection.php';

$owner_id         = $_SESSION['user_id'];
$accommodation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$accommodation_id) {
    header("Location: ../owner-browser/o-property.php?error=invalid_id#section2");
    exit();
}

// 1. Core Security Validation Check: pull image columns if access is authorized
$verifyQuery = "SELECT a.accommodation_id, a.accommodation_image FROM accommodations a 
                INNER JOIN properties p ON a.property_id = p.property_id 
                WHERE a.accommodation_id = ? AND p.owner_id = ? LIMIT 1";

$json_images = null;

if ($vStmt = $conn->prepare($verifyQuery)) {
    $vStmt->bind_param("ii", $accommodation_id, $owner_id);
    $vStmt->execute();
    $vResult = $vStmt->get_result();
    if ($vResult && $vResult->num_rows > 0) {
        $row = $vResult->fetch_assoc();
        $json_images = $row['accommodation_image'];
    } else {
        $vStmt->close();
        header("Location: ../owner-browser/o-property.php?error=unauthorized_deletion#section2");
        exit();
    }
    $vStmt->close();
}

// 2. Clear out old physical asset dependencies from server storage
if (!empty($json_images)) {
    $images_array = json_decode($json_images, true);
    if (is_array($images_array)) {
        foreach ($images_array as $file_path) {
            if (!empty($file_path) && file_exists("../../" . $file_path)) {
                unlink("../../" . $file_path);
            }
        }
    }
}

// 3. Execute the standard table row removal query action
$deleteQuery = "DELETE FROM accommodations WHERE accommodation_id = ?";

if ($stmt = $conn->prepare($deleteQuery)) {
    $stmt->bind_param("i", $accommodation_id);
    if ($stmt->execute()) {
        header("Location: ../owner-browser/o-property.php?success=accommodation_deleted#section2");
        exit();
    } else {
        header("Location: ../owner-browser/o-property.php?error=deletion_failed#section2");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ../owner-browser/o-property.php?error=statement_error#section2");
    exit();
}
