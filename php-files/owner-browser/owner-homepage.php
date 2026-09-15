<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $profilePic = $_SESSION['profile_picture'] ?? 'uploads/default-avatar.png';
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
        <title>Owner Homepage</title>
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/default/header-style.css">
        <link rel="stylesheet" href="../../style/default/footer-style.css">
        <link rel="stylesheet" href="../../style/owner/owner-homepage.css">
        <link rel="stylesheet" href="../../style/default/background-shapes.css">
        <link rel="stylesheet" href="../../style/default/pass-required-input.css">
        <link rel="stylesheet" href="../../style/profile-settings.css">
        <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <div class="square1"></div>
            <div class="square2"></div>

            <header>
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                <button id="home-btn" class="home_button active">HOME</button>
                <button id="property-btn" class="property_button" onclick="window.location.href='o-property.php'">PROPERTY</button>
                <button id="messages-btn" class="messages_button" onclick="window.location.href='o-messages.php'">MESSAGES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='o-about-us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='o-contact.php'">CONTACT</button>
                
                <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
                </div>
                
            </header>

            <main class="Section_1">
                <h1 class="text_1">Show Your Happy Place.</h1>
                <p class="par_1">Upload and manage your: <br> 
                apartment, boarding house, bedspacer, and etc.. 
                    <br> Track renters duration of stay and rent payments. 
                    <br> Chat with renters.
                </p>
                <button id="start-btn" class="start_button" onclick="window.location.href='o-property.php'">Start</button>     
            </main>

            <main class="Section_2">
                <h2 class="section-title">My Active Renters</h2>
                
                <?php if (empty($renters)): ?>
                    <div class="no-renters-box">
                        <p class="no-renters-text">No active renters renting your property yet.</p>
                    </div>
                <?php else: ?>
                    <div class="renters-grid">
                        <?php foreach ($renters as $renter): ?>
                            <div class="renter-card">
                                <h3 class="renter-name">
                                    <?php echo htmlspecialchars($renter['first_name'] . ' ' . $renter['last_name']); ?>
                                </h3>
                                <p class="renter-details"><strong>Property:</strong> <?php echo htmlspecialchars($renter['property_name'] . ' (' . ($renter['accommodation_name'] ?? $renter['accomodation_name']) . ')'); ?></p>
                                <p class="renter-email"> <?php echo htmlspecialchars($renter['email']); ?></p>
                                <p class="renter-phone"> <?php echo htmlspecialchars($renter['phone_number']); ?></p>
                            </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>

            <main class="Section_3">
                <h2 class="reminder">Reminder!!!</h2>
                <p class="warning-text">
                    UHoppy strictly enforces a zero-tolerance policy against fraudulent activities. 
                    <br> Landlords must provide accurate listing information, and renters must present valid credentials. 
                    <br> Any accounts involved in deceptive behavior or payment scams will be permanently banned and reported.
                </p>
            </main>
            
            <?php include '../process-and-setting/profile-settings-view.php'; ?>

            <?php include 'o-footer.php'; ?>
        </div>
    </body>  
</html>
<script src="../../javascript-files/profile-settings-modal.js"></script>