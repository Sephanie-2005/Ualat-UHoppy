document.addEventListener("DOMContentLoaded", function () {
    // 1. Check the URL parameters for success or error keywords
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        
        // 2. Trigger beautiful alerts depending on what was added
        if (successType === 'property_added') {
            alert("🏢 Success! Your new property has been successfully registered.");
        } else if (successType === 'accommodation_added') {
            alert("🛏️ Success! Your accommodation unit has been published.");
        }
        
        // 3. Clean up the URL address bar automatically (removes the ?success=... text)
        // This stops the alert from popping up again if the user refreshes the page!
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }

    // 4. Optional: Handle error codes gracefully too
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        alert("⚠️ Error: " + errorType.replace(/_/g, ' ') + ". Please check your inputs and try again.");
        
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }
});
