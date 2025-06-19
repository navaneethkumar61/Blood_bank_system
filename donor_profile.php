<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "sepm");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = '';
$blood_group = '';
$profile_exists = false;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Get blood group from donors table
    $stmt = $conn->prepare("SELECT blood_type FROM donors WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($blood_group);
    $stmt->fetch();
    $stmt->close();

    // Check if profile already exists
    $check = $conn->prepare("SELECT * FROM donor_profile WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();
    $profile_exists = $result->num_rows > 0;
    $check->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Donor Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 30px;
      backdrop-filter: blur(12px);
      max-width: 600px;
      width: 100%;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
    }

    .container:hover {
      transform: scale(1.01);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 28px;
      letter-spacing: 1px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      font-size: 14px;
      transition: all 0.3s ease;
      outline: none;
    }

    input::placeholder, textarea::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    input:focus, textarea:focus, select:focus {
      border-color: #66fcf1;
      background: rgba(255, 255, 255, 0.25);
    }

    input[readonly] {
      background-color: rgba(255, 255, 255, 0.25);
      cursor: not-allowed;
    }

    input[type="submit"] {
      background: rgba(255, 255, 255, 0.3);
      border: none;
      cursor: pointer;
      font-weight: bold;
      color: white;
      transition: background 0.3s ease, color 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    input[type="submit"]:hover {
      background: #66fcf1;
      color: #0f2027;
    }

    .icon-container {
      position: absolute;
      top: 20px;
      left: 20px;
    }

    .icon-container a {
      color: #fff;
      font-size: 22px;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .icon-container a:hover {
      color: #66fcf1;
    }
  </style>
</head>
<body>
<div class="icon-container">
    <a href="donor_dashboard.php"><i class="fas fa-home"></i></a>
  </div>
  <div class="container">
    <h2><?php echo $profile_exists ? 'Edit Donor Profile' : 'Donor Registration'; ?></h2>
    <form action="donor_profile1.php" method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
      </div>
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" required>
      </div>
      <div class="form-group">
        <label>Gender</label>
        <select name="gender">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label>Date of Birth</label>
        <input type="date" name="dob" required>
      </div>
      <div class="form-group">
        <label>Blood Group</label>
        <input type="text" name="blood_group" value="<?php echo htmlspecialchars($blood_group); ?>" readonly>
      </div>
      <div class="form-group">
        <label>Address</label>
        <textarea name="address" rows="3" required></textarea>
      </div>
      <div class="form-group">
        <label>City</label>
        <input type="text" name="city" required>
      </div>
      <div class="form-group">
        <label>State</label>
        <input type="text" name="state" required>
      </div>
      <div class="form-group">
        <label>Pincode</label>
        <input type="text" name="pincode" required>
      </div>
      <div class="form-group">
        <label>Last Donation Date</label>
        <input type="date" name="last_donation_date">
      </div>
      <div class="form-group">
        <label>Available to Donate?</label>
        <select name="is_available">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <input type="submit" value="<?php echo $profile_exists ? 'Update Profile' : 'Register Profile'; ?>">
      </div>
    </form>
  </div>
</body>
</html>
