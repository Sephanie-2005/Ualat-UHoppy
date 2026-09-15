<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../process-and-setting/database-connection.php';  
  
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') { 
        header("Location: ../default-browser/index.php?error=unauthorized");
        exit();
    }

    $owner_id = $_SESSION['user_id'];
    $renters = [];

    $query = "SELECT DISTINCT 
                r.renter_id, 
                r.first_name, 
                r.last_name, 
                r.email, 
                r.phone_number, 
                r.profile_picture, 
                p.property_name, 
                a.accommodation_name,
                rt.rental_status
              FROM conversations c
              INNER JOIN renters r ON c.renter_id = r.renter_id
              LEFT JOIN rentals rt ON r.renter_id = rt.renter_id
              LEFT JOIN accommodations a ON rt.accommodation_id = a.accommodation_id
              LEFT JOIN properties p ON (a.property_id = p.property_id OR p.owner_id = c.owner_id)
              WHERE c.owner_id = ?
              GROUP BY r.renter_id"; 

    if ($stmt = $conn->prepare($query)) { 
        $stmt->bind_param("i", $owner_id); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 
        while ($row = $result->fetch_assoc()) { 
            $renters[] = $row; 
        } 
        $stmt->close(); 
    } 

    $userData = []; 
    
    $ownerQuery = "SELECT first_name, middle_name, last_name, email, phone_number, profile_picture FROM owners WHERE owner_id = ?";
    if ($ownerStmt = $conn->prepare($ownerQuery)) {
        $ownerStmt->bind_param("i", $owner_id);
        $ownerStmt->execute();
        $ownerResult = $ownerStmt->get_result();
        
        if ($ownerResult && $ownerResult->num_rows > 0) {
            $userData = $ownerResult->fetch_assoc();
        } else {
            $userData = [
                'first_name'      => $_SESSION['first_name'] ?? '',
                'middle_name'     => $_SESSION['middle_name'] ?? '',
                'last_name'       => $_SESSION['last_name'] ?? '',
                'email'           => $_SESSION['email'] ?? '',
                'phone_number'    => $_SESSION['phone_number'] ?? '',
                'profile_picture' => $_SESSION['profile_picture'] ?? ''
            ];
        }
        $ownerStmt->close();
    }

    $generatedUsername = '';
    if (!empty($userData['first_name']) && !empty($userData['last_name'])) {
        $firstLetter = strtolower(substr($userData['first_name'], 0, 1));
        $cleanLastName = strtolower(str_replace(' ', '', $userData['last_name'])); 
        $generatedUsername = $firstLetter . '.' . $cleanLastName;
    }

    $dbPicPath = '../../' . ($userData['profile_picture'] ?? '');
    if (!empty($userData['profile_picture']) && file_exists($dbPicPath)) {
        $profilePic = $dbPicPath;
    } else {
        $profilePic = '../../system-images/default-profile.png';
    }

    $active_renter_id = isset($_GET['renter_id']) ? intval($_GET['renter_id']) : 0;
    $conversation_id = 0;
    $messages_result = [];
    $active_renter_name = "";

    if ($active_renter_id > 0) {
        $verify_relationship = false;
        foreach ($renters as $r) {
            if ($r['renter_id'] == $active_renter_id) {
                $verify_relationship = true;
                $active_renter_name = $r['first_name'] . ' ' . $r['last_name'];
                break;
            }
        }

        if ($verify_relationship) {
            $check_convo = "SELECT conversation_id FROM conversations WHERE owner_id = ? AND renter_id = ? LIMIT 1";
            if ($c_stmt = $conn->prepare($check_convo)) {
                $c_stmt->bind_param("ii", $owner_id, $active_renter_id);
                $c_stmt->execute();
                $c_res = $c_stmt->get_result();
                if ($c_res->num_rows > 0) {
                    $conversation_id = $c_res->fetch_assoc()['conversation_id'];
                } else {
                    $create_convo = "INSERT INTO conversations (owner_id, renter_id, created_at) VALUES (?, ?, NOW())";
                    if ($ins_stmt = $conn->prepare($create_convo)) {
                        $ins_stmt->bind_param("ii", $owner_id, $active_renter_id);
                        $ins_stmt->execute();
                        $conversation_id = $ins_stmt->insert_id;
                        $ins_stmt->close();
                    }
                }
                $c_stmt->close();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && $conversation_id > 0) {
                $message_text = trim($_POST['message']);
                if (!empty($message_text)) {
                    $insert_msg = "INSERT INTO messages (conversation_id, sender_type, message, sent_at) VALUES (?, 'owner', ?, NOW())";
                    if ($msg_stmt = $conn->prepare($insert_msg)) {
                        $msg_stmt->bind_param("is", $conversation_id, $message_text);
                        $msg_stmt->execute();
                        $msg_stmt->close();
                    }
                    header("Location: o-messages.php?renter_id=" . $active_renter_id);
                    exit();
                }
            }

            if ($conversation_id > 0) {
                $history_query = "SELECT sender_type, message, sent_at FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC";
                if ($hist_stmt = $conn->prepare($history_query)) {
                    $hist_stmt->bind_param("i", $conversation_id);
                    $hist_stmt->execute();
                    $hist_res = $hist_stmt->get_result();
                    while ($m_row = $hist_res->fetch_assoc()) {
                        $messages_result[] = $m_row;
                    }
                    $hist_stmt->close();
                }
            }
        } else {
            $active_renter_id = 0;
        }
    }
