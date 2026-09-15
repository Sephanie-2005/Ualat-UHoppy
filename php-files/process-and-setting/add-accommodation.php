<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../default-browser/index.php?error=unauthorized");
    exit();
}

require_once 'database-connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $property_id        = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    $accomodation_name  = trim($_POST['accomodation_name'] ?? '');
    $accomodation_type  = trim($_POST['accomodation_type'] ?? '');
    $capacity           = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
    $available_slots    = filter_input(INPUT_POST, 'available_slots', FILTER_VALIDATE_INT);
    $monthly_rent       = filter_input(INPUT_POST, 'monthly_rent', FILTER_VALIDATE_FLOAT);
    $status             = trim($_POST['status'] ?? 'available');

    if (!$property_id || empty($accomodation_name) || empty($accomodation_type) || $capacity === false || $available_slots === false || $monthly_rent === false) {
        header("Location: ../owner-browser/o-property.php?error=missing_fields#section2");
        exit();
    }

    if ($available_slots > $capacity) {
        header("Location: ../owner-browser/o-property.php?error=invalid_slots#section2");
        exit();
    }

    $owner_id = $_SESSION['user_id'];
    $verify_query = "SELECT property_id FROM properties WHERE property_id = ? AND owner_id = ?";
    
    if ($verify_stmt = $conn->prepare($verify_query)) {
        $verify_stmt->bind_param("ii", $property_id, $owner_id);
        $verify_stmt->execute();
        $verify_stmt->store_result();
        
        if ($verify_stmt->num_rows === 0) {
            $verify_stmt->close();
            header("Location: ../owner-browser/o-property.php?error=forbidden_property#section2");
            exit();
        }
        $verify_stmt->close();
    } else {
        header("Location: ../owner-browser/o-property.php?error=db_error#section2");
        exit();
    }

    // MULTI-IMAGE VALIDATION AND UPLOAD SYSTEM (1-3 IMAGES)
    $uploaded_images_paths = [];

    if (isset($_FILES['accommodation_image']) && !empty($_FILES['accommodation_image']['name'][0])) {
        $file_count = count($_FILES['accommodation_image']['name']);

        // Strictly enforce minimum 1 and maximum 3 constraints
        if ($file_count < 1 || $file_count > 3) {
            header("Location: ../owner-browser/o-property.php?error=invalid_image_count#section2");
            exit();
        }

        $target_dir = "../../uploads/accommodation_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['accommodation_image']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file_name = $_FILES['accommodation_image']['name'][$i];
            $file_tmp  = $_FILES['accommodation_image']['tmp_name'][$i];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_extensions)) {
                $new_file_name = "accom_" . $property_id . "_" . uniqid() . "_" . $i . "." . $file_ext;
                $target_file_path = $target_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file_path)) {
                    $uploaded_images_paths[] = "uploads/accommodation_photos/" . $new_file_name;
                }
            }
        }
    }

    // Fail if there are 0 valid images uploaded
    if (count($uploaded_images_paths) < 1) {
        header("Location: ../owner-browser/o-property.php?error=min_1_image_required#section2");
        exit();
    }

    // Bundle our separate file paths securely into a database-ready JSON string payload
    $db_json_images = json_encode($uploaded_images_paths);

    $insert_query = "INSERT INTO accommodations (property_id, accommodation_name, accommodation_type, capacity, available_slots, monthly_rent, status, accommodation_image) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($insert_query)) {
        $stmt->bind_param("issiidss", $property_id, $accomodation_name, $accomodation_type, $capacity, $available_slots, $monthly_rent, $status, $db_json_images);
        
        if ($stmt->execute()) {
            header("Location: ../owner-browser/o-property.php#section2");
            exit();
        } else {
            header("Location: ../owner-browser/o-property.php?error=insert_failed#section2");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: ../owner-browser/o-property.php?error=statement_error#section2");
        exit();
    }
} else {
    header("Location: ../owner-browser/o-property.php");
    exit();
}
