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

    // 1. Flash message management sets
    $success_message = "";
    if (isset($_SESSION['rental_success'])) {
        $success_message = $_SESSION['rental_success'];
        unset($_SESSION['rental_success']);
    }
    $error_message = "";

    // 2. FETCH OWNER PROFILE INFO FIRST (Keeps data safe for profile-settings-view.php)
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

    // --- TERMINATE LEASE AGREEMENTS ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminate_lease') {
        $target_rental_id = intval($_POST['rental_id']);
        $target_accommodation_id = intval($_POST['accommodation_id']);

        if ($target_rental_id > 0 && $target_accommodation_id > 0) {
            $updateRental = "UPDATE rentals SET rental_status = 'inactive' WHERE rental_id = ?";
            if ($tStmt = $conn->prepare($updateRental)) {
                $tStmt->bind_param("i", $target_rental_id);
                if ($tStmt->execute()) {
                    $restoreSlot = "UPDATE accommodations SET available_slots = available_slots + 1 WHERE accommodation_id = ?";
                    if ($resStmt = $conn->prepare($restoreSlot)) {
                        $resStmt->bind_param("i", $target_accommodation_id);
                        $resStmt->execute();
                        $resStmt->close();
                    }
                    $_SESSION['rental_success'] = "Lease agreement terminated and slot freed up successfully!";
                    header("Location: owner-homepage.php");
                    exit();
                }
                $tStmt->close();
            }
        }
    }

    // --- DIRECT 1-MONTH PAYMENT ADD HANDLER ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'direct_month_pay') {
        $target_rental_id = intval($_POST['rental_id']);
        $payment_amount = doubleval($_POST['amount']);
        $current_end_date = $_POST['current_end_date'];

        if ($target_rental_id > 0 && $payment_amount > 0 && !empty($current_end_date)) {
            $dateObj = new DateTime($current_end_date);
            $dateObj->modify('+1 month');
            $new_end_date = $dateObj->format('Y-m-d');

            $updateLease = "UPDATE rentals SET end_date = ?, duration_months = duration_months + 1 WHERE rental_id = ?";
            if ($lStmt = $conn->prepare($updateLease)) {
                $lStmt->bind_param("si", $new_end_date, $target_rental_id);
                if ($lStmt->execute()) {
                    $insertPay = "INSERT INTO payments (rental_id, amount, payment_date, transaction_reference, created_at) VALUES (?, ?, NOW(), '1-Month Extension Pay', NOW())";
                    if ($pStmt = $conn->prepare($insertPay)) {
                        $pStmt->bind_param("id", $target_rental_id, $payment_amount);
                        $pStmt->execute();
                        $pStmt->close();
                    }
                    $_SESSION['rental_success'] = "Payment recorded! Rent extension extended by 1 month.";
                    header("Location: owner-homepage.php");
                    exit();
                }
                $lStmt->close();
            }
        }
    }

    // --- PROCESS NEW LEASE ENTRIES ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_new_rental') {
        $form_renter_id = intval($_POST['renter_id']);
        $form_accommodation_id = intval($_POST['accommodation_id']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $duration = intval($_POST['duration_months']);

        if ($form_renter_id > 0 && $form_accommodation_id > 0 && !empty($start_date) && !empty($end_date)) {
            $slotCheckQuery = "SELECT available_slots FROM accommodations WHERE accommodation_id = ? LIMIT 1";
            $slots = 0;
            if ($sStmt = $conn->prepare($slotCheckQuery)) {
                $sStmt->bind_param("i", $form_accommodation_id);
                $sStmt->execute();
                $sStmt->bind_result($slots);
                $sStmt->fetch();
                $sStmt->close();
            }

            if ($slots > 0) {
                $insertRental = "INSERT INTO rentals (renter_id, accommodation_id, start_date, end_date, duration_months, rental_status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())";
                if ($rStmt = $conn->prepare($insertRental)) {
                    $rStmt->bind_param("iissi", $form_renter_id, $form_accommodation_id, $start_date, $end_date, $duration);
                    if ($rStmt->execute()) {
                        $updateSlots = "UPDATE accommodations SET available_slots = available_slots - 1 WHERE accommodation_id = ?";
                        if ($uStmt = $conn->prepare($updateSlots)) {
                            $uStmt->bind_param("i", $form_accommodation_id);
                            $uStmt->execute();
                            $uStmt->close();
                        }
                        $_SESSION['rental_success'] = "Renter assigned successfully!";
                        header("Location: owner-homepage.php");
                        exit();
                    } else {
                        $error_message = "Database execution error. Please try again.";
                    }
                    $rStmt->close();
                }
            } else {
                $error_message = "No slots available for this accommodation unit.";
            }
        } else {
            $error_message = "Please fill out all required form fields.";
        }
    }

    // --- CORE CARD DATA GRID QUERY ---
    $query = "SELECT DISTINCT rt.rental_id, rt.start_date, rt.end_date, r.renter_id, r.first_name, r.last_name, r.email, r.phone_number, 
                              p.property_name, a.accommodation_id, a.accommodation_name, a.monthly_rent,
                              (SELECT MAX(p_sub.payment_date) FROM payments p_sub WHERE p_sub.rental_id = rt.rental_id) as last_payment_date
              FROM renters r 
              INNER JOIN rentals rt ON r.renter_id = rt.renter_id 
              INNER JOIN accommodations a ON rt.accommodation_id = a.accommodation_id 
              INNER JOIN properties p ON a.property_id = p.property_id 
              WHERE p.owner_id = ? AND rt.rental_status = 'active'
              GROUP BY rt.rental_id"; 

    if ($stmt = $conn->prepare($query)) { 
        $stmt->bind_param("i", $owner_id); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 
        while ($row = $result->fetch_assoc()) { 
            $renters[] = $row; 
        } 
        $stmt->close(); 
    } 

    $allRentersList = [];
    $renterFetch = "SELECT renter_id, first_name, last_name, email FROM renters ORDER BY last_name ASC";
    if ($rResult = $conn->query($renterFetch)) {
        while($rRow = $rResult->fetch_assoc()) { $allRentersList[] = $rRow; }
    }

    $ownerUnitsList = [];
    $unitFetch = "SELECT a.accommodation_id, a.accommodation_name, p.property_name, a.available_slots 
                  FROM accommodations a 
                  INNER JOIN properties p ON a.property_id = p.property_id 
                  WHERE p.owner_id = ? AND a.available_slots > 0";
    if ($uStmt = $conn->prepare($unitFetch)) {
        $uStmt->bind_param("i", $owner_id);
        $uStmt->execute();
        $uResult = $uStmt->get_result();
        while($uRow = $uResult->fetch_assoc()) { $ownerUnitsList[] = $uRow; }
        $uStmt->close();
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
                <?php if (!empty($success_message)): ?>
                    <div id="successFlashBanner" class="alert-banner alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <script>
                        setTimeout(function() {
                            var banner = document.getElementById('successFlashBanner');
                            if(banner) banner.style.display = 'none';
                        }, 3000);
                    </script>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert-banner alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="section-header-row">
                    <h2 class="section-title" style="padding-left: 0;">My Active Renters</h2>
                    <div class="search-action-group">
                        <input type="text" id="renterSearchInput" onkeyup="searchAllSystemRenters()" placeholder="Search renter to add..." autocomplete="off">
                        <div id="searchDropdownList" class="search-results-dropdown"></div>
                        
                    </div>
                </div>

                <div id="allRentersDataStorage" data-all-renters="<?php echo htmlspecialchars(json_encode($allRentersList), ENT_QUOTES, 'UTF-8'); ?>" style="display:none;"></div>
                
                <?php if (empty($renters)): ?>
                    <div class="no-renters-box">
                        <p class="no-renters-text">No active renters renting your property yet.</p>
                    </div>
                <?php else: ?>
                    <div class="renters-grid" id="rentersGrid">
                        <?php foreach ($renters as $renter): ?>
                            <div class="renter-card">
                                <h3 class="renter-name">
                                    <?php echo htmlspecialchars($renter['first_name'] . ' ' . $renter['last_name']); ?>
                                </h3>
                                <p class="renter-details"><strong>Property:</strong> <?php echo htmlspecialchars($renter['property_name'] . ' (' . $renter['accommodation_name'] . ')'); ?></p>
                                <p class="renter-email"><strong>Email:</strong> <?php echo htmlspecialchars($renter['email']); ?></p>
                                <p class="renter-phone"><strong>Phone:</strong> <?php echo htmlspecialchars(!empty($renter['phone_number']) ? $renter['phone_number'] : 'No number listed'); ?></p>
                                
                                <p class="renter-lease-start" style="margin: 4px 0 0 0; font-size: 0.95rem; color: #444;"><strong>Lease Starts:</strong> <?php echo date('M d, Y', strtotime($renter['start_date'])); ?></p>
                                <p class="renter-lease-end" style="margin: 4px 0 0 0; font-size: 0.95rem; color: #444;"><strong>Lease Ends:</strong> <span style="color: #dc3545; font-weight: bold;"><?php echo date('M d, Y', strtotime($renter['end_date'])); ?></span></p>
                                <p class="renter-last-payment" style="margin: 4px 0 0 0; font-size: 0.95rem; color: #444;"><strong>Last Paid Date:</strong> <?php echo !empty($renter['last_payment_date']) ? date('M d, Y h:i A', strtotime($renter['last_payment_date'])) : 'No payment logged'; ?></p>
                                
                                <div class="renter-card-actions">
                                    <form action="" method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="direct_month_pay">
                                        <input type="hidden" name="rental_id" value="<?php echo $renter['rental_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $renter['monthly_rent']; ?>">
                                        <input type="hidden" name="current_end_date" value="<?php echo $renter['end_date']; ?>">
                                        <button type="submit" class="btn-action-pay">Add Month Pay</button>
                                    </form>

                                    <button type="button" class="btn-action-delete" onclick="openTerminateModal(<?php echo $renter['rental_id']; ?>, <?php echo $renter['accommodation_id']; ?>, '<?php echo htmlspecialchars($renter['first_name'] . ' ' . $renter['last_name'], ENT_QUOTES); ?>')">End Lease</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div id="addRentalModal" class="add-rental-modal-overlay">
                    <div class="modal-form-content">
                        <h3>Assign New Lease Agreement</h3>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="add_new_rental">
                            <div class="modal-form-group">
                                <label for="renter_id_select">Select Renter</label>
                                <select name="renter_id" id="renter_id_select" required>
                                    <option value="">-- Choose Tenant --</option>
                                    <?php foreach($allRentersList as $rItem): ?>
                                        <option value="<?php echo $rItem['renter_id']; ?>">
                                            <?php echo htmlspecialchars($rItem['first_name'] . ' ' . $rItem['last_name'] . ' (' . $rItem['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-form-group">
                                <label for="accommodation_id_select">Select Unit/Accommodation</label>
                                <select name="accommodation_id" id="accommodation_id_select" required>
                                    <option value="">-- Choose Unit --</option>
                                    <?php foreach($ownerUnitsList as $uItem): ?>
                                        <option value="<?php echo $uItem['accommodation_id']; ?>">
                                            <?php echo htmlspecialchars($uItem['property_name'] . ' - ' . $uItem['accommodation_name'] . ' (' . $uItem['available_slots'] . ' slots left)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-form-group">
                                <label for="start_date_input">Start Date</label>
                                <input type="date" name="start_date" id="start_date_input" required>
                            </div>
                            <div class="modal-form-group">
                                <label for="end_date_input">End Date</label>
                                <input type="date" name="end_date" id="end_date_input" required>
                            </div>
                            <div class="modal-form-group">
                                <label for="duration_months_input">Duration (Months)</label>
                                <input type="number" name="duration_months" id="duration_months_input" min="1" max="36" value="1" required>
                            </div>
                            <div class="modal-action-row">
                                <button type="button" class="modal-cancel-btn" onclick="toggleRentalModal(false)">Cancel</button>
                                <button type="submit" class="modal-save-btn">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="terminateLeaseModal" class="add-rental-modal-overlay">
                    <div class="modal-form-content" style="border-top-color: #dc3545;">
                        <h3>End Lease Agreement</h3>
                        <p style="font-size: 14px; color: #555;">Are you sure you want to end the active lease agreement for <strong id="terminate_renter_name"></strong>? This will remove them from your active dashboard lists.</p>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="terminate_lease">
                            <input type="hidden" name="rental_id" id="terminate_rental_id">
                            <input type="hidden" name="accommodation_id" id="terminate_accommodation_id">
                            
                            <div class="modal-action-row">
                                <button type="button" class="modal-cancel-btn" onclick="toggleTerminateModal(false)">Cancel</button>
                                <button type="submit" class="modal-save-btn" style="background: #dc3545;">Confirm End Lease</button>
                            </div>
                        </form>
                    </div>
                </div>
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
<script src="../../javascript-files/search-renter.js"></script>