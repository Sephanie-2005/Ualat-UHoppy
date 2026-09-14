<?php 
    require_once '../process-and-setting/database-connection.php'; 
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/default/about-us.css">
    <link rel="stylesheet" href="../../style/default/background-shapes.css">
    <link rel="stylesheet" href="../../style/sign-in.css">
    <link rel="stylesheet" href="../../style/sign-up.css">
    <link rel="stylesheet" href="../../style/pass-required-input.css">

    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
    </head>

    <body>
        <div class="web-app">
            <header>
                    
                <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
                    
                <button id="home-btn" class="home_button" onclick="window.location.href='index.php'">HOME</button> 
                <button id="listings-btn" class="listings_button" onclick="window.location.href='listings.php'">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
                <button id="about_us-btn" class="about_us_button active" onclick="window.location.href='about-us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button>
                    
                <button id="sign_in-btn" class="sign_in_button" onclick="openModal(event)">Sign In</button>

            </header>

            <main class="Section_1">
                <div class="container">
                    <h1 class="title">UHoppy</h1>
                    <p class="subtitle">Hop into the happiness in finding a place to stay!</p>
                </div>
            </main>

            <main class="Section_2">
                <div class="mission-container">
                    <h1>Our Mission</h1>
                    <p class="mission-text">
                        Our mission is to simplify the housing search by helping users discover, map out, and secure 
                        affordable accommodations while providing built-in tracking for rent payments and direct 
                        communication with landlords.
                    </p>
                </div>
            </main>

            <main class="Section_3">
                <div class="story-container">
                    <h2>How UHoppy Began</h2>
                    <p class="story-paragraph">
                        Finding a place to live shouldn't feel like navigating an endless, exhausting maze. UHoppy was born out of a simple, universal frustration that almost every renter knows too well. The exhausting hustle of walking street by street under the blazing sun or pouring rain, scouring fences for faded "Room for Rent" signs. Too many days were wasted knock on doors just to find a single available room, guessing hidden rental costs, and dealing with the constant anxiety of losing track of loose paper rent receipts. We watched students struggling to balance their classes while hunting for bedspacers, and young professionals spending their entire weekends searching for a decent boarding house.
                    </p>
                    <p class="story-paragraph">
                        As we looked closer at the problem, we realized it wasn't just hard for renters, it was stressful for property owners too. Landlords and landladies were relying on old-school notebooks to log payments, manually tracking who paid for what month, and dealing with chaotic text messages scattered across different apps. It became blindingly clear that students, young professionals, and property owners desperately needed a smarter, unified digital ecosystem built specifically to bridge this gap.
                    </p>
                    <p class="story-paragraph">
                        Driven by the vision to make housing seamless, we built UHoppy to completely eliminate the stress of moving and property management. By bringing advanced mapping precision, automated lease and payment tracking, and a direct, secure instant messaging system under one digital roof, we bridge the gap between hosts and seekers. We want to ensure that from the very first click to your final month's rent payment, finding and securing your next home is as joyful, organized, and happy as it truly deserves to be.
                    </p>
                </div>
            </main>

            <main class="Section_4">
                <h2 class="section-title">What We Do</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <h3>Discover & Map Out</h3>
                        <p>Easily find and visualize local apartments, boarding houses, bedspacers, and alternative spaces tailored to your budget.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Stay & Rent Tracking</h3>
                        <p>Keep a clear history of your exact duration of stay and organize your ongoing rent payments without the messy paperwork.</p>
                    </div>
                    <div class="feature-card">
                        <h3>Direct Chat</h3>
                        <p>Communicate instantly with landlords and landladies to ask questions, book viewings, and finalize stay arrangements.</p>
                    </div>
                </div>
            </main>

            <main class="Section_5">
                <div class="cta-box">
                    <h2>Ready to find your next home?</h2>
                    <p>Start exploring verified properties around you today.</p>
                    <button id="start_search-btn" class="start_search_button" onclick="window.location.href='listings.php'">Start Your Search</button> 
                </div>
            </main>
                
            </div>
                <?php 
                    include 'footer.php';  
                ?>
        </div>
    </body>
        
</html>

<?php 
    include '../sign-in-and-sign-up/sign-in.php';
    include '../sign-in-and-sign-up/sign-up.php'; 
?>

<script src="../../javascript-files/sign-up.js"></script>
<script src="../../javascript-files/sign-in.js"></script>
<script src="../../javascript-files/pass-required-input.js"></script>