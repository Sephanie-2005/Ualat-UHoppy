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
    $renters = [];

    $query = "SELECT DISTINCT r.renter_id, r.first_name, r.last_name, r.email, r.phone_number, p.property_name, a.accommodation_name 
              FROM renters r 
              INNER JOIN rentals rt ON r.renter_id = rt.renter_id 
              INNER JOIN accommodations a ON rt.accommodation_id = a.accommodation_id 
              INNER JOIN properties p ON a.property_id = p.property_id 
              WHERE p.owner_id = ? AND rt.rental_status = 'active'"; 

    if ($stmt = $conn->prepare($query)) { 
        $stmt->bind_param("i", $owner_id); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 
        while ($row = $result->fetch_assoc()) { 
            $renters[] = $row; 
        } 
        $stmt->close(); 
    } 

    $userData = []; 
    
    $ownerQuery = "SELECT first_name, middle_name, last_name, email, phone_number, profile_picture FROM owners WHERE owner_id = ?";
    if ($ownerStmt = $conn->prepare($ownerQuery)) {
        $ownerStmt->bind_param("i", $owner_id);
        $ownerStmt->execute();
        $ownerResult = $ownerStmt->get_result();
        
        if ($ownerResult && $ownerResult->num_rows > 0) {
            $userData = $ownerResult->fetch_assoc();
        } else {
        
            $userData = [
                'first_name'      => $_SESSION['first_name'] ?? '',
                'middle_name'     => $_SESSION['middle_name'] ?? '',
                'last_name'       => $_SESSION['last_name'] ?? '',
                'email'           => $_SESSION['email'] ?? '',
                'phone_number'    => $_SESSION['phone_number'] ?? '',
                'profile_picture' => $_SESSION['profile_picture'] ?? ''
            ];
        }
        $ownerStmt->close();
    }

    $generatedUsername = '';
    if (!empty($userData['first_name']) && !empty($userData['last_name'])) {
        $firstLetter = strtolower(substr($userData['first_name'], 0, 1));
        $cleanLastName = strtolower(str_replace(' ', '', $userData['last_name'])); 
        $generatedUsername = $firstLetter . '.' . $cleanLastName;
    }

    $dbPicPath = '../../' . ($userData['profile_picture'] ?? '');
    if (!empty($userData['profile_picture']) && file_exists($dbPicPath)) {
        $profilePic = $dbPicPath;
    } else {
        $profilePic = '../../system-images/default-profile.png';
    }
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Property Management</title>
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/default/header-style.css">
        <link rel="stylesheet" href="../../style/default/footer-style.css">
        <link rel="stylesheet" href="../../style/owner/o-property.css">
        <link rel="stylesheet" href="../../style/default/background-shapes.css">
        <link rel="stylesheet" href="../../style/profile-settings.css">
        <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <header> 
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                
                <button id="home-btn" class="home_button" onclick="window.location.href='owner-homepage.php'">HOME</button>
                <button id="property-btn" class="property_button active">PROPERTY</button>
                <button id="messages-btn" class="messages_button" onclick="window.location.href='o-messages.php'">MESSAGES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='o-about-us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='o-contact.php'">CONTACT</button>
                 
                <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
                </div>
                
            </header>

            <main class="Section_1">
                <div class="property-management-container">
                    <!-- Navigation Tabs -->
                    <div class="management-tabs">
                        <button class="tab-btn active" onclick="switchTab('manage-view')">Manage Properties</button>
                        <button class="tab-btn" onclick="switchTab('upload-view')">Upload New Property</button>
                    </div>

                    <!-- TAB 1: MANAGE PROPERTIES VIEW -->
                    <div id="manage-view" class="tab-content active-content">
                        <h2>Your Listed Properties</h2>
                        <div class="property-grid">
                            <!-- Fetch and loop existing properties from DB here -->
                            <!-- Example Property Card Item -->
                            <div class="property-card">
                                <div class="property-image-wrapper">
                                    <img src="../../system-images/default-property.png" alt="Property Image">
                                </div>
                                <div class="property-details">
                                    <h3>Sample Property Name</h3>
                                    <p class="location">Location: Dumaguete City</p>
                                    <p class="status-badge active-status">Active</p>
                                    
                                    <div class="property-actions">
                                        <button class="action-btn edit-btn">Edit</button>
                                        <button class="action-btn delete-btn">Delete</button>
                                    </div>
                                </div>
                            </div>
                            <!-- End Example Card -->
                        </div>
                    </div>

                    <!-- TAB 2: UPLOAD PROPERTY VIEW -->
                    <div id="upload-view" class="tab-content">
                        <h2>Register New Property</h2>
                        <form action="../process-and-setting/upload-property-process.php" method="POST" enctype="multipart/form-data" class="upload-form">
                            
                            <!-- Section A: Core Property Details -->
                            <fieldset>
                                <legend>Core Details</legend>
                                <div class="form-group">
                                    <label for="property_name">Property Name *</label>
                                    <input type="text" id="property_name" name="property_name" required placeholder="e.g., Sunset Heights Dormitory">
                                </div>

                                <div class="form-group">
                                    <label for="property_address">Full Address *</label>
                                    <input type="text" id="property_address" name="property_address" required placeholder="Street, Barangay, City, Province">
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="property_type">Property Type *</label>
                                        <select id="property_type" name="property_type" required>
                                            <option value="" disabled selected>Select Type</option>
                                            <option value="dormitory">Dormitory</option>
                                            <option value="apartment">Apartment</option>
                                            <option value="house">House</option>
                                            <option value="room">Single Room</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="property_image">Primary Cover Image *</label>
                                        <input type="file" id="property_image" name="property_image" accept="image/*" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="property_description">Description</label>
                                    <textarea id="property_description" name="property_description" rows="4" placeholder="Describe rules, landmarks nearby, security features..."></textarea>
                                </div>
                            </fieldset>

                            <!-- Section B: Accommodations Units Generator -->
                            <fieldset>
                                <legend>Accommodation Units / Sub-rooms</legend>
                                <p class="helper-text">Add the individual rooms, floors, or studio categories available in this property.</p>
                                
                                <div id="accommodation-rows-container">
                                    <!-- Individual Dynamic Row Item -->
                                    <div class="accommodation-row">
                                        <div class="row-input">
                                            <label>Unit Name/No.</label>
                                            <input type="text" name="acc_name[]" required placeholder="Room 101 / Studio A">
                                        </div>
                                        <div class="row-input">
                                            <label>Monthly Rent (PHP)</label>
                                            <input type="number" name="acc_price[]" min="0" required placeholder="0.00">
                                        </div>
                                        <div class="row-input">
                                            <label>Capacity (Pax)</label>
                                            <input type="number" name="acc_capacity[]" min="1" required placeholder="1">
                                        </div>
                                        <button type="button" class="remove-row-btn" onclick="removeAccommodationRow(this)">Remove</button>
                                    </div>
                                </div>

                                <button type="button" class="add-row-btn" onclick="addAccommodationRow()">Add Another Unit Type</button>
                            </fieldset>

                            <div class="form-submit-wrapper">
                                <button type="submit" class="submit-form-btn">Publish Property Listing</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>

            <?php include '../process-and-setting/profile-settings-view.php'; ?>
            <?php include 'o-footer.php';  ?>
            
        </div>
    </body>  
</html>

<script src="../../javascript-files/profile-settings-modal.js"></script>
<script src="../../javascript-files/property.js"></script>