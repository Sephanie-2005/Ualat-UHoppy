// Tab View Switcher Engine
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active-content'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active-content');
    event.currentTarget.classList.add('active');
}

// Dynamic Sub-Room (Accommodation) Row Generator
function addAccommodationRow() {
    const container = document.getElementById('accommodation-rows-container');
    const newRow = document.createElement('div');
    newRow.className = 'accommodation-row';
    newRow.innerHTML = `
        <div class="row-input">
            <label>Unit Name/No.</label>
            <input type="text" name="acc_name[]" required placeholder="e.g., Room 102">
        </div>
        <div class="row-input">
            <label>Monthly Rent (PHP)</label>
            <input type="number" name="acc_price[]" min="0" required placeholder="0.00">
        </div>
        <div class="row-input">
            <label>Capacity (Pax)</label>
            <input type="number" name="acc_capacity[]" min="1" required placeholder="1">
        </div>
        <button type="button" class="remove-row-btn" onclick="removeAccommodationRow(this)">Remove</button>
    `;
    container.appendChild(newRow);
}

// Remove accommodation row entry
function removeAccommodationRow(button) {
    const rows = document.querySelectorAll('.accommodation-row');
    if(rows.length > 1) {
        button.parentElement.remove();
    } else {
        alert("You must configure at least one active unit type for this property.");
    }
}