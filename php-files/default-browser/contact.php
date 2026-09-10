<?php 
    require_once '../process-and-setting/database-connection.php'; 
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UHoppy Homepage</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/default/contact.css">
    <link rel="stylesheet" href="../../style/default/background-shapes.css">
    <link rel="stylesheet" href="../../style/sign-in.css">
    <link rel="stylesheet" href="../../style/sign-up.css">
    <link rel="stylesheet" href="../../style/pass-required-input.css">

    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <header>
                    
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                    
                <button id="home-btn" class="home_button" onclick="window.location.href='index.php'">HOME</button> 
                <button id="listings-btn" class="listings_button" onclick="window.location.href='listings.php'">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='about-us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button active" onclick="window.location.href='contact.php'">CONTACT</button>
                    
                <button id="sign_in-btn" class="sign_in_button" onclick="openModal(event)">Sign In</button>

            </header>

            <main class="Section_1">
            <div class="contact-hero-container">
                <h1 class="contact-title">Get in Touch</h1>
                <p class="contact-subtitle">Have questions about listings, map tools, or rent tracking? We are here to help!</p>
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
                        <span class="directory-icon">✉️</span>
                        <h3>Email Support</h3>
                        <p>uhoppy@gmail.com</p>
                    </div>
                    <div class="directory-card">
                        <span class="directory-icon">📞</span>
                        <h3>Call Center</h3>
                        <p>(+63) 917 123 4567 <br> (+63) 917 765 4321</p>
                    </div>
                    <div class="directory-card">
                        <span class="directory-icon">📍</span>
                        <h3>Main Office</h3>
                        <p>Dumaguete City, Negros Oriental, Philippines</p>
                    </div>
                </div>
            </main>
            
        </div>
            <?php 
                include 'footer.php';  
            ?>
    </body>
        
</html>

<?php 
    include '../sign-in-and-sign-up/sign-in.php';
    include '../sign-in-and-sign-up/sign-up.php'; 
?>

<script src="../../javascript-files/sign-up.js"></script>
<script src="../../javascript-files/sign-in.js"></script>
<script src="../../javascript-files/pass-required-input.js"></script>