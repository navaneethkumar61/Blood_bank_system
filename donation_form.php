<?php
session_start();

// Make sure user is logged in
if (!isset($_SESSION['username'])) {
    die("You must be logged in.");
}

$username = $_SESSION['username'];

// DB config
$host = "localhost";
$dbname = "sepm";
$dbuser = "root";
$dbpass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Get blood type, gender, and date of birth from `donor_profile` table
$stmt = $pdo->prepare("SELECT blood_group, gender, dob FROM donor_profile WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User profile not found.");
}

$gender = $user['gender'];
$dob = $user['dob'];

// Calculate age from date of birth
$dob_date = new DateTime($dob);
$today = new DateTime();
$age = $today->diff($dob_date)->y;

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $blood_type = $_POST['blood_type'];
    $donation_date = $_POST['donation_date'];
    $quantity = $_POST['quantity'];
    $contact = $_POST['contact'];
    $disease = $_POST['disease'];

    $stmt = $pdo->prepare("INSERT INTO donations (username, blood_type, gender, donation_date, quantity, contact, age, disease,status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $blood_type, $gender, $donation_date, $quantity, $contact, $age, $disease,'pending']);


    
    echo "<h2 style='text-align:center;color:green;'>Thanks, $username! Your donation has been recorded.</h2>";
    echo "<p style='text-align:center;'>Redirecting to your dashboard...(Click this)</p>";
    header("refresh:3;url=donor_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Blood Donation Form</title>
  <style>
    body {
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #1f1c2c, #928dab);
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
}

.back-button {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 16px;
    color: #fff;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 15px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
  }

  .back-button i {
    margin-right: 8px;
  }

  .back-button:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-2px);
  }
  
.form-container {
  background: rgba(255, 255, 255, 0.08);
  padding: 40px;
  border-radius: 20px;
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
  width: 450px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  transition: all 0.3s ease-in-out;
}

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #f0f0f0;
  font-size: 24px;
  text-shadow: 1px 1px 2px #000;
}

form {
  display: flex;
  flex-direction: column;
}

label {
  margin-top: 12px;
  font-weight: bold;
}

input {
  padding: 12px;
  margin-top: 6px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 15px;
  transition: 0.3s;
  outline: none;
}

input::placeholder {
  color: #ccc;
}

input:focus {
  background-color: rgba(255, 255, 255, 0.25);
  box-shadow: 0 0 5px #ffffff88;
}

input[readonly] {
  cursor: not-allowed;
  background-color: rgba(255, 255, 255, 0.12);
  color: #ccc;
  font-style: italic;
}

input[type="submit"] {
  background: linear-gradient(135deg, #ff416c, #ff4b2b);
  border: none;
  margin-top: 25px;
  padding: 12px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: bold;
  color: white;
  font-size: 16px;
  letter-spacing: 1px;
  transition: all 0.3s ease-in-out;
  box-shadow: 0 4px 15px rgba(255, 75, 43, 0.5);
}

input[type="submit"]:hover {
  background: linear-gradient(135deg, #ff4b2b, #ff416c);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 75, 43, 0.6);
}

select {
  padding: 12px;
  margin-top: 6px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 15px;
  transition: 0.3s;
  outline: none;
  appearance: none;
}

select:focus {
  background-color: rgba(255, 255, 255, 0.25);
  box-shadow: 0 0 5px #ffffff88;
}

select option {
  background-color: #1f1c2c;
  color: #fff;
}

    
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Blood Donation Form</h2>
    <form method="POST">
      <label>Username:</label>
      <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" readonly>

      <label>Gender:</label>
      <input type="text" name="gender" value="<?= htmlspecialchars($gender) ?>" readonly>

      <label>Blood Type:</label>
<select name="blood_type" required>
  <option value="" disabled selected>Select your blood type</option>
  <option value="A+">A+</option>
  <option value="A-">A-</option>
  <option value="B+">B+</option>
  <option value="B-">B-</option>
  <option value="O+">O+</option>
  <option value="O-">O-</option>
  <option value="AB+">AB+</option>
  <option value="AB-">AB-</option>
</select>



      <label>Donor Age:</label>
      <input type="text" name="age" value="<?= htmlspecialchars($age) ?>" readonly>

      <label>Disease (if any):</label>
      <input type="text" name="disease" placeholder="Type 'Nothing' if healthy" required>

      <label>Donation Date:</label>
      <input type="date" name="donation_date" required>

      <label>Quantity (units):</label>
      <input type="number" name="quantity" step="0.1" required>

      <label>Contact Number:</label>
      <input type="tel" name="contact" required>

      <input type="submit" value="Submit Donation">
    </form>
  </div>
</body>
</html>
