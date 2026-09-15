<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'renter') { 
        header("Location: ../default-browser/index.php?error=unauthorized");
        exit();
    }

    $renter_id = $_SESSION['user_id'];

    $profilePic = $_SESSION['profile_picture'] ?? 'uploads/default-avatar.png';

    require_once '../process-and-setting/database-connection.php'; 
    
    $userData = []; 
    
    $renterQuery = "SELECT first_name, middle_name, last_name, email, phone_number, profile_picture FROM renters WHERE renter_id = ?";
    
    if ($renterStmt = $conn->prepare($renterQuery)) {
        $renterStmt->bind_param("i", $renter_id);
        $renterStmt->execute();
        $renterResult = $renterStmt->get_result();
        
        if ($renterResult && $renterResult->num_rows > 0) {
            $userData = $renterResult->fetch_assoc();
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
        $renterStmt->close();
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

        $renter_id = $_SESSION['user_id'];
    $activeRental = null;

    // Fetch the active rental agreement details for this specific renter
    $rentalQuery = "SELECT rt.rental_id, rt.start_date, rt.end_date, rt.duration_months, 
                           a.accommodation_name, a.accommodation_type, a.monthly_rent,
                           p.property_name, p.address
                    FROM rentals rt
                    INNER JOIN accommodations a ON rt.accommodation_id = a.accommodation_id
                    INNER JOIN properties p ON a.property_id = p.property_id
                    WHERE rt.renter_id = ? AND rt.rental_status = 'active'
                    LIMIT 1";

    if ($rStmt = $conn->prepare($rentalQuery)) {
        $rStmt->bind_param("i", $renter_id);
        $rStmt->execute();
        $rResult = $rStmt->get_result();
        if ($rResult && $rResult->num_rows > 0) {
            $activeRental = $rResult->fetch_assoc();
        }
        $rStmt->close();
    }

?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/renter/renter-homepage.css">
    <link rel="stylesheet" href="../../style/default/background-shapes.css">
    <link rel="stylesheet" href="../../style/pass-required-input.css">
    <link rel="stylesheet" href="../../style/profile-settings.css">

    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <div class="square1"></div>
            <div class="square2"></div>
            <div class="square3"></div>
            <div class="square4"></div>

            <header>
                
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                
                <button id="home-btn" class="home_button active">HOME</button>
                <button id="listings-btn" class="listings_button" onclick="window.location.href='r-listings.php'">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='r-messages.php'">MESSAGES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='r-about.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='r-contact.php'">CONTACT</button>

                <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
                </div>

            </header>

            <main class="Section_1">
                <h1 class="text_1">Find Your Happy Place.</h1>
                <p class="par_1">Find an apartment, boarding house, 
                    <br> bedspacer, and other place to stay. 
                    <br> Track duration of stay and rent payments. 
                    <br> Chat with landlords and landlady.
                </p>
                <button id="start_search-btn" class="start_search_button" onclick="window.location.href='r-listings.php'">Start Your Search</button>     
            </main>

            <main class="Section_2">
                <h2 class="featured_listings">Featured Listings</h2>
                <button id="view_all-btn" class="view_all_button" onclick="window.location.href='listings.php'">- View All</button>

                <div class="card-container">

                    <div class="room-card">
                        <img src="../../uploaded-images/Shared_room_1.png" alt="Shared room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Shared room</h3>
                            <p class="amenities-list"></p>
                            <div class="price-tag1">₱ 2,250</div>
                        </div>
                    </div>

                    <div class="room-card">
                        <img src="../../uploaded-images/Solo_room.png" alt="Solo room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Solo room</h3>
                            <p class="amenities-list"></p>
                            <div class="price-tag2">₱ 2,000</div>
                        </div>
                    </div>

                    <div class="room-card">
                        <img src="../../uploaded-images/Shared_room_2.png" alt="Shared room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Shared room</h3>
                            <p class="amenities-list"></p>
                            <div class="price-tag3">₱ 1,800</div>
                        </div>
                    </div>
                </div>

                <div class="second-card-container">

                    <div class="second-room-card">
                        <img src="../../uploaded-images/Shared_room_3.png" alt="Shared room" class="second-room-image">
                        <div class="second-room-details">
                        <h3 class="second-room-title">Shared room</h3>
                        <p class="second-amenities-list"></p>
                        <div class="second-price-tag1">₱ 1,600</div>
                        </div>
                    </div>

                    <div class="second-room-card">
                        <img src="../../uploaded-images/Shared_room_4.png" alt="Shared room" class="second-room-image">
                        <div class="second-room-details">
                            <h3 class="second-room-title">Shared room</h3>
                            <p class="second-amenities-list"></p>
                            <div class="second-price-tag2">₱ 3,000</div>
                        </div>
                    </div>

                    <div class="second-room-card">
                        <img src="../../uploaded-images/Bedspacer.png" alt="Bedspacer" class="second-room-image">
                        <div class="second-room-details">
                            <h3 class="second-room-title">Bedspacer</h3> 
                            <p class="second-amenities-list">
                            <div class="second-price-tag3">₱ 3,500</div>
                        </div>
                    </div>
                </div>

            </main>

            <main class="Section_3">
                <h2 class="section-title">My Stay Specifications</h2>
                
                <?php if ($activeRental): ?>
                    <div class="lease-card">
                        <!-- Left Column: Property & Accommodation Metadata -->
                        <div class="property-info">
                            <h3><?php echo htmlspecialchars($activeRental['property_name']); ?></h3>
                            <p class="info-line"><strong>Location:</strong> <?php echo htmlspecialchars($activeRental['address']); ?></p>
                            <p class="info-line"><strong>Unit / Unit Name:</strong> <?php echo htmlspecialchars($activeRental['accommodation_name']); ?></p>
                            <p class="info-line"><strong>Unit Type:</strong> <?php echo htmlspecialchars($activeRental['accommodation_type']); ?></p>
                            <p class="info-line"><strong>Monthly Rent:</strong> PHP <?php echo number_format($activeRental['monthly_rent'], 2); ?></p>
                        </div>
                        
                        <div class="dates-info">
                            <div class="date-row">
                                <strong>Start Date:</strong> 
                                <div><?php echo date('F d, Y', strtotime($activeRental['start_date'])); ?></div>
                            </div>
                            <div class="date-row">
                                <strong>Expiration Date:</strong> 
                                <div class="date-highlight"><?php echo date('F d, Y', strtotime($activeRental['end_date'])); ?></div>
                            </div>
                            <div class="date-row" style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #eee;">
                                <strong>Total Duration:</strong> <?php echo htmlspecialchars($activeRental['duration_months']); ?> Months
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Fallback view panel block if no active contract row is logged -->
                    <div class="no-lease-box">
                        <p class="no-lease-text">You don't have an active rental contract registered under your profile yet.</p>
                    </div>
                <?php endif; ?>
            </main>


            <main class="Section_4">
                <p class="uhoppy">UHoopy</p>
                <p class="par_2"> &emsp; &emsp; is an easy-to-use web application that connects
                    <br>people looking for a room with landlords who have places to rent.
                    <br>It makes finding and managing a boarding house simple and stress-free for both sides.
                </p>
            </main>

            <main class="Section_5">
                <h1 class="how_it_works_text">How it Works</h1>
                <img src="../../system-images/Search icon.png" alt="Search icon" class="search_icon">
                <h2 class="search_text">1. Search</h2>
                <img src="../../system-images/Connect icon.png" alt="Connect icon" class="connect_icon">
                <h2 class="connect_text">2. Connect</h2>
                <img src="../../system-images/Rent icon.png" alt="Rent icon" class="rent_icon">
                <h2 class="rent_text">3. Rent</h2>
                <img src="../../system-images/Live happily icon.png" alt="Live Happily icon" class="live_happily_icon">
                <h2 class="live_happily_text">4. Live Happily</h2>
            </main>

            <main class="Section_6">
                <h2 class="reminder">Reminder!!!</h2>
                <p class="warning-text">
                    UHoppy strictly enforces a zero-tolerance policy against fraudulent activities. 
                    <br> Landlords must provide accurate listing information, and renters must present valid credentials. 
                    <br> Any accounts involved in deceptive behavior or payment scams will be permanently banned and reported.
                </p>
            </main>
            
        </div>

        <?php include '../process-and-setting/profile-settings-view.php'; ?>
        <?php include 'r-footer.php';  ?>
    </body>    
    <script src="../../javascript-files/profile-settings-modal.js"></script>
</html>