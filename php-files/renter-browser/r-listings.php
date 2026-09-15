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
?>

<?php
    require_once '../process-and-setting/database-connection.php';

    if (!isset($conn) || $conn->connect_error) {
        $conn = new mysqli("localhost", "root", "", "uhoppy_db");
    }

    if ($conn->connect_error) {
        die("Database Connection Failure: " . $conn->connect_error);
    }

    $searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

    if ($searchTerm !== '') {
        $sql = "SELECT p.*, 
                    MIN(a.monthly_rent) AS min_rent, 
                    MAX(a.monthly_rent) AS max_rent,
                    SUM(a.available_slots) AS total_slots,
                    pi.image_url
                FROM properties p
                LEFT JOIN accommodations a ON p.property_id = a.property_id
                LEFT JOIN property_images pi ON p.property_id = pi.property_id
                WHERE p.property_name LIKE ? 
                OR p.address LIKE ? 
                OR p.description LIKE ? 
                OR p.property_type LIKE ?
                OR a.accommodation_name LIKE ?
                GROUP BY p.property_id";
                
        $stmt = $conn->prepare($sql);
        $likeTerm = "%" . $searchTerm . "%";
        $stmt->bind_param("sssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT p.*, 
                    MIN(a.monthly_rent) AS min_rent, 
                    MAX(a.monthly_rent) AS max_rent,
                    SUM(a.available_slots) AS total_slots,
                    pi.image_url
                FROM properties p
                LEFT JOIN accommodations a ON p.property_id = a.property_id
                LEFT JOIN property_images pi ON p.property_id = pi.property_id
                GROUP BY p.property_id";
        $result = $conn->query($sql);
    }

    $dummyRooms = [
        ['title' => 'Shared room', 'price' => '₱ 2,250', 'img' => 'Shared_room_1.png', 'price_class' => 'price-tag1'],
        ['title' => 'Solo room', 'price' => '₱ 2,000', 'img' => 'Solo_room.png', 'price_class' => 'price-tag2'],
        ['title' => 'Shared room', 'price' => '₱ 1,800', 'img' => 'Shared_room_2.png', 'price_class' => 'price-tag3'],
        ['title' => 'Shared room', 'price' => '₱ 1,600', 'img' => 'Shared_room_3.png', 'price_class' => 'price-tag1'],
        ['title' => 'Shared room', 'price' => '₱ 3,000', 'img' => 'Shared_room_4.png', 'price_class' => 'price-tag2'],
        ['title' => 'Bedspacer', 'price' => '₱ 3,500', 'img' => 'Bedspacer.png', 'price_class' => 'price-tag3']
    ];
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/default/listings.css">
    <link rel="stylesheet" href="../../style/default/background_shapes.css">
    <link rel="stylesheet" href="../../style/profile-settings.css">
    <link rel="icon" type="image/jpeg" href="../../system-images/link-logo.jpg">
</head>
<body>
    <div class="web-app">
        <header>
            <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
            <button id="home-btn" class="home_button" onclick="window.location.href='renter-homepage.php'">HOME</button>
            <button id="listings-btn" class="listings_button active">LISTINGS</button>
            <button id="messages-btn" class="messages_button" onclick="window.location.href='r-messages.php'">MESSAGES</button>
            <button id="about_us-btn" class="about_us_button" onclick="window.location.href='r-about.php'">ABOUT US</button>
            <button id="contact-btn" class="contact_button" onclick="window.location.href='r-contact.php'">CONTACT</button>

            <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
            </div>
        </header>

        <main>
            <div class="search-container">
                <form action="r-listings.php" method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            name="query" 
                            id="search-input" 
                            placeholder="Search by area, type, or property name..." 
                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                            autocomplete="off"
                        >
                        <button type="button" id="clear-btn" class="clear-btn <?php echo $searchTerm !== '' ? 'visible' : ''; ?>">&times;</button>
                    </div>
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div> 

            <div class="card-container">
                <?php $hasRealProperties = false; ?>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $hasRealProperties = true; ?>
                    <?php $priceIndex = 1; ?>
                    <?php while($property = $result->fetch_assoc()): ?>
                        <?php 
                            $currentPriceClass = 'price-tag' . $priceIndex;
                            $priceIndex = ($priceIndex >= 3) ? 1 : $priceIndex + 1;
                            
                            $propertyId = $property['property_id'] ?? 0; 
                        ?>

                    <div class="room-card" onclick="window.location.href='property-details.php?id=<?php echo $propertyId; ?>';" style="cursor: pointer;">
                        <img class="room-image" 
                            src="<?php echo !empty($property['image_url']) ? htmlspecialchars('../../' . $property['image_url']) : '../../system-images/default-profile.png'; ?>" 
                            alt="<?php echo htmlspecialchars($property['property_name']); ?>">
                        
                        <div class="room-details">
                            <h3 class="room-title"><?php echo htmlspecialchars($property['property_name']); ?></h3>
                            <p class="amenities-list"></p>
                            <div class="<?php echo $currentPriceClass; ?>">
                                <?php if ($property['min_rent'] !== null): ?>
                                    ₱ <?php echo number_format($property['min_rent'], 0); ?>
                                <?php else: ?>
                                    ₱ 0
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>

                    <?php 
                    $shownDummyCount = 0;
                    foreach ($dummyRooms as $dummy): 
                        if ($searchTerm !== '' && stripos($dummy['title'], $searchTerm) === false) {
                            continue;
                        }
                        $shownDummyCount++;
                    ?>
                
                <div class="room-card" onclick="alert('This property is not available.');" style="cursor: pointer;">
                    <img class="room-image" src="../../uploaded-images/<?php echo $dummy['img']; ?>" alt="<?php echo htmlspecialchars($dummy['title']); ?>">
                    <div class="room-details">
                        <h3 class="room-title"><?php echo htmlspecialchars($dummy['title']); ?></h3>
                        <p class="amenities-list"></p>
                        <div class="<?php echo $dummy['price_class']; ?>"><?php echo htmlspecialchars($dummy['price']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$hasRealProperties && $shownDummyCount === 0): ?>
                <p class="no-results">No properties match your search criteria.</p>
            <?php endif; ?>
        </div>
        </main>
        <?php include '../process-and-setting/profile-settings-view.php'; ?>
        <?php include 'r-footer.php'; ?>
        </div>
            
    <script src="../../javascript-files/listings.js?v=2"></script>
    <script src="../../javascript-files/profile-settings-modal.js"></script>
</body>
</html>
