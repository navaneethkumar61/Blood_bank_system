<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sepm';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $medical_record_number = $_POST['medical_record_number'];
    $diagnosis = $_POST['diagnosis'];
    $blood_type = $_POST['blood_type'];
    $quantity = (int)$_POST['quantity'];  // CAST to integer
    $urgency = $_POST['urgency'];
    $current_condition = $_POST['current_condition'];
    $allergies = $_POST['allergies'];

    $sql = "INSERT INTO blood_requests (
        username, full_name, dob, gender, medical_record_number, 
        diagnosis, blood_type, quantity, urgency, 
        current_condition, allergies
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssssisss", 
        $username, $full_name, $dob, $gender, $medical_record_number,
        $diagnosis, $blood_type, $quantity, $urgency,
        $current_condition, $allergies
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Blood request submitted successfully!";
        header("Location: patient_dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting blood request. Please try again.";
        header("Location: patient_request.php");
        exit();
    }

    
}
else {
    // If not POST request, redirect to the form
    header("Location: patient_request.php");
    exit();
}

?>