function toggleRentalModal(show) {
    const modal = document.getElementById('addRentalModal');
    if (!modal) return;
    
    if (show) {
        modal.classList.add('is-visible');
    } else {
        modal.classList.remove('is-visible');
       
        document.getElementById('renter_id_select').value = "";
    }
}

function searchAllSystemRenters() {
    const searchInput = document.getElementById('renterSearchInput');
    const dropdown = document.getElementById('searchDropdownList');
    const storageEl = document.getElementById('allRentersDataStorage');
    
    if (!searchInput || !dropdown || !storageEl) return;
    
    const searchVal = searchInput.value.trim().toLowerCase();
    dropdown.innerHTML = ""; 
    
    if (searchVal === "") {
        dropdown.style.display = "none";
        return;
    }
    
    let allRenters = [];
    try {
        allRenters = JSON.parse(storageEl.getAttribute('data-all-renters')) || [];
    } catch(e) {
        console.error("Error reading renter data payload", e);
        return;
    }
    
    let matchCount = 0;
    
    allRenters.forEach(function(renter) {
        const firstName = (renter.first_name || "").toLowerCase();
        const lastName = (renter.last_name || "").toLowerCase();
        const email = (renter.email || "").toLowerCase();
        const fullName = firstName + " " + lastName;
        
        if (fullName.includes(searchVal) || email.includes(searchVal)) {
            matchCount++;
            
            const itemRow = document.createElement('div');
            itemRow.className = "search-result-item";
            
            const infoDiv = document.createElement('div');
            infoDiv.className = "search-result-info";
            
            const nameSpan = document.createElement('strong');
            nameSpan.innerText = renter.first_name + " " + renter.last_name;
            
            const emailSpan = document.createElement('span');
            emailSpan.className = "search-result-email";
            emailSpan.innerText = renter.email;
            
            infoDiv.appendChild(nameSpan);
            infoDiv.appendChild(emailSpan);
            
            const addBtn = document.createElement('button');
            addBtn.type = "button";
            addBtn.className = "dropdown-add-action-btn";
            addBtn.innerText = "Add";
            
            addBtn.onclick = function(e) {
                e.stopPropagation(); 
                
                const selectElement = document.getElementById('renter_id_select');
                if (selectElement) {
                    selectElement.value = renter.renter_id;
                }
                
                searchInput.value = "";
                dropdown.style.display = "none";
                
                toggleRentalModal(true);
            };
            
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