/**
 * Controller actions for Owner Dashboard Global Renter Search
 */

// Controls modal screen appearance and visibility layout states
function toggleRentalModal(show) {
    const modal = document.getElementById('addRentalModal');
    if (!modal) return;
    
    if (show) {
        modal.classList.add('is-visible');
    } else {
        modal.classList.remove('is-visible');
        // Reset the form fields if closed
        document.getElementById('renter_id_select').value = "";
    }
}

// Search all registered system renters and display with inline Add button
function searchAllSystemRenters() {
    const searchInput = document.getElementById('renterSearchInput');
    const dropdown = document.getElementById('searchDropdownList');
    const storageEl = document.getElementById('allRentersDataStorage');
    
    if (!searchInput || !dropdown || !storageEl) return;
    
    const searchVal = searchInput.value.trim().toLowerCase();
    dropdown.innerHTML = ""; // Clear old dropdown results
    
    if (searchVal === "") {
        dropdown.style.display = "none";
        return;
    }
    
    // Parse the system renters array payload out of our HTML attribute
    let allRenters = [];
    try {
        allRenters = JSON.parse(storageEl.getAttribute('data-all-renters')) || [];
    } catch(e) {
        console.error("Error reading renter data payload", e);
        return;
    }
    
    let matchCount = 0;
    
    // Filter through all data array entries
    allRenters.forEach(function(renter) {
        const firstName = (renter.first_name || "").toLowerCase();
        const lastName = (renter.last_name || "").toLowerCase();
        const email = (renter.email || "").toLowerCase();
        const fullName = firstName + " " + lastName;
        
        if (fullName.includes(searchVal) || email.includes(searchVal)) {
            matchCount++;
            
            // 1. Create container row
            const itemRow = document.createElement('div');
            itemRow.className = "search-result-item";
            
            // 2. Create Left-hand content info text section (Name and Email)
            const infoDiv = document.createElement('div');
            infoDiv.className = "search-result-info";
            
            const nameSpan = document.createElement('strong');
            nameSpan.innerText = renter.first_name + " " + renter.last_name;
            
            const emailSpan = document.createElement('span');
            emailSpan.className = "search-result-email";
            emailSpan.innerText = renter.email;
            
            infoDiv.appendChild(nameSpan);
            infoDiv.appendChild(emailSpan);
            
            // 3. Create Right-hand "+ Add" action button
            const addBtn = document.createElement('button');
            addBtn.type = "button";
            addBtn.className = "dropdown-add-action-btn";
            addBtn.innerText = "+ Add";
            
            // Click Handler: Selects renter, closes dropdown, clears input, and launches modal assignment instantly
            addBtn.onclick = function(e) {
                e.stopPropagation(); // Stops double execution loops
                
                // Assign value to selector item field drop elements
                const selectElement = document.getElementById('renter_id_select');
                if (selectElement) {
                    selectElement.value = renter.renter_id;
                }
                
                // Clear out lookup state variables text entries
                searchInput.value = "";
                dropdown.style.display = "none";
                
                // Open up lease creation panel window display options
                toggleRentalModal(true);
            };
            
            // Append structures together cleanly
            itemRow.appendChild(infoDiv);
            itemRow.appendChild(addBtn);
            dropdown.appendChild(itemRow);
        }
    });
    
    if (matchCount === 0) {
        const noMatchRow = document.createElement('div');
        noMatchRow.className = "search-result-no-match";
        noMatchRow.innerText = "No system renters found";
        dropdown.appendChild(noMatchRow);
    }
    
    dropdown.style.display = "block";
}

// Global click listeners: Closes dropdown if clicked outside the input context wrapper
document.addEventListener('click', function(event) {
    const searchGroup = document.querySelector('.search-action-group');
    const dropdown = document.getElementById('searchDropdownList');
    
    if (searchGroup && dropdown && !searchGroup.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

function openTerminateModal(rentalId, accommodationId, renterFullName) {
    document.getElementById('terminate_rental_id').value = rentalId;
    document.getElementById('terminate_accommodation_id').value = accommodationId;
    document.getElementById('terminate_renter_name').innerText = renterFullName;
    toggleTerminateModal(true);
}

function toggleTerminateModal(show) {
    const modal = document.getElementById('terminateLeaseModal');
    if(show) {
        modal.classList.add('is-visible');
    } else {
        modal.classList.remove('is-visible');
    }
}