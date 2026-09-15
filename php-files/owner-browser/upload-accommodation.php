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

    // Retrieve the single property details to automatically assign the incoming accommodation
    $property_id = 0;
    $property_name = '';

    $propQuery = "SELECT property_id, property_name FROM properties WHERE owner_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($propQuery)) {
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $property_id = $row['property_id'];
            $property_name = $row['property_name'];
        } else {
            $stmt->close();
            header("Location: o-property.php?error=no_property_found");
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
    <title>Upload Accommodation</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/owner/upload-accommodation.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
</head>

<body>
    <div class="web-app">
        <div class="form-outer-wrapper">
            
            <!-- Top navigation line holding page labels and back actionable item -->
            <div class="form-navigation-header">
                <div class="title-area">
                    <h2>Add Accommodation Unit</h2>
                    <p>Register a specific room, bedspace, or rental unit configurations</p>
                </div>
                <button type="button" class="back-nav-btn" onclick="window.location.href='o-property.php#section2'">
                    Back to Dashboard
                </button>
            </div>

            <div class="form-container-card">
                <!-- FIX 1: Added enctype="multipart/form-data" below so files can be sent -->
                <form action="../process-and-setting/add-accommodation.php" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

                    <div class="form-grid-layout">
                        
                        <div class="form-group-item">
                            <label>Target Estate Profile</label>
                            <input type="text" value="<?php echo htmlspecialchars($property_name, ENT_QUOTES, 'UTF-8'); ?>" disabled class="read-only-display">
                        </div>

                        <div class="form-group-item">
                            <label for="accomodation_name">Room or Unit Designation</label>
                            <input type="text" id="accomodation_name" name="accomodation_name" required placeholder="e.g., Room 301-B">
                        </div>

                        <div class="form-group-item">
                            <label for="accomodation_type">Accommodation Classification</label>
                            <select id="accomodation_type" name="accomodation_type" required>
                                <option value="" disabled selected>Select Classification</option>
                                <option value="Single Room">Single Room</option>
                                <option value="Shared Room">Shared Room</option>
                                <option value="Studio Unit">Studio Unit</option>
                                <option value="Bedspace">Bedspace</option>
                            </select>
                        </div>

                        <div class="form-group-item">
                            <label for="monthly_rent">Monthly Rent Rate (PHP)</label>
                            <input type="number" id="monthly_rent" name="monthly_rent" min="0" step="0.01" required placeholder="0.00">
                        </div>

                        <div class="form-group-item">
                            <label for="capacity">Total Capacity Limit</label>
                            <input type="number" id="capacity" name="capacity" min="1" required placeholder="Max persons allowable">
                        </div>

                        <div class="form-group-item">
                            <label for="available_slots">Initial Available Slots</label>
                            <input type="number" id="available_slots" name="available_slots" min="0" required placeholder="Current active open slots">
                        </div>

                        <div class="form-group-item full-row-span">
                            <label for="status">Initial Operational Status</label>
                            <select id="status" name="status" required>
                                <option value="available" selected>Available</option>
                                <option value="fully_occupied">Fully Occupied</option>
                                <option value="maintenance">Under Maintenance</option>
                            </select>
                        </div>

                        <!-- FIX 2: Corrected the double "<<" typo on the div opening tag below -->
                        <div class="form-group-item full-row-span">
                            <label for="accommodation_image">Upload Accommodation Photos (A required minimum of 2 and maximum of 3 images)</label>
                            <input type="file" id="accommodation_image" name="accommodation_image[]" multiple accept="image/*" required>
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

                    <div class="form-action-footer">
                        <button type="submit" class="submit-action-btn">Publish Accommodation Unit</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body>  
</html>
