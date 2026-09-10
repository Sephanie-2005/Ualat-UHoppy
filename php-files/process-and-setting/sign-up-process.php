<?php
session_start();

require_once 'database-connection.php';

if (isset($_POST['signup_submit'])) {
    $role        = $_POST['role']; 
    $first_name  = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password    = $_POST['password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $_SESSION['signup_error'] = "Required data entries are missing.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Please use a valid email framework layout.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $_SESSION['signup_error'] = "Password structure fails security rule conditions.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
 
    if ($role === 'owner') {
        $table = 'owners';
    } elseif ($role === 'renter') {
        $table = 'renters';
    } else {
        $_SESSION['signup_error'] = "Invalid registration category selected.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $check_query = "SELECT email FROM $table WHERE email = ? LIMIT 1";
    $check_stmt  = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['signup_error'] = "This email is already registered as a " . $role . ".";
        $check_stmt->close();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    $check_stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $phone_number    = ""; 
    $profile_picture = "default-avatar.png"; 

    $insert_query = "INSERT INTO $table (first_name, middle_name, last_name, email, password, phone_number, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($insert_stmt = $conn->prepare($insert_query)) {
        $insert_stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $email, $hashed_password, $phone_number, $profile_picture);
        
        if ($insert_stmt->execute()) {
            $_SESSION['signup_success'] = "Account created successfully! Hop into the sign in layout.";
            unset($_SESSION['signup_error']);
        } else {
            $_SESSION['signup_error'] = "Processing error saving parameters. Please check back later.";
        }
        $insert_stmt->close();
    } else {
        $_SESSION['signup_error'] = "Database statement construction failure.";
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
