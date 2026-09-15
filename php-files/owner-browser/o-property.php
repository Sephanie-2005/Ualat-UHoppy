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

            <main class="section_1">
                <div class="dashboard-container">
                    <?php
                    $propDisplayQuery = "SELECT p.*, (SELECT image_url FROM property_images pi WHERE pi.property_id = p.property_id LIMIT 1) as cover_image 
                                         FROM properties p WHERE p.owner_id = ? LIMIT 1";
                    
                    $hasProperty = false;
                    $registered_property_id = 0;
                    $property_name = '';

                    if ($pStmt = $conn->prepare($propDisplayQuery)) {
                        $pStmt->bind_param("i", $owner_id);
                        $pStmt->execute();
                        $pResult = $pStmt->get_result();
                        
                        if ($pResult && $pResult->num_rows > 0) {
                            $hasProperty = true;
                            $property = $pResult->fetch_assoc();
                            $registered_property_id = $property['property_id'];
                            $property_name = $property['property_name'];
                            $coverPath = !empty($property['cover_image']) ? '../../' . $property['cover_image'] : '../../system-images/default-property.png';
                        }
                        $pStmt->close();
                    }
                    ?>

                    <div class="section-header-row">
                        <div class="header-text">
                            <h2>Your Property</h2>
                            <p>Manage and review your registered real estate profile</p>
                        </div>
                        <div class="header-actions">
                            <?php if (!$hasProperty): ?>
                                <button class="action-upload-btn" onclick="window.location.href='upload-property.php'">
                                    Add Your Property
                                </button>
                            <?php else: ?>
                                <button class="action-upload-btn edit-mode-btn" onclick="window.location.href='edit-property.php'">
                                    Edit Property Details
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="listing-grid single-item-view">
                        <?php if ($hasProperty): ?>
                            <div class="card item-card horizontal-layout">
                                <div class="card-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Property Image">
                                </div>
                                <div class="card-content">
                                    <h3><?php echo htmlspecialchars($property['property_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <span class="badge type-badge"><?php echo htmlspecialchars($property['property_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <p class="card-address"><?php echo htmlspecialchars($property['address'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-desc"><?php echo htmlspecialchars($property['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-state-notice">

                                <h3>No property uploaded yet</h3>
                                <p>Click the button above to register your single property profile.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>  

           <main class="section_2" id="section2">
                <div class="dashboard-container">
                    <div class="section-header-row">
                        <div class="header-text">
                            <h2>Rooms & Accommodations</h2>
                            <p>Track pricing, occupancy capacities, and live availability</p>
                        </div>
                        <?php if ($hasProperty): ?>
                            <button class="action-upload-btn secondary-color" onclick="window.location.href='upload-accommodation.php'">
                                Add Accommodation
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="listing-grid">
                        <?php
                        $hasAccommodations = false;

                        if ($hasProperty) {
                            $accomDisplayQuery = "SELECT * FROM accommodations WHERE property_id = ? ORDER BY accommodation_id DESC";
                            
                            if ($aStmt = $conn->prepare($accomDisplayQuery)) {
                                $aStmt->bind_param("i", $registered_property_id);
                                $aStmt->execute();
                                $aResult = $aStmt->get_result();
                                
                                if ($aResult && $aResult->num_rows > 0) {
                                    $hasAccommodations = true;
                                    while ($accom = $aResult->fetch_assoc()) {
                                        $statusClass = strtolower(str_replace(' ', '-', $accom['status']));
                                        // Dynamic path check for accommodation photo row
                                        $images_array = json_decode($accom['accommodation_image'], true);
                                        $accomPicPath = (!empty($images_array) && isset($images_array[0])) ? '../../' . $images_array[0] : '../../system-images/default-property.png';
                                        ?>
                                        <div class="card item-card modular-accommodation-card" 
                                                onclick="window.location.href='edit-accommodation.php?id=<?php echo $accom['accommodation_id']; ?>'" 
                                                style="cursor: pointer;">
                                            <div class="accommodation-thumbnail">
                                                <img src="<?php echo htmlspecialchars($accomPicPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Accommodation Unit Layout">
                                            </div>
                                            <div class="card-content no-img-padding">
                                                <div class="card-header-split">
                                                    <h3><?php echo htmlspecialchars($accom['accommodation_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                                    <span class="status-indicator <?php echo $statusClass; ?>">
                                                        <?php echo htmlspecialchars(str_replace('_', ' ', $accom['status']), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </div>
                                                <p class="belongs-to">Unit under: <strong><?php echo htmlspecialchars($property_name, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                                
                                                <!-- UPDATED SPECS GRIDS FOR INDEPENDENT DISPLAY -->
                                                <div class="accom-specs">
                                                    <div><strong>Type:</strong> <?php echo htmlspecialchars($accom['accommodation_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div><strong>Rent:</strong> ₱<?php echo number_format($accom['monthly_rent'], 2); ?>/mo</div>
                                                    <div><strong>Total Capacity:</strong> <?php echo htmlspecialchars($accom['capacity'], ENT_QUOTES, 'UTF-8'); ?> Person(s)</div>
                                                    <div><strong>Open Slots:</strong> <?php echo htmlspecialchars($accom['available_slots'], ENT_QUOTES, 'UTF-8'); ?> Slot(s) Left</div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                $aStmt->close();
                            }
                        }

                        if (!$hasAccommodations): ?>
                            <div class="empty-state-notice">
                                <div class="icon">🛏️</div>
                                <h3>No accommodation uploaded yet</h3>
                                <?php if ($hasProperty): ?>
                                    <p>Click the button above to publish room configurations under your property space.</p>
                                <?php else: ?>
                                    <p style="color: #ff6b6b;">You must upload a property profile first before adding accommodations.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

            <?php include '../process-and-setting/profile-settings-view.php'; ?>
            <?php include 'o-footer.php';  ?>
            
        </div>
    </body>  
</html>

<script src="../../javascript-files/profile-settings-modal.js"></script>
<script src="../../javascript-files/o-property.js"></script>