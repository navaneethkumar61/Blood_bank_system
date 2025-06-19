<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "sepm";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_POST['otp_verified'] !== "true") {
    echo "OTP not verified.";
    exit;
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$blood_type = "";
$location = "";
$hospital_name = "";

if ($role === "donor") {
    $blood_type = $_POST['donor_blood_type'];
    $location = $_POST['location'];
    $stmt = $conn->prepare("INSERT INTO donors (username, email, password, blood_type, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $blood_type, $location);
} elseif ($role === "patient") {
    $blood_type = $_POST['patient_blood_type'];
    $hospital_name = $_POST['hospital_name'];
    $stmt = $conn->prepare("INSERT INTO patients (username, email, password, blood_type, hospital_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $blood_type, $hospital_name);
} else {
    echo "Invalid role.";
    exit;
}

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
