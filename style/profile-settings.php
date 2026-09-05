.settings-layout-container {
    display: flex;
    max-width: 1100px;
    margin: 30px auto;
    gap: 30px;
    padding: 0px 20px 0px 20px;
}

/* Sidebar Dashboard Configuration Styles */
.settings-sidebar {
    width: 260px;
    background-color: #ffffff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
}

.sidebar-user-card {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eeeeee;
    margin-bottom: 15px;
}

.sidebar-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ff7a59;
}

.sidebar-user-card h3 {
    font-size: 16px;
    margin: 10px 0px 5px 0px;
    color: #333333;
}

.role-badge {
    background-color: #e3f2fd;
    color: #0d47a1;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px 3px 8px;
    border-radius: 12px;
}

.sidebar-tab {
    background: none;
    border: none;
    text-align: left;
    padding: 12px 15px 12px 15px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 4px;
    color: #555555;
    margin-bottom: 5px;
}

.sidebar-tab:hover, .sidebar-tab.active {
    background-color: #f5f5f5;
    color: #007bff;
}

.sidebar-tab.danger-tab:hover {
    background-color: #fff5f5;
    color: #dc3545;
}

.sidebar-logout-link {
    margin-top: 30px;
    text-align: center;
    color: #666666;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #cccccc;
}

.sidebar-logout-link:hover {
    background-color: #fafafa;
    color: #333333;
}

/* Forms Main Configuration Frame Elements */
.settings-main-panel {
    flex: 1;
    background-color: #ffffff;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
}

.settings-section {
    display: none;
}

.settings-section.visible {
    display: block;
}

.avatar-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.form-avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.upload-trigger-btn {
    background-color: #f0f0f0;
    font-size: 13px;
    padding: 6px 12px 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
}

.field-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.field-item label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #666666;
}

.field-item input {
    padding: 10px;
    font-size: 14px;
    border: 1px solid #cccccc;
    border-radius: 4px;
    outline: none;
}

.field-item input.readonly-input {
    background-color: #f9f9f9;
    color: #888888;
    cursor: not-allowed;
}

.commit-btn {
    background-color: #ff7a59;
    color: #ffffff;
    padding: 10px 20px 10px 20px;
    border-radius: 4px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}

/* Danger Zone Cards Framework Layout */
.danger-box-card {
    border: 1px solid #f5c6cb;
    background-color: #fff5f5;
    padding: 20px;
    border-radius: 6px;
}

.annihilate-btn {
    background-color: #dc3545;
    color: #ffffff;
    border: none;
    padding: 10px 20px 10px 20px;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
}

/* Modal Overlay Verification Framework Box */
.modal-overlay {
    position: fixed;
    top: 0px;
    left: 0px;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.modal-overlay.modal-active {
    opacity: 1;
    pointer-events: auto;
}

.modal-box {
    background-color: #ffffff;
    padding: 25px;
    border-radius: 6px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0px 4px 16px rgba(0,0,0,0.2);
}

.modal-input {
    width: 93%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #cccccc;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}