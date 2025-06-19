<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "sepm");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data safely
$name = $_POST['name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

// Insert into table
$sql = "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    // On success, trigger success popup
    echo "<script>alert('Message sent successfully!'); window.location.href='maindashboard.php';</script>";
} else {
    // On error, trigger error popup
    echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='maindashboard.php';</script>";
}

$stmt->close();
$conn->close();
?>
