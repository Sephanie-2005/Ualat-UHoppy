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

    // Fetch the active property profile for modification
    $property = null;
    $query = "SELECT * FROM properties WHERE owner_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $property = $result->fetch_assoc();
        } else {
            $stmt->close();
            header("Location: o-property.php?error=no_property_to_edit");
            exit();
        }
        $stmt->close();
    }
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property Profile</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <!-- Reusing the upload property stylesheet to maintain cohesive layout branding -->
    <link rel="stylesheet" href="../../style/owner/upload-property.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
</head>

<body>
    <div class="web-app">
        <div class="form-outer-wrapper">
            
            <div class="form-navigation-header">
                <div class="title-area">
                    <h2>Edit Property Details</h2>
                    <p>Modify your registered real estate details below</p>
                </div>
                <button type="button" class="back-nav-btn" onclick="window.location.href='o-property.php'">
                    Back to Dashboard
                </button>
            </div>

            <div class="form-container-card">
                <form action="../process-and-setting/update-property.php" method="POST" enctype="multipart/form-data">
                    <!-- Hidden property ID hook for target processing validation -->
                    <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">

                    <div class="form-grid-layout">
                        
                        <div class="form-group-item full-row-span">
                            <label for="property_name">Property Name</label>
                            <input type="text" id="property_name" name="property_name" required 
                                   value="<?php echo htmlspecialchars($property['property_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="property_type">Property Type</label>
                            <select id="property_type" name="property_type" required>
                                <option value="Apartment" <?php echo ($property['property_type'] === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                                <option value="Dormitory" <?php echo ($property['property_type'] === 'Dormitory') ? 'selected' : ''; ?>>Dormitory</option>
                                <option value="Boarding House" <?php echo ($property['property_type'] === 'Boarding House') ? 'selected' : ''; ?>>Boarding House</option>
                                <option value="Commercial" <?php echo ($property['property_type'] === 'Commercial') ? 'selected' : ''; ?>>Commercial</option>
                            </select>
                        </div>

                        <div class="form-group-item">
                            <label for="property_images">Upload Property Images (Min 1, Max 3)</label>
                            <input type="file" id="property_images" name="property_images[]" multiple accept="image/*" required>
                        </div>

                        <script>
                        document.getElementById('property_images').addEventListener('change', function() {
                            if (this.files.length > 3) {
                                alert("You can only upload a maximum of 3 images for your property.");
                                this.value = ""; // Reset the input selection field
                            }
                        });
                        </script>

                        <div class="form-group-item full-row-span">
                            <label for="address">Complete Physical Address</label>
                            <input type="text" id="address" name="address" required 
                                   value="<?php echo htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="latitude">Latitude (Optional)</label>
                            <input type="number" id="latitude" name="latitude" step="any" 
                                   value="<?php echo htmlspecialchars($property['latitude'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item">
                            <label for="longitude">Longitude (Optional)</label>
                            <input type="number" id="longitude" name="longitude" step="any" 
                                   value="<?php echo htmlspecialchars($property['longitude'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group-item full-row-span">
                            <label for="description">Detailed Description</label>
                            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>

                    <div class="form-action-footer">
                        <button type="submit" class="submit-action-btn">Save Modifications</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body>  
</html>
