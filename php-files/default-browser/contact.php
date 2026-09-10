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
    <link rel="stylesheet" href="../../style/default/about-us.css">
    <link rel="stylesheet" href="../../style/default/background-shapes.css">
    <link rel="stylesheet" href="../../style/sign-in.css">
    <link rel="stylesheet" href="../../style/sign-up.css">
    <link rel="stylesheet" href="../../style/pass-required-input.css">

    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
    </head>

    <body>
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
           
        </main>

        <main class="Section_2">
            
        </main>

        <main class="Section_3">
            
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