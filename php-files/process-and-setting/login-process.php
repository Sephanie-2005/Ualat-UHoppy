<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


    require_once 'database-connection.php';

    $fallback_index = "../default-browser/index.php?error=failed";

    if (isset($_POST['login_submit'])) {
        
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? '';

        if (empty($email) || empty($password) || empty($role)) {
            $_SESSION['login_error'] = "Please fill in all fields.";
            header("Location: " . $fallback_index);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = "Invalid email format.";
            header("Location: " . $fallback_index);
            exit();
        }

        if ($role === 'owner') {
            $table = 'owners';
            $id_column = 'owner_id';
        } elseif ($role === 'renter') {
            $table = 'renters';
            $id_column = 'renter_id';
        } else {
            $_SESSION['login_error'] = "Invalid account role selected.";
            header("Location: " . $fallback_index);
            exit();
        }

        $query = "SELECT $id_column, first_name, last_name, email, password FROM $table WHERE email = ? LIMIT 1";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    
                    session_regenerate_id(true);

                    $_SESSION['user_id']    = $user[$id_column];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name']  = $user['last_name'];
                    $_SESSION['email']      = $user['email'];
                    $_SESSION['role']       = $role; 

                    unset($_SESSION['login_error']);

                                       if ($role === 'owner') {
                        echo "<script>window.location.href = '../owner-browser/owner-homepage.php';</script>";
                    } elseif ($role === 'renter') {
                        echo "<script>window.location.href = '../renter-browser/renter-homepage.php';</script>";
                    } else {
                        echo "<script>window.location.href = '../default-browser/index.php';</script>";
                    }
                    exit();
                } else {
                    $_SESSION['login_error'] = "Incorrect password credentials.";
                }
            } else {
                $_SESSION['login_error'] = "The account or email address you entered does not exist.";
            }
            $stmt->close();
        } else {
            $_SESSION['login_error'] = "Database statement preparation failure: " . $conn->error;
        }

        echo "<script>window.location.href = '../default-browser/index.php?error=failed';</script>";
        exit();
    } else {
        die("Form was not submitted properly. Make sure your button has name='login_submit'.");
    }
?>
