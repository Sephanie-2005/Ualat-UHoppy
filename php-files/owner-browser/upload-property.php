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

    // Security Check: Verify they don't already have a property uploaded
    $checkQuery = "SELECT property_id FROM properties WHERE owner_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($checkQuery)) {
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            header("Location: o-property.php?error=already_has_property");
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
    <title>Upload Property Profile</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/owner/upload-property.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
</head>

<body>
    <div class="web-app">
        <div class="form-outer-wrapper">
            
            <!-- Top row layout containing title and top-right navigation back button -->
            <div class="form-navigation-header">
                <div class="title-area">
                    <h2>Register Property Profile</h2>
                    <p>Enter details to establish your single active platform estate</p>
                </div>
                <button type="button" class="back-nav-btn" onclick="window.location.href='o-property.php'">
                    Back to Dashboard
                </button>
            </div>

            <div class="form-container-card">
                <form action="../process-and-setting/add-property.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid-layout">
                        
                        <div class="form-group-item full-row-span">
                            <label for="property_name">Property Name</label>
                            <input type="text" id="property_name" name="property_name" required placeholder="e.g., Sunshine Heights Dormitory">
                        </div>

                        <div class="form-group-item">
                            <label for="property_type">Property Type</label>
                            <select id="property_type" name="property_type" required>
                                <option value="" disabled selected>Select Type</option>
                                <option value="Apartment">Apartment</option>
                                <option value="Dormitory">Dormitory</option>
                                <option value="Boarding House">Boarding House</option>
                                <option value="Commercial">Commercial</option>
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
                            <input type="text" id="address" name="address" required placeholder="e.g., Real Street, Dumaguete City, Negros Oriental">
                        </div>

                        <div class="form-group-item">
                            <label for="latitude">Latitude (Optional)</label>
                            <input type="number" id="latitude" name="latitude" step="any" placeholder="e.g., 9.3068">
                        </div>

                        <div class="form-group-item">
                            <label for="longitude">Longitude (Optional)</label>
                            <input type="number" id="longitude" name="longitude" step="any" placeholder="e.g., 123.3003">
                        </div>

                        <div class="form-group-item full-row-span">
                            <label for="description">Detailed Description</label>
                            <textarea id="description" name="description" rows="5" required placeholder="Describe amenities, near establishments, rules, etc..."></textarea>
                        </div>
                    </div>

                    <div class="form-action-footer">
                        <button type="submit" class="submit-action-btn">Publish Property Listing</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body>  
</html>
