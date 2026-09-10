<?php
// Force full error reporting so nothing fails silently
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Force MySQLi to throw errors instead of failing silently
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Start a secure user session
session_start();

// Import your database connection script 
require_once 'database-connection.php';

if (isset($_POST['login_submit'])) {
    
    // Sanitize user email inputs and pull form values
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? ''; // 'renter' or 'owner'

    // 1. Validate empty inputs
    if (empty($email) || empty($password) || empty($role)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // 2. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Invalid email format.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // 3. Determine target table based on chosen dropdown selection
    if ($role === 'owner') {
        $table = 'owners';
        $id_column = 'owner_id';
    } elseif ($role === 'renter') {
        $table = 'renters';
        $id_column = 'renter_id';
    } else {
        $_SESSION['login_error'] = "Invalid account role selected.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // 4. Prepare a secure query statement to pull account info matching the email 
    $query = "SELECT $id_column, first_name, last_name, email, password FROM $table WHERE email = ? LIMIT 1";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // 5. Explicit Check: Does the email exist in the database table?
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 6. Verify password (works with both password_hash strings and plain text testing)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Save user identity keys inside global Session parameters
                $_SESSION['user_id']    = $user[$id_column];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $role; 

                // Clear out historic error messages
                unset($_SESSION['login_error']);

                // ROLE-BASED REDIRECTION MATRIX
                if ($role === 'owner') {
                    header("Location: ../owner-browser/owner-homepage.php");
                } elseif ($role === 'renter') {
                    header("Location: ../renter-browser/renter-homepage.php");
                } else {
                    // Fallback configuration default
                    header("Location: ../default/index.php");
                }
                exit();
            } else {
                // Email found, but password doesn't match
                $_SESSION['login_error'] = "Incorrect password credentials.";
            }
        } else {
            // Email was not found anywhere inside the selected table
            $_SESSION['login_error'] = "The account or email address you entered does not exist.";
        }
        $stmt->close();
    } else {
        $_SESSION['login_error'] = "Database statement preparation failure: " . $conn->error;
    }

    // Return back to the login page modal screen to display the error alert box
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    die("Form was not submitted properly. Make sure your button has name='login_submit'.");
}
?>