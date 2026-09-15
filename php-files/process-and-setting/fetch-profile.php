<?php 
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