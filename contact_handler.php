<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($db, trim($_POST['name']));
    $email = mysqli_real_escape_string($db, trim($_POST['email']));
    $subject = mysqli_real_escape_string($db, trim($_POST['subject']));
    $message = mysqli_real_escape_string($db, trim($_POST['message']));
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: contact.php');
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: contact.php');
        exit;
    }
    
    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
    
    if (mysqli_query($db, $sql)) {
        $_SESSION['success'] = 'Thank you for your message! We will get back to you soon.';
    } else {
        $_SESSION['error'] = 'Something went wrong. Please try again later.';
    }
    
    header('Location: contact.php');
    exit;
} else {
    header('Location: contact.php');
    exit;
}
?>