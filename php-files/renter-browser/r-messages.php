<?php 
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'renter') { 
        header("Location: ../default-browser/index.php?error=unauthorized");
        exit();
    }

    $renter_id = $_SESSION['user_id'];

    $profilePic = $_SESSION['profile_picture'] ?? 'uploads/default-avatar.png';

    require_once '../process-and-setting/database-connection.php'; 
    
    $userData = []; 
    
    $renterQuery = "SELECT first_name, middle_name, last_name, email, phone_number, profile_picture FROM renters WHERE renter_id = ?";
    
    if ($renterStmt = $conn->prepare($renterQuery)) {
        $renterStmt->bind_param("i", $renter_id);
        $renterStmt->execute();
        $renterResult = $renterStmt->get_result();
        
        if ($renterResult && $renterResult->num_rows > 0) {
            $userData = $renterResult->fetch_assoc();
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
        $renterStmt->close();
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

    // Handle incoming new message submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
        $conversation_id = intval($_POST['conversation_id']);
        $message_text = trim($_POST['message']);

        if (!empty($message_text) && $conversation_id > 0) {
            $insertQuery = "INSERT INTO messages (conversation_id, sender_type, message, sent_at) VALUES (?, 'renter', ?, NOW())";
            if ($insertStmt = $conn->prepare($insertQuery)) {
                $insertStmt->bind_param("is", $conversation_id, $message_text);
                $insertStmt->execute();
                $insertStmt->close();
            }
        }
        header("Location: r-messages.php?conversation_id=" . $conversation_id);
        exit();
    }

    // Fetch conversations list for the sidebar matching this user
    $conversations = [];
    $sidebarQuery = "
        SELECT c.conversation_id, o.first_name, o.last_name, o.profile_picture,
            (SELECT m.message FROM messages m WHERE m.conversation_id = c.conversation_id ORDER BY m.sent_at DESC LIMIT 1) as last_message,
            (SELECT m.sent_at FROM messages m WHERE m.conversation_id = c.conversation_id ORDER BY m.sent_at DESC LIMIT 1) as last_time
        FROM conversations c
        JOIN owners o ON c.owner_id = o.owner_id
        WHERE c.renter_id = ?
        ORDER BY last_time DESC";

    if ($sideStmt = $conn->prepare($sidebarQuery)) {
        $sideStmt->bind_param("i", $renter_id);
        $sideStmt->execute();
        $sideResult = $sideStmt->get_result();
        while ($row = $sideResult->fetch_assoc()) {
            $conversations[] = $row;
        }
        $sideStmt->close();
    }

    // Determine current highlighted active chat window
    $active_conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : (count($conversations) > 0 ? $conversations[0]['conversation_id'] : 0);

    $messages = [];
    $active_owner = null;

    if ($active_conversation_id > 0) {
        $checkQuery = "SELECT c.conversation_id, o.first_name, o.last_name FROM conversations c JOIN owners o ON c.owner_id = o.owner_id WHERE c.conversation_id = ? AND c.renter_id = ?";
        if ($chkStmt = $conn->prepare($checkQuery)) {
            $chkStmt->bind_param("ii", $active_conversation_id, $renter_id);
            $chkStmt->execute();
            $chkResult = $chkStmt->get_result();
            if ($chkResult && $chkResult->num_rows > 0) {
                $active_owner = $chkResult->fetch_assoc();
            }
            $chkStmt->close();
        }

        if ($active_owner) {
            $msgQuery = "SELECT sender_type, message, sent_at FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC";
            if ($msgStmt = $conn->prepare($msgQuery)) {
                $msgStmt->bind_param("i", $active_conversation_id);
                $msgStmt->execute();
                $msgResult = $msgStmt->get_result();
                while ($msgRow = $msgResult->fetch_assoc()) {
                    $messages[] = $msgRow;
                }
                $msgStmt->close();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="../../style/default/web-app.css">
    <link rel="stylesheet" href="../../style/default/header-style.css">
    <link rel="stylesheet" href="../../style/default/footer-style.css">
    <link rel="stylesheet" href="../../style/renter/r-messages.css">
    <link rel="stylesheet" href="../../style/profile-settings.css">
    <link rel="icon" type="image/png" sizes="36x36" href="../../system-images/link-logo.jpg">
</head>
<body>
    <div class="web-app">
        <header>
            <img src="../../system-images/Logo.png" alt="Website Logo" class="transparent_logo">
            <button id="home-btn" class="home_button" onclick="window.location.href='renter-homepage.php'">HOME</button>
            <button id="listings-btn" class="listings_button" onclick="window.location.href='r-listings.php'">LISTINGS</button>
            <button id="features-btn" class="features_button active">MESSAGES</button>
            <button id="about_us-btn" class="about_us_button" onclick="window.location.href='r-about.php'">ABOUT US</button>
            <button id="contact-btn" class="contact_button" onclick="window.location.href='r-contact.php'">CONTACT</button>
            <div class="profile-nav-wrapper">
                <img src="<?php echo htmlspecialchars($profilePic, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Settings" class="header-profile-pic" onclick="openSettingsModal()" style="cursor: pointer; border: 2px solid rgb(246, 144, 104);">
            </div>
        </header>

        <main class="messages">
            <div class="messages-container">
                <div class="conversations-sidebar">
                    <?php if (count($conversations) > 0): ?>
                        <?php foreach ($conversations as $conv): ?>
                            <?php 
                            $ownerPicPath = '../../' . ($conv['profile_picture'] ?? '');
                            $ownerPic = (!empty($conv['profile_picture']) && file_exists($ownerPicPath)) ? $ownerPicPath : '../../system-images/default-profile.png';
                            $isActive = ($conv['conversation_id'] == $active_conversation_id) ? 'active' : '';
                            ?>
                            <a href="r-messages.php?conversation_id=<?php echo $conv['conversation_id']; ?>" class="conversation-item <?php echo $isActive; ?>">
                                <img src="<?php echo htmlspecialchars($ownerPic, ENT_QUOTES, 'UTF-8'); ?>" class="sidebar-avatar" alt="Owner Profile">
                                <div class="conversation-details">
                                    <h4><?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <p><?php echo htmlspecialchars($conv['last_message'] ?? 'No messages yet.', ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-conversations">No active conversations found.</div>
                    <?php endif; ?>
                </div>

                <div class="chat-window">
                    <?php if ($active_conversation_id > 0 && $active_owner): ?>
                        <div class="chat-header">
                            <h3>Conversation with <?php echo htmlspecialchars($active_owner['first_name'] . ' ' . $active_owner['last_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        
                        <div class="chat-body" id="chatBody">
                            <?php if (count($messages) > 0): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message-bubble <?php echo ($msg['sender_type'] === 'renter') ? 'renter' : 'owner'; ?>">
                                        <?php echo htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="message-meta"><?php echo date('g:i A', strtotime($msg['sent_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="start-conversation-prompt">Send a message to start the conversation!</div>
                            <?php endif; ?>
                        </div>

                        <div class="chat-footer">
                            <form action="r-messages.php" method="POST" class="chat-form">
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="conversation_id" value="<?php echo $active_conversation_id; ?>">
                                <input type="text" name="message" class="chat-input" placeholder="Type your message here..." required autocomplete="off">
                                <button type="submit" class="chat-send-btn">Send</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="no-chat-selected">
                            <p>Please select a conversation from the sidebar to view messages.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <?php include '../process-and-setting/profile-settings-view.php'; ?>
        <?php include 'r-footer.php'; ?>
    </div>
</body>
<script src="../../javascript-files/profile-settings-modal.js"></script>
<script>
    const chatBody = document.getElementById('chatBody');
    if (chatBody) {
        chatBody.scrollTop = chatBody.scrollHeight;
    }
</script>
</html>
