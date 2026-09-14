<?php
// Establish connection to your active database file context layout link
require_once '../process-and-setting/database-connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input field elements
    $name    = trim($_POST['contact_name']);
    $email   = trim($_POST['contact_email']);
    $subject = trim($_POST['contact_subject']);
    $message = trim($_POST['contact_message']);

    // Check for empty fields layout structures
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('All form parameter entry data fields are required.'); window.history.back();</script>";
        exit();
    }

    // Insert parameters directly into your new table database columns array fields
    $sql = "INSERT INTO contact_messages (sender_name, sender_email, inquiry_type, message_text) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            echo "<script>alert('Your message has been sent successfully! Thank you for sending your concern!'); window.location.href='../default-browser/contact.php';</script>";
            exit();
        } else {
            echo "<script>alert('Database insertion error. Please check your data variables.'); window.history.back();</script>";
        }
        $stmt->close();
    }
} else {
    header("Location: ../default-browser/contact.php");
    exit();
}
?>