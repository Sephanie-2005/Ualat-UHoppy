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
?>

<?php
// Start session and verify renter login
session_start();
if (!isset($_SESSION['renter_id'])) {
    header("Location: ../auth/login.php"); 
    exit;
}

// Database Configuration
$host = 'localhost';
$db   = 'uhoppy_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$current_renter_id = $_SESSION['renter_id'];
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : null;
$error_message = "";
$owner_info = null;

if ($property_id) {
    // 1. Look up the property to find out who the owner is
    $prop_stmt = $pdo->prepare("
        SELECT p.property_id, p.property_name, o.owner_id, o.first_name, o.last_name 
        FROM properties p
        JOIN owners o ON p.owner_id = o.owner_id
        WHERE p.property_id = ?
    ");
    $prop_stmt->execute([$property_id]);
    $property = $prop_stmt->fetch();

    if ($property) {
        $owner_id = $property['owner_id'];
        $owner_info = $property;

        // 2. Check if a conversation thread already exists between this renter and owner
        $conv_stmt = $pdo->prepare("
            SELECT conversation_id FROM conversations 
            WHERE renter_id = ? AND owner_id = ?
            LIMIT 1
        ");
        $conv_stmt->execute([$current_renter_id, $owner_id]);
        $existing_conv = $conv_stmt->fetch();

        if ($existing_conv) {
            // Thread exists! Instantly redirect straight to the chat window
            header("Location: messages.php?conversation_id=" . $existing_conv['conversation_id']);
            exit;
        }

        // 3. Handle Form Submission to initialize a brand new conversation channel
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_chat'])) {
            $first_message = trim($_POST['first_message']);

            if (!empty($first_message)) {
                // Start a safe database transaction block
                $pdo->beginTransaction();

                try {
                    // Create the conversation entry
                    $ins_conv = $pdo->prepare("INSERT INTO conversations (owner_id, renter_id) VALUES (?, ?)");
                    $ins_conv->execute([$owner_id, $current_renter_id]);
                    $new_conv_id = $pdo->lastInsertId();

                    // Send the initial inquiry text message
                    $ins_msg = $pdo->prepare("INSERT INTO messages (conversation_id, sender_type, message) VALUES (?, 'renter', ?)");
                    $ins_msg->execute([$new_conv_id, $first_message]);

                    // Commit changes if everything runs without hiccups
                    $pdo->commit();

                    // Redirect safely into the newly generated active chat channel
                    header("Location: messages.php?conversation_id=" . $new_conv_id);
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_message = "Failed to open conversation channel. Please try again.";
                }
            } else {
                $error_message = "Please write an introductory message to the owner.";
            }
        }
    } else {
        $error_message = "The selected property listing could not be found.";
    }
} else {
    $error_message = "No valid property specification provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Owner - uHoppy</title>
    <script src="https://tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col justify-center items-center px-4 py-12">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <!-- Logo Branding Header -->
        <div class="text-center mb-6">
            <span class="text-3xl">🐰</span>
            <h2 class="text-xl font-bold text-gray-900 mt-2">Inquire About Accommodation</h2>
        </div>

        <?php if (!empty($error_message) && !$owner_info): ?>
            <!-- Sad Path Error Message UI banner -->
            <div class="bg-red-50 text-red-600 text-sm p-4 rounded-xl mb-6 font-medium text-center">
                <?= htmlspecialchars($error_message) ?>
            </div>
            <a href="dashboard.php" class="block text-center w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition-colors text-sm">
                Return to Browser Dashboard
            </a>
        <?php else: ?>
            <!-- Happy Path Context Form Box UI -->
            <div class="mb-6 bg-indigo-50/50 rounded-xl p-4 border border-indigo-100/50">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-1">Property Reference</p>
                <h3 class="text-base font-bold text-gray-800"><?= htmlspecialchars($owner_info['property_name']) ?></h3>
                <p class="text-xs text-gray-500 mt-1">Managed by: <span class="font-medium text-gray-700"><?= htmlspecialchars($owner_info['first_name'] . ' ' . $owner_info['last_name']) ?></span></p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 text-red-600 text-sm p-3 rounded-xl mb-4 font-medium text-center">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form action="contact_owner.php?property_id=<?= $property_id ?>" method="POST" class="space-y-4">
                <div>
                    <label for="first_message" class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Introduce yourself</label>
                    <textarea id="first_message" name="first_message" rows="4" required placeholder="Hi! I am interested in renting this accommodation. Is this still available for viewing?"
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 text-sm transition-all resize-none"></textarea>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" name="start_chat" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition-colors shadow-sm text-sm">
                        Start Conversation
                    </button>
                    <a href="dashboard.php" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2.5 rounded-xl transition-colors text-xs">
                        Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
