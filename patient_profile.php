<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$password = ''; // Your DB password
$database = 'sepm';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$username = '';

// Check if user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    
    // Prepare and execute query to get patient details if they exist
    $stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $patient_data = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Patient Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
      padding: 20px;
    }

    .container {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      padding: 30px;
      width: 100%;
      max-width: 800px;
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 32px;
      color: #b2fef7;
    }

    h2 {
      color: #b2fef7;
      margin-bottom: 20px;
      font-size: 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      padding-bottom: 10px;
    }

    .form-section {
      margin-bottom: 20px;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
    }

    .form-group-half {
      flex: 1;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      outline: none;
      background-color: rgba(255, 255, 255, 0.9);
      color: #000;
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      font-family: 'Poppins', sans-serif;
    }

    input::placeholder, 
    textarea::placeholder {
      color: #666;
      opacity: 1;
    }

    select {
      appearance: none;
      color: #000;
    }

    select option {
      background-color: white;
      color: #000;
      padding: 10px;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    input[type="submit"] {
      background-color: #00bfa6;
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      padding: 12px;
      font-size: 16px;
      margin-top: 20px;
    }

    input[type="submit"]:hover {
      background-color: #00a090;
    }

    input[readonly] {
      background-color: rgba(255, 255, 255, 0.3);
      cursor: not-allowed;
    }

    @media screen and (max-width: 600px) {
      .container {
        padding: 20px;
      }
    }

    .back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      padding: 10px 20px;
      background-color: rgba(255, 255, 255, 0.1);
      border: none;
      border-radius: 8px;
      color: white;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }

    .back-button:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .required::after {
      content: " *";
      color: #ff6b6b;
    }
  </style>
</head>
<body>
  <a href="patient_dashboard.php" class="back-button">← Back to Dashboard</a>
  <div class="container">
    <h1>Patient Profile</h1>
    <form action="patient_profile1.php" method="POST">
      <!-- Personal Information Section -->
      <div class="form-section">
        <h2>Personal Information</h2>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">Full Name</label>
            <input type="text" name="full_name" required placeholder="Enter patients full name"
                   value="<?php echo isset($patient_data['full_name']) ? htmlspecialchars($patient_data['full_name']) : ''; ?>">
          </div>
          <div class="form-group-half">
            <label class="required">Date of Birth</label>
            <input type="date" name="dob" required
                   value="<?php echo isset($patient_data['dob']) ? htmlspecialchars($patient_data['dob']) : ''; ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">Gender</label>
            <select name="gender" required>
              <option value="">Select Gender</option>
              <option value="Male" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo (isset($patient_data['gender']) && $patient_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>
          <div class="form-group-half">
            <label>Social Security Number/Aadhar number(Indian)</label>
            <input type="text" name="ssn" placeholder="If applicable" 
                   value="<?php echo isset($patient_data['ssn']) ? htmlspecialchars($patient_data['ssn']) : ''; ?>">
          </div>
        </div>
      </div>

      <!-- Contact Information Section -->
      <div class="form-section">
        <h2>Contact Information</h2>
        <div class="form-group">
          <label class="required">Street Address</label>
          <input type="text" name="street_address" required
                 value="<?php echo isset($patient_data['street_address']) ? htmlspecialchars($patient_data['street_address']) : ''; ?>">
        </div>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">City</label>
            <input type="text" name="city" required
                   value="<?php echo isset($patient_data['city']) ? htmlspecialchars($patient_data['city']) : ''; ?>">
          </div>
          <div class="form-group-half">
            <label class="required">State</label>
            <input type="text" name="state" required
                   value="<?php echo isset($patient_data['state']) ? htmlspecialchars($patient_data['state']) : ''; ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">Zip Code</label>
            <input type="text" name="zip_code" required
                   value="<?php echo isset($patient_data['zip_code']) ? htmlspecialchars($patient_data['zip_code']) : ''; ?>">
          </div>
          <div class="form-group-half">
            <label class="required">Phone Number</label>
            <input type="tel" name="phone" required
                   value="<?php echo isset($patient_data['phone']) ? htmlspecialchars($patient_data['phone']) : ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="required">Email Address</label>
          <input type="email" name="email" required
                 value="<?php echo isset($patient_data['email']) ? htmlspecialchars($patient_data['email']) : ''; ?>">
        </div>
      </div>

      <!-- Medical History Section -->
      <div class="form-section">
        <h2>Medical History</h2>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">Primary Care Physician</label>
            <input type="text" name="physician_name" required
                   value="<?php echo isset($patient_data['physician_name']) ? htmlspecialchars($patient_data['physician_name']) : ''; ?>">
          </div>
          <div class="form-group-half">
            <label class="required">Physician's Contact</label>
            <input type="tel" name="physician_contact" required
                   value="<?php echo isset($patient_data['physician_contact']) ? htmlspecialchars($patient_data['physician_contact']) : ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Current Medications</label>
          <textarea name="current_medications" placeholder="List all current medications and dosages"><?php echo isset($patient_data['current_medications']) ? htmlspecialchars($patient_data['current_medications']) : ''; ?></textarea>
        </div>
        <div class="form-group">
          <label>Allergies</label>
          <textarea name="allergies" placeholder="List any known allergies"><?php echo isset($patient_data['allergies']) ? htmlspecialchars($patient_data['allergies']) : ''; ?></textarea>
        </div>
        <div class="form-group">
          <label>Past Medical Conditions</label>
          <textarea name="medical_conditions" placeholder="List any past or current medical conditions"><?php echo isset($patient_data['medical_conditions']) ? htmlspecialchars($patient_data['medical_conditions']) : ''; ?></textarea>
        </div>
        <div class="form-group">
          <label>Surgical History</label>
          <textarea name="surgical_history" placeholder="List any past surgeries with dates"><?php echo isset($patient_data['surgical_history']) ? htmlspecialchars($patient_data['surgical_history']) : ''; ?></textarea>
        </div>
      </div>

      <!-- Emergency Contact Section -->
      <div class="form-section">
        <h2>Emergency Contact</h2>
        <div class="form-row">
          <div class="form-group-half">
            <label class="required">Contact Name</label>
            <input type="text" name="emergency_contact_name" required
                   value="<?php echo isset($patient_data['emergency_contact_name']) ? htmlspecialchars($patient_data['emergency_contact_name']) : ''; ?>">
          </div>
          <div class="form-group-half">
            <label class="required">Relationship</label>
            <input type="text" name="emergency_contact_relationship" required
                   value="<?php echo isset($patient_data['emergency_contact_relationship']) ? htmlspecialchars($patient_data['emergency_contact_relationship']) : ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="required">Emergency Contact Phone</label>
          <input type="tel" name="emergency_contact_phone" required
                 value="<?php echo isset($patient_data['emergency_contact_phone']) ? htmlspecialchars($patient_data['emergency_contact_phone']) : ''; ?>">
        </div>
      </div>

      <div class="form-section">
        <input type="submit" value="Update Profile">
      </div>
    </form>
  </div>
</body>
</html> 