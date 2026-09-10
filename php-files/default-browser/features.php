<?php require_once '../process-and-setting/database-connection.php'; ?> 
<!DOCTYPE html> 
<html lang="en"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>UHoppy Listings</title> 
        <link rel="stylesheet" href="../../style/default/web-app.css">
        <link rel="stylesheet" href="../../style/default/header-style.css">
        <link rel="stylesheet" href="../../style/default/footer-style.css">
        <link rel="stylesheet" href="../../style/default/features.css">
        <link rel="stylesheet" href="../../style/profile-settings.css">
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
                <h2 class="features-title">Find & Map Accommodations</h2>
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
                    <div class="preview-section">
                            <h2 class="section-title">Interactive Neighborhood Mapping</h2>
                            <p class="section-subtitle">Filter instantly by Apartments, Boarding Houses, or Bedspacers.</p>
                            <div class="map-mockup">
                                <div class="map-sidebar">
                                    <div class="sidebar-item active">
                                        <h4>Greenview Apartment</h4>
                                        <p>₱8,500/mo · 2 Beds</p>
                                    </div>
                                    <div class="sidebar-item">
                                        <h4>Dormitel Bedspacer</h4>
                                        <p>₱2,500/mo · Bedspace</p>
                                    </div>
                                    <div class="sidebar-item">
                                        <h4>Cozy Boarding House</h4>
                                        <p>₱4,000/mo · Single Room</p>
                                    </div>
                                </div>
                    
                                <div class="map-canvas">
                                    <span class="map-placeholder-text">Interactive Map View Placeholder</span>
                                
                                    <div class="map-pin" style="top: 30%; left: 45%;"><span class="pin-label">Greenview</span></div>
                                    <div class="map-pin" style="top: 60%; left: 25%;"><span class="pin-label">Dormitel</span></div>
                                    <div class="map-pin" style="top: 45%; left: 70%;"><span class="pin-label">Boarding House</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h2 class="section-title">Stay Duration & Payment Tracker</h2>
                            <p class="section-subtitle">Real-time status updates for your current active tenancy.</p>
                            <div class="tracker-mockup">
                                <div class="tracker-header">
                                    <div><strong>Current Stay:</strong> Greenview Apartment (Room 302)</div>
                                    <div><span class="status-badge paid">Active Contract</span></div>
                                </div>
                                <div class="tracker-stats-grid">
                                    <div class="stat-box">
                                        <span class="stat-label">Total Duration</span>
                                        <span class="stat-value">6 Months</span>
                                        <span class="stat-sub">June 1, 2026 - Nov 30, 2026</span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label">Days Remaining</span>
                                        <span class="stat-value">85 Days</span>
                                        <span class="stat-sub">Next renewal notice: Oct 31</span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label">Next Rent Due</span>
                                        <span class="stat-value">₱8,500.00</span>
                                        <span class="stat-sub">Due on October 1, 2026</span>
                                    </div>
                                </div>
                                <table class="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Billing Period</th>
                                            <th>Amount Paid</th>
                                            <th>Date Transacted</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Sept 1 - Sept 30, 2026</td>
                                            <td>₱8,500.00</td>
                                            <td>Sept 01, 2026</td>
                                            <td><span class="badge paid">Paid</span></td>
                                        </tr>
                                        <tr>
                                            <td>Aug 1 - Aug 31, 2026</td>
                                            <td>₱8,500.00</td>
                                            <td>Aug 02, 2026</td>
                                            <td><span class="badge paid">Paid</span></td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                    <button class="chat-action-btn" title="View Property Details">View Listing</button>
                                </div>
                            </div>

                            <div class="chat-messages-area">
                                <div class="chat-date-separator"><span>Today</span></div>

                                <div class="message incoming">
                                    <div class="message-content">
                                        <p>Hello! Yes, the bedspacer slot and single rooms are still available for viewing tomorrow.</p>
                                        <span class="timestamp">10:30 AM</span>
                                    </div>
                                </div>

                                <div class="message outgoing">
                                    <div class="message-content">
                                        <p>Great! Can I drop by around 2:00 PM after my classes? Is a 1-month deposit required?</p>
                                        <span class="timestamp">10:32 AM</span>
                                    </div>
                                </div>

                                <div class="message incoming">
                                    <div class="message-content">
                                        <p>Sure, 2:00 PM works perfectly. Yes, we require 1 month advance and 1 month deposit to secure the slot.</p>
                                        <span class="timestamp">10:35 AM</span>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-input-mock">
                                <div class="chat-attachment-trigger"></div>
                                <input type="text" class="chat-input-field" placeholder="Type your inquiry message here..." disabled />
                                <button class="send-mock-btn" disabled>
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                    </svg>
                                </button>
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
    include '../process-and-setting/profile-settings.php'; 
?>

<script src="../../javascript-files/sign-up.js"></script>
<script src="../../javascript-files/sign-in.js"></script>
<script src="../../javascript-files/pass-required-input.js"></script>