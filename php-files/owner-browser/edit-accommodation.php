<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../process-and-setting/database-connection.php';  
  
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') { 
        header("Location: ../default-browser/index.php?error=unauthorized");
        exit();
    }

    $owner_id = $_SESSION['user_id'];
    $accommodation_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$accommodation_id) {
        header("Location: o-property.php?error=invalid_accommodation");
        exit();
    }

    $accommodation = null;
    $query = "SELECT a.*, p.property_name FROM accommodations a 
              INNER JOIN properties p ON a.property_id = p.property_id 
              WHERE a.accommodation_id = ? AND p.owner_id = ? LIMIT 1";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $accommodation_id, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $accommodation = $result->fetch_assoc();
        } else {
            $stmt->close();
            header("Location: o-property.php?error=accommodation_not_found");
            exit();
        }
        $stmt->close();
    }

    // This decodes your database images so your HTML can show them below
    $existing_images = [];
    if (!empty($accommodation['accommodation_image'])) {
        $existing_images = json_decode($accommodation['accommodation_image'], true);
    }
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Accommodation</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/owner/upload-accommodation.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
</head>

<body>
    <div class="web-app">
        <div class="form-outer-wrapper">
            
            <div class="form-navigation-header">
                <div class="title-area">
                    <h2>Edit Accommodation Unit</h2>
                    <p>Modify room features, update pricing, or adjust active available slots</p>
                </div>
                <button type="button" class="back-nav-btn" onclick="window.location.href='o-property.php#section2'">
                    Back to Dashboard
                </button>
            </div>

            <div class="form-container-card">
                <form action="../process-and-setting/update-accommodation.php" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="accommodation_id" value="<?php echo $accommodation['accommodation_id']; ?>">

                    <div class="form-grid-layout">
                        
                        <div class="form-group-item">
                            <label>Target Estate Profile</label>
                            <input type="text" value="<?php echo htmlspecialchars($accommodation['property_name'], ENT_QUOTES, 'UTF-8'); ?>" disabled class="read-only-display">
                        </div>

                        <div class="form-group-item">
                            <label for="accommodation_name">Room or Unit Designation</label>
                            <input type="text" id="accommodation_name" name="accommodation_name" required 
                                   value="<?php echo htmlspecialchars($accommodation['accommodation_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="accommodation_type">Accommodation Classification</label>
                            <select id="accommodation_type" name="accommodation_type" required>
                                <option value="Single Room" <?php echo ($accommodation['accommodation_type'] === 'Single Room') ? 'selected' : ''; ?>>Single Room</option>
                                <option value="Shared Room" <?php echo ($accommodation['accommodation_type'] === 'Shared Room') ? 'selected' : ''; ?>>Shared Room</option>
                                <option value="Studio Unit" <?php echo ($accommodation['accommodation_type'] === 'Studio Unit') ? 'selected' : ''; ?>>Studio Unit</option>
                                <option value="Bedspace" <?php echo ($accommodation['accommodation_type'] === 'Bedspace') ? 'selected' : ''; ?>>Bedspace</option>
                            </select>
                        </div>

                        <div class="form-group-item">
                            <label for="monthly_rent">Monthly Rent Rate (PHP)</label>
                            <input type="number" id="monthly_rent" name="monthly_rent" min="0" step="0.01" required 
                                   value="<?php echo htmlspecialchars($accommodation['monthly_rent'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="capacity">Total Capacity Limit</label>
                            <input type="number" id="capacity" name="capacity" min="1" required 
                                   value="<?php echo htmlspecialchars($accommodation['capacity'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="available_slots">Available Slots</label>
                            <input type="number" id="available_slots" name="available_slots" min="0" required 
                                   value="<?php echo htmlspecialchars($accommodation['available_slots'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item full-row-span">
                            <label for="status">Operational Status</label>
                            <select id="status" name="status" required>
                                <option value="available" <?php echo ($accommodation['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="fully_occupied" <?php echo ($accommodation['status'] === 'fully_occupied') ? 'selected' : ''; ?>>Fully Occupied</option>
                                <option value="maintenance" <?php echo ($accommodation['status'] === 'maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                            </select>
                        </div>

                        <!-- I FIXED THIS AREA BELOW: Removed 'required' and added an image viewer -->
                        <div class="form-group-item full-row-span">
                            <label for="accommodation_image">Replace Accommodation Photos (Leave empty to keep existing images. Max 3)</label>
                            <input type="file" id="accommodation_image" name="accommodation_image[]" multiple accept="image/*">
                            
                            <?php if (is_array($existing_images) && !empty($existing_images)): ?>
                                <div style="display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap;">
                                    <p style="width:100%; font-size: 0.85rem; margin: 0; color: #666; font-weight: bold;">Current Photos:</p>
                                    <?php foreach ($existing_images as $img_path): ?>
                                        <img src="../../<?php echo htmlspecialchars($img_path, ENT_QUOTES, 'UTF-8'); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #ccc;" alt="Room Photo">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <script>
                        document.getElementById('accommodation_image').addEventListener('change', function() {
                            if (this.files.length > 3) {
                                alert("You can only upload a maximum of 3 images for this accommodation unit.");
                                this.value = ""; 
                            }
                        });
                        </script>

                    </div>

                    <div class="form-action-footer split-buttons-row">
                        <button type="submit" class="submit-action-btn">Save Changes</button>
                        <button type="button" class="delete-action-btn" onclick="confirmUnitDeletion(<?php echo $accommodation['accommodation_id']; ?>)">
                            Delete Accommodation
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <script>
        function confirmUnitDeletion(accommodationId) {
            if (confirm("Are you sure you want to delete this accommodation unit permanently?")) {
                window.location.href = "../process-and-setting/delete-accommodation.php?id=" + accommodationId;
            }
        }
    </script>
</body>
</html>