?>


<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/default/background-shapes.css">
    <link rel="stylesheet" href="../../style/profile-settings.css">
    <link rel="stylesheet" href="../../style/owner/o-messages.css"> 
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/Link Logo.jpg">
</head>

<body>
    <div class="web-app">
        
        <header>
            <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
            <button id="home-btn" class="home_button" onclick="window.location.href='owner-homepage.php'">HOME</button>
            <button id="property-btn" class="property_button" onclick="window.location.href='o-property.php'">PROPERTY</button>
            <button id="messages-btn" class="messages_button active">MESSAGES</button>
            <button id="about_us-btn" class="about_us_button" onclick="window.location.href='o-about-us.php'">ABOUT US</button>
            <button id="contact-btn" class="contact_button" onclick="window.location.href='o-contact.php'">CONTACT</button>
             
            <div class="profile-nav-wrapper">
                <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
            </div>
        </header>

        <main class="Section_1">
            <div class="messaging-box">
                <div class="renters-sidebar">
                    <div class="sidebar-title">Inbox Channels</div>
                    <div class="renter-list-container">
                        <?php if (!empty($renters)): ?>
                            <?php foreach ($renters as $r): ?>
                                <?php 
                                    $isActive = ($r['renter_id'] == $active_renter_id) ? 'active-chat' : '';
                                    $rPicPath = '../../' . ($r['profile_picture'] ?? '');
                                    $renterPic = (!empty($r['profile_picture']) && file_exists($rPicPath)) ? $rPicPath : '../../system-images/default-profile.png';
                                    
                                    if ($r['rental_status'] === 'active') {
                                        $statusBadge = "Active Tenant";
                                        $subText = htmlspecialchars($r['property_name'] . ' - ' . $r['accommodation_name']);
                                    } else {
                                        $statusBadge = "Inquirer / Applicant";
                                        $subText = "Interested in your properties";
                                    }
                                ?>
                                <a href="o-messages.php?renter_id=<?= $r['renter_id']; ?>" class="renter-card <?= $isActive; ?>">
                                    <img src="<?= htmlspecialchars($renterPic); ?>" alt="User profile" class="renter-avatar">
                                    <div class="renter-info">
                                        <div class="r-name"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></div>
                                        <div class="r-subtext"><?= $subText; ?></div>
                                        <span class="user-badge <?= ($r['rental_status'] === 'active') ? 'tenant' : 'inquirer'; ?>">
                                            <?= $statusBadge; ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 20px; text-align: center; color: #999; font-size: 0.9rem;">Your inbox is empty.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chat-area">
                    <?php if ($active_renter_id > 0): ?>
                        <div class="chat-area-header">
                            Conversation with <?= htmlspecialchars($active_renter_name); ?>
                        </div>
                        
                        <div class="chat-logs" id="chatContainer">
                            <?php if (!empty($messages_result)): ?>
                                <?php foreach ($messages_result as $msg): ?>
                                    <div class="msg-line <?= ($msg['sender_type'] === 'owner') ? 'owner' : 'renter'; ?>">
                                        <div class="msg-bubble">
                                            <?= nl2br(htmlspecialchars($msg['message'])); ?>
                                        </div>
                                        <div class="msg-timestamp">
                                            <?= date('h:i A | M d', strtotime($msg['sent_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-placeholder">No message history yet.</div>
                            <?php endif; ?>
                        </div>

                        <form action="o-messages.php?renter_id=<?= $active_renter_id; ?>" method="POST" class="input-bar-form">
                            <input type="text" name="message" class="input-box-field" placeholder="Type your message here..." required autocomplete="off">
                            <button type="submit" name="send_message" class="msg-send-btn">Send</button>
                        </form>
                    <?php else: ?>
                        <div class="empty-placeholder">
                            <p>Select a contact from the inbox to display chat channels.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <?php include '../process-and-setting/profile-settings-view.php'; ?>
        <?php include 'o-footer.php';  ?>
    </div>

    <script>
        const chatLogs = document.getElementById('chatContainer');
        if (chatLogs) {
            chatLogs.scrollTop = chatLogs.scrollHeight;
        }
    </script>
    <script src="../../javascript-files/profile-settings-modal.js"></script>
</body>  
</html>