<?php 
    require_once 'database-connection.php';
?>

<!DOCTYPE html> 
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UHoppy Homepage</title>
      <link rel="stylesheet" href="../style/web-app.css">
    <link rel="stylesheet" href="../style/header-style.css">
    <link rel="stylesheet" href="../style/footer-style.css">
    <link rel="stylesheet" href="../style/homepage.css">
    <link rel="stylesheet" href="../style/background-shapes.css">
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
                
                <button id="home-btn" class="home_button active">HOME</button>
                <button id="listings-btn" class="listings_button" onclick="window.location.href='listings.php'">LISTINGS</button>
                <button id="features-btn" class="features_button" onclick="window.location.href='features.php'">FEATURES</button>
                <button id="about_us-btn" class="about_us_button" onclick="window.location.href='about_us.php'">ABOUT US</button>
                <button id="contact-btn" class="contact_button" onclick="window.location.href='contact.php'">CONTACT</button>
                <button id="sign_in-btn" class="sign_in_button" onclick="window.location.href='sign_in.php'">Sign In</button>

            </header>

            <main class="Section_1">
                <h1 class="text_1">Find Your Happy Place.</h1>
                <p class="par_1">Find and map out an apartment, boarding house, 
                    <br> bedspacer, and other place to stay. 
                    <br> Track duration of stay and rent payments. 
                    <br> Chat with landlords and landlady.
                </p>
                <button id="start_search-btn" class="start_search_button" onclick="window.location.href='listings.php'">Start Your Search</button>     
            </main>

            <main class="Section_2">
                <h2 class="featured_listings">Featured Listings</h1>
                <button id="view_all-btn" class="view_all_button" onclick="window.location.href='listings.php'">View All</button>

                <div class="card-container">

                    <div class="room-card">
                        <img src="../uploaded-images/Shared_room_1.png" alt="Shared room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Shared room</h3>
                            <p class="free-label">Free:</p>
                            <ul class="amenities-list">
                                <li>Water</li>
                                <li>Electricity</li>
                                <li>Wi-fi</li>
                            </ul>
                            <div class="price-tag1">₱ 2,250</div>
                        </div>
                    </div>

                    <div class="room-card">
                        <img src="../uploaded-images/Solo_room.png" alt="Solo room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Solo room</h3>
                            <p class="free-label">Free:</p>
                            <ul class="amenities-list">
                                <li>Water</li>
                                <li>Wi-fi</li>
                            </ul>
                            <div class="price-tag2">₱ 2,000</div>
                        </div>
                    </div>

                    <div class="room-card">
                        <img src="../uploaded-images/Shared_room_2.png" alt="Shared room" class="room-image">
                        <div class="room-details">
                            <h3 class="room-title">Shared room</h3>
                            <p class="free-label">Free:</p>
                            <ul class="amenities-list">
                                <li>Water</li>
                                <li>Electricity</li>
                                <li>Wi-fi</li>
                                <li>Gated</li>
                            </ul>
                            <div class="price-tag3">₱ 1,800</div>
                        </div>
                    </div>
                </div>

                <div class="second-card-container">

                    <div class="second-room-card">
                        <img src="../uploaded-images/Shared_room_3.png" alt="Shared room" class="second-room-image">
                        <div class="second-room-details">
                        <h3 class="second-room-title">Shared room</h3>
                        <p class="second-free-label">Free:</p>
                        <ul class="second-amenities-list">
                            <li>Water</li>
                            <li>Electricity</li>
                            <li>Wi-fi</li>
                        </ul>
                        <div class="second-price-tag1">₱ 1,600</div>
                        </div>
                    </div>

                    <div class="second-room-card">
                        <img src="../uploaded-images/Shared_room_4.png" alt="Shared room" class="second-room-image">
                        <div class="second-room-details">
                            <h3 class="second-room-title">Shared room</h3>
                            <p class="second-free-label">Free:</p>
                            <ul class="second-amenities-list">
                                <li>Water</li>
                                <li>Electricity</li>
                                <li>Wi-fi</li>
                                <li>24 hr surveilance</li>
                            </ul>
                            <div class="second-price-tag2">₱ 3,000</div>
                        </div>
                    </div>

                    <div class="second-room-card">
                        <img src="../uploaded-images/Bedspacer.png" alt="Bedspacer" class="second-room-image">
                        <div class="second-room-details">
                            <h3 class="second-room-title">Bedspacer</h3>
                            
                            <ul class="second-amenities-list">
                                <p></p>
                                <li>Air conditioned</li>
                                <li>Free wi-fi</li>
                                <li>With parking space</li>
                            </ul>
                            <div class="second-price-tag3">₱ 3,500</div>
                        </div>
                    </div>
                </div>

            </main>

            <main class="Section_3">
                <h2 class="reviews">Reviews</h1>

                    <div class="reviews-container">

                        <div class="review-card">
                            <div class="stars">★★★★★</div>
                            <h2>Sarah L.</h2>
                            <p>
                                I've been burned by messy rental agreements before.
                                Having the rent payment tracker gives me peace of mind.
                            </p>
                        </div>

                        <div class="review-card">
                            <div class="stars">★★★★★</div>
                            <h2>Elena R.</h2>
                            <p>
                                Managing multiple boarding spaces can be stressful,
                                but the platform makes everything easier.
                            </p>
                        </div>

                        <div class="review-card">
                            <div class="stars">★★★★☆</div>
                            <h2>Chloe M.</h2>
                            <p>
                                The map tool helped me find the right location.
                            </p>
                        </div>

                        <div class="review-card">
                            <div class="stars">★★★★★</div>
                            <h2>Marcus V.</h2>
                            <p>
                                The payment tracking feature saves so much time.
                            </p>
                        </div>

                        <div class="review-card">
                            <div class="stars">★★★★★</div>
                            <h2>Alisha T.</h2>
                            <p>
                                I found an amazing place through this platform.
                            </p>
                        </div>

                    </div>

                    <div class="second-reviews-container">

                        <div class="second-review-card">
                            <div class="second-stars">★★★★★</div>
                            <h2>Rica S.</h2>
                            <p>
                                I’ve been burned by messy rental
                                agreements before, so having the
                                built-in rent payment tracker on this
                                app gives me so much peace of mind.
                                I can see exactly how many months
                                I have left on my stay, and my landlord
                                gets the receipts instantly.
                                Highly recommend!
                            </p>
                        </div>

                        <div class="second-review-card">
                            <div class="second-stars">★★★★★</div>
                            <h2>David L.</h2>
                            <p>
                                As a landlady managing multiple boarding
                                spaces, keeping rooms filled can be
                                stressful. Listing on UHoppy was
                                incredibly straightforward.
                            </p>
                        </div>

                        <div class="second-review-card">
                            <div class="second-stars">★★★★☆</div>
                            <h2>Smith M.</h2>
                            <p>
                                The tenants who message me are
                                verified, and the direct chat feature
                                makes it simple to coordinate check-ins
                                and screen applicants beforehand.
                            </p>
                        </div>

                        <div class="second-review-card">
                            <div class="second-stars">★★★★★</div>
                            <h2>John H.</h2>
                            <p>
                                Managing twelve rooms used to mean
                                piles of receipts and logbooks.
                                Now, the app's digital ledger handles
                                the duration of stay and payment
                                tracking automatically. It lost one star
                                because I'd love a feature to export the
                                monthly logs as a CSV file, but
                                otherwise, it's brilliant for landlords.
                            </P>
                        </div>

                        <div class="second-review-card">
                            <div class="second-stars">★★★★★</div>
                            <h2>Jason K.</h2>
                            <p>
                                I was terrified of moving to a new city
                                alone, but the community vibe on this
                                platform is amazing. I didn't just find a
                                cheap room, I found an amazing
                                'hoppy' housemate through the listing
                                details. It feels less like a sterile rental
                                app and more like a community
                            </p>
                        </div>

                    </div>

                </section>

            </main>

            <main class="Section_4">
                <p class="uhoppy">UHoopy</p>
                <p class="par_2"> &emsp; &emsp; is an easy-to-use web application that connects
                    <br>people looking for a room with landlords who have places to rent.
                    <br>It makes finding and managing a boarding house simple and stress-free for both sides.
                </p>
                <h1 class="text_2">Sign Up Now! As a Renter or Owner.</h1>
                <button id="sign_up-btn" class="sign_up_button" onclick="window.location.href='sign_in.html'">Sign Up</button>
            </main>

            <main class="Section_5">
                <h1 class="how_it_works_text">How it Works</h1>
                <img src="../system-images/Search icon.png" alt="Search icon" class="search_icon">
                <h2 class="search_text">1. Search</h2>
                <img src="../system-images/Connect icon.png" alt="Connect icon" class="connect_icon">
                <h2 class="connect_text">2. Connect</h2>
                <img src="../system-images/Rent icon.png" alt="Rent icon" class="rent_icon">
                <h2 class="rent_text">3. Rent</h2>
                <img src="../system-images/Live happily icon.png" alt="Live Happily icon" class="live_happily_icon">
                <h2 class="live_happily_text">4. Live Happily</h2>
            </main>
            
            <?php include 'footer.php'; ?>
            
        </div>
    </body>
        
</html>