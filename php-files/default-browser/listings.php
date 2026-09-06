<?php
require_once '../process-and-setting/database-connection.php';

$conn = new mysqli("localhost", "root", "", "uhoppy_db");

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
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UHoppy Listings</title>
    <link rel="stylesheet" href="../../style/web-app.css">
    <link rel="stylesheet" href="../../style/header-style.css">
    <link rel="stylesheet" href="../../style/footer-style.css">
    <link rel="stylesheet" href="../../style/listings.css">
    <link rel="stylesheet" href="../../style/background_shapes.css">
    <link rel="stylesheet" href="../../style/sign-in.css">
    <link rel="stylesheet" href="../../style/sign-up.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
</head>
<body>
    <div class="web-app">
        <header>
            <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
            <button id="home-btn" class="home_button" onclick="window.location.href='index.php'">HOME</button>
            <button id="listings-btn" class="listings_button active">LISTINGS</button>
            <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
            <button id="about_us-btn" class="about_us_button" onclick="window.location.href='about-us.php'">ABOUT US</button>
            <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button>
            <button id="sign_in-btn" class="sign_in_button" onclick="openModal()">Sign In</button>
        </header>

        <main>
            <div class="search-container">
                <form action="listings.php" method="GET" class="search-form">
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
                </form>
            </div> 

            <button type="submit" class="search-btn">Search</button>

            <div class="listings-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($property = $result->fetch_assoc()): ?>
                        <div class="listing-card">
                            <img class="listing-image" 
                                src="<?php echo !empty($property['image_url']) ? htmlspecialchars($property['image_url']) : 'https://placeholder.com'; ?>" 
                                alt="<?php echo htmlspecialchars($property['property_name']); ?>">
                            
                            <div class="listing-details">
                                <span class="listing-type"><?php echo htmlspecialchars($property['property_type']); ?></span>
                                <h3 class="listing-title"><?php echo htmlspecialchars($property['property_name']); ?></h3>
                                <p class="listing-address">📍 <?php echo htmlspecialchars($property['address']); ?></p>
                                
                                <p class="listing-price">
                                    <?php if ($property['min_rent'] !== null): ?>
                                        ₱<?php echo number_format($property['min_rent'], 2); ?> 
                                        <?php echo ($property['min_rent'] != $property['max_rent']) ? ' - ₱' . number_format($property['max_rent'], 2) : ''; ?> / mo
                                    <?php else: ?>
                                        Price Unlisted
                                    <?php endif; ?>
                                </p>
                                <small class="listing-slots">Slots available: <?php echo (int)$property['total_slots']; ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-results">No properties match your search criteria.</p>
                <?php endif; ?>
            </div>
        </main>
        
        <?php 
            include 'footer.php'; 
        ?>
    </div>
    
    <script src="../../javascript-files/listings.js"></script>
</body>
</html>