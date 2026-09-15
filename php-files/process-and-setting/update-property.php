<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../owner/o-property.php");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../default-browser/index.php?error=unauthorized");
    exit();
}

require_once 'database-connection.php';

$owner_id    = $_SESSION['user_id'];
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$prop_name   = trim($_POST['property_name'] ?? '');
$prop_type   = trim($_POST['property_type'] ?? '');
$address     = trim($_POST['address'] ?? '');
$description = trim($_POST['description'] ?? '');

$latitude    = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
$longitude   = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
$latitude    = ($latitude !== false && $latitude !== null) ? $latitude : null;
$longitude   = ($longitude !== false && $longitude !== null) ? $longitude : null;

if (!$property_id || empty($prop_name) || empty($prop_type) || empty($address)) {
    header("Location: ../owner/edit-property.php?error=missing_fields");
    exit();
}

// Security verification constraint: Confirm ownership access rights
$verifyQuery = "SELECT property_id FROM properties WHERE property_id = ? AND owner_id = ? LIMIT 1";
if ($vStmt = $conn->prepare($verifyQuery)) {
    $vStmt->bind_param("ii", $property_id, $owner_id);
    $vStmt->execute();
    $vStmt->store_result();
    if ($vStmt->num_rows === 0) {
        $vStmt->close();
        header("Location: ../owner/o-property.php?error=access_denied");
        exit();
    }
    $vStmt->close();
}

// Transaction execution layer block logic
$conn->begin_transaction();

try {
    $updateQuery = "UPDATE properties SET property_name = ?, property_type = ?, description = ?, address = ?, latitude = ?, longitude = ? 
                    WHERE property_id = ? AND owner_id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssddii", $prop_name, $prop_type, $description, $address, $latitude, $longitude, $property_id, $owner_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Core row data update operation failure.");
    }
    $stmt->close();

    // Process new files if attached
    if (isset($_FILES['property_images']) && !empty($_FILES['property_images']['name'][0])) {
        $target_dir = "../../uploads/property_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $file_count = count($_FILES['property_images']['name']);

        $imgQuery = "INSERT INTO property_images (property_id, image_url) VALUES (?, ?)";
        $img_stmt = $conn->prepare($imgQuery);

        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['property_images']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file_name = $_FILES['property_images']['name'][$i];
            $file_tmp  = $_FILES['property_images']['tmp_name'][$i];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_extensions)) {
                $new_file_name = "prop_" . $property_id . "_" . uniqid() . "." . $file_ext;
                $target_file_path = $target_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file_path)) {
                    $db_save_path = "uploads/property_photos/" . $new_file_name;
                    $img_stmt->bind_param("is", $property_id, $db_save_path);
                    if (!$img_stmt->execute()) {
                        throw new Exception("Image persistence track mapping initialization failure.");
                    }
                }
            }
        }
        $img_stmt->close();
    }

    $conn->commit();
    header("Location: ../owner/o-property.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../owner/edit-property.php?error=update_failed");
    exit();
}
