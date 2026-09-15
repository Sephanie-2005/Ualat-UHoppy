<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../owner-browser/o-property.php");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../default-browser/index.php?error=unauthorized");
    exit();
}

require_once 'database-connection.php';

$owner_id         = $_SESSION['user_id'];
$accommodation_id = filter_input(INPUT_POST, 'accommodation_id', FILTER_VALIDATE_INT);
$accom_name       = trim($_POST['accommodation_name'] ?? '');
$accom_type       = trim($_POST['accommodation_type'] ?? '');
$capacity         = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
$available_slots  = filter_input(INPUT_POST, 'available_slots', FILTER_VALIDATE_INT);
$monthly_rent     = filter_input(INPUT_POST, 'monthly_rent', FILTER_VALIDATE_FLOAT);
$status           = trim($_POST['status'] ?? 'available');

if (!$accommodation_id || empty($accom_name) || empty($accom_type) || $capacity === false || $available_slots === false || $monthly_rent === false) {
    header("Location: ../owner-browser/edit-accommodation.php?id=" . $accommodation_id . "&error=missing_fields");
    exit();
}

if ($available_slots > $capacity) {
    header("Location: ../owner-browser/edit-accommodation.php?id=" . $accommodation_id . "&error=invalid_slots");
    exit();
}

$verifyQuery = "SELECT a.accommodation_id, a.accommodation_image FROM accommodations a 
                INNER JOIN properties p ON a.property_id = p.property_id 
                WHERE a.accommodation_id = ? AND p.owner_id = ? LIMIT 1";

$current_json_images = null;
if ($vStmt = $conn->prepare($verifyQuery)) {
    $vStmt->bind_param("ii", $accommodation_id, $owner_id);
    $vStmt->execute();
    $vResult = $vStmt->get_result();
    if ($vResult && $vResult->num_rows > 0) {
        $row = $vResult->fetch_assoc();
        $current_json_images = $row['accommodation_image'];
    } else {
        $vStmt->close();
        header("Location: ../owner-browser/o-property.php?error=access_denied");
        exit();
    }
    $vStmt->close();
}

// Default backup set to fallback on current values
$db_final_image_payload = $current_json_images; 

// 2. Process multi-file batch updates if selected (Min 1, Max 3 rule)
if (isset($_FILES['accommodation_image']) && !empty($_FILES['accommodation_image']['name'][0])) {
    $file_count = count($_FILES['accommodation_image']['name']);

    if ($file_count < 1 || $file_count > 3) {
        header("Location: ../owner-browser/edit-accommodation.php?id=" . $accommodation_id . "&error=invalid_image_count");
        exit();
    }

    $target_dir = "../../uploads/accommodation_photos/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $uploaded_images_paths = [];

    for ($i = 0; $i < $file_count; $i++) {
        if ($_FILES['accommodation_image']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $file_name = $_FILES['accommodation_image']['name'][$i];
        $file_tmp  = $_FILES['accommodation_image']['tmp_name'][$i];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = "accom_upd_" . $accommodation_id . "_" . uniqid() . "_" . $i . "." . $file_ext;
            $target_file_path = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file_path)) {
                $uploaded_images_paths[] = "uploads/accommodation_photos/" . $new_file_name;
            }
        }
    }

    // Clean up past database files out of storage if replacement succeeds
    if (count($uploaded_images_paths) > 0) {
        $old_images_array = json_decode($current_json_images, true);
        if (is_array($old_images_array)) {
            foreach ($old_images_array as $old_img_path) {
                if (!empty($old_img_path) && file_exists("../../" . $old_img_path)) {
                    unlink("../../" . $old_img_path);
                }
            }
        }
        // Swap file tracking targets payload onto freshly updated JSON string array
        $db_final_image_payload = json_encode($uploaded_images_paths);
    }
}

// 3. Save updates into MySQL
$updateQuery = "UPDATE accommodations SET accommodation_name = ?, accommodation_type = ?, capacity = ?, available_slots = ?, monthly_rent = ?, status = ?, accommodation_image = ? 
                WHERE accommodation_id = ?";

if ($stmt = $conn->prepare($updateQuery)) {
    // FIXED: Changed the 6th character placeholder to 's' for your status variable string
    $stmt->bind_param("ssiisssi", $accom_name, $accom_type, $capacity, $available_slots, $monthly_rent, $status, $db_final_image_payload, $accommodation_id);
    
    if ($stmt->execute()) {
        header("Location: ../owner-browser/o-property.php#section2");
        exit();
    } else {
        header("Location: ../owner-browser/edit-accommodation.php?id=" . $accommodation_id . "&error=update_failed");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ../owner-browser/edit-accommodation.php?id=" . $accommodation_id . "&error=statement_error");
    exit();
}
