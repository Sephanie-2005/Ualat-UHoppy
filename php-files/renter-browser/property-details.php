<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'renter') { 
    header("Location: ../default-browser/index.php?error=unauthorized");
    exit();
}

require_once '../process-and-setting/database-connection.php'; 

$propertyId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($propertyId <= 0) {
    header("Location: index.php?error=invalid_property");
    exit();
}

$property = null;
$query = "SELECT 
            p.property_id, 
            p.property_name, 
            p.description, 
            p.address,
            p.owner_id,
            MIN(a.monthly_rent) AS min_rent,
            (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.property_id LIMIT 1) AS image_url
          FROM properties p
          LEFT JOIN accommodations a ON p.property_id = a.property_id
          WHERE p.property_id = ?
          GROUP BY p.property_id"; 

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$property) {
    die("Error: The requested property could not be found or is no longer available.");
}

$accommodationsList = [];
$accomQuery = "SELECT accommodation_id, accommodation_name, accommodation_type, capacity, available_slots, monthly_rent, status 
               FROM accommodations 
               WHERE property_id = ?";

if ($accomStmt = $conn->prepare($accomQuery)) {
    $accomStmt->bind_param("i", $propertyId);
    $accomStmt->execute();
    $accomResult = $accomStmt->get_result();
    
    while ($row = $accomResult->fetch_assoc()) {
        $accommodationsList[] = $row;
    }
    $accomStmt->close();
}

$ownerId = $property['owner_id'] ?? 0;
?>
<!DOCTYPE html> 
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($property['property_name']); ?> - Details</title>
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/property-details.css">
        <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <header>
                <h2>Property Specifications</h2>
                <button id="back-btn" class="back-btn" onclick="history.back()">BACK</button> 
            </header>
            
            <main class="main">

            <h2>Property Specifications</h2>
                <button id="back-btn" class="back-btn" onclick="history.back()">BACK</button> 
        
                <section class="property-main-info">
                    <div class="image-wrapper">
                        <img class="detailed-image" 
                            src="<?php echo !empty($property['image_url']) ? htmlspecialchars('../../' . $property['image_url']) : '../../system-images/default-profile.png'; ?>" 
                            alt="<?php echo htmlspecialchars($property['property_name']); ?>">
                    </div>
                    
                    <div class="text-wrapper">
                        <h1 class="property-title"><?php echo htmlspecialchars($property['property_name']); ?></h1>
                        <p class="property-address">Location: <?php echo htmlspecialchars($property['address'] ?? 'No address provided'); ?></p>
                        
                        <div class="price-tag-large">
                            PHP <?php echo number_format($property['min_rent'] ?? 0, 2); ?> <span class="per-month">/ month minimum</span>
                        </div>

                        <div class="property-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($property['description'] ?? 'No description provided for this property.')); ?></p>
                        </div>
                    </div>
                </section>

                <section class="property-accommodations">
                    <h3>Available Units & Accommodations</h3>
                    <!-- The outer container controls the overflow limits safely -->
                    <div class="accommodations-list-container">
                        <?php if (!empty($accommodationsList)): ?>
                            <table class="accommodations-table">
                                <thead>
                                    <tr>
                                        <th>Unit Name</th>
                                        <th>Type</th>
                                        <th>Capacity</th>
                                        <th>Slots Left</th>
                                        <th>Monthly Rent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accommodationsList as $unit): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($unit['accommodation_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($unit['accommodation_type']); ?></td>
                                            <td><?php echo htmlspecialchars($unit['capacity']); ?> persons</td>
                                            <td><?php echo htmlspecialchars($unit['available_slots']); ?> slots</td>
                                            <td>PHP <?php echo number_format($unit['monthly_rent'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-amenities">No individual room listings or available slots are registered under this property.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="booking-action-panel">
                    <button class="secondary-action-btn" onclick="window.location.href='r-messages.php?recipient_id=<?php echo $ownerId; ?>&property_id=<?php echo $propertyId; ?>';">Message Owner</button>
                </section>
            </main>
        </div>
    </body>
</html>
