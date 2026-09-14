<div id="settings-popup-overlay" class="settings-popup-overlay">
    <div class="settings-popup-box">
        <div class="settings-popup-header">
            <h3>Account Settings</h3>
            <button class="settings-close-btn" type="button" onclick="closeSettingsModal()">&times;</button>
        </div>

        <div class="settings-popup-nav">
            <button class="popup-nav-tab active" type="button" onclick="switchPopupTab('popup-account', this)">Account Info</button>
            <button class="popup-nav-tab" type="button" onclick="switchPopupTab('popup-security', this)">Security</button>
            <button class="popup-nav-tab danger-tab" type="button" onclick="switchPopupTab('popup-danger', this)">Delete Account</button>
            <button class="popup-nav-tab logout-tab" type="button" onclick="window.location.href='../process-and-setting/logout.php'">Log Out</button>
        </div>

        <div class="settings-popup-body">
            <?php if(!empty($modalError)): ?>
                <div class="modal-error-banner">
                    <?php echo htmlspecialchars($modalError); ?>
                </div>
                <script>document.addEventListener('DOMContentLoaded', () => openSettingsModal());</script>
            <?php endif; ?>
        
            <div id="popup-account" class="popup-section visible">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modal_settings_action" value="save_info">
                    
                    <div class="popup-avatar-row">
                        <img src="<?php echo $profilePic; ?>" id="modal-preview-avatar" alt="Avatar">
                        <label for="modal_profile_pic" class="modal-upload-btn">Upload Photo</label>
                        <input type="file" name="profile_pic" id="modal_profile_pic" accept="image/*" style="display: none;">
                    </div>

                    <div class="popup-field">
                        <label for="m_first_name">First Name</label>
                        <input type="text" name="first_name" id="m_first_name" value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_middle_name">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" id="m_middle_name" value="<?php echo htmlspecialchars($userData['middle_name'] ?? ''); ?>">
                    </div>

                    <div class="popup-field">
                        <label for="m_last_name">Last Name</label>
                        <input type="text" name="last_name" id="m_last_name" value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_username">Username</label>
                        <?php 
                            if (!isset($generatedUsername) && !empty($userData['first_name']) && !empty($userData['last_name'])) {
                                $firstLetter = strtolower(substr($userData['first_name'], 0, 1));
                                $cleanLastName = strtolower(str_replace(' ', '', $userData['last_name'])); 
                                $generatedUsername = $firstLetter . '.' . $cleanLastName;
                            }
                        ?>
                        <input type="text" name="username" id="m_username" value="<?php echo htmlspecialchars($generatedUsername ?? ''); ?>" readonly class="modal-readonly">
                    </div>


                    <div class="popup-field">
                        <label for="m_email">Email Address</label>
                        <input type="email" name="email" id="m_email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                    </div>

                    <div class="popup-field">
                        <label for="m_phone">Phone Number</label>
                        <input type="text" name="phone_number" id="m_phone" placeholder="e.g. 09123456789" value="<?php echo htmlspecialchars($userData['phone_number'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="popup-submit-btn">Save Changes</button>
                </form>
            </div>

            <div id="popup-security" class="popup-section">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="modal_settings_action" value="change_password">
                    <div class="popup-field">
                        <label for="m_curr_pass">Current Password</label>
                        <input type="password" name="current_password" id="m_curr_pass" required>
                    </div>
                    <div class="popup-field">
                        <label for="m_new_pass">New Password</label>
                        <input type="password" name="new_password" id="m_new_pass" required>
                    </div>
                    <ul class="password-rules-list" id="password-rules">
                        <li id="rule-length" class="rule-invalid">Minimum 8 characters</li>
                        <li id="rule-uppercase" class="rule-invalid">At least (1) uppercase letter</li>
                        <li id="rule-lowercase" class="rule-invalid">At least (1) lowercase letter</li>
                        <li id="rule-number" class="rule-invalid">At least (1) number</li>
                        <li id="rule-special" class="rule-invalid">At least (1) special character</li>
                    </ul>
                    <button type="submit" class="popup-submit-btn">Update Password</button>
                </form>
            </div>

            <div id="popup-danger" class="popup-section">
                <div class="popup-danger-warning">
                    <p style="margin: 0 0 12px 0; font-weight: 600;">Warning: Deleting your account will permanently remove all your data and cannot be undone.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                        <input type="hidden" name="modal_settings_action" value="delete_account">
                        <div class="popup-field">
                            <label for="delete_password_confirm">Confirm Deletion</label>
                            <input type="password" name="delete_password_confirm" id="delete_password_confirm" placeholder="Enter password to confirm account deletion" required class="popup-danger-input">
                        </div>
                        <button type="submit" class="popup-delete-btn" style="margin-top: 10px;">Permanently Delete Account</button>
                    </form>
                </div>
            </div>
        </div> 
    </div> 
</div>
