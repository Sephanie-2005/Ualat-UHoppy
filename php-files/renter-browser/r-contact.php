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

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/owner/o-contact.css">
    <link rel="stylesheet" href="../../style/profile-settings.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <header>
                    
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                    
                <button id="home-btn" class="home_button" onclick="window.location.href='renter-homepage.php'">HOME</button> 
                <button id="listings-btn" class="listings_button" onclick="window.location.href='r-listings.php'">LISTINGS</button>
                <button id="messages-btn" class="messages_button" onclick="window.location.href='r-messages.php'">MESSAGES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='r-about.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button active">CONTACT</button>
                    
                <div class="profile-nav-wrapper">
                    <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
                </div> 

            </header>

            <main class="Section_1">
            <div class="contact-hero-container">
                <h1 class="contact-title">Get in Touch</h1>
                <p class="contact-subtitle">Have questions? We are here to help!</p>
            </div>
            </main>

            <main class="Section_2">
                <div class="form-container">
                    <h2>Send Us a Message</h2>
                    <form action="../process-and-setting/send-contact.php" method="POST" class="contact-form">
                        <div class="form-group">
                            <input type="text" name="contact_name" placeholder="Your Full Name" required class="contact-input">
                        </div>
                        <div class="form-group">
                            <input type="email" name="contact_email" placeholder="Your Email Address" required class="contact-input">
                        </div>
                        <div class="form-group">
                            <select name="contact_subject" required class="contact-select">
                                <option value="" disabled selected>Select Your Inquiry Type</option>
                                <option value="General Question">General Question</option>
                                <option value="Renting / Mapping Support">Renting / Mapping Support</option>
                                <option value="Landlord / Property Listing Support">Landlord / Property Listing Support</option>
                                <option value="Payment Tracking / Technical Bug">Payment Tracking / Technical Bug</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <textarea name="contact_message" placeholder="Type your message here..." rows="5" required class="contact-text-area"></textarea>
                        </div>
                        <button type="submit" class="contact-submit-btn">Send Message</button>
                    </form>
                </div>
            </main>

            <main class="Section_3">
                <h2 class="directory-title">Other Ways to Connect</h2>
                <div class="directory-grid">
                    <div class="directory-card">
                        <h3>Email Support</h3>
                        <p>uhoppy@gmail.com</p>
                    </div>
                    <div class="directory-card">
                        <h3>Call Center</h3>
                        <p>(+63) 917 123 4567 <br> (+63) 917 765 4321</p>
                    </div>
                    <div class="directory-card">
                        <h3>Main Office</h3>
                        <p>Dumaguete City, Negros Oriental, Philippines</p>
                    </div>
                </div>
            </main>
            <?php include 'o-footer.php';  ?>
            <?php include '../process-and-setting/profile-settings-view.php'; ?>
        </div>
            <script src="../../javascript-files/profile-settings-modal.js"></script>
    </body>
        
</html>