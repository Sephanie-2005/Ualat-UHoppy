<?php 
    require_once 'database-connection.php';
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UHoppy Listings</title>
    <link rel="stylesheet" href="../style/web-app.css">
    <link rel="stylesheet" href="../style/header-style.css">
    <link rel="stylesheet" href="../style/footer-style.css">
    <link rel="stylesheet" href="../style/listings.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../system-images/Link Logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <div class="square1"></div>
            <div class="square2"></div>
            <div class="square3"></div>
            <div class="square4"></div>
            <div class="square5"></div>
            <div class="square6"></div>
            <div class="square7"></div>

            <header>
                
                <img src="../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                
                <button id="home-btn" class="home_button" onclick="window.location.href='index.php'">HOME</button>
                <button id="listings-btn" class="listings_button active">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='about_us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button>
                <button id="sign_in-btn" class="sign_in_button" onclick="window.location.href='sign_in.php'">Sign In</button>
            </header>

            <main>
                
            </main>
            
            <?php include 'footer.php'; ?>
            
        </div>
    </body>
        
</html>