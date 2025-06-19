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

// Fetch existing profile details
$stmt2 = $conn->prepare("SELECT full_name, phone, gender, dob, address, city, state, pincode, last_donation_date, is_available FROM donor_profile WHERE username = ?");
$stmt2->bind_param("s", $username);
$stmt2->execute();
$stmt2->bind_result($full_name, $phone, $gender, $dob, $address, $city, $state, $pincode, $last_donation_date, $is_available);
$stmt2->fetch();
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Donor Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #e65a61, #833ab4);
      color: white;
      padding: 30px;
    }
    .container {
      max-width: 600px;
      margin: auto;
      background: rgba(255,255,255,0.1);
      padding: 20px;
      border-radius: 10px;
      backdrop-filter: blur(10px);
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin: 6px 0;
      border: none;
      border-radius: 8px;
      background-color: rgba(255,255,255,0.2);
      color: white;
    }
    input[type="submit"] {
      background-color: #fff;
      color: #333;
      font-weight: bold;
      cursor: pointer;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      margin-top: 10px;
      display: block;
    }
    .icon-container {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .icon-container a {
      color: #fff;
      font-size: 20px;
      text-decoration: none;
    }
  </style>
</head>
<body>
<div class="icon-container">
    <a href="donor_dashboard.php"><i class="fas fa-home"></i></a>
  </div>
  <div class="container">
    <h2>Edit Donor Profile</h2>
    <form action="donor_profile1.php" method="POST">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>

      <label>Full Name</label>
      <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

      <label>Gender</label>
      <select name="gender" required>
        <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
        <option value="Other" <?php if ($gender == 'Other') echo 'selected'; ?>>Other</option>
      </select>

      <label>Date of Birth</label>
      <input type="date" name="dob" value="<?php echo $dob; ?>" required>

      <label>Blood Group</label>
      <input type="text" name="blood_group" value="<?php echo htmlspecialchars($blood_group); ?>" readonly>

      <label>Address</label>
      <textarea name="address" required><?php echo htmlspecialchars($address); ?></textarea>

      <label>City</label>
      <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" required>

      <label>State</label>
      <input type="text" name="state" value="<?php echo htmlspecialchars($state); ?>" required>

      <label>Pincode</label>
      <input type="text" name="pincode" value="<?php echo htmlspecialchars($pincode); ?>" required>

      <label>Last Donation Date</label>
      <input type="date" name="last_donation_date" value="<?php echo $last_donation_date; ?>">

      <label>Available to Donate?</label>
      <select name="is_available">
        <option value="1" <?php if ($is_available == 1) echo 'selected'; ?>>Yes</option>
        <option value="0" <?php if ($is_available == 0) echo 'selected'; ?>>No</option>
      </select>

      <input type="submit" value="Update Profile">
    </form>
  </div>
</body>
</html>
