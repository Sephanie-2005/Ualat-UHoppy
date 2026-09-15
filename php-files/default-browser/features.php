<?php require_once '../process-and-setting/database-connection.php'; ?> 
<!DOCTYPE html> 
<html lang="en"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>Listings</title> 
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/default/header-style.css">
        <link rel="stylesheet" href="../../style/default/footer-style.css">
        <link rel="stylesheet" href="../../style/default/features.css">
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
                <button id="features-btn" class="features_button active">FEATURES</button> 
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='about-us.php'">ABOUT US</button> 
                <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button> 
                <button id="sign_in-btn" class="sign_in_button" onclick="openModal()">Sign In</button> 
            </header> 

            <main class="features-section-1">  
                <h1 class="features-title">UHoppy Features</h1> 
                <h3 class="features-description">Discover the unique features of UHoppy that make finding your perfect home a breeze. <br> From our user-friendly interface to our comprehensive property listings, <br> we provide everything you need to make your home search effortless and enjoyable.</hr> 
            </main>
            
            <main class="features-section-2">
                <h2 class="features-title">Find Accommodations</h2>
                <p>Locate the perfect apartment, boarding house, or bedspacer via our interactive mapping interface.</p>
            </main>

            <main class="features-section-3">          
                <h2>Track Stay & Payments</h2>
                <p>Keep a clear history of your stay duration. <br> Monitor monthly payments and deadlines effortlessly.</p>
            </main>

            <main class="features-section-4">
                <h2>Direct Landlord Chat</h2>
                <p>Connect instantly with landlords and landladies <br> to negotiate terms and finalize bookings.</p>
            </main>

            <main class="features-section-5">
                <div class="feature-previews">
    
                    <div class="lease-card">
                        <div class="property-info">
                            <h3>Maria Dorminatory</h3>
                            <p class="info-line"><strong>Location:</strong> Culipapa, Negros Occidental</p>
                            <p class="info-line"><strong>Unit / Unit Name:</strong> Room 204</p>
                            <p class="info-line"><strong>Unit Type:</strong> Single Room</p>
                            <p class="info-line"><strong>Monthly Rent:</strong> PHP 2,000.00</p>
                        </div>
                        
                        <div class="dates-info">
                            <div class="date-row">
                                Start Date:<br>
                                <span>September 15, 2026</span>
                            </div>
                            <div class="date-row">
                                Expiration Date:<br>
                                <span class="date-highlight">October 15, 2026</span>
                            </div>
                            <div class="date-row total-duration">
                                Total Duration: 1 Months
                            </div>
                        </div>
                    </div>
                
                    <div class="preview-section">
                        <h2 class="section-title">Direct Landlord Chat Box</h2>
                        <p class="section-subtitle">Clear transparent communications without leaving the app.</p>
                
                        <div class="chat-mockup">
                            <div class="chat-sidebar">
                                <div class="chat-user-node active">
                                    <div class="chat-avatar">MM</div>
                                    <div class="chat-node-info">
                                        <strong>Maria Mercedes (Landlady)</strong>
                                        <span class="preview-text">The room is available...</span>
                                    </div>
                                </div>
                                <div class="chat-user-node">
                                    <div class="chat-avatar">RS</div>
                                    <div class="chat-node-info">
                                        <strong>Rodolfo Santos (Landlord)</strong>
                                        <span class="preview-text">Payment received, thanks!</span>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-window">
                                <div class="chat-window-header">
                                    <div class="header-user-details">
                                        <div class="chat-avatar header-avatar">MM</div>
                                        <div>
                                            <h4 class="chat-target-name">Maria Mercedes</h4>
                                            <span class="chat-target-status">Active now · Greenview Apartment</span>
                                        </div>
                                    </div>
                                    <div class="chat-header-actions">
                                        <button class="chat-action-btn" type="button">View Listing</button>
                                    </div>
                                </div>

                                <div class="chat-messages-area">
                                    <div class="chat-date-separator"><span>Today</span></div>
                                    <div class="msg-bubble incoming">
                                        <p>Hello! Is the room still open for viewing this upcoming weekend?</p>
                                        <span class="msg-time">10:14 AM</span>
                                    </div>
                                    <div class="msg-bubble outgoing">
                                        <p>Yes, certainly! You can drop by around 2:00 PM on Saturday.</p>
                                        <span class="msg-time">10:15 AM</span>
                                    </div>
                                </div>
                                <div class="chat-input-area-mock">
                                    <div class="chat-input-placeholder">Type your query message details here...</div>
                                    <button class="chat-send-btn-mock" type="button">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

             <main class="features-section-6">  
                <h1>SIGN UP NOW!</h1> 
                <h3 class="features-description">Discover the unique features of UHoppy that make finding your perfect home a breeze.     
                <br> From our user-friendly interface to our comprehensive property listings,  
                <br> we provide everything you need to make your home search effortless and enjoyable.</h3> 
                <button id="sign_up-btn" class="sign_up_button" onclick="openSignupModal()">Sign Up</button>
            </main>

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