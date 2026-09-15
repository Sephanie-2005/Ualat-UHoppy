<?php
require_once '../process-and-setting/database-connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST['contact_name']);
    $email   = trim($_POST['contact_email']);
    $subject = trim($_POST['contact_subject']);
    $message = trim($_POST['contact_message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('All form parameter entry data fields are required.'); window.history.back();</script>";
        exit();
    }

    $sql = "INSERT INTO contact_messages (sender_name, sender_email, inquiry_type, message_text) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            // This script handles contact.php, o-contact.php, and r-contact.php dynamically
            echo "<script>
                    alert('Your message has been sent successfully! Thank you for sending your concern!'); 
                    if (document.referrer) {
                        window.location.href = document.referrer;
                    } else {
                        // Fallback in case browser privacy settings block the referrer string
                        window.history.back();
                    }
                  </script>";
            exit();
        } else {
            echo "<script>alert('Database insertion error. Please check your data variables.'); window.history.back();</script>";
        }
        $stmt->close();
    }
} else {
    // Default fallback if someone hits this process route link directly
    header("Location: ../default-browser/contact.php");
    exit();
}
?>