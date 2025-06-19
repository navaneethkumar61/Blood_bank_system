<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sepm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    die("Unauthorized access.");
}

$username = $_SESSION['username'];

// Get blood group from donors table
$blood_group = '';
$stmt1 = $conn->prepare("SELECT blood_type FROM donors WHERE username = ?");
$stmt1->bind_param("s", $username);
$stmt1->execute();
$stmt1->bind_result($blood_group);
$stmt1->fetch();
$stmt1->close();

// Get form values
$full_name = $_POST['full_name'];
$phone = $_POST['phone'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$address = $_POST['address'];
$city = $_POST['city'];
$state = $_POST['state'];
$pincode = $_POST['pincode'];
$last_donation_date = $_POST['last_donation_date'];
$is_available = $_POST['is_available'];

// Check if user already has a profile
$check = $conn->prepare("SELECT * FROM donor_profile WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Profile exists – update
    $stmt2 = $conn->prepare("UPDATE donor_profile SET full_name=?, phone=?, gender=?, dob=?, blood_group=?, address=?, city=?, state=?, pincode=?, last_donation_date=?, is_available=? WHERE username=?");
    $stmt2->bind_param("ssssssssssis", $full_name, $phone, $gender, $dob, $blood_group, $address, $city, $state, $pincode, $last_donation_date, $is_available, $username);
    $stmt2->execute();
    $stmt2->close();
    echo "✅ Profile updated successfully! <a href='donor_dashboard.php'>Go to Dashboard</a>";
} else {
    // Profile doesn't exist – insert
    $stmt3 = $conn->prepare("INSERT INTO donor_profile (username, full_name, phone, gender, dob, blood_group, address, city, state, pincode, last_donation_date, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt3->bind_param("ssssssssssis", $username, $full_name, $phone, $gender, $dob, $blood_group, $address, $city, $state, $pincode, $last_donation_date, $is_available);
    if ($stmt3->execute()) {
        echo "✅ Profile registered successfully! <a href='donor_dashboard.php'>Go to Dashboard</a>";
    } else {
        echo "❌ Error: " . $stmt3->error;
    }
    $stmt3->close();
}
$conn->close();
?>
