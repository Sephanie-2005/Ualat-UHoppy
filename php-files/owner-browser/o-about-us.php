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
        <title>About Us</title>
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/default/header-style.css">
        <link rel="stylesheet" href="../../style/default/footer-style.css">
        <link rel="stylesheet" href="../../style/owner/o-about.css">
        <link rel="stylesheet" href="../../style/profile-settings.css">
        <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
    </head>

    <body>
        <div class="web-app">

           <header>
                
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                
                <button id="home-btn" class="home_button" onclick="window.location.href='owner-homepage.php'">HOME</button>
                <button id="property-btn" class="property_button" onclick="window.location.href='o-property.php'">PROPERTY</button>
                <button id="messages-btn" class="messages_button" onclick="window.location.href='o-messages.php'">MESSAGES</button>
                <button id="about_us-btn" class="about_us_button active">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='o-contact.php'">CONTACT</button>
                 
                <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
                </div>

            </header>

            <main class="Section_1">
                <div class="container">
                    <h1 class="title">UHoppy</h1>
                    <p class="subtitle">Hop into the happiness in finding a place to stay!</p>
                </div>
            </main>

            <main class="Section_2">
                <div class="mission-container">
                    <h1>Our Mission</h1>
                    <p class="mission-text">
                        Our mission is to simplify the housing search by helping users discover, map out, and secure 
                        affordable accommodations while providing built-in tracking for rent payments and direct 
                        communication with landlords.
                    </p>
                </div>
            </main>

            <main class="Section_3">
                <div class="story-container">
                    <h2>How UHoppy Began</h2>
                    <p class="story-paragraph">
                        Finding a place to live shouldn't feel like navigating an endless, exhausting maze. UHoppy was born out of a simple, universal frustration that almost every renter knows too well. The exhausting hustle of walking street by street under the blazing sun or pouring rain, scouring fences for faded "Room for Rent" signs. Too many days were wasted knock on doors just to find a single available room, guessing hidden rental costs, and dealing with the constant anxiety of losing track of loose paper rent receipts. We watched students struggling to balance their classes while hunting for bedspacers, and young professionals spending their entire weekends searching for a decent boarding house.
                    </p>
                    <p class="story-paragraph">
                        As we looked closer at the problem, we realized it wasn't just hard for renters, it was stressful for property owners too. Landlords and landladies were relying on old-school notebooks to log payments, manually tracking who paid for what month, and dealing with chaotic text messages scattered across different apps. It became blindingly clear that students, young professionals, and property owners desperately needed a smarter, unified digital ecosystem built specifically to bridge this gap.
                    </p>
                    <p class="story-paragraph">
                        Driven by the vision to make housing seamless, we built UHoppy to completely eliminate the stress of moving and property management. By bringing advanced mapping precision, automated lease and payment tracking, and a direct, secure instant messaging system under one digital roof, we bridge the gap between hosts and seekers. We want to ensure that from the very first click to your final month's rent payment, finding and securing your next home is as joyful, organized, and happy as it truly deserves to be.
                    </p>
                </div>
            </main>
                
            <?php include '../process-and-setting/profile-settings-view.php'; ?>
            <?php include 'o-footer.php';  ?>
        </div>
    </body>
        
</html>

<script src="../../javascript-files/profile-settings-modal.js"></script>