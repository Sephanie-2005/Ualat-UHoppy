<?php 
    require_once 'database-connection.php';
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UHoppy About Us</title>
    <link rel="stylesheet" href="../style/web-app.css">
    <link rel="stylesheet" href="../style/header-style.css">
    <link rel="stylesheet" href="../style/footer-style.css">
    <link rel="stylesheet" href="../style/about-us.css">
    <link rel="stylesheet" href="../style/background-shapes.css">
    <link rel="stylesheet" href="../style/sign-in.css">
    <link rel="stylesheet" href="../style/sign-up.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../system-images/Link Logo.jpg">
    </head>

    <body>
        <div class="web-app">

            <header>
                
                <img src="../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                
                <button id="home-btn" class="home_button" onclick="window.location.href='index.php'">HOME</button>
                <button id="listings-btn" class="listings_button" onclick="window.location.href='listings.php'">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
                <button id="about_us-btn" class="about_us_button active">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button>
                <button id="sign_in-btn" class="sign_in_button" onclick="openModal()">Sign In</button>
            </header>

            <main>
                
            </main>
            
            <?php 
                include 'footer.php';
                include 'sign-in.php'; 
                include 'sign-up.php'; 
            ?>
            
        </div>
    </body>
        
</html>