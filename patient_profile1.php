<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

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

// Collect form data securely
$full_name = $_POST['full_name'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$ssn = $_POST['ssn'];
$street_address = $_POST['street_address'];
$city = $_POST['city'];
$state = $_POST['state'];
$zip_code = $_POST['zip_code'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$physician_name = $_POST['physician_name'];
$physician_contact = $_POST['physician_contact'];
$current_medications = $_POST['current_medications'];
$allergies = $_POST['allergies'];
$medical_conditions = $_POST['medical_conditions'];
$surgical_history = $_POST['surgical_history'];
$emergency_contact_name = $_POST['emergency_contact_name'];
$emergency_contact_relationship = $_POST['emergency_contact_relationship'];
$emergency_contact_phone = $_POST['emergency_contact_phone'];

// Check if patient already has a profile
$stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    // Update existing profile
    $update = $conn->prepare("UPDATE patients SET 
        full_name = ?, dob = ?, gender = ?, ssn = ?, 
        street_address = ?, city = ?, state = ?, zip_code = ?, 
        phone = ?, email = ?, physician_name = ?, physician_contact = ?, 
        current_medications = ?, allergies = ?, medical_conditions = ?, surgical_history = ?, 
        emergency_contact_name = ?, emergency_contact_relationship = ?, emergency_contact_phone = ? 
        WHERE username = ?");

    $update->bind_param("sssssssssssssssssss", 
        $full_name, $dob, $gender, $ssn, 
        $street_address, $city, $state, $zip_code, 
        $phone, $email, $physician_name, $physician_contact, 
        $current_medications, $allergies, $medical_conditions, $surgical_history, 
        $emergency_contact_name, $emergency_contact_relationship, $emergency_contact_phone,
        $username);
    
    if ($update->execute()) {
        header("Location: patient_profile.php?status=updated");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
    $update->close();
} else {
    // Insert new profile
    $insert = $conn->prepare("INSERT INTO patients (
        username, full_name, dob, gender, ssn, 
        street_address, city, state, zip_code, phone, email, 
        physician_name, physician_contact, current_medications, allergies, 
        medical_conditions, surgical_history, emergency_contact_name, 
        emergency_contact_relationship, emergency_contact_phone
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $insert->bind_param("ssssssssssssssssssss",
        $username, $full_name, $dob, $gender, $ssn, 
        $street_address, $city, $state, $zip_code, $phone, $email, 
        $physician_name, $physician_contact, $current_medications, $allergies, 
        $medical_conditions, $surgical_history, $emergency_contact_name, 
        $emergency_contact_relationship, $emergency_contact_phone
    );

    if ($insert->execute()) {
        header("Location: patient_profile.php?status=created");
        exit();
    } else {
        echo "Error creating profile: " . $conn->error;
    }
    $insert->close();
}

$conn->close();
?>
